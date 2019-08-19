<form name="itemAddForm" action="" method="POST">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block background">
                <div class="sideTable--block--title">Фон страницы</div>
                <div class="sideTable--block--content background">
                    <input type="hidden" id="bg" name="bg" class="sideTable--block--background" onchange="appendBackground(this);" maxlength="255" value="<?=@$item_to_edit['bg']?>">
                    <a href="javascript:void(0)" onclick="browseServer();" class="button buttonMedium buttonGreen buttonBlock">Выбрать картинку</a>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Выводить в подменю</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="menu" value="n" <?=@$item_to_edit['menu']=='n'?'checked':''?> /><div class="label--radio--button">Не выводить</div></label>
                        <label class="label--radio"><input type="radio" name="menu" value="y" <?=empty($item_to_edit['menu'])||@$item_to_edit['menu']=='y'?'checked':''?> /><div class="label--radio--button">Выводить</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Отображать как</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="islink" value="n" <?=empty($item_to_edit['islink'])||@$item_to_edit['islink']=='n'?'checked':''?> /><div class="label--radio--button">Контент</div></label>
                        <label class="label--radio"><input type="radio" name="islink" value="y" <?=@$item_to_edit['islink']=='y'?'checked':''?> /><div class="label--radio--button">Ссылка</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Вариант шаблона</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="template" value="default" <?=empty($item_to_edit['template'])||@$item_to_edit['template']=='default'?'checked':''?> /><div class="label--radio--button">Обычный</div></label>
                        <label class="label--radio"><input type="radio" name="template" value="wide" <?=@$item_to_edit['template']=='wide'?'checked':''?> /><div class="label--radio--button">Широкий</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Статус страницы</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="enabled" value="y" <?=empty($item_to_edit['enabled'])||@$item_to_edit['enabled']=='y'?'checked':''?> /><div class="label--radio--button">Включена</div></label>
                        <label class="label--radio"><input type="radio" name="enabled" value="n" <?=@$item_to_edit['enabled']=='n'?'checked':''?> /><div class="label--radio--button">Выключена</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block button">
                <button type="button" onclick="itemAdd(this.form)" class="button buttonLarge buttonRed buttonBlock">Сохранить страницу</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование страницы</div>
                <div class="contentContainer--header--right">
                    <a href="javascript:void(0)" data-tab="block_form_stock" class="active">Основной</a>
                    <?php $this->call_plugins('pagedescription', 'admin_item_buttons', @$edit_id); ?>
                </div>
            </div>
            <div class="contentContainer--areas">
                <?php $this->call_plugins('pagedescription', 'admin_item_form', @$edit_id); ?>
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Рубрика</td>
                            <td class="areasTable--content"><?php $this->items_list(@$item_to_edit['parent']) ?></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Заголовок</td>
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
                            <td class="areasTable--title" width="160">Описание на странице</td>
                            <td class="areasTable--content"><textarea name="text" id="editor1" class="text--editor"><?=@$item_to_edit['text']?></textarea></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Meta заголовок</td>
                            <td class="areasTable--content"><input type="text" name="meta_title" class="areasTable--input" maxlength="1000" value="<?=@$item_to_edit['meta_title']?>"></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Meta описание</td>
                            <td class="areasTable--content"><input type="text" name="meta_description" class="areasTable--input" maxlength="1000" value="<?=@$item_to_edit['meta_description']?>"></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Meta ключевые слова</td>
                            <td class="areasTable--content"><input type="text" name="meta_keywords" class="areasTable--input" maxlength="1000" value="<?=@$item_to_edit['meta_keywords']?>"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>