$(function () {
    if (window.innerWidth < 768) {
        $('#table_list').toggleClass('show');
        $('a[href=\"#table_list\"]').toggleClass('collapsed');
    }

    $("#entity_search_field").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#entity_list .list-group .entity_info").filter(function () {
            $(this).toggle($(this).text().toLowerCase().includes(value));
        });
    });
})