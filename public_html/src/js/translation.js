$(document).ready(function(){
    $(".lang-imp").click(function(){
        alert_message({
            message: _t("lang_import_info"),
            type:  BootstrapDialog.TYPE_INFO,
            callback: function(){
                $.ajax({
                    url : root +"/admin/ajax/langimp",
                    type: "POST"
                })
            }
        });
    });

    $(".lang-exp").click(function(){
        alert_message({
            message: _t("lang_export_info"),
            type:  BootstrapDialog.TYPE_INFO,
            callback: function(){
                $.ajax({
                    url : root +"/admin/ajax/langexp",
                    type: "POST"
                })
            }
        });
    });
})