var control_table;

$(document).ready(function () {
   $(".newfield").click(function (){
       $.ajax({
          url: root + "/admin/ajax/get_input_field",
          method: "post",
          data: {index: $("#result_table tbody tr").length },
          success : function (data) {
                let row = $(data);
                $("#result_table tbody").append(row);
                row.find(".selectpicker").selectpicker().find("input[name='field_name']").focus();
            }
       });
   });
   $(document).on("change","select.type-control", type_conrolchange);
   $(document).on("click",".removefield", function () {
       $(this).parents("tr").remove();
   });

   $(".dropfield").click(function(){
       let tablename = $("input[name='table_name']").val();
       let column = $(this).parent().next().children("input").val();
       let row = $(this).parents("tr");
       alertMessage(_t(387, [column]), _t(54), BootstrapDialog.TYPE_DANGER, function(){
        $.ajax({
            url: root + "/admin/ajax/dropfield",
            method: "post",
            dataType: "json",
            data: {tablename : tablename, column: column},
            success : function (data) {
                if(data.status){
                    alertMessage(data.message);
                    row.fadeOut(1000);
                }
              }
         });
       })
   })
   
   $("#new_table").submit(function (){
       if($(".has-error input:enabled").length !== 0){
           alertMessage(_t(80));
           return false;
       }        
   });
   
   $(".tabledrop").click(function (){
       var tablename = getTableNameFromList($(this));
        alertMessage(_t(93, [tablename]),_t(54), BootstrapDialog.TYPE_DANGER,function() {
                droptable(tablename);
            });
   });
   
   $(".tabletruncate").click(function (){
       var tablename = getTableNameFromList($(this));
        alertMessage(_t(109, [tablename]),_t(54), BootstrapDialog.TYPE_DANGER,function() {
                truncatetable(tablename);
            });
   });
    
    $(".delete-user").click(function (e) {
        e.preventDefault();
        var controlElement = $(this);
        var username = controlElement.attr("data-username");
        alertMessage(_t(98, [username]), _t(54), BootstrapDialog.TYPE_DANGER, function (){
            $.ajax({
                url : root + "/admin/ajax/delete_user",
                type: 'POST',
                data : {"USERNAME" : username},
                dataType: 'json',
                success: function (response) {
                     controlElement.parents("tr").remove();
                }
             });
        });
    });

    $(".add-role").click(function(e) {
        e.preventDefault();
        let modal_content = $(
                '<form method="post" class="row">'+
                    '<div class="col-sm-3">'+
                        '<label>'+ _t(50) + '</label>'+
                    '</div>'+
                    '<div class="col-sm-9">'+
                        '<input class="form-control uppercase_filter" type="text" name="ROLE"/>'+
                    '</div>'+
                '</form>');
        BootstrapDialog.show({
            message : modal_content,
            title : _t(11),
            buttons: [{
                label: _t(14),
                action: function(){
                    modal_content.submit();
                }
            }]
        });
    })
    
    $(".remove-role").click(function (e) {
        e.preventDefault();
        controlElement = $(this);
        var role = $(this).attr("data-role-name");
        alertMessage(_t(81), _t(54), BootstrapDialog.TYPE_DANGER, function(){
            $.ajax({
                url : root + "/admin/ajax/remove_role",
                type: 'POST',
                dataType: 'json',
                data: {ROLE: role},
                success: function (data, textStatus, jqXHR) {
                        controlElement.parents("tr").remove();
                }
            });
        })
    });
    
    $(".rowadd").click(function (){
        var tablename = getTableNameFromList($(this));
        window.location = root+"/admin/insert/"+tablename;
    });
    
    $(".recordelete").click(function (e){
        var currentelement = $(this);
        alertMessage(_t(81), _t(54), BootstrapDialog.TYPE_DANGER, function (){
            $(currentelement).next().click();
        });
        e.preventDefault();
    });
    
    $(".remove_document").click(function () {
        var element = $(this);
        alertMessage(_t(81), _t(54),  BootstrapDialog.TYPE_DANGER, function (){
            var id = element.attr("data-bind");
            $.ajax({
                url : root +"/admin/ajax/remove_document",
                type: "POST",
                dataType: 'json',
                data : {id: id},
                success: function (data, textStatus, jqXHR) {
                        alertMessage(data.msg, _t(52), BootstrapDialog.TYPE_INFO, function (){
                           location.reload(); 
                        });
                }
            })
        });
    });
    
    $(".lang-imp").click(function(){
        alertMessage(_t(103), _t(52), BootstrapDialog.TYPE_INFO, function(){
            $.ajax({
                url : root +"/admin/ajax/langimp",
                type: "POST"
            })
        })
    });
    
    $(".lang-exp").click(function(){
        alertMessage(_t(104), _t(52), BootstrapDialog.TYPE_INFO, function(){
            $.ajax({
                url : root +"/admin/ajax/langexp",
                type: "POST"
            })
        })
    });
    
    $(document).on("click",".dbl_click_fk", function () {
        var columnname = columns[$(this).parents("td").index()];
        var activetable = get_active_table();
        var fk_val = $(this).text();
        window.location = root +"/admin/table/"+activetable+"/fk/"+columnname+"/"+fk_val;
    });
    
    $(document).on("mouseenter", ".dbl_click_fk",function () {
        if($(this).attr("data-trigger")){
            return;
        }else{
            $(this).attr("data-trigger", true);
        }
        var activeelement = $("<span>"+$(this).html()+"</span>")
        $(this).html(activeelement);
        var columnname = columns[$(this).parents("td").index()];
        fk_hover = setTimeout(function () {
            var fk_val = activeelement.text();
            var activetable = get_active_table();
            $.ajax({
                url: root + "/admin/ajax/get_fk_entry",
                type: 'POST',
                data : {fk : fk_val, column : columnname, table: activetable },
                success: function (data, textStatus, jqXHR) {
                    activeelement.attr("data-content", data);
                    activeelement.attr("data-placement","left");
                    activeelement.attr("data-trigger","hover");
                    activeelement.attr("data-container","body");
                    activeelement.popover("show");
                }
            })
        }, 500);   
    }).on("mouseleave", ".dbl_click_fk",function () {
        clearTimeout(fk_hover);
    });
    
    $(document).on("click",".rowdelete",function (){
         var controlElement = $(this);
         alertMessage(_t(81), _t(54), BootstrapDialog.TYPE_DANGER, function () {
            var refresh = "glyphicon-refresh";
            controlElement.removeClass("glyphicon-remove");
            controlElement.addClass(refresh);
            var data = new FormData();
            var values = controlElement.parents("tr").find("td").each(function(i, td){
                if(i===0) return;
                data.append(columns[i], $(td).clone().find("a").remove().end().text() );
            });
            var activetable = get_active_table();
            data.append("table", activetable);
            $.ajax({
                url: root+"/admin/ajax/delete",
                type: "POST",
                processData:false,
                contentType:false,
                data : data,
                error: function(){
                    controlElement.removeClass(refresh);
                    controlElement.addClass("glyphicon-remove");
                },
                success : function(response){
                    controlElement.removeClass(refresh);
                    controlElement.addClass("glyphicon-ok");
                    setTimeout(function (){
                        controlElement.parents("tr").remove();
                    }, 1000);
                }
            });
        });
     });
});

function droptable(tablename){
    $.ajax({
       url: root + "/admin/ajax/drop",
       type: 'POST',
       dataType: 'json',
       data : {tablename : tablename},
       success: function (data, textStatus, jqXHR) {
                alertMessage(data.message,_t(52), BootstrapDialog.TYPE_INFO, function () {
                        location.reload();
                    });
       }
    });
}

function truncatetable(tablename){
    $.ajax({
       url: root + "/admin/ajax/truncate",
       type: 'POST',
       dataType: 'json',
       data : {tablename : tablename},
       success: function (data, textStatus, jqXHR) {
                alertMessage(data.message,_t(52), BootstrapDialog.TYPE_INFO, function () {
                        location.reload();
                    });
       }
    });
}

var type_conrolchange = function (){
   var value = $(this).val();
   var optionalfield = $(this).parents("tr").find("td:nth-child(6)");
   var optionalexplainfield = $(this).parents("tr").find("td:nth-child(5)");
   let index = $(this).parents("tr").index();
   if(value === "VARCHAR"){
       optionalexplainfield.html(_t(62))
       optionalfield.html("<input type='number' class='form-control' max='255' min='0' name='fields["+index+"][field_length]' value='255'/>");
   }else if(value === "MUL"){
       $.ajax({
       url : root + "/admin/ajax/get_table_list",
       dataType: 'json',
       success: function (data, textStatus, jqXHR) {
            var selectMenu = $('<select class="form-control selectpicker" data-live-search="true" name="fields['+index+'][mul_table]"></select>');
            var length = data.length;
            for(var i = 0; i<length; i++){
                var option = $("<option value='"+data[i]+"'>"+data[i]+"</option>");
                selectMenu.append(option);
            }
            optionalexplainfield.html(_t(63))
            optionalfield.html(selectMenu);
            selectMenu.selectpicker();
        }
     });
   }else {
       optionalfield.html("");
       optionalexplainfield.html("");
   }
};
   
function get_active_table() {
    if(control_table){
        return  control_table;
    }else{
        return  $(".list-group-item.tablelist.active").find(".tablebtn").text();
    }
}
function getTableNameFromList(element){
    return element.parents(".tablelist").children("a").text().trim();
}
