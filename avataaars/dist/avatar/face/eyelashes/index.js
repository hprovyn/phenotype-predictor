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
var FemaleLashes_1 = require("./FemaleLashes");
var Default_1 = require("./Default");
var options_1 = require("../../../options");
var Eyelashes = /** @class */ (function (_super) {
    __extends(Eyelashes, _super);
    function Eyelashes() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Eyelashes.prototype.render = function () {
        return (React.createElement(options_1.Selector, { defaultOption: Default_1.default, option: options_1.EyelashesOption },
            React.createElement(FemaleLashes_1.default, null),
            React.createElement(Default_1.default, null)))
    };
    return Eyelashes;
}(React.Component));
exports.default = Eyelashes;
