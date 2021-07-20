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
/******/ 	return __webpack_require__(__webpack_require__.s = 8);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./base_theme/src/components/collapsible_widget_card/collapsible_widget_card.js":
/*!**************************************************************************************!*\
  !*** ./base_theme/src/components/collapsible_widget_card/collapsible_widget_card.js ***!
  \**************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function($){$(document).on("click",".add-new-entity",function(e){e.preventDefault();var button=$(this);var entityName=button.data("entity");var name=button.data("name");var hiddenFields=button.data("hidden-fields");var index=$(".collapsible-widget-group[data-entity='".concat(entityName,"'] > div > div")).length;$.ajax({url:root+"/ajax/getEntityCard",method:"post",data:{entity:entityName,name:name,index:index,hiddenFields:hiddenFields},success:function success(response){response=$(response);$(".collapsible-widget-group[data-entity='".concat(entityName,"']")).append(response);response.find("select").each(function(i,el){selectpicker(el);});if(typeof window.loadTimeInput==="function"){loadTimeInput();loadDateInput();loadDateTimeInput();}}});}).on("click",".remove-entity",function(e){e.preventDefault();var button=$(this);alert({message:_t("record_remove_accept"),okLabel:_t("yes"),callback:function callback(){button.closest(".card").fadeOut(500).delay(500,function(){$(this).remove();});}});});});

/***/ }),

/***/ 8:
/*!********************************************************************************************!*\
  !*** multi ./base_theme/src/components/collapsible_widget_card/collapsible_widget_card.js ***!
  \********************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./base_theme/src/components/collapsible_widget_card/collapsible_widget_card.js */"./base_theme/src/components/collapsible_widget_card/collapsible_widget_card.js");


/***/ })

/******/ });
//# sourceMappingURL=collapsible_widget_card.js.map