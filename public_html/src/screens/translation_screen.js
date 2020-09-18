$(function ($) {
    $(".lang-imp").on("click" ,function () {
        alert({
            message: _t("lang_import_info"),
            title: _t("info"),
            callback: function () {
                $.ajax({
                    url: root + "/admin/ajax/langimp",
                    type: "POST"
                })
            }
        });
    });

    $(".lang-exp").on("click", function () {
        alert({
            message: _t("lang_export_info"),
            title: _t("info"),
            callback: function () {
                $.ajax({
                    url: root + "/admin/ajax/langexp",
                    type: "POST"
                })
            }
        });
    });
})