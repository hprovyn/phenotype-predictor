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
var Vampire = /** @class */ (function (_super) {
    __extends(Vampire, _super);
    function Vampire() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Vampire.prototype.render = function () {
        return (React.createElement("g", { id: 'Eyes/Vampire-\uD83D\uDE00', transform: 'translate(0.000000, 8.000000)', fillOpacity: '0.599999964' },
            React.createElement("circle", { id: 'Eye', cx: '30', cy: '22', r: '6', fill: '#FFFF00' }),
            React.createElement("circle", { id: 'Eye', cx: '82', cy: '22', r: '6', fill: '#FFFF00' })));
    };
    Vampire.optionValue = 'Vampire';
    return Vampire;
}(React.Component));
exports.default = Vampire;
