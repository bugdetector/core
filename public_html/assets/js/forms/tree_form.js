$(function($){
    $(document).on("click", ".add-new-node", function(e){
        e.preventDefault();
        let templateCard = $("#node_card_template").children().first().clone();
        let index = $(".new-node-card").length;
        let parent = $(this).data("parent");
        let fieldName = $(this).data("field-name");
        templateCard.addClass("new-node-card");
        templateCard.find(".card-header").attr("href", `#new-node-card-${index}`);
        templateCard.find(".collapse").attr("id", `new-node-card-${index}`);
        templateCard.find(".field").attr("name", `tree[new-${index}][${fieldName}]`);
        templateCard.find(".parent").attr("name", `tree[new-${index}][parent]`).val(parent);
        templateCard.find(".add-new-node").hide();
        templateCard.find(".remove-node").attr("data-node", `new-${index}`);
        templateCard.attr("data-parent", `new-${index}`);
        $("#parent-"+parent).append(templateCard);
        loadSelect2(templateCard.find("select"));
    })
    
    $(document).on("dragend", ".node-card", function(e){
        let item = $(this);
        console.log(item);
        let parent = item.parents().closest(".node-card").data("parent");
        console.log(parent);
        item.find(".parent").first().val(parent);
    })
    
    $(document).on("click", ".remove-node", function(e){
        e.preventDefault();
        let nodeId = $(this).data("node");
        let serviceUrl = $(this).data("service-url");
        swal.fire({
            title: _t("warning"),
            html: _t("node_remove_warning"),
            icon: "warning",
            buttonsStyling: false,
            confirmButtonText: _t("yes"),
            showCancelButton: true,
            cancelButtonText: _t("cancel"),
            customClass: {
                confirmButton: "btn btn-light-primary",
                cancelButton: "btn btn-light-danger"
            }
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                $.ajax({
                    url: serviceUrl,
                    method: "post",
                    data: {
                        nodeId: nodeId
                    },
                    success: function(){
                        $(`.node-card[data-parent='${nodeId}']`)
                        .fadeOut(500).delay(500, function(){
                            $(this).remove();
                        });
                    }
                });
            }
        });
    })
})
