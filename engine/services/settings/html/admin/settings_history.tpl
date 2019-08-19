<div class="usersPanel">
    <div class="usersPanel--left" style="margin-right: 0;">
        <div class="usersPanel--left--title">Последние действия в административной панели</div>
        <table class="contentTable">
            <tr class="contentTable--header">
                <td width="180">Имя пользователя</td>
                <td width="180">Дата действия</td>
                <td>Действие</td>
                <td width="120">IP адрес</td>
            </tr>
            <?php foreach($items as $item){ ?>
            <tr class="contentTable--tr">
                <td class="title"><?=$item['user']?></td>
                <td><?=date('Y.m.d H:i:s', strtotime($item['date']))?></td>
                <td class="message--history"><?=$item['action']?></td>
                <td><?=$item['ip']?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>