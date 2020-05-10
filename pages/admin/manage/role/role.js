$(document).ready(function(){
    $(".add-role").click(function(e) {
        e.preventDefault();
        let modal_content = $(
                    '<div class="col-sm-3">'+
                        '<label>'+ _t("role_name") + '</label>'+
                    '</div>'+
                    '<div class="col-sm-9">'+
                        '<input class="form-control uppercase_filter" id="role_name" type="text"/>'+
                    '</div>');

        alert_message({
            message: modal_content,
            title: _t("add_role"),
            type: BootstrapDialog.TYPE_PRIMARY,
            callback: function(){
                let role_name = modal_content.find("#role_name").val();
                $.ajax({
                    url: `${root}/admin/ajax/add_role`,
                    data: {role: role_name},
                    method: "post",
                    okLabel: _t("add"),
                    dataType: "json",
                    success: function(response){
                        alert_message({
                            message: response.message, 
                            callback: function(){
                                location.reload();
                            }
                        });
                    }
                })
            }
        })
    });


    $(".remove-role").click(function (e) {
        e.preventDefault();
        controlElement = $(this);
        var role = $(this).attr("data-role-name");
        alert_message({
            message: _t("record_remove_accept"),
            callback: function(){
                $.ajax({
                    url : root + "/admin/ajax/remove_role",
                    type: 'POST',
                    dataType: 'json',
                    data: {ROLE: role},
                    success: function () {
                            controlElement.parents("tr").remove();
                    }
                });
            }
        })
    });
})