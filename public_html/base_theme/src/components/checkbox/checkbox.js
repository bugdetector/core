window.loadCheckbox = function (element) {
    if (window.$(element).attr("disabled")) {
        return;
    }
    let replace = $("<div class='checkbox_div'>" + element.outerHTML + "</div>");
    let replace_input = replace.find("input");
    window.$(replace_input).val(1);
    if (replace_input[0].checked) {
        window.$(replace_input).after("<input type='hidden' value='0' name='" + $(replace_input).attr("name") + "' disabled='disabled'>");
    } else {
        window.$(replace_input).after("<input type='hidden' value='0' name='" + $(replace_input).attr("name") + "'>");
    }
    $(replace_input).on("change", function () {
        if (window.$(this).is(':checked')) {
            window.$(this).next().attr("disabled", "disabled");
        } else {
            window.$(this).next().removeAttr("disabled");
        }
    });

    window.$(element).replaceWith(replace);
}

window.$(function ($) {
    $("input[type='checkbox']").each(function (i, element) {
        loadCheckbox(element);
    });
})