$(document).on("click", ".rowdelete",function(e){
    e.preventDefault();
    let button = $(this);
    let table_name = $(this).data("table");
    let id = $(this).data("id");
    alert({
        message: _t("record_remove_accept"),
        title: _t("warning"),
        callback: function(){
            $.ajax({
                url: `${root}/admin/ajax/delete`,
                method: "post",
                data: {table: table_name, id: id},
                success: function(){
                    button.parents("tr").fadeOut(1000);
                }
            })
        }
    })
})