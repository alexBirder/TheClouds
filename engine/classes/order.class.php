<?php

class TOrder {
    private $STACK;

    public function __construct(){
        if(is_array($this->STACK = TSession::get(BASKET_ID)) == false){
            $this->clear();
        }
    }

    public function __destruct(){
        TSession::set(BASKET_ID, $this->STACK);
    }

    public function add($id, $price, $count = 1, $item_data = array()){
        $data = array('price' => $price, 'item_data' => $item_data);
        for($i = 0; $i < $count; $i++) $this->store($id, $data);
    }

    public function delete($id){
        if(isset($this->STACK['items'][$id])){
            unset($this->STACK['items'][$id]);
            $this->recalculate();
        }
    }

    public function clear(){
        $this->STACK = array('number' => null, 'items' => array(), 'total' => 0, 'sum' => 0);
    }

    public function set_number($num){
        $this->STACK['number'] = (int)$num;
    }

    public function get_number(){
        return (int)$this->STACK['number'];
    }

    public function get_total($format = false){
        return $format ? word4num($this->STACK['total'], array('?????', '??????', '???????')) : (int)$this->STACK['total'];
    }

    public function get_sum($format = true){
        return $format ? number_format($this->STACK['sum'], 2, ',', ' ') : (float)$this->STACK['sum'];
    }

    public function get_items(){
        return $this->STACK['items'];
    }

    public function get_items_multiply(){
        $items = $this->STACK['items'];
        foreach($items as $k => $v){
            $items[$k]['amount'] = $v['price'] * $v['count'];
        }
        return $items;
    }

    public function multipy_item($id){
        return isset($this->STACK['items'][$id]) ? ($this->STACK['items'][$id]['price'] * $this->STACK['items'][$id]['count']) : null;
    }

    // -------------------------------------------------------------------------

    private function store($id, $data){
        if(empty($this->STACK['items'][$id])){
            $this->STACK['items'][$id] = $data;
            $this->STACK['items'][$id]['count'] = 1;
            //$this->STACK['items'][$id]['size'] = $size;
        }
        else{
            $this->STACK['items'][$id]['count']++;
            //$this->STACK['items'][$id]['size'] = $size;
        }
        $this->STACK['sum'] += $data['price'];
        //$this->STACK['size'] = $size;
        $this->STACK['total'] += 1;
    }

    public function refresh($id, $count){
        if(isset($this->STACK['items'][$id])){
            if($count > 0){
                $this->STACK['items'][$id]['count'] = $count;
                $this->recalculate();
            }
            else{
                $this->delete($id);
            }
        }
    }

    private function recalculate(){
        $this->STACK['total'] = $this->STACK['sum'] = 0;
        foreach($this->STACK['items'] as $item){
            $this->STACK['total'] += $item['count'];
            $this->STACK['sum'] += $item['count'] * $item['price'];
        }
    }
}

?>