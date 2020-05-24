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
/******/ 	__webpack_require__.p = "/js/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./js/gadgetbridge.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./js/gadgetbridge.js":
/*!****************************!*\
  !*** ./js/gadgetbridge.js ***!
  \****************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("/**\n * @copyright (c) 2017 Joas Schilling <coding@schilljs.com>\n *\n * @author Joas Schilling <coding@schilljs.com>\n *\n * This file is licensed under the Affero General Public License version 3 or\n * later. See the COPYING file.\n */\n(function(OC, OCA, _) {\n\tOCA = OCA || {};\n\n\tOCA.GadgetBridge = {\n\t\tdatabaseFileId: 0,\n\t\tselectedDevice: 0,\n\t\tlastRawKind: 0,\n\n\t\t_deviceTemplate: null,\n\t\t_deviceHTML: '' +\n\t\t'<li class=\"device\" data-device-id=\"{{_id}}\">' +\n\t\t\t'<a id=\"import-data\" href=\"#\">' +\n\t\t\t\t'<img alt=\"\" src=\"<?php print_unescaped(image_path(\\'core\\', \\'actions/upload.svg\\')); ?>\">' +\n\t\t\t\t'<span>{{NAME}}</span> <em>({{IDENTIFIER}})</em>' +\n\t\t\t'</a>' +\n\t\t'</li>',\n\n\t\tinitialise: function() {\n\t\t\t$('#import-data').on('click', _.bind(this._importButtonOnClick, this));\n\t\t\t// this._deviceTemplate = Handlebars.compile(this._deviceHTML);\n\t\t\tthis._selectedDatabase($('#app-content').attr('data-database-id'), $('#app-content').attr('data-database-path'));\n\t\t},\n\n\t\t_importButtonOnClick: function(e) {\n\t\t\te.preventDefault();\n\t\t\tOCdialogs.filepicker(\n\t\t\t\tt('gadgetbridge', 'Choose a file to import'),\n\t\t\t\t_.bind(this._filePickerCallback, this)\n\t\t\t)\n\t\t},\n\n\t\t_selectedDatabase: function(id, path) {\n\t\t\tthis.databaseFileId = id;\n\t\t\tthis.databaseFilePath = path;\n\t\t\t$('.settings-caption').text(this.databaseFilePath);\n\t\t\t$('#app-content').attr('data-database-id', this.databaseFileId);\n\t\t\t$('#app-content').attr('data-database-path', this.databaseFilePath);\n\n\t\t\tif (this.databaseFileId > 0) {\n\t\t\t\tthis._loadDevices();\n\t\t\t}\n\t\t},\n\n\t\t_filePickerCallback: function(path) {\n\t\t\tvar self = this;\n\n\t\t\t$.ajax({\n\t\t\t\turl: OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + 'database',\n\t\t\t\ttype: 'POST',\n\t\t\t\tbeforeSend: function (request) {\n\t\t\t\t\trequest.setRequestHeader('Accept', 'application/json');\n\t\t\t\t},\n\t\t\t\tdata: {\n\t\t\t\t\tpath: path\n\t\t\t\t},\n\t\t\t\tsuccess: function(result) {\n\t\t\t\t\tself._selectedDatabase(\n\t\t\t\t\t\tresult.ocs.data.fileId,\n\t\t\t\t\t\tpath.substring(1) // Remove leading slash\n\t\t\t\t\t);\n\t\t\t\t},\n\t\t\t\terror: function() {\n\t\t\t\t\tOC.Notification. showTemporary(t('gadgetbridge', 'The selected file is not a readable Gadgetbridge database'));\n\t\t\t\t}\n\t\t\t});\n\t\t},\n\n\t\t_loadDevices: function() {\n\t\t\tvar self = this;\n\t\t\t$.ajax({\n\t\t\t\turl: OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices',\n\t\t\t\tbeforeSend: function (request) {\n\t\t\t\t\trequest.setRequestHeader('Accept', 'application/json');\n\t\t\t\t},\n\t\t\t\tsuccess: function(result) {\n\t\t\t\t\t// TODO Remove previous devices\n\n\n\t\t\t\t\tvar singleDeviceDatabase = result.ocs.data.length === 1;\n\n\t\t\t\t\t_.each(result.ocs.data, function(device) {\n\t\t\t\t\t\tvar $device = $(self._deviceTemplate(device));\n\t\t\t\t\t\t$device.on('click', function() {\n\t\t\t\t\t\t\tself.selectedDevice = $(this).attr('data-device-id');\n\t\t\t\t\t\t\t$(this).addClass('active');\n\t\t\t\t\t\t\tif (self.selectedDevice !== $(this).attr('data-device-id')) {\n\t\t\t\t\t\t\t\tself._loadDevice(moment().format('YYYY/MM/DD/HH/mm'));\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t});\n\t\t\t\t\t\t$('#app-navigation ul').append($device);\n\n\t\t\t\t\t\tif (singleDeviceDatabase) {\n\t\t\t\t\t\t\tself.selectedDevice = device._id;\n\t\t\t\t\t\t\tself._loadDevice(moment().format('YYYY/MM/DD/HH/mm'));\n\t\t\t\t\t\t\t$device.addClass('active');\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\t\t\t\t},\n\t\t\t\terror: function() {\n\t\t\t\t\tOC.Notification. showTemporary(t('gadgetbridge', 'The selected file is not a readable Gadgetbridge database'));\n\t\t\t\t}\n\t\t\t});\n\t\t},\n\n\t\t_loadDevice: function(date) {\n\t\t\tvar self = this;\n\t\t\t$.ajax({\n\t\t\t\turl: OC.linkToOCS('apps/gadgetbridge/api/v1', 2) + this.databaseFileId + '/devices/' + self.selectedDevice + '/samples/' + date,\n\t\t\t\tbeforeSend: function (request) {\n\t\t\t\t\trequest.setRequestHeader('Accept', 'application/json');\n\t\t\t\t},\n\t\t\t\tsuccess: function(result) {\n\t\t\t\t\tvar labelData = [],\n\t\t\t\t\t\tkindData = [],\n\t\t\t\t\t\tstepData = [],\n\t\t\t\t\t\tactivityColor = [],\n\t\t\t\t\t\theartRate = [],\n\t\t\t\t\t\tkind = 0,\n\t\t\t\t\t\tlastHeartRate = null;\n\t\t\t\t\t_.each(result.ocs.data, function(tick) {\n\t\t\t\t\t\tlabelData.push(moment(tick.TIMESTAMP * 1000).calendar());\n\t\t\t\t\t\tkind = parseInt(tick.RAW_KIND, 10);\n\t\t\t\t\t\tkindData.push(kind * 10);\n\t\t\t\t\t\tactivityColor.push(self._getActivityColor(kind));\n\t\t\t\t\t\tstepData.push(self._getSteps(kind, tick.STEPS));\n\n\t\t\t\t\t\tif (tick.HEART_RATE > 0 && tick.HEART_RATE < 255) {\n\t\t\t\t\t\t\tlastHeartRate = tick.HEART_RATE;\n\t\t\t\t\t\t\theartRate.push(tick.HEART_RATE);\n\t\t\t\t\t\t} else if (tick.HEART_RATE > 0) {\n\t\t\t\t\t\t\theartRate.push(lastHeartRate);\n\t\t\t\t\t\t\tlastHeartRate = null;\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\theartRate.push(null);\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\n\t\t\t\t\tvar ctx = $('#steps');\n\t\t\t\t\tvar myChart = new Chart(ctx, {\n\t\t\t\t\t\ttype: 'bar',\n\t\t\t\t\t\tdata: {\n\t\t\t\t\t\t\tlabels: labelData,\n\t\t\t\t\t\t\tdatasets: [\n\t\t\t\t\t\t\t\t{\n\t\t\t\t\t\t\t\t\tlabel: 'Activity',\n\t\t\t\t\t\t\t\t\tdata: stepData,\n\t\t\t\t\t\t\t\t\tbackgroundColor: activityColor,\n\t\t\t\t\t\t\t\t\tbarThickness: 100\n\t\t\t\t\t\t\t\t},\n\t\t\t\t\t\t\t\t{\n\t\t\t\t\t\t\t\t\tlabel: 'Heart rate',\n\t\t\t\t\t\t\t\t\tdata: heartRate,\n\t\t\t\t\t\t\t\t\tbackgroundColor: '#ffa500',\n\t\t\t\t\t\t\t\t\tborderColor: '#ffa500',\n\t\t\t\t\t\t\t\t\ttype: 'line',\n\t\t\t\t\t\t\t\t\tpointStyle: 'rect',\n\t\t\t\t\t\t\t\t\tpointRadius: 0,\n\t\t\t\t\t\t\t\t\tfill: false\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t]\n\t\t\t\t\t\t},\n\t\t\t\t\t\toptions: {\n\t\t\t\t\t\t\tlegend: {\n\t\t\t\t\t\t\t\tdisplay: false\n\t\t\t\t\t\t\t},\n\t\t\t\t\t\t\tscales: {\n\t\t\t\t\t\t\t\txAxes: [{\n\t\t\t\t\t\t\t\t\tgridLines: {\n\t\t\t\t\t\t\t\t\t\toffsetGridLines: true\n\t\t\t\t\t\t\t\t\t},\n\t\t\t\t\t\t\t\t\tstacked: true\n\t\t\t\t\t\t\t\t}],\n\t\t\t\t\t\t\t\tyAxes: [{\n\t\t\t\t\t\t\t\t\tstacked: true\n\t\t\t\t\t\t\t\t}]\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\t\t\t\t},\n\t\t\t\terror: function() {\n\t\t\t\t\tOC.Notification. showTemporary(t('gadgetbridge', 'Device data could not be loaded from the database'));\n\t\t\t\t}\n\t\t\t});\n\t\t},\n\n\t\t_getActivityColor: function(current) {\n\t\t\tswitch (current) {\n\t\t\t\tcase 1: // Activity\n\t\t\t\t\treturn '#3ADF00';\n\t\t\t\tcase 2: // Light sleep\n\t\t\t\t\treturn '#2ECCFA';\n\t\t\t\tcase 4: // Deep sleep\n\t\t\t\t\treturn '#0040FF';\n\t\t\t\tcase 8: // Not worn\n\t\t\t\t\treturn '#AAAAAA';\n\t\t\t\tdefault:\n\t\t\t\t\treturn '#AAAAAA';\n\t\t\t}\n\t\t},\n\n\t\t_getSteps: function(current, steps) {\n\t\t\tswitch (current) {\n\t\t\t\tcase 1: // Activity\n\t\t\t\tcase 2: // Light sleep\n\t\t\t\tcase 4: // Deep sleep\n\t\t\t\tcase 8: // Not worn\n\t\t\t\t\treturn Math.min(250, Math.max(10, steps));\n\t\t\t\tdefault:\n\t\t\t\t\treturn 2;\n\t\t\t}\n\t\t}\n\t};\n})(OC, OCA, _);\n\n$(document).ready(function () {\n\tOCA.GadgetBridge.initialise();\n});\n\n\n//# sourceURL=webpack:///./js/gadgetbridge.js?");

/***/ })

/******/ });