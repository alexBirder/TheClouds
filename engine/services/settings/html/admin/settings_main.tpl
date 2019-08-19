<div class="mainPanels">
    <div class="mainPanels--element">
        <div class="mainPanels--element--content">
            <div class="mainPanels--main">Статистика</div>
            <div class="mainPanels--title">Количество страниц</div>
            <div class="mainPanels--count"><?php echo $page_count ?></div>
        </div>
    </div>
    <div class="mainPanels--element">
        <div class="mainPanels--element--content">
            <div class="mainPanels--main">Статистика</div>
            <div class="mainPanels--title">Количество новостей</div>
            <div class="mainPanels--count"><?php echo $news_count ?></div>
        </div>
    </div>
    <div class="mainPanels--element">
        <div class="mainPanels--element--content">
            <div class="mainPanels--main">Статистика</div>
            <div class="mainPanels--title">Количество баннеров</div>
            <div class="mainPanels--count"><?php echo $banners_count ?></div>
        </div>
    </div>
</div>

<div class="usersPanel" style="margin-top: 15px;">
    <div class="usersPanel--left" style="margin-right: 0;">
        <div class="usersPanel--left--title" style="margin-bottom: 15px;">Последние действия в административной панели</div>
        <table class="contentTable history">
            <tr class="contentTable--header">
                <td width="180">Имя пользователя</td>
                <td width="180">Дата действия</td>
                <td>Действие</td>
                <td width="120">IP адрес</td>
            </tr>
            <?php foreach($bases as $item){ ?>
            <tr class="contentTable--tr history">
                <td class="title"><?=$item['user']?></td>
                <td><?=date('Y.m.d H:i:s', strtotime($item['date']))?></td>
                <td class="message--history"><?=$item['action']?></td>
                <td><?=$item['ip']?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>