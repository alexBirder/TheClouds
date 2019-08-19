<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block">
                <div class="sideTable--block--title">Статус формы</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="enabled" value="y" <?=empty($item_to_edit['enabled'])||@$item_to_edit['enabled']=='y'?'checked':''?> /><div class="label--radio--button">Включена</div></label>
                        <label class="label--radio"><input type="radio" name="enabled" value="n" <?=@$item_to_edit['enabled']=='n'?'checked':''?> /><div class="label--radio--button">Выключена</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block button">
                <button type="submit" class="button buttonLarge buttonRed buttonBlock">Сохранить данные</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование формы</div>
            </div>
            <div class="contentContainer--areas">
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Название формы</td>
                            <td class="areasTable--content"><?php $this->titles_list(@$item_to_edit['title']) ?></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Поля в этой форме</td>
                            <td class="areasTable--content"><?php $this->areas_list(@$item_to_edit['id']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>