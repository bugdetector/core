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

/***/ "./base_theme/src/forms/tree_form.js":
/*!*******************************************!*\
  !*** ./base_theme/src/forms/tree_form.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function($){$(document).on("click",".add-new-node",function(e){e.preventDefault();var templateCard=$("#node_card_template").children().first().clone();var index=$(".new-node-card").length;var parent=$(this).data("parent");var fieldName=$(this).data("field-name");templateCard.addClass("new-node-card");templateCard.find(".card-header").attr("href","#new-node-card-".concat(index));templateCard.find(".collapse").attr("id","new-node-card-".concat(index));templateCard.find(".field").attr("name","tree[new-".concat(index,"][").concat(fieldName,"]"));templateCard.find(".parent").attr("name","tree[new-".concat(index,"][parent]")).val(parent);templateCard.find(".add-new-node").hide();templateCard.find(".remove-node").attr("data-node","new-".concat(index));templateCard.attr("data-parent","new-".concat(index));$("#parent-"+parent).append(templateCard);var selectpicker=templateCard.find(".bootstrap-select");if(selectpicker.length>0){selectpicker.replaceWith(selectpicker.find("select"));setTimeout(function(){window.selectpicker(templateCard.find("select"));},200);}});$(document).on("dragend",".node-card",function(e){var item=$(e.target);var parent=item.parent().closest(".node-card").data("parent");item.find(".parent").first().val(parent);});$(document).on("click",".remove-node",function(e){e.preventDefault();var nodeId=$(this).data("node");var serviceUrl=$(this).data("service-url");bootbox.confirm(_t("node_remove_warning"),function(result){if(result){$.ajax({url:serviceUrl,method:"post",data:{nodeId:nodeId},success:function success(){$(".node-card[data-parent='".concat(nodeId,"']")).fadeOut(500).delay(500,function(){$(this).remove();});}});}});});});

/***/ }),

/***/ 13:
/*!*************************************************!*\
  !*** multi ./base_theme/src/forms/tree_form.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./base_theme/src/forms/tree_form.js */"./base_theme/src/forms/tree_form.js");


/***/ })

/******/ });
//# sourceMappingURL=tree_form.js.map