$(function($){
    $(document).on("click", ".add-new-entity", function(e){
        e.preventDefault();
        let button = $(this);
        let entityName = button.data("entity");
        let name = button.data("name");
        let hiddenFields = button.data("hidden-fields");
        let index = $(`.collapsible-widget-group[data-entity='${entityName}'] > div > div`).length;
        $.ajax({
            url: root + "/ajax/getEntityCard",
            method: "post",
            data: {entity: entityName, name: name, index: index, hiddenFields: hiddenFields},
            success: function(response){
                response = $(response);
                $(`.collapsible-widget-group[data-entity='${entityName}']`).append(response);
                response.find("select").each(function(i, el){
                    selectpicker(el);
                });
                if (typeof window.loadTimeInput === "function") { 
                    loadTimeInput();
                    loadDateInput();
                    loadDateTimeInput();
                }
            }
        });
    }).on("click", ".remove-entity", function(e){
        e.preventDefault();
        let button = $(this);
        alert({
            message: _t("record_remove_accept"),
            okLabel: _t("yes"),
            callback: function callback() {
              button.closest(".card").fadeOut(500).delay(500, function(){
                  $(this).remove();
              })
            }
          });
    })
})