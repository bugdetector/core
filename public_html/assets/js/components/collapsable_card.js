$(function ($) {
    var list = $(".sortable_list");
    if (list.find(".sortable").length > 0) {
        for (var item of list) {
            new Sortable.default(item, {
                animation: 100,
                group: 'sortable_list',
                draggable: '.sortable',
                handle: '.sortable .move_icon',
                sort: true,
                filter: '.sortable-disabled',
                mirror: {
                    appendTo: 'body',
                    constrainDimensions: true
                }
              });
        }
    }
})