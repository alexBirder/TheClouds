<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block background">
                <div class="sideTable--block--title">Главная картинка</div>
                <?php
                    if(strlen(@$item_to_edit['prev']) || strlen(@$item_to_edit['full'])):
                    $delete_link = sprintf('?module=%s&action=%s&edit=%s&image=delete', $this->module_id, $this->action, $edit_id);
                ?>
                <div class="sideTable--block--content background">
                    <img src="/uploadfiles/news/<?=@$item_to_edit['prev']?>" alt="" width="100%" class="background--image" style="margin-top: 0;" />
                    <a href="<?=$delete_link?>" onclick="return confirm('Удалить картинку?');" class="button buttonMedium buttonBlock buttonGrey buttonRemove">Удалить картинку</a>
                </div>
                <?php else: ?>
                <div class="sideTable--block--content background">
                    <input type="file" id="photo" name="photo" class="sideTable--block--background" onchange="showBackground(event);" style="display: none;" maxlength="255">
                    <label for="photo" class="button buttonMedium buttonGreen buttonBlock">Выбрать картинку</label>
                </div>
                <?php endif; ?>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Дата публикации</div>
                <div class="sideTable--block--content"><input type="text" name="date" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['date']?>"></div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Автор публикации</div>
                <div class="sideTable--block--content"><input type="text" name="author" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['author']?>"></div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Источник</div>
                <div class="sideTable--block--content"><input type="text" name="source" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['source']?>"></div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Ссылка на источник</div>
                <div class="sideTable--block--content"><input type="text" name="link" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['link']?>"></div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Отображение картинок</div>
                <div class="sideTable--block--content">
                    <div class="panelBoxes inside">
                        <label class="labelCheckbox"><input type="radio" id="images_3" name="images" value="3" <?=@$item_to_edit['images']===null||$item_to_edit['images']=='3'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Показывать все картинки</div></label>
                        <label class="labelCheckbox"><input type="radio" id="images_0" name="images" value="0" <?=@$item_to_edit['images']==='0'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Не показывать ни одной</div></label>
                        <label class="labelCheckbox"><input type="radio" id="images_1" name="images" value="1" <?=@$item_to_edit['images']=='1'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Показывать только превью</div></label>
                        <label class="labelCheckbox"><input type="radio" id="images_2" name="images" value="2" <?=@$item_to_edit['images']=='2'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Показывать только большую</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Выводить на главную</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="main_list" value="y" <?=@$item_to_edit['main_list']=='y'?'checked':''?> /><div class="label--radio--button">Да</div></label>
                        <label class="label--radio"><input type="radio" name="main_list" value="n" <?=empty($item_to_edit['main_list'])||@$item_to_edit['main_list']=='n'?'checked':''?> /><div class="label--radio--button">Нет</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Основная новость</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="main_top" value="y" <?=@$item_to_edit['main_top']=='y'?'checked':''?> /><div class="label--radio--button">Да</div></label>
                        <label class="label--radio"><input type="radio" name="main_top" value="n" <?=empty($item_to_edit['main_top'])||@$item_to_edit['main_top']=='n'?'checked':''?> /><div class="label--radio--button">Нет</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block">
                <div class="sideTable--block--title">Статус публикации</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="enabled" value="y" <?=empty($item_to_edit['enabled'])||@$item_to_edit['enabled']=='y'?'checked':''?> /><div class="label--radio--button">Включена</div></label>
                        <label class="label--radio"><input type="radio" name="enabled" value="n" <?=@$item_to_edit['enabled']=='n'?'checked':''?> /><div class="label--radio--button">Выключена</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block button">
                <button type="button" onclick="itemAdd(this.form)" class="button buttonLarge buttonRed buttonBlock">Сохранить новость</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование новости</div>
                <div class="contentContainer--header--right">
                    <a href="javascript:void(0)" data-tab="block_form_stock" class="active">Основной</a>
                    <?php $this->call_plugins('newsdescription', 'admin_item_buttons', @$edit_id); ?>
                </div>
            </div>
            <div class="contentContainer--areas">
                <?php $this->call_plugins('newsdescription', 'admin_item_form', @$edit_id); ?>
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Рубрика</td>
                            <td class="areasTable--content"><?php $this->issues_list(@$item_to_edit['issue']) ?></td>
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
                            <td class="areasTable--title" width="160">Краткое описание</td>
                            <td class="areasTable--content"><textarea name="intro" class="areasTable--area"><?=@$item_to_edit['intro']?></textarea></td>
                        </tr>
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Полное описание</td>
                            <td class="areasTable--content"><textarea name="text" class="text--editor"><?=@$item_to_edit['text']?></textarea></td>
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