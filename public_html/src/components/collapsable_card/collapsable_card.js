import "bootstrap/js/src/collapse";
import Sortable from "sortablejs";
$(function($){
    var form = $("form");
    if(form.find(".sortable").length > 0){
        Sortable.create(form[0], {
            animation: 100,
            group: 'sortable_list',
            draggable: '.sortable',
            handle: '.sortable .move_icon',
            sort: true,
            filter: '.sortable-disabled',
          });
    }
})