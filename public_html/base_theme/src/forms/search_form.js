$(document).on("click", ".rowdelete",function (e) {
    e.preventDefault();
    let button = $(this);
    let table_name = $(this).data("table");
    let id = $(this).data("id");
    alert({
        message: _t("record_remove_accept"),
        title: _t("warning"),
        callback: function () {
            $.ajax({
                url: `${root}/admin/ajax/delete`,
                method: "post",
                data: {table: table_name, id: id},
                success: function () {
                    button.parents("tr").fadeOut(1000);
                }
            })
        }
    })
})

$(document).on("click", "input[type='reset']", function (e) {
    e.preventDefault();
    $(this).parents("form").find("input:not([type='submit']):not([type='reset']),textarea").val("");
    $(this).parents("form").find("select").val("NULL").selectpicker("refresh");
    $(this).parents("form").find("input[type='checkbox']").prop("checked", false).trigger("change");
});