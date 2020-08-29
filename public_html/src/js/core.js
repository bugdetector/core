$(document).ready(function () {
    $(document).on("keyup", ".uppercase_filter", function () {
        $(this).val($(this).val().toUpperCase());
        if (!($(this).val().match(/^[A-Z1-9_\s]+$/))) {
            $(this).parent().addClass("has-error");
        } else {
            $(this).parent().removeClass("has-error");
        }
    });
    $(document).on("keyup", ".lowercase_filter", function () {
        $(this).val($(this).val().toLowerCase());
        if (!($(this).val().match(/^[a-z1-9_\s]+$/))) {
            $(this).parent().addClass("has-error");
        } else {
            $(this).parent().removeClass("has-error");
        }
    });

    $(document).on("click", "input[type='reset']", function (e) {
        e.preventDefault();
        $(this).parents("form").find("input:not([type='submit']):not([type='reset']),textarea").val("");
        $(this).parents("form").find("select").val("NULL").selectpicker("refresh");
        $(this).parents("form").find("input[type='checkbox']").prop("checked", false).change();
    });

    $(document).on("click", ".clear-cache", function (e) {
        e.preventDefault();
        $.ajax(`${root}/admin/ajax/clearCache`);
    });

    $(".summernote").summernote({
        lang: language
    });

    $("input[type='checkbox']").each(function (i, element) {
        loadCheckbox(element);
    });

    $(".timeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        el.datetimepicker({
            format: "HH:mm",
            locale: language
        });
        el.val(default_value);
    });
    $(".dateinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        el.datetimepicker({
            format: "YYYY-MM-DD",
            locale: language
        });
        el.val(default_value);
    });
    $(".datetimeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        el.datetimepicker({
            format: "YYYY-MM-DD HH:mm",
            locale: language
        });
        el.val(default_value);
    });

    $(".daterangeinput").daterangepicker({
        autoUpdateInput: false,
        locale: {
            "format": "YYYY-MM-DD",
            "separator": " & "
        }
    }).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' & ' + picker.endDate.format('YYYY-MM-DD'));
    }).on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    })


    var filter_caller = null;
    $(document).on("keyup", ".bootstrap-select.autocomplete .bs-searchbox input", function (event) {
        var input = String.fromCharCode(event.keyCode);
        var text = $(this).val();
        //input key is not a character
        if (text && !/[a-zA-Z0-9-_ ]/.test(input)) {
            return;
        }
        if (filter_caller) {
            clearInterval(filter_caller);
        }
        var select_field = $(this).parents(".bootstrap-select").find("select");
        filter_caller = setTimeout(
            function () {
                let data = {
                    table: $(select_field).attr("data-reference-table"),
                    column: $(select_field).attr("data-reference-column"),
                    data: text
                };
                if ($(select_field).attr("data-reference-filter-column")) {
                    data["filter-column"] = $(select_field).attr("data-reference-filter-column");
                }
                if ($(select_field).attr("data-reference-filter-value")) {
                    data["filter-value"] = $(select_field).attr("data-reference-filter-value");
                }
                $.ajax({
                    url: root + "/ajax/AutoCompleteSelectBoxFilter",
                    method: "post",
                    data: data,
                    success: function (response) {
                        response_data = $.parseJSON(response);
                        let options = "";
                        let null_option = select_field.find("option[value='0']");
                        options += null_option.length > 0 ? null_option[0].outerHTML : "";
                        let selected_value = select_field.val();
                        let selected_option = select_field.find("option[value='" + selected_value + "']")[0].outerHTML;
                        let selected = false;
                        for (data of response_data) {
                            if (data[0] == selected_value) {
                                selected = true;
                            }
                            options += "<option value='" + data[0] + "'>" + data[1] + "</option>";
                        }
                        if (!selected && selected_value != 0) {
                            options += selected_option;
                        }
                        select_field.html(options);
                        if (select_field.hasClass("create_if_not_exist")) {
                            if ($(select_field).find("option:contains('" + text + "')").length == 0) {
                                $(select_field).append("<option value='" + text + "'>" + text + "</option>");
                            }
                        }
                        select_field.selectpicker("refresh");
                    }
                });
            }
            , 500);
    });


    /**
     * Ajax loader functions
     */

    $(document).submit(function () {
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
                var resp = $.parseJSON(data);
                if (resp.messages[0]) {
                    alert_message({
                        message: resp.messages[0].join("<br/>"),
                        title: _t("error"),
                        type: BootstrapDialog.TYPE_DANGER
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
                var resp = $.parseJSON(data);
                if (resp.messages[3]) {
                    alert_message({
                        message: resp.messages[3].join("<br/>"),
                        title: _t("info"),
                        type: BootstrapDialog.TYPE_INFO
                    });
                }
            } catch (ex) {

            }
        }
    });
})


function _t(key, arguments) {
    if (arguments) {
        return translations[key].format(arguments);
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

function alert_message(options) {
    let message = options.message;
    let title = options.title ? options.title : _t("warning");
    let type = options.type ? options.type : BootstrapDialog.TYPE_WARNING;
    let okLabel = options.okLabel ? options.okLabel : _t("ok");
    let cancelLabel = options.cancelLabel ? options.cancelLabel : _t("cancel");
    let callback = options.callback ? options.callback : function () { };
    BootstrapDialog.show({
        type: type,
        title: title,
        message: message,
        closable: false,
        buttons: [
            {
                label: cancelLabel,
                cssClass: "btn-danger",
                action: function (dialog) {
                    dialog.close();
                }
            },
            {
                label: okLabel,
                cssClass: "btn-primary",
                action: function (dialog) {
                    callback();
                    dialog.close();
                }
            }
        ]
    });
}

function loadCheckbox(element) {
    if($(element).attr("disabled")){
        return;
    }
    let replace = $("<div class='checkbox_div'>" + element.outerHTML + "</div>");
    let replace_input = replace.find("input");
    $(replace_input).val(1);
    if (replace_input[0].checked) {
        $(replace_input).after("<input type='hidden' value='0' name='" + $(replace_input).attr("name") + "' disabled='disabled'>");
    } else {
        $(replace_input).after("<input type='hidden' value='0' name='" + $(replace_input).attr("name") + "'>");
    }
    $(replace_input).change(function () {
        if ($(this).is(':checked')) {
            $(this).next().attr("disabled", "disabled");
        } else {
            $(this).next().removeAttr("disabled");
        }
    });

    $(element).replaceWith(replace);
}