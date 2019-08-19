<table class="contentTable">
    <tr class="contentTable--header">
        <td width="40" style="text-align: center;">ID</td>
        <td>Название</td>
        <td width="100">Дата</td>
        <td width="88">Сортировка</td>
        <td width="126">Управление</td>
    </tr>
    <?php
        $i = 0;
        foreach($items as $item){
        $class = $item['enabled'] == 'y' ? 'enabled' : 'disabled';

        $issue_url = $this->DB->sql2result("SELECT `url` FROM news_issues WHERE `id` = {$item['issue']}");
        $link = "/news/".$issue_url."/".$item['url'].".html";

        $edit_link = sprintf("?module=%s&action=%s&edit=%d", $this->module_id, $this->action, $item['id']);
        $delete_link = sprintf("?module=%s&action=%s&delete=%d", $this->module_id, $this->action, $item['id']);
        $move_up_link = sprintf("?module=%s&action=%s&move=%d&to=up", $this->module_id, $this->action, $item['id']);
        $move_down_link = sprintf("?module=%s&action=%s&move=%d&to=down", $this->module_id, $this->action, $item['id']);
    ?>
    <tr class="contentTable--tr <?=$class?>">
        <td align="center" nowrap=""><b><?=$item['id']?></b></td>
        <td class="title"><i class="flaticon-pointer level--url"></i><e onclick="window.location = '/panel.php<?=$edit_link?>';"><?=$item['title']?></e> <a href="<?=$link?>" target="_blank"><i class="flaticon-link link--url"></i></a></td>
        <td><?=date2str($item['date'])?></td>
        <td>
            <a href="<?=$move_up_link?>" class="button buttonSmall buttonGrey" data-tooltip="Поднять вверх">↑</a>
            <a href="<?=$move_down_link?>" class="button buttonSmall buttonGrey" data-tooltip="Опустить вниз">↓</a>
        </td>
        <td>
            <a href="#" class="button buttonSmall buttonGreen" onclick="window.location = '/panel.php<?=$edit_link?>'"><i class="flaticon-edit"></i></a>
            <a href="#" class="button buttonSmall buttonRed" onclick="if(confirm('Удалить публикацию &laquo;<?=str_replace("'", "", $item['title'])?>&raquo; ?')) window.location = '<?=$delete_link?>'">Удалить</a>
        </td>
    </tr>
    <?php $i++; } ?>
</table>

