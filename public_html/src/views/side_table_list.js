$(function () {
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

    $(".tabletruncate").on("click", function (e) {
        e.preventDefault();
        var tablename = $(this).attr("data-table-name");
        alert({
            message: _t("truncate_accept", [tablename]),
            okLabel: _t("yes"),
            callback: function () {
                $.ajax({
                    url: `${root} / admin / ajax / truncate`,
                    type: 'POST',
                    dataType: 'json',
                    data: { tablename: tablename },
                    success: function () {
                        setTimeout(function () {
                            //location.reload();
                        }, 1000);
                    }
                });
            }
        })
    });

    $(".tabledrop").on("click", function (e) {
        e.preventDefault();
        var tablename = $(this).attr("data-table-name");
        alert({
            message: _t("drop_accept", [tablename]),
            okLabel: _t("yes"),
            callback: function () {
                $.ajax({
                    url: `${root} / admin / ajax / drop`,
                    type: 'POST',
                    dataType: 'json',
                    data: { tablename: tablename },
                    success: function () {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                });
            }
        })
    });
})