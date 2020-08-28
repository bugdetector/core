$(document).ready(function () {
    $(".table_config_export").click(function(e){
        e.preventDefault();
        $.ajax({
            url: `${root}/admin/ajax/tableConfigurationExport`,
            method: "GET",
            success: function () {
                setTimeout(function () {
                    location.reload();
                }, 1000);
            }
        });
    });

    $(".table_config_import").click(function(e){
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
    });
})