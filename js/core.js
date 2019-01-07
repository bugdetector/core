$.expr[':'].textEquals = function(el, i, m) {
    var searchText = m[3];
    var match = $(el).text().trim().match("^" + searchText + "$")
    return match && match.length > 0;
}
$(document).ready(function () {
   $(document).on("keyup", ".uppercase_filter", uppercase_filter);
   $(document).on("keyup", ".lowercase_filter", lowercase_filter);
   $(".datetimeinput").datetimepicker();
    
    $(".search-field").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#main_content table tr:not('.head')").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
     
     $('.search-field').keyup(function(e){//on press enter
            if(e.keyCode == 13)
            {
                $(".btn-search").click();
            }
        });
    $(".table-search-field").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $(".tablelist").filter(function() {
          $(this).toggle($(this).find("a").text().toLowerCase().includes(value));
      });
    });
    
    $(".summernote").summernote({
        lang: language
    });
    
    $(".btn-search").click(function () {
        var link = "";
        var search_text = $(".search-field").val();
        search_text.split(" ").forEach(function (item){
            if(item && !link.includes(item)){
                link += (link ? "&" : "")+item;
            }
        });
        window.location = "?"+link;
    });
    
    $(document).ready( function() {
        $(".file-field").click(function() {
            var file_input = $(this).next("input");
            var file_path = $(this).next().next().children("input");
            file_input.change(function (){
                if(file_input.get(0).files){
                    var file = file_input.get(0).files[0];
                    file_path.val(file.name);
                }
            });
            file_input.click();
        });
    })

    $(document).submit(function () {
        $(".loader").removeClass("hidden");
    }); 
    $(document).ajaxSend(function(){
        $(".loader").removeClass("hidden");
    });
    $(document).ajaxComplete(function () {
        if($.active == 1){
            $(".loader").addClass("hidden");
        }
    })
    
    $(document).ajaxError(function (evt, request, settings){
        var data=request.responseText;
        if (data.length>0) {
            try{
                var resp=$.parseJSON(data);
                if (resp.msg)
                {
                    alertMessage(resp.msg, _t(53), BootstrapDialog.TYPE_DANGER);
                }         
            }catch(ex){

            }
        }   
    });

    $(document).ajaxSuccess(function (evt, request, settings){

        var data=request.responseText;
        if (data.length>0) {
            try{
                var resp=$.parseJSON(data);
                if (resp.msg) {
                    alertMessage(resp.msg, _t(52), BootstrapDialog.TYPE_INFO);
                }           
            }catch(ex){

            }
        }   
    });
});

function alertMessage(message, title = _t(54) , type = BootstrapDialog.TYPE_WARNING, callback = function () {}){
       BootstrapDialog.show({
            type : type,
            title: title,
            message: message,
            closable: false,
            buttons : [
                {
                    label : _t(76),
                    cssClass: "btn-danger",
                    action : function (dialog){
                        dialog.close();
                    }
                },
                {
                    label : _t(77),
                    action : function (dialog){
                        callback();
                        dialog.close();
                    }
                }
            ]
        });
   }

var lowercase_filter = function () {
    $(this).val($(this).val().toLowerCase());
    if(!($(this).val().match(/^[a-z1-9_\s]+$/))){
        $(this).parent().addClass("has-error");
    }else{
        $(this).parent().removeClass("has-error");
    }
}
   
var uppercase_filter = function () {
    $(this).val($(this).val().toUpperCase());
    if(!($(this).val().match(/^[A-Z1-9_\s]+$/))){
        $(this).parent().addClass("has-error");
    }else{
        $(this).parent().removeClass("has-error");
    }
}

function _t(id, arguments){
    if(arguments){
        return translations[id].format(arguments);
    }
    return translations[id];
}

String.prototype.format = function () {
    var a = this, b;
    for (b in arguments) {
        a = a.replace(/%[a-z]/, arguments[b]);
    }
    return a; // Make chainable
};