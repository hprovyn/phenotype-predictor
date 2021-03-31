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
var Blue = /** @class */ (function (_super) {
    __extends(Blue, _super);
    function Blue() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Blue.prototype.render = function () {
        return (React.createElement("g", { id: 'Eyes/Blue-\uD83D\uDE00', transform: 'translate(0.000000, 8.000000)', fillOpacity: '1.0' },
            React.createElement("path", { id: 'Eye', d:'M 18 22 A 13 10, 0 0 1, 43 22 A 14 10, 0 0 1, 18 22', stroke: "#000000", fill: '#FFFFFF' }),
	    React.createElement("path", { id: 'Eye', d:'M 18 22 A 13 10, 0 0 1, 43 22', stroke: '#000000', strokeWidth: '2', fillOpacity: '0.0'}),
            React.createElement("circle", { id: 'Eye', cx: '30', cy: '21', r: '6', stroke: "#000000", fill: '#4287F5', strokeWidth:'0.5' }),
            React.createElement("circle", { id: 'Eye', cx: '30', cy: '21', r: '2.5', fill: '#000000' }),
            React.createElement("path", { id: 'Eye', d:'M 69 22 A 13 10, 0 0 1, 94 22 A 14 10, 0 0 1, 69 22', stroke: "#000000", fill: '#FFFFFF' }),
	    React.createElement("path", { id: 'Eye', d:'M 69 22 A 13 10, 0 0 1, 94 22', stroke: '#000000', strokeWidth: '1.5', fillOpacity: '0.0'}),
            React.createElement("circle", { id: 'Eye', cx: '82', cy: '21', r: '6', stroke: "#000000", fill: '#4287F5', strokeWidth:'0.5' }),
            React.createElement("circle", { id: 'Eye', cx: '82', cy: '21', r: '2.5', fill: '#000000' })
	))
    };
    Blue.optionValue = 'Blue';
    return Blue;
}(React.Component));
exports.default = Blue;
