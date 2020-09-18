import {$} from "../jquery/jquery";
import "bootstrap-daterangepicker";
import "bootstrap-daterangepicker/daterangepicker.css";

$(function($){
    $(".daterangeinput").daterangepicker({
        autoUpdateInput: false,
        locale: {
            "format": "YYYY-MM-DD",
            "separator": " & "
        }
    }).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' & ' + picker.endDate.format('YYYY-MM-DD'));
    }).on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    })
})