$(document).on("click", ".clear-cache", function (e) {
    e.preventDefault();
    $.ajax(`${root}/admin/ajax/clearCache`);
}).on("click", "#dark-mode-switch", function(){
    KTCookie.set("dark-mode", this.checked);
    location.reload();
});
$(function(){
    if(darkMode){
        $("#dark-mode-switch").prop("checked", true);
    }
})
window._t = function (key, args) {
    if (args) {
        return translations[key].format(args);
    }
    return translations[key];
}
String.prototype.format = function () {
    var a = this, b;
    for (b in arguments) {
        a = a.replace(/%[a-z]/, arguments[b]);
    }
    return a; // Make chainable
};

window.alert = function (options) {
    let message = typeof options == 'string' ? options : options.message;
    let title = options.title ? options.title : _t("warning");
    let okLabel = options.okLabel ? options.okLabel : _t("ok");
    let cancelLabel = options.cancelLabel ? options.cancelLabel : _t("cancel");
    let icon = options.icon ? options.icon : "warning";
    let callback = options.callback ? options.callback : function () { };
    swal.fire({
        title: title,
        html: message,
        icon: icon,
        buttonsStyling: false,
        confirmButtonText: okLabel,
        showCancelButton: cancelLabel ? true : false,
        cancelButtonText: cancelLabel,
        customClass: {
            confirmButton: "btn btn-light-primary",
            cancelButton: "btn btn-light-danger"
        }
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          callback();
        }
    });
}

$(document).ajaxError(function (evt, request, settings) {
    var data = request.responseText;
    if (data && data.length > 0) {
        try {
            var resp = JSON.parse(data);
            if (resp.messages[0]) {
                alert({
                    message: resp.messages[0].join("<br/>"),
                    title: _t("error"),
                    icon: "error"
                });
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
                setTimeout(function(){
                    swal.fire({
                        html: resp.messages[3].join("<br/>"),
                        title: _t("info"),
                        icon: "success",
                        toast: true,
                        timer: 3000,
                        timerProgressBar: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        customClass: {
                            popup: "bg-light-info"
                        }
                    });
                })
            }
        } catch (ex) {
        }
    }
});

/**
 * Ajax loader functions
 */

 $(document).on("submit", function () {
    swal.showLoading();
});
var loadingShown = false;
var isModalVisible = false;
$(document).ajaxSend(function () {
    setTimeout(function(){
        if($.active > 0){
            loadingShown = true;
            isModalVisible = swal.isVisible();
            swal.showLoading();
        }
    }, 300);
});
$(document).ajaxComplete(function () {
    if ($.active == 1 && loadingShown) {
        if(isModalVisible){
            swal.hideLoading();
        }else{
            swal.closeModal();
        }
        loadingShown = false;
    }
})
