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
var Moderate = /** @class */ (function (_super) {
    __extends(Moderate, _super);
    function Moderate() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Moderate.prototype.render = function () {
        return (React.createElement("g", { id: 'Freckles/Moderate-\uD83D\uDE00', transform: 'translate(0.000000, 8.000000)', fillOpacity: '0.599999964' },
            React.createElement("circle", { id: 'Freckle', cx: '23', cy: '45', r: '2', fill: '#4F372B'}),
            React.createElement("circle", { id: 'Freckle', cx: '31', cy: '39', r: '2', fill: '#4F372B'}),
            React.createElement("circle", { id: 'Freckle', cx: '36', cy: '41', r: '2', fill: '#4F372B'}),
            React.createElement("circle", { id: 'Freckle', cx: '52', cy: '34', r: '2', fill: '#4F372B'}),
            React.createElement("circle", { id: 'Freckle', cx: '57', cy: '28', r: '2', fill: '#4F372B'}),
            React.createElement("circle", { id: 'Freckle', cx: '74', cy: '38', r: '2', fill: '#4F372B'}),
            React.createElement("circle", { id: 'Freckle', cx: '86', cy: '41', r: '2', fill: '#4F372B'})));
    };
    Moderate.optionValue = 'Moderate';
    return Moderate;
}(React.Component));
exports.default = Moderate;
