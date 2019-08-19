<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="edit" value="<?=@$edit_id?>">
    <input type="hidden" name="save" value="yes">
    <div class="contentContainer">
        <div class="contentContainer--right">
            <div class="sideTable--block">
                <div class="sideTable--block--title">Отображение</div>
                <div class="sideTable--block--content">Основные и модульные шаблоны.</div>
            </div>
            <div class="sideTable--block button">
                <button type="button" onclick="wordAdd(this.form)" class="button buttonLarge buttonRed buttonBlock">Сохранить данные</button>
            </div>
        </div>
        <div class="contentContainer--left">
            <div class="contentContainer--header">
                <div class="contentContainer--header--left">Добавление/редактирование слова</div>
                <div class="contentContainer--header--right">
                    <a href="javascript:void(0)" data-tab="block_form_stock" class="active">Основной</a>
                    <?php $this->call_plugins('settingsdescription', 'admin_item_buttons', @$edit_id); ?>
                </div>
            </div>
            <div class="contentContainer--areas">
                <?php $this->call_plugins('settingsdescription', 'admin_item_form', @$edit_id); ?>
                <div class="languageBlock" id="block_form_stock">
                    <table class="areasTable">
                        <tr class="areasTable--tr">
                            <td class="areasTable--title" width="160">Слово или предложение</td>
                            <td class="areasTable--content"><input type="text" name="word" class="areasTable--input" maxlength="255" value="<?=@$item_to_edit['word']?>"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>