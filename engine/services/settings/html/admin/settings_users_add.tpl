<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block">
                <div class="sideTable--block--title">Статус пользователя</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="enabled" value="y" <?=empty($item_to_edit['enabled'])||@$item_to_edit['enabled']=='y'?'checked':''?> /><div class="label--radio--button">Включен</div></label>
                        <label class="label--radio"><input type="radio" name="enabled" value="n" <?=@$item_to_edit['enabled']=='n'?'checked':''?> /><div class="label--radio--button">Выключен</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Доступ до модулей</div>
                <div class="sideTable--block--content">
                    <div class="panelBoxes inside"><?php $this->modules_list(@$item_to_edit['id']) ?></div>
                </div>
            </div>
            <div class="sideTable--block button">
                <button type="button" onclick="userAdd(this.form)" class="button buttonLarge buttonRed buttonBlock">Сохранить данные</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование пользователя</div>
            </div>
            <div class="contentContainer--areas">
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Имя пользователя</td>
                            <td class="areasTable--content"><input type="text" name="name" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['name']?>"></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Логин</td>
                            <td class="areasTable--content"><input type="text" name="login_admin" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['login']?>"></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Основной пароль</td>
                            <td class="areasTable--content"><input type="password" name="password_admin" class="areasTable--input" maxlength="255" value=""></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>