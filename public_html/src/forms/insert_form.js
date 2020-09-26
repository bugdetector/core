$(document).on("click", ".remove_accept", function (e) {
    e.preventDefault();
    alert({
        message: _t("record_remove_accept"),
        okLabel: _t("yes"),
        callback: function () {
            $("input[name='delete']").click();
        }
    });
});