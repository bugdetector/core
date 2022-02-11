$(function(){
    $(document).on("click", ".captcha-refresh", function(){
        let image = $(this).parents(".input_widget").find(".captcha-image");
        image.attr('src', image.attr('src'));
    }).on("input", ".captcha-input", function(){
        $(this).val(this.value.toLocaleUpperCase());
    });
})