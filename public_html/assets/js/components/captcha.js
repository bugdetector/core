$(function () {
    $(document).on("click", ".captcha-refresh", function () {
        let image = $(this).parents(".input_widget").find(".captcha-image");
        fetch(`${root}/captcha/generateCaptchaImage`)
            .then((response) => response.blob())
            .then((data) => {
                var imageUrl = URL.createObjectURL(data);
                image.attr('src', imageUrl);
            });
    }).on("input", ".captcha-input", function () {
        $(this).val(this.value.toLocaleUpperCase());
    });
})