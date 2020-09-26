import "bootstrap/js/src/modal";
import jQuery from "jquery";
import bootbox from "bootbox";
window.$ = window.jQuery = $ = jQuery;
window.bootbox = bootbox;

window.alert = function (options) {
    let message = options.message;
    let title = options.title ? options.title : _t("warning");
    let okLabel = options.okLabel ? options.okLabel : _t("ok");
    let cancelLabel = options.cancelLabel ? options.cancelLabel : _t("cancel");
    let callback = options.callback ? options.callback : function () { };
    bootbox.dialog({
        title: title,
        message: message,
        closeButton: false,
        buttons: {
            cancel : {
                label: cancelLabel,
                className: "btn-danger",
                callback: function () {}
            },
            ok : {
                label: okLabel,
                className: "btn-primary",
                callback: function () {
                    return callback();
                }
            }
        }
    });
}

/**
 * Ajax loader functions
 */

$(document).on("submit", function () {
    $("body").append("<div class='loader'></div>");
});
$(document).ajaxSend(function () {
    $("body").append("<div class='loader'></div>");
});
$(document).ajaxComplete(function () {
    if ($.active == 1) {
        $(".loader").remove();
    }
})

$(document).ajaxError(function (evt, request, settings) {
    var data = request.responseText;
    if (data.length > 0) {
        try {
            var resp = JSON.parse(data);
            if (resp.messages[0]) {
                bootbox.alert(resp.messages[0].join("<br/>"));
            }
        } catch (ex) {

        }
    }
});

$(document).ajaxSuccess(function (evt, request, settings) {
    var data = request.responseText;
    if (data.length > 0) {
        try {
            var resp = JSON.parse(data);
            if (resp.messages[3]) {
                bootbox.alert(resp.messages[3].join("<br/>"));
            }
        } catch (ex) {

        }
    }
});
export let $ = jQuery;