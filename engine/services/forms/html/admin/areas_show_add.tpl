<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block">
                <div class="sideTable--block--title">Тип поля</div>
                <div class="sideTable--block--content">
                    <select name="type" id="" class="areasTable--input">
                        <option value="text">Обычное поле</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio</option>
                        <option value="area">Текстовое поле</option>
                    </select>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Обязательное поле</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="required" value="y" <?=empty($item_to_edit['required'])||@$item_to_edit['required']=='y'?'checked':''?> /><div class="label--radio--button">Да</div></label>
                        <label class="label--radio"><input type="radio" name="required" value="n" <?=@$item_to_edit['required']=='n'?'checked':''?> /><div class="label--radio--button">Нет</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Статус поля</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="enabled" value="y" <?=empty($item_to_edit['enabled'])||@$item_to_edit['enabled']=='y'?'checked':''?> /><div class="label--radio--button">Включено</div></label>
                        <label class="label--radio"><input type="radio" name="enabled" value="n" <?=@$item_to_edit['enabled']=='n'?'checked':''?> /><div class="label--radio--button">Выключено</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block button">
                <button type="submit" class="button buttonLarge buttonRed buttonBlock">Сохранить данные</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование поля</div>
            </div>
            <div class="contentContainer--areas">
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Заголовок поля</td>
                            <td class="areasTable--content"><?php $this->titles_list(@$item_to_edit['title']) ?></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Название поля</td>
                            <td class="areasTable--content"><input type="text" name="name" class="areasTable--input" maxlength="1000" value="<?=@$item_to_edit['name']?>"></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Значение поля</td>
                            <td class="areasTable--content"><input type="text" name="value" class="areasTable--input" maxlength="1000" value="<?=@$item_to_edit['value']?>"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>