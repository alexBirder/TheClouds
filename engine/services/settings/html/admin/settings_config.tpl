<form name="itemAddForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <input type="hidden" name="save" value="yes">

    <div class="containerConfig">
        <div class="containerConfig--left">
            <div class="containerConfig--left--title">Корневые настройки</div>
            <table class="areasTable">
                <tr class="areasTable--tr">
                    <td class="areasTable--content" style="padding-left: 0; padding-top: 0;">
                        <span class="containerConfig--title">Фавикон сайта <small style="padding-left: 10px;">64 х 64 пикселей</small></span>
                        <label class="button buttonMedium buttonBlock buttonGreen" for="project_favicon">Загрузить картинку</label>
                        <input type="file" name="project_favicon" id="project_favicon" class="areasTable--input favicon--area" style="display: none;" onchange="appendFavicon(this);">
                        <div class="faviconContainer"></div>
                    </td>
                </tr>
                <tr class="areasTable--tr">
                    <td class="areasTable--content" style="padding-left: 0;"><span class="containerConfig--title">Хлебные крошки</span><input type="text" name="project_bread" class="areasTable--input" maxlength="255" value="<?=$project['project_bread']?>"></td>
                </tr>
            </table>
            <div class="containerConfig--right--block" style="margin-top: 15px;">
                <div class="block--title">Статус проекта</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="project_status" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['project_status']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Запустить проект для всех</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">Дублировать статику в меню</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="change_menu" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['change_menu']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить дублирование</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">Социальные сети</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="policy_socials" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['policy_socials']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить на сайте</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">Политика сохранения COOKIE</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="policy_cookie" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['policy_cookie']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить вывод политики</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">Политика конфиденциальности</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="policy_confidence" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['policy_confidence']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить вывод политики</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">Распознаватель ADBLOCK</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="change_adblock" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['change_adblock']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить распознаватель</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">GZIP сжатие данных</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="change_gzip" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['change_gzip']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить GZIP сжатие</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">Отпимизация HTML кода</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="change_minify" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['change_minify']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить оптимизацию</div></label>
                    </div>
                </div>
            </div>
            <div class="containerConfig--right--block">
                <div class="block--title">Мультиязычные шаблоны</div>
                <div class="block--content">
                    <div class="panelBoxes inside">
                        <label><input type="checkbox" name="change_template" value="y" onclick="$(this).attr('value', this.checked ? 'y' : 'n')" <?=$project['change_template']=='y'?'checked':''?>><div class="panelBoxes--box"></div><div class="panelBoxes--title">Включить смену шаблонов</div></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="containerConfig--center">
            <div class="containerConfig--center--title">Основные настройки</div>
            <table class="areasTable">
                <tr class="areasTable--tr">
                    <td class="areasTable--content" style="padding-top: 0;"><span class="containerConfig--title">Название проекта</span><input type="text" name="project_name" class="areasTable--input" maxlength="255" value="<?=$project['project_name']?>"></td>
                </tr>
                <tr class="areasTable--tr">
                    <td class="areasTable--content"><span class="containerConfig--title">URL адрес проекта</span><input type="text" name="project_url" class="areasTable--input" maxlength="255" value="<?=$project['project_url']?>"></td>
                </tr>
                <tr class="areasTable--tr">
                    <td class="areasTable--content"><span class="containerConfig--title">E-mail проекта</span><input type="text" name="project_email" class="areasTable--input" maxlength="255" value="<?=$project['project_email']?>"></td>
                </tr>
                <tr class="areasTable--tr">
                    <td class="areasTable--content"><span class="containerConfig--title">E-mail ответа проекта</span><input type="text" name="project_email_reply" class="areasTable--input" maxlength="255" value="<?=$project['project_email_reply']?>"></td>
                </tr>
                <tr class="areasTable--tr">
                    <td class="areasTable--content"><span class="containerConfig--title">RECAPTCHA секретный ключ</span><input type="text" name="project_recaptcha" class="areasTable--input" maxlength="255" value="<?=$project['project_recaptcha']?>"><span class="containerConfig--small">Получить данный ключ можно на сайте <a href="https://www.google.com/recaptcha/intro/v3.html" target="_blank">Google Recaptcha</a></span></td>
                </tr>
                <tr class="areasTable--tr">
                    <td class="areasTable--content"><span class="containerConfig--title">Аналитика и дополнительные скрипты</span><textarea name="project_scripts" class="areasTable--scripts"><?=$project['project_scripts']?></textarea></td>
                </tr>
            </table>
            <br />
            <button type="submit" class="button buttonRed buttonBlock buttonLarge">Сохранить все корневые и основные настройки</button>
        </div>
    </div>
</form>