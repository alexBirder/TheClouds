<div class="containerTitles">
    <div class="containerTitles--title">
        <div class="containerTitles--title--left">Переводы слов и предложений</div>
        <div class="containerTitles--title--right"><a href="/panel.php?module=settings&action=8&add=1" class="button buttonSmall buttonGreen">Добавить новое слово</a></div>
    </div>
    <div class="containerTitles--content">
        <table class="contentTable">
            <tr class="contentTable--header">
                <td width="80" style="text-align: center">ID слова</td>
                <td>Слово или предложение</td>
                <td>Вывод в шаблонах</td>
                <td width="126">Управление</td>
            </tr>
            <?php
                foreach($titles as $title){
                $edit_link = sprintf("?module=%s&action=%s&edit=%d", $this->module_id, $this->action, $title['id']);
                $delete_link = sprintf("?module=%s&action=%s&delete=%d", $this->module_id, $this->action, $title['id']);
            ?>
            <tr class="contentTable--tr">
                <td style="text-align: center"><?=$title['id']?></td>
                <td class="title"><a href="/panel.php<?=$edit_link?>"><?=$title['word']?></a></td>
                <td>{WORD_<?=$title['id']?>}</td>
                <td>
                    <a href="#" class="button buttonSmall buttonGreen" onclick="window.location = '/panel.php<?=$edit_link?>'"><i class="flaticon-edit"></i></a>
                    <a href="#" class="button buttonSmall buttonRed" onclick="if(confirm('Удалить МЕТА данные &laquo;<?=str_replace("'", "", $user['name'])?>&raquo; ?')) window.location = '<?=$delete_link?>'">Удалить</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>