$(function ($) {
    $(".table_config_export").on("click", function (e) {
        $.ajax({
            url: `${root}/admin/ajax/tableConfigurationExport`,
            method: "GET",
            success: function () {
                setTimeout(function () {
                    location.reload();
                }, 1000);
            }
        });
        e.preventDefault();
    })

    $(".table_config_import").on("click", function (e) {
        e.preventDefault();
        $.ajax({
            url: `${root}/admin/ajax/tableConfigurationImport`,
            method: "GET",
            success: function () {
                setTimeout(function () {
                    location.reload();
                }, 1000);
            }
        });
    })
})