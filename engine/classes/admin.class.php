<?php

class TAdmin {
    static $MESSAGES = array(0 => array(), 1 => array());

    protected $CONF;
    protected $DB;

    protected $plugins;

    protected $actions;
    protected $action;
    protected $page_max = 25;
    protected $sort = array();


    public function __get($name){
        if(preg_match('/module_path|module_id|module_name/', $name))
            return $this->$name;
    }

    public function __construct($CONF, $DB){
        $this->CONF = $CONF;
        $this->DB = $DB;

        $this->plugins = array();
    }

    public function __destruct(){
    }

    public function ShowMessages(){
        if(count(self::$MESSAGES[0])) $this->ErrorMessage(implode(self::$MESSAGES[0], '<li>'));
        if(count(self::$MESSAGES[1])) $this->MessageBox(implode(self::$MESSAGES[1], '<li>'));
    }

    public function AddMessage($text = 'успешно выполненно'){
        if(is_array($text))
            self::$MESSAGES[1] = count(self::$MESSAGES[1]) > 0 ? array_merge(self::$MESSAGES[1], $text) : $text;
        else
            self::$MESSAGES[1][] = $text;
    }

    public function AddError($text = 'неизвестная ошибка'){
        if(is_array($text))
            self::$MESSAGES[0] = count(self::$MESSAGES[0]) > 0 ? array_merge(self::$MESSAGES[0], $text) : $text;
        else
            self::$MESSAGES[0][] = $text;
    }

    public function NoErrors(){
        return count(self::$MESSAGES[0]) == 0 ? true : false;
    }

    public function call_module($module_name, $module_method){
        global $ALL_MODS;

        if(isset($ALL_MODS[$module_name]) && is_object($ALL_MODS[$module_name])){
            if(method_exists($ALL_MODS[$module_name], $module_method)){
                $args = array_slice(func_get_args(), 2);
                return call_user_func_array(array($ALL_MODS[$module_name], $module_method), $args);
            }
        }

        return null;
    }

    public function __initialize(){
        return true;
    }

    // PROTECTED ---------------------------------------------------------------

    protected function add_plugin($name){
        $name = strtolower(trim($name));
        $this->plugins[$name] = array();
    }

    protected function load_plugins($parent, $name){
        foreach($this->plugins as $interface => $objects){
            $ipath = realpath(sprintf('%s/%s/%s.interface.php', ROOT, $this->CONF['services_dir'] . '/' . $name . '/translate/', strtolower($interface)));
            $ifile = preg_replace('/^I(\w+)$/i', '\\1', $interface) . '.interface.php';
            if(file_exists($ipath) && is_file($ipath)){
                require_once($ipath);
                $dir_name = substr($ifile, 0, ($len = strpos($ifile, '.')) > 0 ? $len : strlen($ifile));
                $dir = ROOT . $this->CONF['services_dir'] . '/' . $name  . '/translate/' . strtolower($dir_name);
                if(($handle = opendir($dir)) == false) $this->AddError("Can not open path: $ipath.");
                while(($node = readdir($handle)) !== false){
                    if($node != "." && $node != ".."){
                        if(is_dir($dir . '/' . $node)){
                            $plugin_file = sprintf('%s/%s/%s.%s.plugin.php', $dir, $node, $node, $dir_name);
                            if(file_exists($plugin_file) && is_file($plugin_file)){
                                require_once($plugin_file);
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }

        $classes = get_declared_classes();
        foreach($this->plugins as $interface => &$objects){
            $interface = 'I' . ucfirst(strtolower($interface));
            foreach($classes as $class){
                $reflection = new ReflectionClass($class);
                if($reflection->implementsInterface($interface))
                    $objects[] = new $class($parent, $this->CONF, $this->DB, $this->MID, $this->TPL);
            }
        }
    }

    protected function module_issues(){
        $arr = array();
        return $arr;
    }

    protected function validate_url($url, $table = null, $field = 'url', $id = null, $pk = 'id'){
        if(preg_match('/[^A-Za-z0-9_\-]/i', $url) == false){
            if($table !== null && strlen($table) > 0){
                if($id > 0){
                    if($this->DB->sql2result("SELECT COUNT(*) FROM `{$table}` WHERE `{$pk}` <> '{$id}' AND `{$field}` LIKE '{$url}'") == 0){
                        return true;
                    }
                } elseif($this->DB->sql2result("SELECT COUNT(*) FROM `{$table}` WHERE `{$field}` LIKE '{$url}'") == 0){
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    protected function table_remove_photos($table, $fields, $id, $directory, $pk = 'id'){
        if($table == false || is_array($fields) == false || $id == 0 || $directory == false) return false;

        if(is_array($id) && sizeof($id) > 0){
            foreach($id as $i_d) $this->items_remove_photos($table, $fields, $_id, $directory, $pk);
        } else {
            $files = $this->DB->sql2row("SELECT `" . join('`,`', $fields) . "` FROM `{$table}` WHERE `{$pk}` = {$id}");
            foreach($files as $file){
                $path = sprintf('%s%s%s/%s', ROOT, $this->CONF['upload_dir'], $directory, $file);
                if(file_exists($path) && is_file($path)) @unlink($path);
            }
            for($i = 0, $keys = array(); $i < sizeof($fields); $i++) $keys[$fields[$i]] = null;
            return $this->DB->update($table, $id, $keys);
        }

        return true;
    }

    protected function sortable($key, $title){
        $result = $title; $key = strtolower($key);
        if(array_key_exists($key, $this->sort)){
            $arrow = array_key_exists('sort', $_GET) && $_GET['sort'] == $key ? (strcasecmp(@$_GET['sdir'], 'desc') ? '&darr;' : '&uarr;') : '';
            $result = sprintf('<a href="%s" style="color: BLACK;">%s</a></u> %s<u>', $this->sort_link($key), $title, $arrow);
        }
        return $result;
    }

    protected function sorting($sorting = 'i.`id` DESC'){
        if(array_key_exists('sort', $_GET) && array_key_exists('sdir', $_GET)){
            $sort = strtolower(trim($_GET['sort']));
            $sdir = strtoupper(trim($_GET['sdir']));
            if(array_key_exists($sort, $this->sort)){
                $sorting = sprintf('%s %s, %s', $this->sort[$sort], $sdir, $sorting);
            }
        }
        return $sorting;
    }

    protected function sort_link($key, $direction = 'asc'){
        $directions = array('asc', 'desc');

        $url = parse_url($_SERVER['REQUEST_URI']);
        if(!key_exists('query', $url)) $url['query'] = '';
        parse_str($url['query'], $get);

        if(!key_exists('module', $get)) $get['module'] = $this->module_id;
        if(!key_exists('action', $get)) $get['action'] = $this->action;

        $get['sort'] = $key;
        $get['sdir'] = empty($get['sdir']) ? $direction : array_shift(array_diff($directions, (array)$get['sdir']));

        return sprintf('%s?%s', $url['path'], vars2url($get));
    }

    protected function PrintPages($total, $ppage = 50, $all = false){
        if($total == 0 || $ppage == 0) return;

        print('<table class="pageTable" style="margin-top: 10px;"><tr><td>Страницы:</td><td width="100%">');

        $url = parse_url($_SERVER['REQUEST_URI']);
        if(!key_exists('query', $url)) $url['query'] = '';
        parse_str($url['query'], $get);

        if(!key_exists('module', $get)) $get['module'] = $this->module_id;
        if(!key_exists('action', $get)) $get['action'] = $this->action;

        for($i = 0; $i < $total; $i += $ppage){
            $get['page'] = ceil($i / $ppage) + 1;
            if ($get['page'] != $this->CurrentPage()){
                printf(' <a href="%s?%s" class="pageCurrent">%d</a>', $url['path'], vars2url($get), $get['page']);
            } else {
                printf(' <b class="pageElement">%d</b>', $get['page']);
            }
        }

        if($all == true){
            if($this->CurrentPage() == -1){
                printf(' [<b>ВСЕ</b>]');
            } else {
                $get['page'] = -1;
                printf(' [<a href="%s?%s">ВСЕ</a>]', $url['path'], vars2url($get));
            }
        }

        print('</td></tr></table>');
    }

    protected function CurrentPage(){
        return isset($_GET['page']) ? (int)$_GET['page'] : 1;
    }

    // PUBLIC ---------------------------------------------------------------

    public function call_plugins(){
        $stack = debug_backtrace();
        if(isset($stack[0]["args"]) && is_array($stack[0]["args"]) && count($stack[0]["args"]) >= 2){
            $plugins_group = array_shift($stack[0]["args"]);
            $method = array_shift($stack[0]["args"]);
            $args = array();
            for($i = 0; $i < count($stack[0]["args"]); $i++){
                $args[] = $stack[0]["args"][$i];
            }
            if(isset($this->plugins[$plugins_group])){
                foreach($this->plugins[$plugins_group] as $plugin_id => $plugin_obj){
                    if(method_exists($plugin_obj, $method)){
                        call_user_func_array(array($plugin_obj, $method), $args);
                    }
                }
            }
        }
    }

    public function PrintMenu(){
        global $module;

        if(empty($this->disabled)){
            if($this->module_id == 'settings'){

            } elseif($this->module_id == 'catalogue') {
                    printf('
                    <h3 class="catalogue">Интернет-магазин</h3>
                    <a href="/panel.php?module=%s">%s<i class="flaticon-right-arrow"></i></a>
                    <a href="/panel.php?module=catalogue&action=3">Рубрики и производители<i class="flaticon-right-arrow"></i></a>
                    <a href="/panel.php?module=catalogue&action=7">Акции для товаров<i class="flaticon-right-arrow"></i></a>
                    <a href="/panel.php?module=catalogue&action=9">Фильтры / Настройки / Импорт<i class="flaticon-right-arrow"></i></a>
            ', $this->module_id, $this->module_name);
            } else {
                if($module == $this->module_id)
                    printf('<a href="/panel.php?module=%s" class="active">%s<i class="flaticon-right-arrow"></i></a>', $this->module_id, $this->module_name);
                else
                    printf('<a href="/panel.php?module=%s">%s<i class="flaticon-right-arrow"></i></a>', $this->module_id, $this->module_name);
            }
        }

        return strcmp($module, $this->module_id) ? false : true;
    }

    public function PrintNavigationString(){
        printf('%s &raquo; ', $this->module_name);
        if ($this->action) printf('%s ', $this->actions[$this->action]);
    }

    public function MainBlock(){
        $this->call_module('page', 'settings_main');
    }

    public function PrintTitle(){
        printf($this->module_name);
    }

    public function PrintSubMenu(){
        global $module;

        foreach($this->actions as $k => $v)
            if($this->module_id == 'catalogue') {

            } else {
                if($k == $this->action)
                    printf('<a href="?module=%s&action=%d" class="button buttonSmall buttonRed">%s</a>', $this->module_id, $k, $v);
                else
                    printf('<a href="?module=%s&action=%d" class="button buttonSmall buttonGrey">%s</a>', $this->module_id, $k, $v);
            }

    }

    // PRIVATE -----------------------------------------------------------------

    private function ErrorMessage($message = ''){
        $title = 'Ошибка выполнения программы';
        printf('<div class="pageError"><h3>%s</h3><p>%s</p></div>', $title, $message);
    }

    private function MessageBox($message = ''){
        $title = 'Отчет о выполнении программы';
        printf('<div class="pageSuccess"><h3>%s</h3><p>%s</p></div>', $title, $message);
    }
}

?>