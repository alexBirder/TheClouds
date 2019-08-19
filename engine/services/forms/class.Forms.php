<?php

class TForms extends TCore {
    protected $module_path = '/engine/services/forms';
    protected $module_id = 'forms';
    protected $module_name = 'Формы и обратная связь';

    private $action;

    public function __construct($MID, $CONF, $DB, $TPL){
        global $mod_rewrite;
        parent::__construct($MID, $CONF, $DB, $TPL);

        $this->action = sizeof($mod_rewrite) > 0 ? $mod_rewrite[0] : get_or_post('action');

        $this->load_plugins($this, 'forms');
    }

    //-- PUBLIC ----------------------------------------------------------------

    public function process(){
        $this->form_generate();

        switch(strtolower($this->action)){
            case 'send':
                $this->form_send();
                break;
        }
    }

    public function execute(){
        $this->form_generate();
    }

    //-- PRIVATE ---------------------------------------------------------------

    public function form_generate(){
        $forms = $this->DB->sql2array("SELECT * FROM forms_items");
        foreach($forms as $form){
            $template = $this->template_file("/templates/html/modules", "module_forms.tpl", $this->lang);
            $this->TPL->assign_file('FORM_'.$form['id'].'', $template);

            $data = array('id' => $form['id'], 'title' => $this->call_module('settings', 'get_word', $form['title']));

            $areas = $this->DB->sql2array("SELECT i.*, a.`title`, a.`name`, a.`value`, a.`type`, a.`required`, a.`enabled` FROM forms_attached AS i LEFT JOIN forms_areas AS a ON a.`id` = i.`area` WHERE i.`form` = {$data['id']}");
            foreach ($areas as $area){
                $data_areas = array(
                    'id' => $area['id'],
                    'title' => $this->call_module('settings', 'get_word', $area['title']),
                    'name' => $area['name'],
                    'value' => $area['value'],
                    'type' => $area['type'],
                    'required' => $area['required'] ? 'required' : '',
                    'enabled' => $area['enabled'],
                    'policy' => 'Я соглашаюсь на обработку моих персональных данных',
                );
                $this->TPL->assign(array('AREA' => $data_areas));

                if($data_areas['type'] == "area") {
                    $this->TPL->parse($this->CONF['base_tpl'] . '.form_'.$form['id'].'.area_textarea');
                } else {
                    $this->TPL->parse($this->CONF['base_tpl'] . '.form_'.$form['id'].'.area_input');
                }
            }

            if(policy_confidence() == 'y'){
                $this->TPL->parse($this->CONF['base_tpl'] . '.form_'.$form['id'].'.area_policy');
            }

            $this->TPL->assign(array('FORM' => $data));
            $this->TPL->parse($this->CONF['base_tpl'] . '.form_'.$form['id'].'');
        }
    }

    private function form_send(){
        $Mailer = new PHPMailer();
        $body = array();

        foreach ($_POST['form'] as $key => $val) {
            $body[] = sprintf("%s: %s", stripslashes($key), stripslashes($val));
        }

        $Mailer->CharSet = 'utf-8';
        $Mailer->SetFrom(project_reply(), project_name());
        $Mailer->AddReplyTo(project_reply(), project_name());
        $Mailer->AddAddress(project_email(), project_name());
        $Mailer->Subject = $_POST['form_name'];
        $Mailer->Body = join("\n", $body);

        if($Mailer->Send()){
            $return = true;
            $result = 'Форма успешно отправлена';
            die(json_encode(array('return' => $return, 'result' => $result)));
        }

    }

    //-- CORE ------------------------------------------------------------------

    public function get_doctitle(){
        return $this->module_name;
    }

    public function get_pagetitle(){
        return $this->module_name;
    }

    public function get_description(){
        return project_description($this->lang);
    }

    public function get_keywords(){
        return project_keywords($this->lang);
    }

    public function get_navigationstring(){
        return null;
    }

    public function get_template(){
        return 'global_default.tpl';
    }

    public function is_main(){
        global $module;
        return strcmp($module, $this->module_id) == 0;
    }

    public function is_active(){
        return true;
    }

}

?>