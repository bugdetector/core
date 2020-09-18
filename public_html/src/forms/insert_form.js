$(document).on("click", ".remove_accept", function (e) {
    e.preventDefault();
    var currentelement = $(this);
    alert({
        message: _t("record_remove_accept"),
        okLabel: _t("yes"),
        callback: function () {
            $(currentelement).next().click();
        }
    });
});