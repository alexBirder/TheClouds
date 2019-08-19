<section class="windowGenerated" id="form-{FORM.id}">
    <div class="windowGenerated--container">
        <div class="windowGenerated--close" onclick="window_close(this)">&times;</div>
        <div class="windowGenerated--title">{FORM.title}</div>
        <form id="areas-{FORM.id}" class="form--values" onsubmit="window_send(this); return false;">
            <input type="hidden" name="form_name" value="{FORM.title}">
            <div class="windowGenerated--content">
                <div class="windowGenerated--loader"><img src="/templates/img/loader.gif" alt=""></div>
                <!-- BEGIN: area_input -->
                <label class="windowGenerated--label"><input class="windowGenerated--text" type="{AREA.type}" value="{AREA.value}" placeholder="{AREA.title}" name="form[{AREA.title}]" {AREA.required}></label>
                <!-- END: area_input -->
                <!-- BEGIN: area_textarea -->
                <label class="windowGenerated--label"><textarea class="windowGenerated--area" name="form[{AREA.title}]" placeholder="{AREA.title}" {AREA.required}>{AREA.value}</textarea></label>
                <!-- END: area_textarea -->
                <!-- BEGIN: area_policy -->
                <label class="windowGenerated--label policy">
                    <input type="checkbox" class="windowGenerated--checkbox" name="policy" required>
                    <span class="checkbox_title">{AREA.policy}</span>
                </label>
                <!-- END: area_policy -->
                <div class="windowGenerated--content--button"><button type="submit" class="button buttonBlock buttonLarge buttonGreen">Отправить</button></div>
            </div>
        </form>
    </div>   
</section>