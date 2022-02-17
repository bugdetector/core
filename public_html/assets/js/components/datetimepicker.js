$(function ($) {
    loadTimeInput();
    loadDateInput();
    loadDateTimeInput();
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
            locale: language
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
            }
        });
        el.val(default_value);
    });
}