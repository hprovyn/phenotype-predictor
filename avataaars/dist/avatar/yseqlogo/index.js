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
var Heart_1 = require("./Heart");
var options_1 = require("../../options");
var Yseqlogo = /** @class */ (function (_super) {
    __extends(Yseqlogo, _super);
    function Yseqlogo() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Yseqlogo.prototype.render = function () {
        return (React.createElement(options_1.Selector, { option: options_1.YseqlogoOption, defaultOption: Heart_1.default },
            React.createElement(Heart_1.default, null)))
    };
    return Yseqlogo;
}(React.Component));
exports.default = Yseqlogo;
