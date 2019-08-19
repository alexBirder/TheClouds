<div class="containerTitles">
    <div class="containerTitles--title">
        <div class="containerTitles--title--left">Основные МЕТА данные сайта</div>
        <div class="containerTitles--title--right"><a href="/panel.php?module=settings&action=7" class="button buttonSmall buttonGreen">Добавить новые МЕТА данные</a></div>
    </div>
    <div class="containerTitles--content">
        <table class="contentTable">
            <tr class="contentTable--header">
                <td>Заголовок</td>
                <td>Описание</td>
                <td>Ключевые слова</td>
                <td width="100">Язык</td>
                <td width="126">Управление</td>
            </tr>
            <?php
                foreach($titles as $title){
                $edit_link = sprintf("?module=%s&action=%s&edit=%d", $this->module_id, $this->action, $title['id']);
                $delete_link = sprintf("?module=%s&action=%s&delete=%d", $this->module_id, $this->action, $title['id']);
            ?>
            <tr class="contentTable--tr">
                <td class="title"><?=$title['title']?></td>
                <td><?=$title['description']?></td>
                <td><?=$title['keywords']?></td>
                <td><?=$title['lang']?></td>
                <td>
                    <a href="#" class="button buttonSmall buttonGreen" onclick="window.location = '/panel.php<?=$edit_link?>'"><i class="flaticon-edit"></i></a>
                    <a href="#" class="button buttonSmall buttonRed" onclick="if(confirm('Удалить МЕТА данные &laquo;<?=str_replace("'", "", $user['name'])?>&raquo; ?')) window.location = '<?=$delete_link?>'">Удалить</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>