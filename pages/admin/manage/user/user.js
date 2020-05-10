$(document).ready(function () {
    $(".delete-user").click(function (e) {
        e.preventDefault();
        var controlElement = $(this);
        var username = controlElement.attr("data-username");
        alert_message({
            message: _t("remove_user_accept", ["username"]),
            title: _t("warning"),
            callback: function () {
                $.ajax({
                    url: root + "/admin/ajax/delete_user",
                    type: 'POST',
                    data: { "username": username },
                    dataType: 'json',
                    success: function (response) {
                        controlElement.parents("tr").remove();
                    }
                });
            }
        })
    });
})