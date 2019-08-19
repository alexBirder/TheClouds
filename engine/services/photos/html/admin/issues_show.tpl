<table class="contentTable">
    <tr class="contentTable--header">
        <td width="40" style="text-align: center;">ID</td>
        <td>Название</td>
        <td>Ссылка</td>
        <td width="88">Сортировка</td>
        <td width="126">Управление</td>
    </tr>
    <?php
        $i = 0;
        foreach($items as $item){
            $l_1 = $item['level'] == '1' ? 'l_1' : '';
            $l_2 = $item['level'] == '2' ? 'l_2' : '';
            $l_3 = $item['level'] == '3' ? 'l_3' : '';
            $l_4 = $item['level'] == '4' ? 'l_4' : '';
            $l_5 = $item['level'] == '5' ? 'l_5' : '';
            $status = $item['enabled'] == 'y' ? 'enabled' : 'disabled';
            $move_up_link = sprintf("?module=%s&action=%s&move=%d&to=up", $this->module_id, $this->action, $item['id']);
            $move_down_link = sprintf("?module=%s&action=%s&move=%d&to=down", $this->module_id, $this->action, $item['id']);
            $edit_link = sprintf("?module=%s&action=%s&edit=%d", $this->module_id, $this->action, $item['id']);
            $delete_link = sprintf("?module=%s&action=%s&delete=%d", $this->module_id, $this->action, $item['id']);
    ?>
    <tr class="contentTable--tr <?=$l_1?> <?=$l_2?> <?=$l_3?> <?=$l_4?> <?=$l_5?> <?=$status?>">
        <td align="center" nowrap=""><b><?=$item['id']?></b></td>
        <td class="title"><i class="flaticon-pointer level--url"></i><a href="/panel.php<?=$edit_link?>"><?=$item['title']?></a> <i class="flaticon-link link--url"></i></td>
        <td><?=$item['url']?> <i class="flaticon-copy copy--url"></i></td>
        <td>
            <a href="<?=$move_up_link?>" class="button buttonSmall buttonGrey" data-tooltip="Поднять вверх">↑</a>
            <a href="<?=$move_down_link?>" class="button buttonSmall buttonGrey" data-tooltip="Опустить вниз">↓</a>
        </td>
        <td>
            <a href="#" class="button buttonSmall buttonGreen" onclick="window.location = '/panel.php<?=$edit_link?>'"><i class="flaticon-edit"></i></a>
            <a href="#" class="button buttonSmall buttonRed" onclick="if(confirm('Удалить страницу &laquo;<?=str_replace("'", "", $item['title'])?>&raquo; ?')) window.location = '/panel.php<?=$delete_link?>'">Удалить</a>
        </td>
    </tr>
    <?php $i++; } ?>
</table>