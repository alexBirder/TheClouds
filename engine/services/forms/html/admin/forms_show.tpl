<div class="containerTitles">
    <div class="containerTitles--content">
        <table class="contentTable">
            <tr class="contentTable--header">
                <td width="40" style="text-align: center;">ID</td>
                <td>Название формы</td>
                <td>Поля</td>
                <td>Вывод в BEGIN и END</td>
                <td width="100">Статус</td>
                <td width="126">Управление</td>
            </tr>
            <?php
                foreach($titles as $title){
                $edit_link = sprintf("?module=%s&action=%s&edit=%d", $this->module_id, $this->action, $title['id']);
                $delete_link = sprintf("?module=%s&action=%s&delete=%d", $this->module_id, $this->action, $title['id']);

                $title_name = $this->DB->sql2result("SELECT `word` FROM settings_words WHERE `id` = {$title['title']} ");
                $status_name = $title['enabled'] == 'y' ? 'Включена' : 'Выключена';
                $areas = $this->DB->sql2array("SELECT * FROM forms_attached WHERE `form` = {$title['id']}");
                $result = '';
                foreach($areas as $area){
                    $name = $this->DB->sql2result("SELECT `title` FROM forms_areas WHERE `id` = {$area['area']}");
                    $result .= sprintf('%s &nbsp;', $this->DB->sql2result("SELECT `word` FROM settings_words WHERE `id` = {$name}"));
                }
            ?>
            <tr class="contentTable--tr">
                <td style="text-align: center"><?=$title['id']?></td>
                <td class="title"><?=$title_name?></td>
                <td><?= $result ?></td>
                <td>{FILE {FORM_<?=$title['id']?>}}</td>
                <td><?= $status_name ?></td>
                <td>
                    <a href="#" class="button buttonSmall buttonGreen" onclick="window.location = '/panel.php<?=$edit_link?>'"><i class="flaticon-edit"></i></a>
                    <a href="#" class="button buttonSmall buttonRed" onclick="if(confirm('Удалить МЕТА данные &laquo;<?=str_replace("'", "", $user['name'])?>&raquo; ?')) window.location = '<?=$delete_link?>'">Удалить</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>