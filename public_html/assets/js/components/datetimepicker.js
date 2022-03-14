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
        flatpickr(el, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            defaultDate: default_value,
            locale: language,
            allowInput: true
        });
        el.val(default_value);
    });
}

window.loadDateInput = function () {
    $(".dateinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        flatpickr(el, {
            dateFormat: "d-m-Y",
            locale: language,
            defaultDate: default_value,
            allowInput: true,
        });
        el.val(default_value);
    });
}

window.loadDateTimeInput = function () {
    $(".datetimeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        flatpickr(el, {
            dateFormat: "d-m-Y H:i",
            enableTime: true,
            time_24hr: true,
            locale: language,
            defaultDate: default_value,
            icons: {
                time: "fa fa-clock"
            },
            allowInput: true
        });
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
            locale: { rangeSeparator: ' & ' },
            defaultDate: default_value,
            mode: "range",
            allowInput: true
        });
    })
}