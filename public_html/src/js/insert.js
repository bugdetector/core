$(document).ready(function(){
    $(".remove_accept").click(function (e){
        var currentelement = $(this);
        alert_message({
            message: _t("record_remove_accept"),
            type: BootstrapDialog.TYPE_DANGER,
            okLabel: _t("yes"),
            callback: function (){
                $(currentelement).next().click();
            }
        })
        e.preventDefault();
    });
})