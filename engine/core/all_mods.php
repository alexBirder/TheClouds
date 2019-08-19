<?php

foreach($MODS as $MID => $MOD){
    $_prefix = ADMIN_MODE == true ? 'admin' : 'class';
    $_mod_file = sprintf('%s%s/%s/%s.%s.php', ROOT, $CONF['services_dir'], strtolower($MOD['path']), $_prefix, ucfirst($MOD['path']));
    $_mod_name = sprintf("T%s", $MOD['path']);

    if(file_exists($_mod_file) && is_file($_mod_file)){
        require_once($_mod_file);
        if(class_exists($_mod_name)){
            if(ADMIN_MODE == true){
                $tmp_mod = new $_mod_name($CONF, $DB);
                if(preg_match("/" . services() . "/ui", $tmp_mod->module_id)){
                    $ALL_MODS[$tmp_mod->module_id] = $tmp_mod;
                }
            } else {
                $tmp_mod = new $_mod_name($MID, $CONF, $DB, $TPL);
                if(!defined(services()) || preg_match("/" . services() . "/ui", $tmp_mod->module_id)){
                    $ALL_MODS[$tmp_mod->module_id] = $tmp_mod;
                }
            }
        }
    }
}

foreach($ALL_MODS as  $i => $module_to_init){
    $module_to_init->__initialize();
}

function FindMainModule(){
    global $ALL_MODS;
    $result = null;
    foreach($ALL_MODS as $k => $mod) if($ALL_MODS[$k]->is_main()) $result = $ALL_MODS[$k];
    return $result;
}

function ExecuteAllModules(){
    global $ALL_MODS;
    foreach($ALL_MODS as $k => $mod) if($mod->is_active()) $mod->execute();
}

function Id2Mid($module_id){
    global $MODS;
    $mods = array_flip(array_map('strtolower', $MODS));
    return isset($mods[$module_id]) ? $mods[$module_id] : 0;
}