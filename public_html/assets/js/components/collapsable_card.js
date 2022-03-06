$(function ($) {
    new Sortable.default($(".sortable_list").toArray(), {
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
})