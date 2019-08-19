<?php

class TNova {
    protected $key;
    protected $throwErrors = false;
    protected $format = 'array';
    protected $language = 'ru';
    protected $connectionType = 'curl';
    protected $areas;
    protected $model = 'Common';
    protected $method;
    protected $params;

    public function __construct($key, $language = 'ru', $throwErrors = false, $connectionType = 'curl'){
        $this->throwErrors = $throwErrors;
        return $this
            ->setKey($key)
            ->setLanguage($language)
            ->setConnectionType($connectionType)
            ->model('Common');
    }

    public function setKey($key){
        $this->key = $key;
        return $this;
    }

    public function getKey(){
        return $this->key;
    }

    public function setConnectionType($connectionType){
        $this->connectionType = $connectionType;
        return $this;
    }

    public function getConnectionType(){
        return $this->connectionType;
    }

    public function setLanguage($language){
        $this->language = $language;
        return $this;
    }

    public function getLanguage(){
        return $this->language;
    }

    public function setFormat($format){
        $this->format = $format;
        return $this;
    }

    public function getFormat(){
        return $this->format;
    }

    private function prepare($data){
        //Returns array
        if ('array' == $this->format) {
            $result = is_array($data)
                ? $data
                : json_decode($data, 1);
            // If error exists, throw Exception
            if ($this->throwErrors and $result['errors']) {
                throw new \Exception(is_array($result['errors']) ? implode("\n", $result['errors']) : $result['errors']);
            }
            return $result;
        }
        // Returns json or xml document
        return $data;
    }

    private function array2xml(array $array, $xml = false){
        (false === $xml) and $xml = new \SimpleXMLElement('<root/>');
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }
            if (is_array($value)) {
                $this->array2xml($value, $xml->addChild($key));
            } else {
                $xml->addChild($key, $value);
            }
        }
        return $xml->asXML();
    }

    private function request($model, $method, $params = null){
        // Get required URL
        $url = 'xml' == $this->format
            ? 'https://api.novaposhta.ua/v2.0/xml/'
            : 'https://api.novaposhta.ua/v2.0/json/';

        $data = array(
            'apiKey' => $this->key,
            'modelName' => $model,
            'calledMethod' => $method,
            'language' => $this->language,
            'methodProperties' => $params,
        );
        // Convert data to neccessary format
        $post = 'xml' == $this->format
            ? $this->array2xml($data)
            : $post = json_encode($data);

        if ('curl' == $this->getConnectionType()) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.('xml' == $this->format ? 'text/xml' : 'application/json')));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $result = file_get_contents($url, null, stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded;\r\n",
                    'content' => $post,
                ),
            )));
        }

        return $this->prepare($result);
    }

    public function model($model = ''){
        if (!$model) {
            return $this->model;
        }

        $this->model = $model;
        $this->method = null;
        $this->params = null;
        return $this;
    }

    public function method($method = ''){
        if (!$method) {
            return $this->method;
        }

        $this->method = $method;
        $this->params = null;
        return $this;
    }

    public function params($params){
        $this->params = $params;
        return $this;
    }

    public function execute(){
        return $this->request($this->model, $this->method, $this->params);
    }

    public function documentsTracking($track){
        $params = array('Documents' => array(array('DocumentNumber' => $track)));

        return $this->request('TrackingDocument', 'getStatusDocuments', $params);
    }

    public function getCities($page = 0, $findByString = '', $ref = ''){
        return $this->request('Address', 'getCities', array(
            'Page' => $page,
            'FindByString' => $findByString,
            'Ref' => $ref,
        ));
    }

    public function getWarehouses($cityRef, $page = 0){
        return $this->request('Address', 'getWarehouses', array(
            'CityRef' => $cityRef,
            'Page' => $page,
        ));
    }

    public function findNearestWarehouse($searchStringArray){
        $searchStringArray = (array) $searchStringArray;
        return $this->request('Address', 'findNearestWarehouse', array(
            'SearchStringArray' => $searchStringArray,
        ));
    }

    public function getWarehouse($cityRef, $description = ''){
        $warehouses = $this->getWarehouses($cityRef);
        $data = array();
        if (is_array($warehouses['data'])) {
            if (1 === count($warehouses['data']) or !$description) {
                $data = $warehouses['data'][0];
            } elseif (count($warehouses['data']) > 1) {
                foreach ($warehouses['data'] as $warehouse) {
                    if (false !== mb_stripos($warehouse['Description'], $description)
                        or false !== mb_stripos($warehouse['DescriptionRu'], $description)) {
                        $data = $warehouse;
                        break;
                    }
                }
            }
        }
        // Error
        (!$data) and $error = 'Warehouse was not found';
        // Return data in same format like NovaPoshta API
        return $this->prepare(
            array(
                'success' => empty($error),
                'data' => array($data),
                'errors' => (array) $error,
                'warnings' => array(),
                'info' => array(),
            )
        );
    }

    public function getStreet($cityRef, $findByString = '', $page = 0){
        return $this->request('Address', 'getStreet', array(
            'FindByString' => $findByString,
            'CityRef' => $cityRef,
            'Page' => $page,
        ));
    }

    protected function findArea(array $areas, $findByString = '', $ref = ''){
        $data = array();
        if (!$findByString and !$ref) {
            return $data;
        }
        // Try to find current region
        foreach ($areas as $key => $area) {
            // Is current area found by string or by key
            $found = $findByString
                ? ((false !== mb_stripos($area['Description'], $findByString))
                    or (false !== mb_stripos($area['DescriptionRu'], $findByString))
                    or (false !== mb_stripos($area['Area'], $findByString))
                    or (false !== mb_stripos($area['AreaRu'], $findByString)))
                : ($key == $ref);
            if ($found) {
                $area['Ref'] = $key;
                $data[] = $area;
                break;
            }
        }
        return $data;
    }

    public function getArea($findByString = '', $ref = ''){
        // Load areas list from file
        empty($this->areas) and $this->areas = include dirname(__FILE__).'/NovaPoshtaApi2Areas.php';
        $data = $this->findArea($this->areas, $findByString, $ref);
        // Error
        empty($data) and $error = 'Area was not found';
        // Return data in same format like NovaPoshta API
        return $this->prepare(
            array(
                'success' => empty($error),
                'data' => $data,
                'errors' => (array) $error,
                'warnings' => array(),
                'info' => array(),
            )
        );
    }

    public function getAreas($ref = '', $page = 0){
        return $this->request('Address', 'getAreas', array(
            'Ref' => $ref,
            'Page' => $page,
        ));
    }

    protected function findCityByRegion($cities, $areaName){
        $data = array();
        $areaRef = '';
        // Get region id
        $area = $this->getArea($areaName);
        $area['success'] and $areaRef = $area['data'][0]['Ref'];
        if ($areaRef and is_array($cities['data'])) {
            foreach ($cities['data'] as $city) {
                if ($city['Area'] == $areaRef) {
                    $data[] = $city;
                }
            }
        }
        return $data;
    }

    public function getCity($cityName, $areaName = ''){
        // Get cities by name
        $cities = $this->getCities(0, $cityName);
        if (is_array($cities['data'])) {
            // If cities more then one, calculate current by area name
            $data = (count($cities['data']) > 1)
                ? $this->findCityByRegion($cities, $areaName)
                : $cities['data'][0];
        }
        // Error
        (!$data) and $error = 'City was not found';
        // Return data in same format like NovaPoshta API
        return $this->prepare(
            array(
                'success' => empty($error),
                'data' => array($data),
                'errors' => (array) $error,
                'warnings' => array(),
                'info' => array(),
            )
        );
    }

    public function __call($method, $arguments){
        $common_model_method = array(
            'getTypesOfCounterparties',
            'getBackwardDeliveryCargoTypes',
            'getCargoDescriptionList',
            'getCargoTypes',
            'getDocumentStatuses',
            'getOwnershipFormsList',
            'getPalletsList',
            'getPaymentForms',
            'getTimeIntervals',
            'getServiceTypes',
            'getTiresWheelsList',
            'getTraysList',
            'getTypesOfAlternativePayers',
            'getTypesOfPayers',
            'getTypesOfPayersForRedelivery',
        );
        // Call method of Common model
        if (in_array($method, $common_model_method)) {
            return $this
                ->model('Common')
                ->method($method)
                ->params(null)
                ->execute();
        }
    }

    public function delete($params){
        return $this->request($this->model, 'delete', $params);
    }

    public function update($params){
        return $this->request($this->model, 'update', $params);
    }

    public function save($params){
        return $this->request($this->model, 'save', $params);
    }

    public function getCounterparties($counterpartyProperty = 'Recipient', $page = null, $findByString = null, $cityRef = null){
        // Any param can be skipped
        $params = array();
        $params['CounterpartyProperty'] = $counterpartyProperty ? $counterpartyProperty : 'Recipient';
        $page and $params['Page'] = $page;
        $findByString and $params['FindByString'] = $findByString;
        $cityRef and $params['City'] = $cityRef;
        return $this->request('Counterparty', 'getCounterparties', $params);
    }

    public function cloneLoyaltyCounterpartySender($cityRef){
        return $this->request('Counterparty', 'cloneLoyaltyCounterpartySender', array('CityRef' => $cityRef));
    }

    public function getCounterpartyContactPersons($ref){
        return $this->request('Counterparty', 'getCounterpartyContactPersons', array('Ref' => $ref));
    }

    public function getCounterpartyAddresses($ref, $page = 0){
        return $this->request('Counterparty', 'getCounterpartyAddresses', array('Ref' => $ref, 'Page' => $page));
    }

    public function getCounterpartyOptions($ref){
        return $this->request('Counterparty', 'getCounterpartyOptions', array('Ref' => $ref));
    }

    public function getCounterpartyByEDRPOU($edrpou, $cityRef){
        return $this->request('Counterparty', 'getCounterpartyByEDRPOU', array('EDRPOU' => $edrpou, 'cityRef' => $cityRef));
    }

    public function getDocumentPrice($citySender, $cityRecipient, $serviceType, $weight, $cost){
        return $this->request('InternetDocument', 'getDocumentPrice', array(
            'CitySender' => $citySender,
            'CityRecipient' => $cityRecipient,
            'ServiceType' => $serviceType,
            'Weight' => $weight,
            'Cost' => $cost,
        ));
    }

    public function getDocumentDeliveryDate($citySender, $cityRecipient, $serviceType, $dateTime){
        return $this->request('InternetDocument', 'getDocumentDeliveryDate', array(
            'CitySender' => $citySender,
            'CityRecipient' => $cityRecipient,
            'ServiceType' => $serviceType,
            'DateTime' => $dateTime,
        ));
    }

    public function getDocumentList($params = null){
        return $this->request('InternetDocument', 'getDocumentList', $params ? $params : null);
    }

    public function getDocument($ref){
        return $this->request('InternetDocument', 'getDocument', array(
            'Ref' => $ref,
        ));
    }

    public function generateReport($params){
        return $this->request('InternetDocument', 'generateReport', $params);
    }

    protected function checkInternetDocumentRecipient(array &$counterparty){
        // Check required fields
        if (!$counterparty['FirstName']) {
            throw new \Exception('FirstName is required filed for recipient');
        }
        // MiddleName realy is not required field, but manual says otherwise
        // if ( ! $counterparty['MiddleName'])
        // throw new \Exception('MiddleName is required filed for sender and recipient');
        if (!$counterparty['LastName']) {
            throw new \Exception('LastName is required filed for recipient');
        }
        if (!$counterparty['Phone']) {
            throw new \Exception('Phone is required filed for recipient');
        }
        if (!($counterparty['City'] or $counterparty['CityRef'])) {
            throw new \Exception('City is required filed for recipient');
        }
        if (!($counterparty['Region'] or $counterparty['CityRef'])) {
            throw new \Exception('Region is required filed for recipient');
        }

        // Set defaults
        if (!$counterparty['CounterpartyType']) {
            $counterparty['CounterpartyType'] = 'PrivatePerson';
        }
    }

    protected function checkInternetDocumentParams(array &$params){
        if (!$params['Description']) {
            throw new \Exception('Description is required filed for new Internet document');
        }
        if (!$params['Weight']) {
            throw new \Exception('Weight is required filed for new Internet document');
        }
        if (!$params['Cost']) {
            throw new \Exception('Cost is required filed for new Internet document');
        }
        (!$params['DateTime']) and $params['DateTime'] = date('d.m.Y');
        (!$params['ServiceType']) and $params['ServiceType'] = 'WarehouseWarehouse';
        (!$params['PaymentMethod']) and $params['PaymentMethod'] = 'Cash';
        (!$params['PayerType']) and $params['PayerType'] = 'Recipient';
        (!$params['SeatsAmount']) and $params['SeatsAmount'] = '1';
        (!$params['CargoType']) and $params['CargoType'] = 'Cargo';
        (!$params['VolumeGeneral']) and $params['VolumeGeneral'] = '0.0004';
    }

    public function newInternetDocument($sender, $recipient, $params){
        // Check for required params and set defaults
        $this->checkInternetDocumentRecipient($recipient);
        $this->checkInternetDocumentParams($params);
        if (!$sender['CitySender']) {
            $senderCity = $this->getCity($sender['City'], $sender['Region']);
            $sender['CitySender'] = $senderCity['data'][0]['Ref'];
        }
        $sender['CityRef'] = $sender['CitySender'];
//        var_dump($sender['CityRef']);die;
        if (!$sender['SenderAddress'] and $sender['CitySender'] and $sender['Warehouse']) {
            $senderWarehouse = $this->getWarehouse($sender['CitySender'], $sender['Warehouse']);
            $sender['SenderAddress'] = $senderWarehouse['data'][0]['Ref'];
        }
        if (!$sender['Sender']) {
            $sender['CounterpartyProperty'] = 'Sender';
            $fullName = trim($sender['LastName'].' '.$sender['FirstName'].' '.$sender['MiddleName']);
            // Set full name to Description if is not set
            if (!$sender['Description']) {
                $sender['Description'] = $fullName;
            }
            // Check for existing sender
            $senderCounterpartyExisting = $this->getCounterparties('Sender', 1, $fullName, $sender['CityRef']);
            // Copy user to the selected city if user doesn't exists there
            if ($senderCounterpartyExisting['data'][0]['Ref']) {
                // Counterparty exists
                $sender['Sender'] = $senderCounterpartyExisting['data'][0]['Ref'];
                $contactSender = $this->getCounterpartyContactPersons($sender['Sender']);
                $sender['ContactSender'] = $contactSender['data'][0]['Ref'];
                $sender['SendersPhone'] = $sender['Phone'] ? $sender['Phone'] : $contactSender['data'][0]['Phones'];
            }
        }

        // Prepare recipient data
        $recipient['CounterpartyProperty'] = 'Recipient';
        $recipient['RecipientsPhone'] = $recipient['Phone'];
        if (!$recipient['CityRecipient']) {
            $recipientCity = $this->getCity($recipient['City'], $recipient['Region']);
            $recipient['CityRecipient'] = $recipientCity['data'][0]['Ref'];
        }
        $recipient['CityRef'] = $recipient['CityRecipient'];
        if (!$recipient['RecipientAddress']) {
            $recipientWarehouse = $this->getWarehouse($recipient['CityRecipient'], $recipient['Warehouse']);
            $recipient['RecipientAddress'] = $recipientWarehouse['data'][0]['Ref'];
        }
        if (!$recipient['Recipient']) {
            $recipientCounterparty = $this->model('Counterparty')->save($recipient);
            $recipient['Recipient'] = $recipientCounterparty['data'][0]['Ref'];
            $recipient['ContactRecipient'] = $recipientCounterparty['data'][0]['ContactPerson']['data'][0]['Ref'];
        }
        // Full params is merge of arrays $sender, $recipient, $params
        $paramsInternetDocument = array_merge($sender, $recipient, $params);
        // Creating new Internet Document
        return $this->model('InternetDocument')->save($paramsInternetDocument);
    }

    protected function printGetLink($method, $documentRefs, $type){
        $data = 'https://my.novaposhta.ua/orders/'.$method.'/orders[]/'.implode(',', $documentRefs)
            .'/type/'.str_replace('_link', '', $type)
            .'/apiKey/'.$this->key;
        // Return data in same format like NovaPoshta API
        return $this->prepare(
            array(
                'success' => true,
                'data' => array($data),
                'errors' => array(),
                'warnings' => array(),
                'info' => array(),
            )
        );
    }

    public function printDocument($documentRefs, $type = 'html'){
        $documentRefs = (array) $documentRefs;
        // If needs link
        if ('html_link' == $type or 'pdf_link' == $type) {
            return $this->printGetLink('printDocument', $documentRefs, $type);
        }
        // If needs data
        return $this->request('InternetDocument', 'printDocument', array('DocumentRefs' => $documentRefs, 'Type' => $type));
    }

    public function printMarkings($documentRefs, $type = 'new_html'){
        $documentRefs = (array) $documentRefs;
        // If needs link
        if ('html_link' == $type or 'pdf_link' == $type) {
            return $this->printGetLink('printMarkings', $documentRefs, $type);
        }
        // If needs data
        return $this->request('InternetDocument', 'printMarkings', array('DocumentRefs' => $documentRefs, 'Type' => $type));
    }
}
