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
var Heart = /** @class */ (function (_super) {
    __extends(Heart, _super);
    function Heart() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Heart.prototype.render = function () {
        return (React.createElement("g", { id: 'Yseqlogo/Heart-text-\uD83D\uDE00', transform: 'translate(98.000000, 257.000000)', fillOpacity: '1.0' },
	    React.createElement("text", {style: {fontSize: 20, fontWeight: "bold"}, fill:'#800000'}, 'I   YSEQ'),
	    React.createElement("text", {style: {fontSize: 20, fontWeight: "bold"}, fill:'#FF0000'}, ' ♥')
	));
    };
    Heart.optionValue = 'Heart';
    return Heart;
}(React.Component));
exports.default = Heart;
