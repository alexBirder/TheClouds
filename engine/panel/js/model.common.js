$(document).ready(function(){

    //------
    $('select').niceSelect();

    //------
    $('input[name="date"], input[name="date_from"], input[name="date_till"]').datepicker({ dateFormat: 'yyyy-mm-dd' });

    //------
    $('.generate').on('click', function(e){
        $('input[name="title"]').liTranslit({ elAlias: $('input[name="url"]') });
        e.preventDefault();
    });

    //------
    load_editor();

    //------
    $('.contentContainer--header--right a').on('click', function(e){
        let rel = $(this).data('tab');
        $('.contentContainer--header--right a').removeClass('active');
        $(this).addClass('active');
        $('.languageBlock').hide();
        $('#' + rel).fadeIn(100);
        e.preventDefault();
    });

    //------
    $('.contentTable--tr .message--history').each(function(){
        let values = $(this).text();
        $(this).html(values);
    });

    //------
    if($('.sideTable--block--background').val().length > 0){
        appendBackground();
    }

    //------
    $('.catalogueAdditional--tab--title').on('click', function(){
        $(this).toggleClass('rotate');
        $(this).parent().find('.catalogueAdditional--tab--content').slideToggle(100);
    });

});

function load_editor(){
    tinymce.init({
        selector: '.text--editor',
        language: 'ru',
        image_dimensions: false,
        height: 280,
        theme: 'modern',
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'emoticons template paste textcolor colorpicker textpattern imagetools codesample moxiemanager'
        ],
        toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image | media | forecolor backcolor',
        image_advtab: true,
        paste_as_text: false,
        content_css: '/engine/panel/css/content.css',
        file_browser_callback : '#BrowseServer',
        relative_urls : false,
        remove_script_host : true,
        document_base_url : "/",
        code_dialog_width: 1100
    });
}

function browseServer(){
    moxman.browse({ fields: 'bg' })
}

function showBackground(event){
    let value = URL.createObjectURL(event.target.files[0]);
    let image = '<img src="' + value + '" alt="" width="100%;" class="background--image" />';
    let button = '<a href="javascript:void(0)" onclick="removeBackground()" class="button buttonMedium buttonBlock buttonGrey buttonRemove">Удалить картинку</a>';
    $('.sideTable--block--content.background').append(image + button);
}

function appendBackground(element){
    let value = $(element).val();
    let image = '<img src="' + value + '" alt="" width="100%;" class="background--image" />';
    let button = '<a href="javascript:void(0)" onclick="removeBackground()" class="button buttonMedium buttonBlock buttonGrey buttonRemove">Удалить картинку</a>';
    $('.sideTable--block--content.background').append(image + button);
}

function appendFavicon(event){
    let value = URL.createObjectURL(event.files[0]);
    let image = '<img src="' + value + '" alt="" />';
    let button = '<a href="javascript:void(0)" onclick="removeBackground()" class="button buttonMedium buttonBlock buttonGrey buttonRemove">Удалить картинку</a>';
    $('.faviconContainer').append(image + button);
}

function removeBackground(){
    $('.sideTable--block--background').val('');
    $('.favicon--area').val('');
    $('.buttonRemove').remove();
    $('.background--image').remove();
    $('.faviconContainer').html('');
}

function add_filter_area(){
    let content = '<input type="text" name="values[]" class="areasTable--input ajax" maxlength="255" value="">';
    $('.areasTable--content--ajax').append(content);
}

function remove_filter(item){
    $.ajax('?module=catalogue&action=10', {
        data: ({ item: item }), dataType: 'json', type: 'post', complete: function(data){
            window.location.reload();
        }
    })
}

function previewImages() {
    var preview = document.querySelector('#attachContainer');
    if (this.files) {
        [].forEach.call(this.files, readAndPreview);
    }
    function readAndPreview(file) {
        if (!/\.(jpe?g|png|gif)$/i.test(file.name)) { return alert(file.name + " is not an image"); }
        var reader = new FileReader();
        reader.addEventListener("load", function() {
            var image = new Image();
            image.height = 50;
            image.title  = file.name;
            image.src    = this.result;
            preview.appendChild(image);
        }, false);
        reader.readAsDataURL(file);
    }
}