$(document).ready(function(){
    $(".newfield").click(function (){
        let button = $(this);
        let index = $(".field_row").length;
        $.ajax({
           url: root + "/admin/ajax/get_input_field",
           method: "post",
           data: {index: index },
           success : function (data) {
                 let row = $(data);
                 button.parents(".row").before(row);
                 row.find(".selectpicker").selectpicker({container:"body"});
                 row.find(`input[name='fields[${index}][field_name]']`).focus();
             }
        });
    });
    $(document).on("change","select.type-control", function (){
        var value = $(this).val();
        var optionalfield = $(this).parents(".row").find(".col:nth-child(5)");
        let index = $(this).parents(".field_row").index();
        if(value === "VARCHAR"){
            let text = _t("length_varchar");
            optionalfield.html(`<label class='w-100 text-primary'> ${text} : </label> <input type='number' class='form-control' max='255' min='0' name='fields[${index}][field_length]' value='255'/>`);
        }else if(value === "MUL"){
            $.ajax({
            url : root + "/admin/ajax/get_table_list",
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                 var selectMenu = $(`<select class="form-control selectpicker" data-live-search="true" name="fields[${index}][mul_table]"></select>`);
                 var length = data.length;
                 for(var i = 0; i<length; i++){
                     var option = $(`<option value='${data[i]}'>${data[i]}</option>`);
                     selectMenu.append(option);
                 }
                 let text = _t("reference_table");
                 optionalfield.html(`<label class='w-100 text-primary'> ${text} :</label> `)
                 optionalfield.append(selectMenu);
                 selectMenu.selectpicker({container:"body"});
             }
          });
        }else {
            optionalfield.html("");
        }
     });
    $(document).on("click",".removefield", function () {
        $(this).parents(".field_row").remove();
    });
    
    $(".dropfield").click(function(){
        let tablename = $("input[name='table_name']").val();
        let column = $(this).parent().next().children("input").val();
        let row = $(this).parents(".field_row");
        alert_message({
            message: _t("field_drop_accept", [column]),
            type: BootstrapDialog.TYPE_DANGER,
            okLabel : _t("yes"),
            callback: function(){
                $.ajax({
                    url: `${root}/admin/ajax/dropfield`,
                    method: "post",
                    dataType: "json",
                    data: {tablename : tablename, column: column},
                    success : function (data) {
                        if(data.status){
                            alert_message({
                                message: data.message
                            });
                            row.fadeOut(1000);
                        }
                      }
                 });
            }
        })
    })
    
    $("#new_table").submit(function (){
        if($(".has-error input:enabled").length !== 0){
            alert_message(
                {message: _t("check_wrong_fields")});
            return false;
        }        
    });
})