$(function($){
    $(document).on("click", ".entity-finder .find", function(){
        let button = $(this);
        let finderArea = $(this).parents(".entity-finder");
        let className = button.data("class");
        let dialog = null;
        loadData();
        function loadData(data = [], orderBy = ""){
            data.push({
                name: "className",
                value: className
            });
            $.ajax({
                url: `${root}/finder/findData` + orderBy,
                data: data,
                success: function(response){
                    if(!dialog){
                        dialog = bootbox.dialog({
                            message: response,
                            size: 'xl'
                        });
                    }else{
                        dialog.find(".bootbox-body").html(response);
                    }
                    dialog.find("form").on("submit", function(e){
                        e.preventDefault();
                        loadData($(this).serializeArray());
                        return false;
                    });
                    dialog.find("th a").on("click", function(e){
                        e.preventDefault();
                        loadData([], $(this).attr("href"));
                    })
                    dialog.find("select").each(function(i, el){
                        selectpicker(el);
                    });
                    dialog.find(".finder-select").on("click", function(e){
                        e.preventDefault();
                        finderArea.find(".finder-input").val(this.value);
                        let row = $(this).parents("tr");
                        finderArea.find(".entity-finder-display-text").val(
                            row.find("td:eq(1)").text().trim() + " - " +
                            row.find("td:eq(2)").text().trim()
                        );
                        bootbox.hideAll();
                    })
                }
            })
        }
    })
})