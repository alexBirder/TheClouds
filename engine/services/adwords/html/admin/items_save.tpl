<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block background">
                <div class="sideTable--block--title">Картинка баннера</div>
                <?php if($item_to_edit['image']) { ?>
                <div class="sideTable--block--content background">
                    <img src="/uploadfiles/banners/<?=@$item_to_edit['image']?>" width="100%" class="background--image" style="margin-top: 0;" />
                    <input type="file" id="image" name="image" class="sideTable--block--background" onchange="showBackground(event);" style="display: none;" maxlength="255">
                    <label for="image" class="button buttonMedium buttonGreen buttonBlock">Выбрать другую картинку</label>
                </div>
                <?php } else { ?>
                <div class="sideTable--block--content background">
                    <input type="file" id="image" name="image" class="sideTable--block--background" onchange="showBackground(event);" style="display: none;" maxlength="255">
                    <label for="image" class="button buttonMedium buttonGreen buttonBlock">Выбрать картинку</label>
                </div>
                <?php } ?>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Расположение</div>
                <div class="sideTable--block--content">
                    <div class="panelBoxes inside">
                        <label class="labelCheckbox"><input type="radio" name="position" value="top" <?=@$item_to_edit['position']===null||$item_to_edit['position']=='top'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Верхний баннер</div></label>
                        <label class="labelCheckbox"><input type="radio" name="position" value="bottom" <?=@$item_to_edit['position']==='bottom'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Нижний баннер</div></label>
                        <label class="labelCheckbox"><input type="radio" name="position" value="left" <?=@$item_to_edit['position']=='left'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Левый баннер</div></label>
                        <label class="labelCheckbox"><input type="radio" name="position" value="right" <?=@$item_to_edit['position']=='right'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Правый баннер</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Статус баннера</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="enabled" value="y" <?=empty($item_to_edit['enabled'])||@$item_to_edit['enabled']=='y'?'checked':''?> /><div class="label--radio--button">Включен</div></label>
                        <label class="label--radio"><input type="radio" name="enabled" value="n" <?=@$item_to_edit['enabled']=='n'?'checked':''?> /><div class="label--radio--button">Выключен</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block button">
                <button type="button" onclick="fileAdd(this.form)" class="button buttonLarge buttonRed buttonBlock">Сохранить страницу</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование баннера</div>
                <div class="contentContainer--header--right">
                    <a href="javascript:void(0)" data-tab="block_form_stock" class="active">Основной</a>
                    <?php $this->call_plugins('adwordsdescription', 'admin_item_buttons', @$edit_id); ?>
                </div>
            </div>
            <div class="contentContainer--areas">
                <?php $this->call_plugins('adwordsdescription', 'admin_item_form', @$edit_id); ?>
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Название</td>
                            <td class="areasTable--content"><input type="text" name="title" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['title']?>"></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">URL адрес</td>
                            <td class="areasTable--content">
                                <div class="areasTable--content--right"><a href="#" class="button buttonMedium buttonGreen buttonBlock generate">Генерировать адрес</a></div>
                                <div class="areasTable--content--left"><input type="text" name="url" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['url']?>"></div>
                            </td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Описание на баннере</td>
                            <td class="areasTable--content"><textarea name="intro" class="text--editor"><?=@$item_to_edit['intro']?></textarea></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>