<?php

// -------
define("ADMIN_MODE", true);

// -------
require_once("engine/core/globals.php");
require_once("engine/core/configuration.php");
require_once("engine/core/setup.php");
require_once("engine/core/functions.php");
require_once("engine/modules/MySQLi/mysqli.class.php");
require_once("engine/modules/Template/xtemplate.class.php");

// -------
$DB	= new TPDConnector($CONF['db_host'], $CONF['db_user'], $CONF['db_password'], $CONF['db_name']);
$API = new TAdmin($CONF, $DB);
$module = ($_module = get_or_post('module')) ? strtolower(trim($_module)) : null;

// -------
require_once('engine/core/all_mods.php');
require_once("engine/panel/panel.php");