$(document).ready(function () {
    if (window.innerWidth < 768) {
        $('#table_list').toggleClass('show');
        $('a[href=\"#table_list\"]').toggleClass('collapsed');
    }

    $("#table_search_field").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#table_list .list-group .table_info").filter(function () {
            $(this).toggle($(this).text().toLowerCase().includes(value));
        });
    });

    $(".tabletruncate").click(function () {
        var tablename = $(this).attr("data-table-name");
        alert_message({
            message: _t("truncate_accept", [tablename]),
            okLabel: _t("yes"),
            type: BootstrapDialog.TYPE_DANGER,
            callback: function () {
                $.ajax({
                    url: `${root}/admin/ajax/truncate`,
                    type: 'POST',
                    dataType: 'json',
                    data: { tablename: tablename },
                    success: function (data, textStatus, jqXHR) {
                        alert_message({
                            message: data.message,
                            title: _t("info"),
                            type: BootstrapDialog.TYPE_INFO,
                            callback: function () {
                                location.reload();
                            }
                        });
                    }
                });
            }
        })
    });

    $(".tabledrop").click(function () {
        var tablename = $(this).attr("data-table-name");
        alert_message({
            message: _t("drop_accept", [tablename]),
            okLabel: _t("yes"),
            type: BootstrapDialog.TYPE_DANGER,
            callback: function () {
                $.ajax({
                    url: `${root}/admin/ajax/drop`,
                    type: 'POST',
                    dataType: 'json',
                    data: { tablename: tablename },
                    success: function (data, textStatus, jqXHR) {
                        alert_message({
                            message: data.message,
                            title: _t("info"),
                            type: BootstrapDialog.TYPE_INFO,
                            callback: function () {
                                location.reload();
                            }
                        });
                    }
                });
            }
        })
    });

    $(".rowdelete").click(function(e){
        e.preventDefault();
        let button = $(this);
        let table_name = $(this).data("table");
        let id = $(this).data("id");
        alert_message({
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
})