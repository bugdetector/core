$(function(){
    for(let select of $(".form-select")){
        loadSelect2(select);
    }
})

window.loadSelect2 = function(element, defaults = {}){
    let options = {
        language: language,
        width: "100%",
        theme: "bootstrap5",
        ...defaults,
        ...$(element).data()
    };
    if($(element).hasClass("autocomplete")){
        let request = {
            token : $(element).data("autocomplete-token")
        };
        options.ajax = {
                url: root+"/ajax/autocompleteFilter",
                method: "post",
                dataType: 'json',
                data: function(params){
                    request.term = params.term;
                    return request;
                },
                processResults: function (response) {
                    if(response.data == null){
                        response.data = [];
                    }
                    return {
                      results: request.term ? response.data : 
                        $(this.$element).find("option").map(function(i, el){ return {id: $(el).attr("value"), text: $(el).text() }; })
                    };
                  }
            };
    }
    $(element).select2(options);
}
