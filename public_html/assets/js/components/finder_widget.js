$(function(){
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
                        [dialog, dialogContent] = openModal(
                            "",
                            response,
                            null,
                            "modal-xl"
                        );
                    }else{
                        dialogContent.find(".modal-body").html(response);
                    }
                    dialogContent.find("form").on("submit", function(e){
                        e.preventDefault();
                        loadData($(this).serializeArray());
                        return false;
                    });
                    dialogContent.find("th a").on("click", function(e){
                        e.preventDefault();
                        loadData([], $(this).attr("href"));
                    })
                    dialogContent.find("select").each(function(i, el){
                        loadSelect2(el);
                    });
                    dialogContent.find(".finder-select").on("click", function(e){
                        e.preventDefault();
                        finderArea.find(".finder-input").val(this.value).trigger("change");
                        let row = $(this).parents("tr");
                        finderArea.find(".entity-finder-display-text").val(
                            row.find("td:eq(1)").text().trim() + " - " +
                            row.find("td:eq(2)").text().trim()
                        ).trigger("change");
                        dialog.hide();
                    })
                }
            })
        }
    })
})