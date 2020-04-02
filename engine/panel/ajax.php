<?php

ob_start();

if($module){
    $servicePtr = null;
    foreach($ALL_MODS as $k => $v){
        if($ALL_MODS[$k]->module_id == $module){
            $ALL_MODS[$k]->execute();
            $ob_contents = ob_get_contents();
            break;
        }
    }
}

ob_end_clean();

header("Content-type: text/plain");
echo TAjaxer::get();

$DB->close();