<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block">
                <div class="sideTable--block--title">Статус рубрики</div>
                <div class="sideTable--block--content">
                    <div class="sideTable--label--area">
                        <label class="label--radio"><input type="radio" name="enabled" value="y" <?=empty($item_to_edit['enabled'])||@$item_to_edit['enabled']=='y'?'checked':''?> /><div class="label--radio--button">Включена</div></label>
                        <label class="label--radio"><input type="radio" name="enabled" value="n" <?=@$item_to_edit['enabled']=='n'?'checked':''?> /><div class="label--radio--button">Выключена</div></label>
                    </div>
                </div>
            </div>
            <div class="sideTable--block button">
                <button type="button" onclick="issueAdd(this.form)" class="button buttonLarge buttonRed buttonBlock">Сохранить рубрику</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование новости</div>
                <div class="contentContainer--header--right">
                    <a href="javascript:void(0)" data-tab="block_form_stock" class="active">Основной</a>
                    <?php $this->call_plugins('newsdescription', 'admin_issue_buttons', @$edit_id); ?>
                </div>
            </div>
            <div class="contentContainer--areas">
                <?php $this->call_plugins('newsdescription', 'admin_issue_form', @$edit_id); ?>
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Рубрика</td>
                            <td class="areasTable--content"><?php $this->issues_list(@$item_to_edit['parent']) ?></td>
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