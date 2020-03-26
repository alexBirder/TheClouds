$(document).ready(function(){

    $('.containerHeader--burger').on('click', function(){
        $('.containerHeader--menu').slideToggle('fast');
    });

    //-----------------------------------------
    $('.masked').mask("+38 (999) 999-99-99");
    $('input[name="form[Телефон]"]').mask("+38 (999) 999-99-99");

    //-----------------------------------------
    $('.containerSlider--content--sliders').lightSlider({ auto: true, pager: false, controls: false, item: 1, mode: 'fade', loop: true, pause: 4000 });

    //-----------------------------------------
    $('.sliderFooter').lightSlider({ auto: true, pager: false, controls: false, item: 3, loop: true, adaptiveHeight: true, pause: 4000, responsive : [{ breakpoint: 1000, settings: { item: 2, }}, { breakpoint: 600, settings: { item: 1 }}] });

    //-----------
    if(window.localStorage.getItem('policy_cookies') == null){
        setTimeout(function(){ $(".containerCookies").fadeIn(100); }, 1500);
    }

    //-----------
    $('.containerCookies--bind').bind('click', function(){
        window.localStorage.setItem('policy_cookies', 'complete');
        $(".containerCookies").fadeOut(100);
    });

    //-----------
    $('[data-fancybox]').fancybox();

    //-----------
    $('select').niceSelect();

    //-----------------------------------------
    let language_page = $('html').attr('lang');
    $('[data-language="'+language_page+'"').addClass('active');

});

function init_mask(){
    $(".masked").mask("+38 (999) 999-99-99");
    $('input[name="form[Телефон]"]').mask("+38 (999) 999-99-99");
}

function window_height(){
    $('body .windowGenerated').each(function(i){
        let height_window = $(this).find('.windowGenerated--content').height();
        $(this).find('.windowGenerated--container').css('height', height_window + 90);
    });
}

function window_close(element){
    $(element).closest('.windowGenerated').fadeOut(100);
}

function window_open(number){
    $('#form-'+number+'').fadeIn('fast');
    init_mask();
    window_height();
}

function window_send(form){
    let values = $(form).serialize();
    $(form).closest('.windowGenerated').find('.windowGenerated--loader').show();
    $.ajax('/?module=forms&media=ajax&action=send', {
        data: values, dataType: 'json', type: 'post', success: function(data){
            $(form).closest('.windowGenerated').find('.windowGenerated--loader').hide();
            $('.windowGenerated--content').html('<div class="windowGenerated--message">'+data.result+'</div>');
            window_height();
        }
    })
}