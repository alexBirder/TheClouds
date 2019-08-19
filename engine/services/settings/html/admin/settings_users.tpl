<div class="containerTitles">
    <div class="containerTitles--title">
        <div class="containerTitles--title--left">Пользователи админ-панели</div>
        <div class="containerTitles--title--right"><a href="/panel.php?module=settings&action=1&add=1" class="button buttonSmall buttonGreen">Добавить нового пользователя</a></div>
    </div>
    <div class="containerTitles--content">
        <table class="contentTable">
            <tr class="contentTable--header">
                <td width="200">Имя пользователя</td>
                <td width="200">Логин пользователя</td>
                <td>Модули</td>
                <td width="126">Управление</td>
            </tr>
            <?php
                foreach($users as $user){
                $modules = $user['services'] ? $user['services'] : 'Все модули';
                $delete_link = sprintf("?module=%s&action=%s&delete=%d", $this->module_id, $this->action, $user['id']);
                $edit_link = sprintf("?module=%s&action=%s&edit=%d", $this->module_id, $this->action, $user['id']);
            ?>
            <tr class="contentTable--tr">
                <td class="title"><?=$user['name']?></td>
                <td><?=$user['login']?></td>
                <td><?=$modules?></td>
                <td>
                    <a href="#" class="button buttonSmall buttonGreen" onclick="window.location = '/panel.php<?=$edit_link?>'"><i class="flaticon-edit"></i></a>
                    <a href="#" class="button buttonSmall buttonRed" onclick="if(confirm('Удалить пользователя &laquo;<?=str_replace("'", "", $user['name'])?>&raquo; ?')) window.location = '<?=$delete_link?>'">Удалить</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>