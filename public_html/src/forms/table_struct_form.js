$(function($){
    $(document).on("click", ".newfield", function () {
        let button = $(this);
        let index = $(".column_definition").length;
        $.ajax({
            url: root + "/admin/ajax/getColumnDefinition",
            method: "post",
            data: { index: index },
            success: function (data) {
                let row = $(data);
                button.parents(".row.mt-4.mb-5").before(row);
                selectpicker(row.find(".selectpicker"));
                row.find(`input[name='fields[${index}][field_name]']`).focus();
                row.find("input[type='checkbox']").each(function (i, element) {
                    loadCheckbox(element);
                });
            }
        });
    });
    $(document).on("change", "select.type-control", function () {
        var value = $(this).val();
    
        if (value === "short_text") {
            $(this).parents(".column_definition").find(".field_length").parent().parent().removeClass("d-none");
        } else {
            $(this).parents(".column_definition").find(".field_length").parent().parent().addClass("d-none");
        }
        if (value === "table_reference") {
            $(this).parents(".column_definition").find(".reference_table").parent().removeClass("d-none");
        } else {
            $(this).parents(".column_definition").find(".reference_table").parent().addClass("d-none");
        }
    
        if (value === "enumarated_list") {
            $(this).parents(".column_definition").find(".list_values").parent().removeClass("d-none");
        } else {
            $(this).parents(".column_definition").find(".list_values").parent().addClass("d-none");
        }
    });
    $(document).on("click", ".removefield", function (e) {
        e.preventDefault();
        $(this).parents(".column_definition").remove();
    });
    
    $(document).on("click", ".dropfield", function (e) {
        e.preventDefault();
        let tablename = $("input[name='table_name']").val();
        let column = $(this).parents(".column_definition").find(".column_name").val();
        let row = $(this).parents(".column_definition");
        alert({
            message: _t("field_drop_accept", [column]),
            okLabel: _t("yes"),
            callback: function () {
                $.ajax({
                    url: `${root}/admin/ajax/dropfield`,
                    method: "post",
                    dataType: "json",
                    data: { tablename: tablename, column: column },
                    success: function (data) {
                        row.fadeOut(1000);
                    }
                });
            }
        })
    })
    
    $("#new_table").on("submit", function () {
        if ($(".has-error input:enabled").length !== 0) {
            alert(
                { message: _t("check_wrong_fields") });
            return false;
        }
    });
})