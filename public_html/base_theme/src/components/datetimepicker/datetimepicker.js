import moment from 'moment';
export default (window.moment = moment);
require("tempusdominus-bootstrap-4");
import 'tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css';

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
        el.datetimepicker({
            format: "HH:mm",
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
        el.datetimepicker({
            format: "DD-MM-YYYY",
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
        el.datetimepicker({
            format: "DD-MM-YYYY HH:mm",
            locale: language,
            icons: {
                time: "fa fa-clock"
            }
        });
        el.val(default_value);
    });
}