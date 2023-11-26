$(function ($) {
    loadTimeInput();
    loadDateInput();
    loadDateTimeInput();
    loadDateRangeInput();
})

window.loadTimeInput = function () {
    $(".timeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        let instance = flatpickr(el, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            defaultDate: default_value,
            locale: language,
            allowInput: true
        });
        el.on("input", function(e){
            let dateMoment = moment(el.val(), "HH:mm", true);
            if(dateMoment.isValid()){
                instance.setDate(el.val());
            }
        })
        el.val(default_value);
    });
}

window.loadDateInput = function () {
    $(".dateinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        let instance = flatpickr(el, {
            dateFormat: "d-m-Y",
            locale: { 
                firstDayOfWeek: 1
            },
            defaultDate: default_value,
            allowInput: true,
        });
        el.on("input", function(e){
            let dateMoment = moment(el.val(), "DD-MM-YYYY", true);
            if(dateMoment.isValid()){
                instance.setDate(el.val());
            }
        })
        el.val(default_value);
    });
}

window.loadDateTimeInput = function () {
    $(".datetimeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        let instance = flatpickr(el, {
            dateFormat: "d-m-Y H:i",
            enableTime: true,
            time_24hr: true,
            locale: { 
                firstDayOfWeek: 1
            },
            defaultDate: default_value,
            icons: {
                time: "fa fa-clock"
            },
            allowInput: true
        });
        el.on("input", function(e){
            let dateMoment = moment(el.val(), "DD-MM-YYYY HH:mm", true);
            if(dateMoment.isValid()){
                instance.setDate(el.val());
            }
        })
        el.val(default_value);
    });
}

window.loadDateRangeInput = function(){
    $(".daterangeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        flatpickr(el, {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            locale: { 
                rangeSeparator: ' & ',
                firstDayOfWeek: 1
            },
            defaultDate: default_value,
            mode: "range",
            allowInput: true
        });
    })
}