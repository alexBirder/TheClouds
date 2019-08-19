<?php

define("ADMIN_MODE", false);
ini_set("display_errors", 0);

require_once("engine/core/globals.php");
require_once("engine/core/configuration.php");
require_once("engine/core/setup.php");
require_once("engine/core/functions.php");
require_once("engine/core/overload.php");
require_once("engine/core/redirects.php");
require_once("engine/modules/MySQLi/mysqli.class.php");
require_once("engine/modules/PHPMailer/class.phpmailer.php");
require_once("engine/modules/Template/xtemplate.class.php");

if (array_key_exists($_SERVER['REQUEST_URI'], $redirects)) $redirect = "/{$redirects[$_SERVER['REQUEST_URI']]}"; redirect($redirect);
$get = explode('/', $_SERVER['REQUEST_URI']);
$lang = ($_lang = $get[1]) && isset($CONF['langs'][$_lang]) ? strtolower(trim($_lang)) : key($CONF['langs']);
$media = ($_media = get_or_post('media')) ? strtolower(trim($_media)) : 'normal';
$module = ($_module = get_or_post('module')) ? strtolower(trim($_module)) : null;
$mod_rewrite = isset($_GET['mod_rewrite']) ? preg_split('#\/#', $_GET['mod_rewrite'], -1, PREG_SPLIT_NO_EMPTY) : array();

$DB	 = new TPDConnector($CONF['db_host'], $CONF['db_user'], $CONF['db_password'], $CONF['db_name']);
$TPL = new XTemplate(template_file($CONF['template_dir'], 'global_base.tpl', $lang));

$THUMBS = new TThumbnail();
$THUMBS->out($_SERVER['QUERY_STRING']);

// MAIN BLOCK BEGIN ------------------------------------------------------------

try {
    if(project_status() == 'n' || project_status() == null) require_once("engine/panel/login.php");
	$tpl_data = array();
	require_once(ROOT . '/engine/core/all_mods.php');
	$MOD = FindMainModule();
	if(!empty($MOD) && is_object($MOD) && $media != 'ajax'){
		$tpl_data['DOC_TITLE']			= $MOD->get_doctitle();
		$tpl_data['DESCRIPTION']		= $MOD->get_description();
		$tpl_data['KEYWORDS'] 			= $MOD->get_keywords();
		$tpl_data['BREAD_CRUMBS'] 	    = sprintf('<a href="/">%s</a> › %s', project_bread(), $MOD->get_navigationstring_core());
		$tpl_data['PAGE_TITLE'] 		= $MOD->get_pagetitle();
	} elseif($media != 'ajax') {
		$tpl_data['DOC_TITLE']			= project_title($lang);
		$tpl_data['DESCRIPTION']		= project_description($lang);
		$tpl_data['KEYWORDS']			= project_keywords($lang);
		$tpl_data['BREAD_CRUMBS'] 	    = sprintf('<a>%s</a>', project_title($lang));
		$tpl_data['PAGE_TITLE'] 		= project_title($lang);
	}
	if($media == 'normal') {
        $tpl_file = template_file($CONF['template_dir'], (is_object($MOD) ? $MOD->get_template() : $CONF['main_tpl']), $lang);
		$TPL->assign_file('CURRENT_TEMPLATE', $tpl_file);
        if($MOD) $tpl_data['MAIN'] = 'class="inside"';
        $tpl_data['JAVASCRIPT_DIR'] 	= $CONF['javascript_dir'];
		$tpl_data['STYLES_DIR'] 		= $CONF['styles_dir'];
		$tpl_data['IMAGES_DIR'] 		= $CONF['images_dir'];
		$tpl_data['SOCIALS']            = socials_class();
        $tpl_data['SCRIPTS']            = project_scripts();
        $tpl_data['FAVICON']            = project_favicon();
		ExecuteAllModules();
	} elseif($media == 'ajax') {
		unset($TPL); if(is_object($MOD)) $MOD->process();
	} else {
		die('Unknown media type.');
	}
} catch(Exception $e) {
	if($media == 'normal') {
        $tpl_file = template_file($CONF['template_dir'], $CONF['main_tpl'], $lang);
		$TPL->assign_file('CURRENT_TEMPLATE', $tpl_file);
		$tpl_data['JAVASCRIPT_DIR'] 	= $CONF['javascript_dir'];
		$tpl_data['STYLES_DIR'] 		= $CONF['styles_dir'];
		$tpl_data['IMAGES_DIR'] 		= $CONF['images_dir'];
        $tpl_data['SOCIALS']            = socials_class();
        $tpl_data['SCRIPTS']            = project_scripts();
        $tpl_data['FAVICON']            = project_favicon();
		$tpl_data['DOC_TITLE']			= project_title($lang);
		$tpl_data['DESCRIPTION']		= project_description($lang);
		$tpl_data['KEYWORDS']			= project_keywords($lang);
		$tpl_data['BREAD_CRUMBS'] 	    = sprintf('<a>%s</a>', project_title($lang));
		$tpl_data['PAGE_TITLE'] 		= project_title($lang);
	}
	$CoreException = new TException($CONF, $DB, $TPL);
}

// ECHO BEGIN ------------------------------------------------------------------

if(preg_match('/normal|print/ui', $media)) {
	$TPL->assign($tpl_data);
	if($media == 'normal' && is_object($MOD)){
		if(mb_strlen($tpl_data['BREAD_CRUMBS'])){
			$TPL->parse($CONF['base_tpl'] . '.bread_crumbs');
		} try {
			$MOD->process();
		} catch(Exception $e){
			$CoreException = new TException($CONF, $DB, $TPL);
		}
	}
	if(isset($CoreException) && is_object($CoreException) == true){
		$CoreException->process($e);
	}
	if(change_template() == 'y'){
		$_all_langs = array_keys($CONF['langs']);
		$_regular = sprintf('/^\/(%s)/i', join('|', $_all_langs));
		foreach($CONF['langs'] as $_lang => $_language){
		    if(strlen($_SERVER['REQUEST_URI']) > 1 && preg_match($_regular, $_SERVER['REQUEST_URI'])){
		        $_lang_link = preg_replace($_regular, $_lang, $_SERVER['REQUEST_URI']);
            } elseif(strlen($_SERVER['REQUEST_URI']) <= 1) {
                $_lang_link = $_lang;
            } else {
		        $_lang_link = $_lang . $_SERVER['REQUEST_URI'];
            }
		    $TPL->assign('LANG_' . strtoupper($_lang), '/' . $_lang_link);
		}
	}
    $TPL->assign("LANG", $lang);
	$TPL->parse($CONF['base_tpl']);
	$HTML = change_minify() == 'y' ? TMinify::minify($TPL->text($CONF['base_tpl'])) : $TPL->text($CONF['base_tpl']);
    change_gzip() == 'y' ? TGzip::out($HTML) : print($HTML);

} elseif($media == 'ajax') {
    header("Content-type: text/plain");
    echo TAjaxer::get();
}

// ECHO END --------------------------------------------------------------------

if(isset($tm) && is_object($tm)) $tm->end();
if(session_id()) session_write_close();
$DB->close();