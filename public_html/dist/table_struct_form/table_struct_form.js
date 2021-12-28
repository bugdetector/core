/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "../../";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 13);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./base_theme/src/forms/table_struct_form.js":
/*!***************************************************!*\
  !*** ./base_theme/src/forms/table_struct_form.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function($){$(document).on("click",".newfield",function(){var button=$(this);var index=$(".column_definition").length;$.ajax({url:"".concat(root,"/admin/ajax/getColumnDefinition"),method:"post",data:{index:index},success:function success(data){var row=$(data);button.parents(".row.mt-4.mb-5").prev().append(row);//selectpicker(row.find(".selectpicker"));
row.find("input[name = 'fields[".concat(index,"][field_name]']")).focus();row.find("input[type='checkbox']").each(function(i,element){loadCheckbox(element);});}});});$(document).on("change","select.type-control",function(){var value=$(this).val();if(value==="short_text"){$(this).parents(".column_definition").find(".field_length").parent().parent().removeClass("d-none");}else{$(this).parents(".column_definition").find(".field_length").parent().parent().addClass("d-none");}if(value==="table_reference"){$(this).parents(".column_definition").find(".reference_table").parent().parent().removeClass("d-none");}else{$(this).parents(".column_definition").find(".reference_table").parent().parent().addClass("d-none");}if(value==="enumarated_list"){$(this).parents(".column_definition").find(".list_values").parent().parent().removeClass("d-none");}else{$(this).parents(".column_definition").find(".list_values").parent().parent().addClass("d-none");}});$(document).on("click",".removefield",function(e){e.preventDefault();$(this).parents(".column_definition").remove();});$(document).on("click",".dropfield",function(e){e.preventDefault();var tablename=$("input[name='table_name']").val();var column=$(this).parents(".column_definition").find(".column_name").val();var row=$(this).parents(".column_definition");alert({message:_t("field_drop_accept",[column]),okLabel:_t("yes"),callback:function callback(){$.ajax({url:"".concat(root,"/admin/ajax/dropfield"),method:"post",dataType:"json",data:{tablename:tablename,column:column},success:function success(data){row.fadeOut(1000);}});}});});$("#new_table").on("submit",function(){if($(".has-error input:enabled").length!==0){alert({message:_t("check_wrong_fields")});return false;}});});

/***/ }),

/***/ 13:
/*!*********************************************************!*\
  !*** multi ./base_theme/src/forms/table_struct_form.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./base_theme/src/forms/table_struct_form.js */"./base_theme/src/forms/table_struct_form.js");


/***/ })

/******/ });
//# sourceMappingURL=table_struct_form.js.map