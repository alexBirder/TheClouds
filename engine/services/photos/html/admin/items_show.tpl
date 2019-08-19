<form id="galleryForm" name="galleryForm" action="" method="GET">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <table class="contentTable">
        <tr class="contentTable--tr">
            <td width="150" nowrap>Альбом<span class="star">*</span>:</td>
            <td width="100%"><?php $this->issues_list_photo($issue) ?></td>
        </tr>
        <?php if($issue > 0): ?>
        <tr class="contentTable--tr">
            <td width="150" nowrap>Фотографии<span class="star">*</span>:</td>
            <td width="100%">
                <input type="button" id="upload_button" value="Загрузить фотографии" class="button buttonMedium buttonGreen">
            </td>
        </tr>
        <?php endif; ?>
    </table>
</form>
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="margin-top: 15px;">
    <tr>
        <td>
            <div id="galleryPhotosAll" class="galleryAdmin">
                <?= $this->items_list($issue); ?>
            </div>
        </td>
    </tr>
</table>

<?php if($issue > 0): ?>

<script type="text/javascript">
    let gallerySaveTitle = function(id, text){
        $.ajax("?module=<?=$this->module_id?>&action=13", {
            method: "get", data: { id: id, text: text },
            success: function(){ galleryLoadContent(); }
        });
    };
    let galleryDeletePhoto = function(id){
        if(confirm('Удалить фотографию?') == true){
            $.ajax("?module=<?=$this->module_id?>&action=12", {
                type: "POST", data: { issue: '<?=$issue?>', id: id },
                success: function(){ $('#galleryList').load(location.href+' #galleryList>*',''); }
            });
        }
    };
    let galleryLoadContent = function(){
        $('#galleryList').sortable({
            update: function(){
                var data = $(this).sortable('serialize');
                $.ajax("?module=<?=$this->module_id?>&action=11&" + data,{
                    method: "get", data: { issue: '<?=$issue?>' },
                    success: function(){ $('#galleryList').load(location.href+" #galleryList>*",""); }
                });
            }
        });
        $('#galleryList .gallery-photo-title').each(function(e){
            let currentValue = '';
            $(this).focus(function(e){
                currentValue = $(this).val(); $(this).addClass('gallery-active-input');
            });
            $(this).blur(function(e){
                $(this).removeClass('gallery-active-input');
                if(currentValue != $(this).val()){ gallerySaveTitle($(this).attr('rel'), $(this).val()); }
            });
        });
    };
    let uploadFiles = new AjaxUpload('#upload_button', {
        name: 'photos[]', action: '?module=<?=$this->module_id?>&action=10', data: { issue: '<?=$issue?>' }, multiple: true,
        onSubmit : function(file, ext){
            $('#upload_button').val('Загружается...');
            if (!(ext && /^(jpg|png|jpeg|gif)$/i.test(ext))){ alert('Error: неверный формат файла изображения.'); return false; }
        },
        onComplete: function(){
            $('#upload_button').val('Загрузить фотографии');
            $.ajax("?module=<?=$this->module_id?>&action=4", {
                method: "post", data: { issue: '<?=$issue?>' },
                success: function(){ window.location.reload(); }
            });
        }
    });
    galleryLoadContent();
</script>

<?php endif; ?>