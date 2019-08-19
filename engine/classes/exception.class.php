<?php

class TException extends TCore{
	public function __construct($CONF, $DB, $TPL){
		$this->CONF	= $CONF;
		$this->DB	= $DB;
		$this->TPL	= $TPL;
	}

	public function __destruct(){
	}

	public function process(Exception $e){

        $template = $this->template_file("{$this->CONF['template_dir']}/errors", "404.html", $this->lang);
		$this->TPL->assign_file('WORK_PART', $template);

		$data = array('code' => $e->getCode(), 'message' => $e->getMessage());
		$this->TPL->assign(array('CMS_ERROR' => $data));

		$this->TPL->parse($this->CONF['base_tpl'] . '.work_part');
	}
}

?>