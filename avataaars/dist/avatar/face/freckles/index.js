"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    }
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
var React = require("react");
var Default_1 = require("./Default");
var Severe_1 = require("./Severe");
var Moderate_1 = require("./Moderate");
var Light_1 = require("./Light");
var options_1 = require("../../../options");
var Freckles = /** @class */ (function (_super) {
    __extends(Freckles, _super);
    function Freckles() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Freckles.prototype.render = function () {
        return (React.createElement(options_1.Selector, { defaultOption: Default_1.default, option: options_1.FrecklesOption },
            React.createElement(Default_1.default, null),
            React.createElement(Light_1.default, null),
	    React.createElement(Moderate_1.default, null),
            React.createElement(Severe_1.default, null)));
    };
    return Freckles;
}(React.Component));
exports.default = Freckles;
