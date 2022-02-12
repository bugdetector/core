$(function ($) {
    loadTimeInput();
    loadDateInput();
    loadDateTimeInput();
})

window.loadTimeInput = function(){
    $(".timeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        flatpickr(el,{
            format: "H:i",
            noCalendar: true,
            time_24hr: true,
            locale: language
        });
        el.val(default_value);
    });
}

window.loadDateInput = function(){
    $(".dateinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        flatpickr(el, {
            dateFormat: "d-m-Y",
            locale: language
        });
        el.val(default_value);
    });
}

window.loadDateTimeInput = function(){
    $(".datetimeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        flatpickr(el,{
            format: "d-m-Y H:i",
            enableTime: true,
            time_24hr: true,
            locale: language,
            icons: {
                time: "fa fa-clock"
            }
        });
        el.val(default_value);
    });
}