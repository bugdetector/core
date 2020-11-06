import moment from 'moment';
export default (window.moment = moment);
require("tempusdominus-bootstrap-4");
import 'tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css';

$(function ($) {
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
    $(".dateinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        el.datetimepicker({
            format: "YYYY-MM-DD",
            locale: language
        });
        el.val(default_value);
    });
    $(".datetimeinput").each(function (i, el) {
        el = $(el);
        let default_value = el.val();
        el.val("");
        el.datetimepicker({
            format: "YYYY-MM-DD HH:mm",
            locale: language
        });
        el.val(default_value);
    });
})