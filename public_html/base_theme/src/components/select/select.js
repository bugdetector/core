import "./select.scss";


window.selectpicker = function (element, func = $) {
    //$(element).selectpicker(func);
}

$(document).on("keyup", ".bootstrap-select.autocomplete .bs-searchbox input", autocompleteFilter);

var filter_caller = null;
function autocompleteFilter(event){
    var input = String.fromCharCode(event.keyCode);
    var text  = $(this).val();
    //input key is not a character
    if (text && !/[a-zA-Z0-9-_ ]/.test(input)){
        return;
    }
    if(filter_caller){
        clearInterval(filter_caller);
    }
    var selectField = $(this).parents(".bootstrap-select").find("select");
    filter_caller = setTimeout(
        function(){
            let data = {
                token : $(selectField).data("autocomplete-token"),
                data : text
            };
            $.ajax({
                url: root+"/ajax/autocompleteFilter",
                method: "post",
                data: data,
                success: function(response){
                    let response_data = JSON.parse(response);
                    let options = "";
                    let selectedOptions = selectField.find("option:selected").map(function(i, option){
                        return $(option.outerHTML);
                    });
                    let nullOption = selectField.find("option[value='0']");
                    options += nullOption.length > 0 ? null_option[0].outerHTML : "";
                    let data = response_data.data;
                    if( data instanceof Object ){
                        for(let id in data){
                            options += `<option value='${id}'>${data[id]}</option>`;
                        }
                    }
                    selectedOptions.each(function(i, selected){
                        options += selected[0].outerHTML;
                    });
                    selectField.html(options);
                    if(selectField.hasClass("create-if-not-exist")){
                        if($(selectField).find("option:contains('"+text+"')").length == 0){
                            $(selectField).append("<option value='"+text+"'>"+text+"</option>");
                        }
                    }
                    //selectField.selectpicker("refresh");
                }
            });
        }
    , 500);
}