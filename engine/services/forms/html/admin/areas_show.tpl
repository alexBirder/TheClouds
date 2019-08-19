<div class="containerTitles">
    <div class="containerTitles--content">
        <table class="contentTable">
            <tr class="contentTable--header">
                <td width="40" style="text-align: center;">ID</td>
                <td>Заголовок поля</td>
                <td>Тип поля</td>
                <td>Название</td>
                <td width="200">Обязательное</td>
                <td width="88">Сортировка</td>
                <td width="126">Управление</td>
            </tr>
            <?php
                foreach($titles as $title){
                $required_name = $title['required'] == 'y' ? 'Да' : 'Нет';
                $title_name = $this->DB->sql2result("SELECT `word` FROM settings_words WHERE `id` = {$title['title']} ");
                $edit_link = sprintf("?module=%s&action=%s&edit=%d", $this->module_id, $this->action, $title['id']);
                $delete_link = sprintf("?module=%s&action=%s&delete=%d", $this->module_id, $this->action, $title['id']);
                $move_up_link = sprintf("?module=%s&action=%s&move=%d&to=up", $this->module_id, $this->action, $title['id']);
            $move_down_link = sprintf("?module=%s&action=%s&move=%d&to=down", $this->module_id, $this->action, $title['id']);
            ?>
            <tr class="contentTable--tr">
                <td style="text-align: center"><?=$title['id']?></td>
                <td class="title"><?=$title_name?></td>
                <td><?=$title['type']?></td>
                <td><?=$title['name']?></td>
                <td><?=$required_name?></td>
                <td>
                    <a href="<?=$move_up_link?>" class="button buttonSmall buttonGrey" data-tooltip="Поднять вверх">↑</a>
                    <a href="<?=$move_down_link?>" class="button buttonSmall buttonGrey" data-tooltip="Опустить вниз">↓</a>
                </td>
                <td>
                    <a href="#" class="button buttonSmall buttonGreen" onclick="window.location = '/panel.php<?=$edit_link?>'"><i class="flaticon-edit"></i></a>
                    <a href="#" class="button buttonSmall buttonRed" onclick="if(confirm('Удалить поле ?')) window.location = '<?=$delete_link?>'">Удалить</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>