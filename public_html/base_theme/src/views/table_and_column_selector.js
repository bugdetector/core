$(function () {
    $(document).on("change", ".table_select", function () {
        var select = $(this);
        if (!select.val()) {
            return;
        }
        var column_select = select.parents(".table_and_column_selector").find("select.column_select");
        var type = column_select.data("type");
        $.ajax({
            url : `${root}/admin/ajax/getTableColumns`,
            method: "post",
            dataType : "json",
            data: {table: select.val(), type: type},
            success: function (response) {
                column_select.html("");
                for ( var column in response.data) {
                    var option = $("<option>");
                    option.val(column).text(response.data[column]);
                    column_select.append(option);
                }
                selectpicker(column_select, "refresh");
            }
        })
    })

    $(document).on("click", ".removefilter", function (e) {
        e.preventDefault();
        $(this).parents(".table_and_column_selector").fadeOut(500).delay(500).queue(function () {
            $(this).remove();
        });
    })

    $(".new_filter, .new_field").on("click", function (e) {
        e.preventDefault();
        var button = $(this);
        var type = button.data("type");
        $.ajax({
            url : `${root}/admin/ajax/getTableAndColumnSelector`,
            method : "post",
            data : {
                index : button.closest(".filters, .fields").find(".table_and_column_selector").length + 1,
                type : type,
                name : button.data("name")
            },
            success : function (response) {
                let row = $(response);
                button.before(row);
                selectpicker(row.find(".selectpicker"));
            }
        })
    })
})