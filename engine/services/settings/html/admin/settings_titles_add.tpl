<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block">
                <div class="sideTable--block--title">Язык заголовков</div>
                <div class="sideTable--block--content"><?php $this->lang_list(@$item_to_edit['lang']) ?></div>
            </div>
            <div class="sideTable--block button">
                <button type="button" onclick="itemAdd(this.form)" class="button buttonLarge buttonRed buttonBlock">Сохранить данные</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование МЕТА заголовков</div>
            </div>
            <div class="contentContainer--areas">
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Заголовок</td>
                            <td class="areasTable--content"><input type="text" name="title" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['title']?>"></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Описание</td>
                            <td class="areasTable--content"><textarea name="description" class="areasTable--area"><?=@$item_to_edit['description']?></textarea></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Ключевые слова</td>
                            <td class="areasTable--content"><textarea name="keywords" class="areasTable--area"><?=@$item_to_edit['keywords']?></textarea></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>