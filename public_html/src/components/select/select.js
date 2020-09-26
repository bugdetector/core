import {$} from "../jquery/jquery";
import "bootstrap-select-v4";
import "./select.scss";


window.selectpicker = function(element, func = $){
    $(element).selectpicker(func);
}