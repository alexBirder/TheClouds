<?php

interface IGeneric{
	public function __construct($obj, $CONF, $DB, $MID = null, $TPL = null);
	public function __destruct();
	public function process();
	public function execute();
	public function executable();
}

?>