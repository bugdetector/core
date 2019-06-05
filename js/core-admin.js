var control_table;

$(document).ready(function () {
   $(".newfield").click(function (){
       var this_input_field = $(this).parents("div.row");
       $.ajax({
          url: root + "/admin/ajax/get_input_field",
          success : function (data) {
                this_input_field.before($(data));
                this_input_field.prev().find(".selectpicker").selectpicker();
                this_input_field.prev().find("input[name='field_name']").focus();
            }
       });
   });
   $(document).on("change","select.type-control", type_conrolchange);
   $(document).on("click",".removefield", removefield);
   
   $(".save_table").click(function (){
       if($(".has-error").length !== 0){
           alertMessage(_t(80));
           return ;
       }
       var table_name = $("input[name='table_name']").val();
       var fields = [];
       $('div.field_definition').each(function() {
           var data = {};
           $(this).find(":input").serializeArray().map(function(x){data[x.name] = x.value;})
           fields.push(data);
        });
        var form_build_id = $("input[name='form_build_id']").val();
        define(table_name, fields, form_build_id);
        
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
   
   $(".tableadd").click(function (){
        var tablename = getTableNameFromList($(this));
        window.location = root +"/admin/table/new/"+tablename;
    });
    
    $(".alter_table").click(function () {
       if($("div.field_definition .has-error").length !== 0){
           alertMessage(_t(80));
           return ;
       }
       var table_name = $("input[name='table_name']").val();
       var fields = [];
       $('div.field_definition').each(function() {
           var data = {};
           $(this).find(":input").serializeArray().map(function(x){data[x.name] = x.value;})
           fields.push(data);
        });
        var form_build_id = $("input[name='form_build_id']").val();
        alter(table_name, fields, form_build_id);
    });
    
    $(".user-logins").click(function () {
        var controlElement = $('input[name=chosen]:checked');
        if(controlElement.length == 0){
           alertMessage(_t(79), _t(54), BootstrapDialog.TYPE_WARNING);
           return; 
        }
        var element = controlElement.parents("tr").find("td")[2];
        var username = $(element).html();
        window.location = root+"/admin/table/WATCHDOG?VALUE="+username;
    });
    
    $(".edit-user").click(function () {
        var controlElement = $('input[name=chosen]:checked');
        if(controlElement.length == 0){
           alertMessage(_t(79), _t(54), BootstrapDialog.TYPE_WARNING);
           return; 
        }
        var element = controlElement.parents("tr").find("td")[2];
        var username = $(element).html();
        window.location = root+"/admin/user/"+username;
    });
    
    $(".delete-user").click(function () {
        var controlElement = $('input[name=chosen]:checked');
        if(controlElement.length == 0){
           alertMessage(_t(79), _t(54), BootstrapDialog.TYPE_WARNING);
           return; 
        }
        var element = controlElement.parents("tr").find("td")[2];
        var username = $(element).html();
        alertMessage(_t(98, [username]), _t(54), BootstrapDialog.TYPE_DANGER, function (){
            $.ajax({
                url : root + "/admin/ajax/delete_user",
                type: 'POST',
                data : {"USERNAME" : username},
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                     controlElement.parents("tr").remove();
                }
             });
        });
    });
    
    $(".remove-role").click(function () {
        var controlElement = $('input[name=chosen]:checked');
        if(controlElement.length == 0){
           alertMessage(_t(79), _t(54), BootstrapDialog.TYPE_WARNING);
           return; 
        }
        var element = controlElement.parents("tr").find("td")[2];
        var role = $(element).html();
        $.ajax({
             url : root + "/admin/ajax/remove_role",
             type: 'POST',
             dataType: 'json',
             data: {ROLE: role},
             success: function (data, textStatus, jqXHR) {
                     alertMessage(data.msg, _t(52), BootstrapDialog.TYPE_INFO);
                     controlElement.parents("tr").remove();
             }
         });
    });
    
    $("#logout").click(function (){
        $.ajax({
            url: root+"/admin/ajax/logout",
            success:function(response){
                window.location = root;
            } 
        });
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
        var columnname = columns[$(this).index() - 1];
        var activetable = get_active_table();
        var fk_val = $(this).text();
        window.location = root +"/admin/table/"+activetable+"/"+columnname+"/"+fk_val;
    });
    
    $(document).on("mouseenter", ".dbl_click_fk",function () {
        if($(this).find("span").attr("data-content")){
            return;
        }
        var activeelement = $("<span>"+$(this).html()+"</span>")
        $(this).html(activeelement);
        var columnname = columns[$(this).index() - 1];
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
         var element = $(this);
         alertMessage(_t(81), _t(54), BootstrapDialog.TYPE_DANGER, function () {
            do_control_operation(element, "delete", "glyphicon-remove");
        });
     });
     
     $(document).on("click",".rowbrowse",function(){
         var id = $(this).parents("tr").find("td:eq(1)").text();
         window.location = root + "/admin/insert/"+get_active_table()+"/"+id;
     });
});

function alter(table_name, fields, form_build_id){
    $.ajax({
            url: root + "/admin/ajax/alter_table",
            type: 'POST',
            dataType: 'json',
            data: {tablename : table_name, fields: fields, form_build_id: form_build_id},
            success: function (data, textStatus, jqXHR) {
                    alertMessage(data.message, _t(52), BootstrapDialog.TYPE_INFO, function () {
                        location.reload();
                    });
            }
        });
}

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

function define(table_name, fields, form_build_id){
    $.ajax({
            url: root + "/admin/ajax/new_table_definition",
            type: 'POST',
            dataType: 'json',
            data: {table_name : table_name, fields: fields, form_build_id: form_build_id },
            success: function (data) {
                    alertMessage(data.message, _t(52), BootstrapDialog.TYPE_INFO, function () {
                        window.location += "/"+table_name;
                    });
            }
        });
}
var type_conrolchange = function (){
   var value = $(this).val();
   var optionalfield = $(this).parents(".row.content").find("div.optional");
   var optionalexplainfield = $(this).parents(".row.content").find("div.optionalexplain");

   if(value === "VARCHAR"){
       optionalexplainfield.html(_t(62))
       optionalfield.html("<input type='number' class='form-control' max='255' min='0' name='field_length' value='255'/>");
   }else if(value === "MUL"){
       $.ajax({
       url : root + "/admin/ajax/get_table_list",
        dataType: 'json',
       success: function (data, textStatus, jqXHR) {
            var selectMenu = $('<select class="form-control selectpicker" data-live-search="true" name="mul_table"></select>');
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

var removefield = function () {
       $(this).parents(".field_definition, .comparation_definition, .select_definition").remove();
   }
   
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

function send_insert_form_to_service(currentelement ,service_name) {
    var data = new FormData();
    $("#insertForm").serializeArray().map(function(x){data.append(x.name, x.value)});
    var files = $("#insertForm input[type='file']");
    files.each(function (i, element){
        data.append(element.name, element.files[0]);
    });
    currentelement.after('<span class="glyphicon glyphicon-refresh" style="font-size:30px;"></span>');
    currentelement.hide();
    var icon = "glyphicon-ok";
    $.ajax({
        url: root+"/admin/ajax/"+service_name,
        type: "POST",
        processData:false,
        contentType:false,
        data : data,
        error: function(){
            icon = "glyphicon-remove";
        },
        complete : function(response){
            currentelement.next().remove();
            currentelement.after('<span class="glyphicon '+icon+'" style="font-size:30px;"></span>');        
            setTimeout(function (){
                currentelement.show();
                currentelement.next().remove();
            }, 1500);
        }
    });
}

function do_control_operation(controlElement, operation, glypicon_class){
    var refresh = "glyphicon-refresh";
    controlElement.removeClass(glypicon_class);
    controlElement.addClass(refresh);
    var data = new FormData();
    var values = controlElement.parents("tr").find("td").each(function(i, td){
        if(i===0) return;
        data.append(columns[i-1], $(td).clone().find("a").remove().end().text() );
    });
    if(operation !== "delete"){
        var files = controlElement.parents("tr").find("input[type='file']");
        files.each(function (i, element){
            data.append(columns[$(element).parents("td").index()-1], element.files[0]);
        });
    }
    var activetable = get_active_table();
    data.append("table", activetable);
    $.ajax({
        url: root+"/admin/ajax/"+operation,
        type: "POST",
        processData:false,
        contentType:false,
        data : data,
        error: function(){
            controlElement.removeClass(refresh);
            controlElement.addClass(glypicon_class);
        },
        success : function(response){
            controlElement.removeClass(refresh);
            controlElement.addClass("glyphicon-ok");
            if(operation === "delete"){
                setTimeout(function (){
                    controlElement.parents("tr").remove();
                }, 1000);
            }
        }
    });
}

function create_input(element, inputtype){
    var input = $('<input type="'+inputtype+'" value="'+element.text()+'"/>');
    element.append(input);
    input.focus();
    input.focusout(function(){
        $(this).parent().html($(this).val());
    });
}

var columns = [];
function array_to_table(tableData, skeleton, add_controls = true) {
    var table = $('<table class="content" id="result_table"></table>');
    var thead = $('<thead></thead>');
    var headrow = $('<tr class="head"></tr>');
    if(add_controls) {
        headrow.append('<td></td>');
    }
    var inputtypes = [];
    columns = [];
    $(skeleton).each(function (i, columnData){
        headrow.append('<td>'+columnData[0]+'</td>');
        columns.push(columnData[0]);
        if(i!=0){
            inputtypes.push(getClassByDataType(columnData[1],columnData[3]));
        }else{
            inputtypes.push("");
        }
    });
    thead.append(headrow);
    table.append(thead);
    var tbody = $("<tbody></tbody>");
    $(tableData).each(function (i, rowData) {
        var row = $('<tr></tr>');
        row.append($('<td>'+getControls()+'</td>')); 
        $(rowData).each(function (j, cellData) {
            if(inputtypes[j] !== "dbl_click_file"){
                row.append($('<td class="'+inputtypes[j]+'">'+cellData+'</td>'));
            }else{
                row.append($('<td class="'+inputtypes[j]+'"><a target="_blank" href="'+root + "/files/uploaded/"+get_active_table()+"/"+skeleton[j][0]+"/"+cellData+'">'+cellData+'</td>'));
            }
        });
        tbody.append(row);
    });
    table.append(tbody);
    return table;
}

function getControls(){
    return '<a href="#" title="'+_t(82)+'"><span class="glyphicon glyphicon-remove rowdelete core-control"></span></a>'+
           '<a href="#" title="'+_t(83)+'"><span class="glyphicon glyphicon-eye-open rowbrowse core-control"></span> </a>';
}

function getClassByDataType(type = "", constraint = ""){
    if (constraint.includes("MUL")) {
        return "dbl_click_fk";
    } else if(type.includes("int")){
        return 'dbl_click_num';
    } else if(type.includes("datetime")){
        return 'dbl_click_datetime';
    } else if(type.includes("tinytext")){
        return 'dbl_click_file';
    } else if(type.includes("text")){
        return 'dbl_click_text_long';
    }
    return 'dbl_click_text';
}
