$(function ($) {
    let sortable = new Sortable.default($(".sortable_list").toArray(), {
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

    sortable.on("sortable:sorted", function (event) {
        setTimeout(function(){
            $(event.data.dragEvent.data.originalSource).trigger("dragend");
        }, 500);
    });
})