/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 804:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isValue         = __webpack_require__(618)
  , isPlainFunction = __webpack_require__(205)
  , assign          = __webpack_require__(191)
  , normalizeOpts   = __webpack_require__(516)
  , contains        = __webpack_require__(981);

var d = (module.exports = function (dscr, value/*, options*/) {
	var c, e, w, options, desc;
	if (arguments.length < 2 || typeof dscr !== "string") {
		options = value;
		value = dscr;
		dscr = null;
	} else {
		options = arguments[2];
	}
	if (isValue(dscr)) {
		c = contains.call(dscr, "c");
		e = contains.call(dscr, "e");
		w = contains.call(dscr, "w");
	} else {
		c = w = true;
		e = false;
	}

	desc = { value: value, configurable: c, enumerable: e, writable: w };
	return !options ? desc : assign(normalizeOpts(options), desc);
});

d.gs = function (dscr, get, set/*, options*/) {
	var c, e, options, desc;
	if (typeof dscr !== "string") {
		options = set;
		set = get;
		get = dscr;
		dscr = null;
	} else {
		options = arguments[3];
	}
	if (!isValue(get)) {
		get = undefined;
	} else if (!isPlainFunction(get)) {
		options = get;
		get = set = undefined;
	} else if (!isValue(set)) {
		set = undefined;
	} else if (!isPlainFunction(set)) {
		options = set;
		set = undefined;
	}
	if (isValue(dscr)) {
		c = contains.call(dscr, "c");
		e = contains.call(dscr, "e");
	} else {
		c = true;
		e = false;
	}

	desc = { get: get, set: set, configurable: c, enumerable: e };
	return !options ? desc : assign(normalizeOpts(options), desc);
};


/***/ }),

/***/ 430:
/***/ ((module) => {



// eslint-disable-next-line no-empty-function
module.exports = function () {};


/***/ }),

/***/ 191:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



module.exports = __webpack_require__(560)() ? Object.assign : __webpack_require__(346);


/***/ }),

/***/ 560:
/***/ ((module) => {



module.exports = function () {
	var assign = Object.assign, obj;
	if (typeof assign !== "function") return false;
	obj = { foo: "raz" };
	assign(obj, { bar: "dwa" }, { trzy: "trzy" });
	return obj.foo + obj.bar + obj.trzy === "razdwatrzy";
};


/***/ }),

/***/ 346:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var keys  = __webpack_require__(103)
  , value = __webpack_require__(745)
  , max   = Math.max;

module.exports = function (dest, src /*, …srcn*/) {
	var error, i, length = max(arguments.length, 2), assign;
	dest = Object(value(dest));
	assign = function (key) {
		try {
			dest[key] = src[key];
		} catch (e) {
			if (!error) error = e;
		}
	};
	for (i = 1; i < length; ++i) {
		src = arguments[i];
		keys(src).forEach(assign);
	}
	if (error !== undefined) throw error;
	return dest;
};


/***/ }),

/***/ 914:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var _undefined = __webpack_require__(430)(); // Support ES3 engines

module.exports = function (val) { return val !== _undefined && val !== null; };


/***/ }),

/***/ 103:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



module.exports = __webpack_require__(446)() ? Object.keys : __webpack_require__(137);


/***/ }),

/***/ 446:
/***/ ((module) => {



module.exports = function () {
	try {
		Object.keys("primitive");
		return true;
	} catch (e) {
		return false;
	}
};


/***/ }),

/***/ 137:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isValue = __webpack_require__(914);

var keys = Object.keys;

module.exports = function (object) { return keys(isValue(object) ? Object(object) : object); };


/***/ }),

/***/ 516:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isValue = __webpack_require__(914);

var forEach = Array.prototype.forEach, create = Object.create;

var process = function (src, obj) {
	var key;
	for (key in src) obj[key] = src[key];
};

// eslint-disable-next-line no-unused-vars
module.exports = function (opts1 /*, …options*/) {
	var result = create(null);
	forEach.call(arguments, function (options) {
		if (!isValue(options)) return;
		process(Object(options), result);
	});
	return result;
};


/***/ }),

/***/ 290:
/***/ ((module) => {



module.exports = function (fn) {
	if (typeof fn !== "function") throw new TypeError(fn + " is not a function");
	return fn;
};


/***/ }),

/***/ 745:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isValue = __webpack_require__(914);

module.exports = function (value) {
	if (!isValue(value)) throw new TypeError("Cannot use null or undefined");
	return value;
};


/***/ }),

/***/ 981:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



module.exports = __webpack_require__(591)() ? String.prototype.contains : __webpack_require__(42);


/***/ }),

/***/ 591:
/***/ ((module) => {



var str = "razdwatrzy";

module.exports = function () {
	if (typeof str.contains !== "function") return false;
	return str.contains("dwa") === true && str.contains("foo") === false;
};


/***/ }),

/***/ 42:
/***/ ((module) => {



var indexOf = String.prototype.indexOf;

module.exports = function (searchString /*, position*/) {
	return indexOf.call(this, searchString, arguments[1]) > -1;
};


/***/ }),

/***/ 370:
/***/ ((module, exports, __webpack_require__) => {



var d        = __webpack_require__(804)
  , callable = __webpack_require__(290)

  , apply = Function.prototype.apply, call = Function.prototype.call
  , create = Object.create, defineProperty = Object.defineProperty
  , defineProperties = Object.defineProperties
  , hasOwnProperty = Object.prototype.hasOwnProperty
  , descriptor = { configurable: true, enumerable: false, writable: true }

  , on, once, off, emit, methods, descriptors, base;

on = function (type, listener) {
	var data;

	callable(listener);

	if (!hasOwnProperty.call(this, '__ee__')) {
		data = descriptor.value = create(null);
		defineProperty(this, '__ee__', descriptor);
		descriptor.value = null;
	} else {
		data = this.__ee__;
	}
	if (!data[type]) data[type] = listener;
	else if (typeof data[type] === 'object') data[type].push(listener);
	else data[type] = [data[type], listener];

	return this;
};

once = function (type, listener) {
	var once, self;

	callable(listener);
	self = this;
	on.call(this, type, once = function () {
		off.call(self, type, once);
		apply.call(listener, this, arguments);
	});

	once.__eeOnceListener__ = listener;
	return this;
};

off = function (type, listener) {
	var data, listeners, candidate, i;

	callable(listener);

	if (!hasOwnProperty.call(this, '__ee__')) return this;
	data = this.__ee__;
	if (!data[type]) return this;
	listeners = data[type];

	if (typeof listeners === 'object') {
		for (i = 0; (candidate = listeners[i]); ++i) {
			if ((candidate === listener) ||
					(candidate.__eeOnceListener__ === listener)) {
				if (listeners.length === 2) data[type] = listeners[i ? 0 : 1];
				else listeners.splice(i, 1);
			}
		}
	} else {
		if ((listeners === listener) ||
				(listeners.__eeOnceListener__ === listener)) {
			delete data[type];
		}
	}

	return this;
};

emit = function (type) {
	var i, l, listener, listeners, args;

	if (!hasOwnProperty.call(this, '__ee__')) return;
	listeners = this.__ee__[type];
	if (!listeners) return;

	if (typeof listeners === 'object') {
		l = arguments.length;
		args = new Array(l - 1);
		for (i = 1; i < l; ++i) args[i - 1] = arguments[i];

		listeners = listeners.slice();
		for (i = 0; (listener = listeners[i]); ++i) {
			apply.call(listener, this, args);
		}
	} else {
		switch (arguments.length) {
		case 1:
			call.call(listeners, this);
			break;
		case 2:
			call.call(listeners, this, arguments[1]);
			break;
		case 3:
			call.call(listeners, this, arguments[1], arguments[2]);
			break;
		default:
			l = arguments.length;
			args = new Array(l - 1);
			for (i = 1; i < l; ++i) {
				args[i - 1] = arguments[i];
			}
			apply.call(listeners, this, args);
		}
	}
};

methods = {
	on: on,
	once: once,
	off: off,
	emit: emit
};

descriptors = {
	on: d(on),
	once: d(once),
	off: d(off),
	emit: d(emit)
};

base = defineProperties({}, descriptors);

module.exports = exports = function (o) {
	return (o == null) ? create(base) : defineProperties(Object(o), descriptors);
};
exports.methods = methods;


/***/ }),

/***/ 372:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isPrototype = __webpack_require__(60);

module.exports = function (value) {
	if (typeof value !== "function") return false;

	if (!hasOwnProperty.call(value, "length")) return false;

	try {
		if (typeof value.length !== "number") return false;
		if (typeof value.call !== "function") return false;
		if (typeof value.apply !== "function") return false;
	} catch (error) {
		return false;
	}

	return !isPrototype(value);
};


/***/ }),

/***/ 940:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isValue = __webpack_require__(618);

// prettier-ignore
var possibleTypes = { "object": true, "function": true, "undefined": true /* document.all */ };

module.exports = function (value) {
	if (!isValue(value)) return false;
	return hasOwnProperty.call(possibleTypes, typeof value);
};


/***/ }),

/***/ 205:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isFunction = __webpack_require__(372);

var classRe = /^\s*class[\s{/}]/, functionToString = Function.prototype.toString;

module.exports = function (value) {
	if (!isFunction(value)) return false;
	if (classRe.test(functionToString.call(value))) return false;
	return true;
};


/***/ }),

/***/ 60:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var isObject = __webpack_require__(940);

module.exports = function (value) {
	if (!isObject(value)) return false;
	try {
		if (!value.constructor) return false;
		return value.constructor.prototype === value;
	} catch (error) {
		return false;
	}
};


/***/ }),

/***/ 618:
/***/ ((module) => {



// ES3 safe
var _undefined = void 0;

module.exports = function (value) { return value !== _undefined && value !== null; };


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {

// EXTERNAL MODULE: ./node_modules/event-emitter/index.js
var event_emitter = __webpack_require__(370);
var event_emitter_default = /*#__PURE__*/__webpack_require__.n(event_emitter);
;// CONCATENATED MODULE: ./src/ui.js
/**
 * @author mrdoob https://github.com/mrdoob/ui.js
 */

const ERROR_MSG = "is not an instance of UIElement.";

/**
 * UIElement
 * @param {string} tag
 */
class UIElement {
	
	constructor(tag) {

		this.dom = document.createElement(tag); 
	}

	add() {

		for (let i = 0; i < arguments.length; i++) {

			const argument = arguments[i];

			if (argument instanceof UIElement) {

				this.dom.appendChild(argument.dom);

			} else if (Array.isArray(argument)) {

				for (let j = 0; j < argument.length; j++) {

					const element = argument[j];

					if (element instanceof UIElement) {

						this.dom.appendChild(element.dom);
					} else {

						console.error("UIElement:", element, ERROR_MSG);
					}
				}
			} else {

				console.error("UIElement:", argument, ERROR_MSG);
			}
		}
		return this;
	}

	remove() {

		for (let i = 0; i < arguments.length; i++) {

			const argument = arguments[i];

			if (argument instanceof UIElement) {

				this.dom.removeChild(argument.dom);

			} else if (Number.isInteger(argument)) {

				this.dom.removeChild(this.dom.childNodes[argument]);
			} else {

				console.error("UIElement:", argument, ERROR_MSG);
			}
		}
		return this;
	}

	clear() {

		while (this.dom.children.length) {

			this.dom.removeChild(this.dom.lastChild);
		}
		return this;
	}

	setId(id) {

		this.dom.id = id;
		return this;
	}

	getId() {

		return this.dom.id;
	}

	setClass(name) {

		this.dom.className = name;
		return this;
	}

	addClass(name) {

		this.dom.classList.add(name);
		return this;
	}

	removeClass(name) {

		this.dom.classList.remove(name);
		return this;
	}

	setStyle(key, value) {

		this.dom.style[key] = value;
		return this;
	}

	setTextContent(text) {

		this.dom.textContent = text;
		return this;
	}

	getBoundingClientRect() {

		return this.dom.getBoundingClientRect();
	}
}

/**
 * UISpan
 * @param {string} text
 */
class UISpan extends UIElement {

	constructor(text) {

		super("span");
		this.setTextContent(text);
	}
}

/**
 * UIDiv
 */
class UIDiv extends UIElement {

	constructor() {

		super("div");
	}
}

/**
 * UIRow
 */
class UIRow extends UIDiv {

	constructor() {

		super();

		this.dom.className = "row";
	}
}

/**
 * UIPanel
 */
class UIPanel extends UIDiv {

	constructor() {

		super();

		this.dom.className = "panel";
	}
}

/**
 * UILabel
 * @param {string} text
 * @param {string} id
 */
class UILabel extends UIElement {

	constructor(text, id) {

		super("label");

		this.dom.textContent = text;
		if (id) this.dom.htmlFor = id;
	}
}

/**
 * UILink
 * @param {string} href
 * @param {string} text
 */
class UILink extends UIElement {

	constructor(href, text) {

		super("a");

		this.dom.href = href || "#";
		this.dom.textContent = text || "";
	}

	setHref(url) {

		this.dom.href = url;
		return this;
	}
}

/**
 * UIText
 * @param {string} text
 */
class UIText extends UISpan {

	constructor(text) {

		super();

		this.dom.textContent = text;
	}

	getValue() {

		return this.dom.textContent;
	}

	setValue(text) {

		this.dom.textContent = text;
		return this;
	}
}

/**
 * UITextArea
 */
class UITextArea extends UIElement {

	constructor() {

		super("textarea");

		this.dom.spellcheck = false;
		this.dom.onkeydown = (e) => {

			e.stopPropagation();
		};
	}

	getValue() {

		return this.dom.value;
	}

	setValue(value) {

		this.dom.value = value;
		return this;
	}
}

/**
 * UISelect
 */
class UISelect extends UIElement {

	constructor() {

		super("select");
	}

	setMultiple(boolean) {

		this.dom.multiple = boolean || false;
		return this;
	}

	setOptions(options) {

		const selected = this.dom.value;
		this.clear();

		for (const key in options) {

			const option = document.createElement("option");
			option.value = key;
			option.text = options[key];
			this.dom.appendChild(option);
		}
		this.dom.value = selected;
		return this;
	}

	getValue() {

		return this.dom.value;
	}

	setValue(value) {

		value = String(value);

		if (this.dom.value !== value)
			this.dom.value = value;
		return this;
	}
}

/**
 * UIInput
 * @param {*} type
 * @param {*} value
 * @param {*} title
 */
class UIInput extends UIElement {

	constructor(type, value, title) {

		super("input");

		this.dom.type = type;
		this.dom.onkeydown = (e) => {

			e.stopPropagation();
		};
		this.setValue(value);
		this.setTitle(title);
	}

	getName() {

		return this.dom.name;
	}

	setName(name) {

		this.dom.name = name;
		return this;
	}

	getTitle() {

		return this.dom.title;
	}

	setTitle(title) {

		if (this.dom.title !== title && title)
			this.dom.title = title;
		return this;
	}

	getType() {

		return this.dom.type;
	}

	setType(type) {

		this.dom.type = type;
		return this;
	}

	getValue() {

		return this.dom.value;
	}

	setValue(value) {

		if (this.dom.value !== value && value)
			this.dom.value = value;
		return this;
	}
}

/**
 * UIColor
 */
class UIColor extends (/* unused pure expression or super */ null && (UIElement)) {

	constructor() {

		super("input");

		try {

			this.dom.type = "color";
			this.dom.value = "#ffffff";

		} catch (e) {

			console.exception(e);
		}
	}

	getValue() {

		return this.dom.value;
	}

	getHexValue() {

		return parseInt(this.dom.value.substr(1), 16);
	}

	setValue(value) {

		this.dom.value = value;
		return this;
	}

	setHexValue(hex) {

		this.dom.value = "#" + ("000000" + hex.toString(16)).slice(-6);
		return this;
	}
}

/**
 * UINumber
 * @param {number} value
 * @param {number} step
 * @param {number} min
 * @param {number} max
 * @param {number} precision
 */
class UINumber extends UIElement {

	constructor(value, step, min, max, precision) {

		super("input");

		this.dom.type = "number";
		this.dom.step = step || 1;
		this.dom.onkeydown = (e) => {

			e.stopPropagation();
		};
		this.value = value || 0;
		this.min = min || -Infinity;
		this.max = max || +Infinity;
		this.precision = precision || 0;
		this.setValue(value);
		this.dom.onchange = (e) => {

			this.setValue(this.value);
		};
	}

	getName() {

		return this.dom.name;
	}

	setName(name) {

		this.dom.name = name;
		return this;
	}

	setPrecision(precision) {

		this.precision = precision;
		this.setValue(this.value);
		return this;
	}

	setRange(min, max) {

		this.min = min;
		this.max = max;
		this.dom.min = min;
		this.dom.max = max;
		return this;
	}

	setStep(step) {

		this.dom.step = step;
		return this;
	}

	setTitle(text) {

		this.dom.title = text;
		return this;
	}

	getValue() {

		return parseFloat(this.dom.value);
	}

	setValue(value) {

		if (value !== undefined) {
			value = parseFloat(value);

			if (value < this.min)
				value = this.min;
			if (value > this.max)
				value = this.max;

			this.value = value;
			this.dom.value = value.toFixed(this.precision);
		}
		return this;
	}
}

/**
 * UIBreak
 */
class UIBreak extends (/* unused pure expression or super */ null && (UIElement)) {

	constructor() {

		super("br");
	}
}

/**
 * UIHorizontalRule
 */
class UIHorizontalRule extends (/* unused pure expression or super */ null && (UIElement)) {

	constructor() {

		super("hr");
	}
}

/**
 * UIProgress
 * @param {*} value
 */
class UIProgress extends (/* unused pure expression or super */ null && (UIElement)) {

	constructor(value) {

		super("progress");

		this.dom.value = value;
	}

	setValue(value) {

		this.dom.value = value;
		return this;
	}
}

/**
 * UITabbedPanel
 * @param {string} align (horizontal | vertical)
 */
class UITabbedPanel extends UIDiv {

	constructor(align) {

		super();

		this.align = align || "horizontal";
		this.tabs = [];
		this.panels = [];
		this.selector = new UISpan().setClass("tab-selector");
		this.tabsDiv = new UIDiv().setClass("tabs");
		this.tabsDiv.add(this.selector);
		this.panelsDiv = new UIDiv().setClass("panels");
		this.selected = "";
		this.add(this.tabsDiv);
		this.add(this.panelsDiv);
	}

	addTab(id, label, items) {

		const tab = new UITab(label, this);
		tab.setId(id);
		tab.setClass("tab");
		this.tabs.push(tab);
		this.tabsDiv.add(tab);

		const panel = new UIDiv();
		panel.setId(id);
		panel.add(items);
		this.panels.push(panel);
		this.panelsDiv.add(panel);
		this.select(id);
	}

	select(id) {

		for (let tab of this.tabs) {
			if (tab.dom.id === id) {
				tab.addClass("selected");
				this.transformSelector(tab);
			} else if (tab.dom.id === this.selected) {
				tab.removeClass("selected");
			}
		}

		for (let panel of this.panels) {
			if (panel.dom.id === id) {
				panel.dom.style.display = "block";
			} else if (panel.dom.id === this.selected) {
				panel.dom.style.display = "none";
			}
		}

		this.selected = id;
		return this;
	}

	transformSelector(tab) {

		let size;
		const rect = tab.getBoundingClientRect();
		if (this.align === "horizontal") {
			size = rect.width * this.tabs.indexOf(tab);
			this.selector.dom.style.transform = `translateX(${size}px)`;
		} else {
			size = rect.height * this.tabs.indexOf(tab);
			this.selector.dom.style.transform = `translateY(${size}px)`;
		}
	}
}

/**
 * UITab
 * @param {*} label
 * @param {*} parent
 */
class UITab extends UIDiv {

	constructor(label, parent) {

		super();
		const button = new UIInput("button");
		button.dom.title = label;
		this.dom.onclick = (e) => {

			parent.select(this.dom.id);
			e.preventDefault();
		};
		this.add(button);
	}
}

/**
 * UIList
 * @param {UIItem} parent
 */
class UIList extends UIElement {

	constructor(parent) {

		super("ul");
		this.parent = parent && parent.parent; // LI->UL
		this.expanded = false;
	}

	expand() {

		this.expanded = true;
		this.dom.style.display = "block";
		if (this.parent)
			this.parent.expand();
		return this;
	}

	collaps() {

		this.expanded = false;
		this.dom.style.display = "none";
		return this;
	}
}

/**
 * UIItem
 * @param {UIList} parent
 */
class UIItem extends UIElement {

	constructor(parent) {

		super("li");
		this.parent = parent; // UL
		this.selected = false;
	}

	select() {

		this.selected = true;
		this.setClass("selected");
		return this;
	}

	unselect() {

		this.selected = false;
		this.dom.removeAttribute("class");
		return this;
	}
}
;// CONCATENATED MODULE: ./src/toolbar/metadata.js


class MetadataPanel extends UIPanel {

	constructor(reader) {

		super();
		super.setId("metadata");

		const title = new UIText().setId("book-title");
		const creator = new UIText().setId("book-creator");
		const separator = new UIText().setId("book-title-separator");

		super.add([title, separator, creator]);

		//-- events --//

		reader.on("metadata", (meta) => {

			document.title = meta.title;
			document.title = meta.creator ? " - " + meta.creator : "";
			title.setValue(meta.title);
			if (meta.creator) {
				creator.setValue(meta.creator);
				separator.dom.style.display = "inline-block";
			}
		});
	}
}

;// CONCATENATED MODULE: ./src/toolbar.js



class Toolbar {

	constructor(reader) {

		const strings = reader.strings;

		const container = new UIDiv().setId("toolbar");
		const keys = [
			"toolbar/opener",
			"toolbar/openbook",
			"toolbar/openbook/error",
			"toolbar/bookmark",
			"toolbar/fullsceen"
		];

		const start = new UIPanel().setId("start");
		const opener = new UIInput("button").setId("btn-s");
		opener.dom.title = strings.get(keys[0]);
		opener.dom.onclick = () => {

			const isOpen = opener.dom.classList.length > 0;

			reader.emit("sidebaropener", !isOpen);

			if (!isOpen) {
				opener.addClass("open");
			} else {
				opener.removeClass("open");
			}
		};

		start.add(opener);

		const onload = (e) => {

			storage.clear();
			storage.set(e.target.result, () => {
				reader.unload();
				reader.init(e.target.result, { restore: true });
				const url = new URL(window.location.origin);
				window.history.pushState({}, "", url);
			});
		};
		const onerror = (e) => {
			console.error(e);
		};
		const storage = window.storage;
		const end = new UIPanel().setId("end");
		const openbook = new UIInput("file").setId("btn-o");
		openbook.dom.title = strings.get(keys[1]);
		openbook.dom.accept = "application/epub+zip";
		openbook.dom.onchange = (e) => {

			if (e.target.files.length === 0)
				return;

			if (window.FileReader) {

				const fr = new FileReader();
				fr.onload = onload;
				fr.readAsArrayBuffer(e.target.files[0]);
				fr.onerror = onerror;
			} else {
				alert(strings.get(keys[2]));
			}
		};

		end.add(openbook);

		const bookmark = new UIInput("button").setId("btn-b");
		bookmark.dom.title = strings.get(keys[3]);
		bookmark.dom.onclick = (e) => {

			const cfi = this.locationCfi;
			const val = reader.isBookmarked(cfi) === -1;
			reader.emit("bookmarked", val);
		};

		end.add(bookmark);

		let fullscreen = null;
		if (document.fullscreenEnabled) {

			fullscreen = new UIInput("button").setId("btn-f");
			fullscreen.dom.title = strings.get(keys[4]);
			fullscreen.dom.onclick = (e) => {

				this.toggleFullScreen();
			};

			document.onkeydown = (e) => {

				if (e.key === "F11") {
					e.preventDefault();
					this.toggleFullScreen();
				}
			};

			document.onfullscreenchange = (e) => {
				
				const w = window.screen.width === e.target.clientWidth;
				const h = window.screen.height === e.target.clientHeight;

				if (w && h) {
					fullscreen.addClass("resize-small");
				} else {
					fullscreen.removeClass("resize-small");
				}
			};

			end.add(fullscreen);
		}

		container.add([start, new MetadataPanel(reader), end]);
		document.body.appendChild(container.dom);

		//-- events --//

		reader.on("relocated", (location) => {

			const cfi = location.start.cfi;
			const val = reader.isBookmarked(cfi) === -1;
			if (val) {
				bookmark.removeClass("bookmarked");
			} else {
				bookmark.addClass("bookmarked");
			}
			this.locationCfi = cfi; // save location cfi
		});

		reader.on("bookmarked", (boolean) => {

			if (boolean) {
				bookmark.addClass("bookmarked");
			} else {
				bookmark.removeClass("bookmarked");
			}
		});

		reader.on("languagechanged", (value) => {

			opener.dom.title = strings.get(keys[0]);
			openbook.dom.title = strings.get(keys[1]);
			bookmark.dom.title = strings.get(keys[3]);

			if (fullscreen) {
				fullscreen.dom.title = strings.get(keys[4]);
			}
		});
	}

	toggleFullScreen() {

		document.activeElement.blur();

		if (document.fullscreenElement === null) {
			document.documentElement.requestFullscreen();
		} else if (document.exitFullscreen) {
			document.exitFullscreen();
		}
	}
}

;// CONCATENATED MODULE: ./src/sidebar/toc.js


class TocPanel extends UIPanel {

	constructor(reader) {

		super();
		const container = new UIDiv().setClass("list-container");
		this.setId("contents");
		this.reader = reader;
		this.selector = undefined; // save reference to selected tree item

		//-- events --//

		reader.on("navigation", (toc) => {

			container.clear();
			container.add(this.generateToc(toc));
			this.add(container);
		});
	}

	generateToc(toc, parent) {

		const list = new UIList(parent);

		toc.forEach((chapter) => {

			const link = new UILink(chapter.href, chapter.label);
			const item = new UIItem(list).setId(chapter.id);
			const tbox = new UIDiv().setId("expander");

			link.dom.onclick = () => {

				if (this.selector && this.selector !== item)
					this.selector.unselect();

				item.select();
				this.selector = item;
				this.reader.settings.sectionId = chapter.id;
				this.reader.rendition.display(chapter.href);
				return false;
			};
			item.add([tbox, link]);
			this.reader.navItems[chapter.href] = {
				id: chapter.id,
				label: chapter.label
			};

			if (this.reader.settings.sectionId === chapter.id) {
				list.expand();
				item.select();
				this.selector = item;
			}

			if (chapter.subitems && chapter.subitems.length > 0) {

				const subItems = this.generateToc(chapter.subitems, item);
				const tbtn = new UISpan().setClass("toggle-collapsed");
				tbtn.dom.onclick = () => {

					if (subItems.expanded) {
						subItems.collaps();
						tbtn.setClass("toggle-collapsed");
					} else {
						subItems.expand();
						tbtn.setClass("toggle-expanded");
					}
					return false;
				};
				tbox.add(tbtn);
				item.add(subItems);
			}

			list.add(item);
		});

		return list;
	}
}

;// CONCATENATED MODULE: ./src/sidebar/bookmarks.js


class BookmarksPanel extends UIPanel {

	constructor(reader) {

		super();
		const container = new UIDiv().setClass("list-container");
		const strings = reader.strings;
		const ctrlRow = new UIRow();
		const ctrlStr = [
			strings.get("sidebar/bookmarks/add"),
			strings.get("sidebar/bookmarks/remove"),
			strings.get("sidebar/bookmarks/clear"),
		];
		const btn_a = new UIInput("button", ctrlStr[0]).addClass("btn-start");
		const btn_r = new UIInput("button", ctrlStr[1]).addClass("btn-medium");
		const btn_c = new UIInput("button", ctrlStr[2]).addClass("btn-end");

		btn_a.dom.onclick = () => {

			reader.emit("bookmarked", true);
			return false;
		};

		btn_r.dom.onclick = () => {

			reader.emit("bookmarked", false);
			return false;
		};

		btn_c.dom.onclick = () => {

			this.clearBookmarks();
			reader.emit("bookmarked", false);
			return false;
		};

		ctrlRow.add([btn_a, btn_r, btn_c]);

		this.bookmarks = new UIList();
		container.add(this.bookmarks);
		this.setId("bookmarks");
		this.add([ctrlRow, container]);
		this.reader = reader;

		const update = () => {

			btn_r.dom.disabled = reader.settings.bookmarks.length === 0;
			btn_c.dom.disabled = reader.settings.bookmarks.length === 0;
		};

		//-- events --//

		reader.on("displayed", (renderer, cfg) => {

			cfg.bookmarks.forEach((cfi) => {

				this.setBookmark(cfi);
			});
			update();
		});

		reader.on("relocated", (location) => {

			const cfi = location.start.cfi;
			const val = reader.isBookmarked(cfi) === -1;
			btn_a.dom.disabled = !val;
			btn_r.dom.disabled = val;
			this.locationCfi = cfi; // save location cfi
		});

		reader.on("bookmarked", (boolean) => {

			if (boolean) {
				this.appendBookmark();
				btn_a.dom.disabled = true;
			} else {
				this.removeBookmark();
				btn_a.dom.disabled = false;
			}
			update();
		});
	}

	appendBookmark() {

		const cfi = this.locationCfi;
		if (this.reader.isBookmarked(cfi) > -1) {
			return;
		}
		this.setBookmark(cfi);
		this.reader.settings.bookmarks.push(cfi);
	}

	removeBookmark() {

		const cfi = this.locationCfi;
		const index = this.reader.isBookmarked(cfi);
		if (index === -1) {
			return;
		}
		this.bookmarks.remove(index);
		this.reader.settings.bookmarks.splice(index, 1);
	}

	clearBookmarks() {

		this.bookmarks.clear();
		this.reader.settings.bookmarks = [];
	}

	setBookmark(cfi) {

		const link = new UILink();
		const item = new UIItem();
		const navItem = this.reader.navItemFromCfi(cfi);
		let idref;
		let label;

		if (navItem === undefined) {
			const spineItem = this.reader.book.spine.get(cfi);
			idref = spineItem.idref;
			label = spineItem.idref
		} else {
			idref = navItem.id;
			label = navItem.label;
		}

		link.setHref("#" + cfi);
		link.dom.onclick = () => {

			this.reader.rendition.display(cfi);
			return false;
		};
		link.setTextContent(label);

		item.add(link);
		item.setId(idref);
		this.bookmarks.add(item);
	}
}

;// CONCATENATED MODULE: ./src/sidebar/annotations.js


class AnnotationsPanel extends UIPanel {

	constructor(reader) {

		super();
		const container = new UIDiv().setClass("list-container");
		const strings = reader.strings;
		const textRow = new UIRow();
		const ctrlRow = new UIRow();
		const ctrlStr = [
			strings.get("sidebar/annotations/add"),
			strings.get("sidebar/annotations/clear")
		];

		const textBox = new UITextArea();
		textBox.dom.oninput = (e) => {

			this.update();
		};

		const btn_a = new UIInput("button", ctrlStr[0]).addClass("btn-start");
		btn_a.dom.disabled = true;
		btn_a.dom.onclick = () => {

			const note = {
				cfi: this.cfiRange,
				date: new Date(),
				text: textBox.getValue(),
				uuid: reader.uuid()
			};

			reader.settings.annotations.push(note);

			textBox.setValue("");
			this.set(note);
			return false;
		};

		const btn_c = new UIInput("button", ctrlStr[1]).addClass("btn-end");
		btn_c.dom.disabled = true;
		btn_c.dom.onclick = () => {

			this.clearNotes();
			return false;
		};

		textRow.add(textBox);
		ctrlRow.add([btn_a, btn_c]);

		this.notes = new UIList();
		container.add(this.notes);
		this.setId("annotations");
		this.add([textRow, ctrlRow, container]);
		this.reader = reader;
		this.update = () => {

			btn_a.dom.disabled = !this.range || textBox.getValue().length === 0;
			btn_c.dom.disabled = reader.settings.annotations.length === 0;
		};

		//-- events --//

		reader.on("bookready", (cfg) => {

			cfg.annotations.forEach((note) => {

				this.set(note);
			});
		});

		reader.on("selected", (cfiRange, contents) => {

			this.cfiRange = cfiRange;
			this.range = contents.range(cfiRange);
			this.update();
		});

		reader.on("unselected", () => {

			this.range = undefined;
			this.update();
		});
	}

	set(note) {

		const link = new UILink("#" + note.cfi, note.text);
		const item = new UIItem().setId("note-" + note.uuid);
		const btnr = new UISpan().setClass("btn-remove");
		const call = () => { };

		link.onclick = () => {

			this.reader.rendition.display(note.cfi);
			return false;
		};

		btnr.dom.onclick = () => {

			this.removeNote(note);
			return false;
		};

		item.add([link, btnr]);
		this.notes.add(item);
		this.reader.rendition.annotations.add(
			"highlight", note.cfi, {}, call, "note-highlight", {});
		this.update();
	}

	removeNote(note) {

		const index = this.reader.settings.annotations.indexOf(note);
		if (index === -1)
			return;

		this.notes.remove(index);
		this.reader.settings.annotations.splice(index, 1);
		this.reader.rendition.annotations.remove(note.cfi, "highlight");
		this.update();
	}

	clearNotes() {

		this.reader.settings.annotations.forEach(note => {
			this.reader.rendition.annotations.remove(note.cfi, "highlight");
		});
		this.notes.clear();
		this.reader.settings.annotations = [];
		this.update();
	}
}

;// CONCATENATED MODULE: ./src/sidebar/search.js


class SearchPanel extends UIPanel {

	constructor(reader) {

		super();
		const container = new UIDiv().setClass("list-container");
		const strings = reader.strings;

		let searchQuery = undefined;
		const searchBox = new UIInput("search");
		searchBox.dom.placeholder = strings.get("sidebar/search/placeholder");
		searchBox.dom.onsearch = () => {

			const value = searchBox.getValue();

			if (value.length === 0) {
				this.items.clear();
			} else if (searchQuery !== value) {
				this.items.clear();
				this.doSearch(value).then(results => {

					results.forEach(data => {
						this.set(data);
					});
				});
			}
			searchQuery = value;
		};

		const ctrlRow = new UIRow();
		ctrlRow.add(searchBox);

		this.setId("search");
		this.items = new UIList();
		container.add(this.items);
		this.add([ctrlRow, container]);
		this.reader = reader;
		//
		// improvement of the highlighting of keywords is required...
		//
	}

	/**
	 * Searching the entire book
	 * @param {*} q Query keyword
	 * @returns The search result array.
	 */
	async doSearch(q) {

		const book = this.reader.book;
		const results = await Promise.all(
			book.spine.spineItems.map(item => item.load(book.load.bind(book))
				.then(item.find.bind(item, q)).finally(item.unload.bind(item))));
		return await Promise.resolve([].concat.apply([], results));
	}

	set(data) {

		const link = new UILink("#" + data.cfi, data.excerpt);
		const item = new UIItem();
		link.dom.onclick = () => {

			this.reader.rendition.display(data.cfi);
			return false;
		};
		item.add(link);
		this.items.add(item);
	}
}

;// CONCATENATED MODULE: ./src/sidebar/settings.js


class SettingsPanel extends UIPanel {

	constructor(reader) {

		super();
		super.setId("settings");

		const strings = reader.strings;
		const keys = [
			"sidebar/settings/language",
			"sidebar/settings/fontsize",
			"sidebar/settings/layout",
			"sidebar/settings/spread",
			"sidebar/settings/spread/pagewidth"
		];

		const languageLabel = new UILabel(strings.get(keys[0]), "language-ui");
		const languageRow = new UIRow();
		const language = new UISelect().setOptions({
			en: "English",
			fr: "French",
			ja: "Japanese",
			ru: "Russian"
		});
		language.dom.onchange = (e) => {

			reader.emit("languagechanged", e.target.value);
		};
		language.setId("language-ui");

		languageRow.add(languageLabel);
		languageRow.add(language);

		const fontSizeLabel = new UILabel(strings.get(keys[1]), "fontsize");
		const fontSizeRow = new UIRow();
		const fontSize = new UINumber(100, 1);
		fontSize.dom.onchange = (e) => {

			reader.emit("styleschanged", {
				fontSize: parseInt(e.target.value)
			});
		};
		fontSize.setId("fontsize")

		fontSizeRow.add(fontSizeLabel);
		fontSizeRow.add(fontSize);

		//-- layout configure --//

		const layoutLabel = new UILabel(strings.get(keys[2]), "layout");
		const layoutRow = new UIRow();
		const layout = new UISelect().setOptions({
			paginated: "Paginated",
			scrolled: "Scrolled"
		});
		layout.dom.onchange = (e) => {

			reader.emit("flowchanged", e.target.value);

			if (e.target.value === "scrolled") {
				reader.emit("spreadchanged", {
					mod: "none",
					min: undefined
				});
			} else {
				reader.emit("spreadchanged", {
					mod: undefined,
					min: undefined
				});
			}
		};
		layout.setId("layout");

		layoutRow.add(layoutLabel);
		layoutRow.add(layout);

		//-- spdead configure --//

		const spreadLabel = new UILabel(strings.get(keys[3]), "spread");
		const spreadRow = new UIRow();
		const spread = new UISelect().setOptions({
			none: "None",
			auto: "Auto"
		});
		spread.dom.onchange = (e) => {

			reader.emit("spreadchanged", {
				mod: e.target.value,
				min: undefined
			});
		};
		spread.setId("spread");

		spreadRow.add(spreadLabel);
		spreadRow.add(spread);

		const minSpreadWidthLabel = new UILabel(strings.get(keys[4]), "min-spread-width");
		const minSpreadWidthRow = new UIRow();
		const minSpreadWidth = new UINumber(800, 1);
		minSpreadWidth.dom.onchange = (e) => {

			reader.emit("spreadchanged", {
				mod: undefined,
				min: parseInt(e.target.value)
			});
		};
		minSpreadWidth.setId("min-spread-width");

		minSpreadWidthRow.add(minSpreadWidthLabel);
		minSpreadWidthRow.add(minSpreadWidth);

		//-- pagination --//

		const paginationStr = strings.get("sidebar/settings/pagination");
		const paginationRow = new UIRow();
		const pagination = new UIInput("checkbox", false, paginationStr[1]);
		pagination.setId("pagination");
		pagination.dom.onclick = (e) => {

			// not implemented
		};

		paginationRow.add(new UILabel(paginationStr[0], "pagination"));
		paginationRow.add(pagination);

		super.add([
			languageRow,
			fontSizeRow,
			layoutRow,
			spreadRow,
			minSpreadWidthRow,
			//paginationRow
		]);

		//-- events --//

		reader.on("bookready", (cfg) => {

			language.setValue(cfg.language);
			fontSize.setValue(cfg.styles.fontSize);
			layout.setValue(cfg.flow);
			spread.setValue(cfg.spread.mod);
			minSpreadWidth.setValue(cfg.spread.min);
		});

		reader.on("layout", (props) => {

			if (props.flow === "scrolled") {
				spread.dom.disabled = true;
				spread.setValue("none");
			} else {
				spread.dom.disabled = false;
			}
		});

		reader.on("languagechanged", (value) => {

			languageLabel.dom.textContent = strings.get(keys[0]);
			fontSizeLabel.dom.textContent = strings.get(keys[1]);
			layoutLabel.dom.textContent = strings.get(keys[2]);
			spreadLabel.dom.textContent = strings.get(keys[3]);
			minSpreadWidthLabel.dom.textContent = strings.get(keys[4]);
		});
	}
}

;// CONCATENATED MODULE: ./src/sidebar.js







class Sidebar {

	constructor(reader) {

		const strings = reader.strings;
		const tabs = [
			strings.get('sidebar/contents'),
			strings.get('sidebar/bookmarks'),
			strings.get('sidebar/annotations'),
			strings.get('sidebar/search'),
			strings.get('sidebar/settings')
		];

		const container = new UITabbedPanel('vertical').setId('sidebar');

		container.addTab('tab-t', tabs[0], new TocPanel(reader));
		container.addTab('tab-b', tabs[1], new BookmarksPanel(reader));
		container.addTab('tab-n', tabs[2], new AnnotationsPanel(reader));
		container.addTab('tab-s', tabs[3], new SearchPanel(reader));
		container.addTab('tab-c', tabs[4], new SettingsPanel(reader));
		container.select('tab-t');

		document.body.appendChild(container.dom);
	}
}

;// CONCATENATED MODULE: ./src/content.js


class Content {

	constructor(reader) {

		const container = new UIDiv().setId("content");
		container.dom.ontransitionend = (e) => {

			reader.emit("sidebarreflow");
			e.preventDefault();
		};

		const prev = new UIDiv().setId("prev").setClass("arrow");
		prev.dom.onclick = (e) => {

			reader.emit("prev");
			e.preventDefault();
		};
		prev.add(new UISpan("<"));

		const next = new UIDiv().setId("next").setClass("arrow");
		next.dom.onclick = (e) => {

			reader.emit("next");
			e.preventDefault();
		};
		next.add(new UISpan(">"));

		const viewer = new UIDiv().setId("viewer");
		const divider = new UIDiv().setId("divider");
		const loader = new UIDiv().setId("loader");

		container.add([prev, viewer, next, divider, loader]);
		document.body.appendChild(container.dom);

		//-- events --//

		reader.on("bookready", (cfg) => {

			loader.dom.style.display = "block";
		});

		reader.on("bookloaded", () => {

			loader.dom.style.display = "none";
		});

		reader.on("sidebaropener", (value) => {

			if (value) {
				container.addClass("closed");
			} else {
				container.removeClass("closed");
			}
		});

		reader.on("layout", (props) => {

			if (props.spread && props.width > props.spreadWidth) {
				divider.dom.style.display = "block";
			} else {
				divider.dom.style.display = "none";
			}
		});

		reader.on("relocated", (location) => {

			if (location.atStart) {
				prev.addClass("disabled");
			} else {
				prev.removeClass("disabled");
			}

			if (location.atEnd) {
				next.addClass("disabled");
			} else {
				next.removeClass("disabled");
			}
		});

		reader.on("prev", () => {

			prev.addClass("active");
			setTimeout(() => { prev.removeClass("active"); }, 100);
		});

		reader.on("next", () => {

			next.addClass("active");
			setTimeout(() => { next.removeClass("active"); }, 100);
		});

		reader.on("viewercleanup", () => {

			viewer.clear();
		});
	}
}

;// CONCATENATED MODULE: ./src/strings.js
class Strings {

	constructor(reader) {

		this.language = reader.settings.language || "en";
		this.values = {
			en: {
				"toolbar/opener": "Sidebar",
				"toolbar/openbook": "Open book",
				"toolbar/openbook/error": "Your browser does not support the required features.\nPlease use a modern browser such as Google Chrome, or Mozilla Firefox.",
				"toolbar/bookmark": "Add this page to bookmarks",
				"toolbar/fullsceen": "Fullscreen",

				"sidebar/contents": "Contents",
				"sidebar/bookmarks": "Bookmarks",
				"sidebar/bookmarks/add": "Add",
				"sidebar/bookmarks/remove": "Remove",
				"sidebar/bookmarks/clear": "Clear",
				"sidebar/annotations": "Annotations",
				"sidebar/annotations/add": "Add",
				"sidebar/annotations/clear": "Clear",
				"sidebar/annotations/anchor": "Anchor",
				"sidebar/annotations/cancel": "Cancel",
				"sidebar/search": "Search",
				"sidebar/search/placeholder": "Search",
				"sidebar/settings": "Settings",
				"sidebar/settings/language": "Language",
				"sidebar/settings/fontsize": "Font size (%)",
				"sidebar/settings/layout": "Layout",
				"sidebar/settings/pagination": ["Pagination", "Generate pagination"],
				"sidebar/settings/spread": "Spread",
				"sidebar/settings/spread/pagewidth": "Page width"
			},
			fr: {
				"toolbar/opener": "Barre latérale",
				"toolbar/openbook": "Ouvrir un livre local",
				"toolbar/openbook/error": "Votre navigateur ne prend pas en charge les fonctions nécessaires.\nVeuillez utiliser un navigateur moderne tel que Google Chrome ou Mozilla Firefox.",
				"toolbar/bookmark": "Insérer un marque page ici",
				"toolbar/fullsceen": "Plein écran",

				"sidebar/contents": "Sommaire",
				"sidebar/bookmarks": "Marque-pages",
				"sidebar/bookmarks/add": "Ajouter",
				"sidebar/bookmarks/remove": "Retirer",
				"sidebar/bookmarks/clear": "Tout enlever",
				"sidebar/annotations": "Annotations",
				"sidebar/annotations/add": "Ajouter",
				"sidebar/annotations/clear": "Tout enlever",
				"sidebar/annotations/anchor": "Ancre",
				"sidebar/annotations/cancel": "Annuler",
				"sidebar/search": "Rechercher",
				"sidebar/search/placeholder": "rechercher",
				"sidebar/settings": "Réglages",
				"sidebar/settings/language": "Langue",
				"sidebar/settings/fontsize": "???",
				"sidebar/settings/layout": "???",
				"sidebar/settings/pagination": ["Pagination", "Établir une pagination"],
				"sidebar/settings/spread": "???",
				"sidebar/settings/spread/pagewidth": "???"
			},
			ja: {
				"toolbar/opener": "サイドバー",
				"toolbar/openbook": "本を開く",
				"toolbar/openbook/error": "ご利用のブラウザは必要な機能をサポートしていません。\nGoogle Chrome、Mozilla Firefox、その他のモダンなブラウザでご利用ください。",
				"toolbar/bookmark": "このページに栞を設定する",
				"toolbar/fullsceen": "フルスクリーン",

				"sidebar/contents": "目次",
				"sidebar/bookmarks": "栞",
				"sidebar/bookmarks/add": "追加",
				"sidebar/bookmarks/remove": "削除",
				"sidebar/bookmarks/clear": "クリア",
				"sidebar/annotations": "注釈",
				"sidebar/annotations/add": "追加",
				"sidebar/annotations/clear": "クリア",
				"sidebar/annotations/anchor": "アンカー",
				"sidebar/annotations/cancel": "キャンセル",
				"sidebar/search": "検索",
				"sidebar/search/placeholder": "検索",
				"sidebar/settings": "設定",
				"sidebar/settings/language": "表示言語",
				"sidebar/settings/fontsize": "???",
				"sidebar/settings/layout": "???",
				"sidebar/settings/pagination": ["ページネーション", "ページネーションを生成します。"],
				"sidebar/settings/spread": "???",
				"sidebar/settings/spread/pagewidth": "???"
			},
			ru: {
				"toolbar/opener": "Боковая панель",
				"toolbar/openbook": "Открыть книгу",
				"toolbar/openbook/error": "Ваш браузер не поддерживает необходимые функции.\nПожалуйста, используйте современный браузер, такой как Google Chrome или Mozilla Firefox.",
				"toolbar/bookmark": "Добавить эту страницу в закладки",
				"toolbar/fullsceen": "Полноэкранный режим",

				"sidebar/contents": "Содержание",
				"sidebar/bookmarks": "Закладки",
				"sidebar/bookmarks/add": "Добавить",
				"sidebar/bookmarks/remove": "Удалить",
				"sidebar/bookmarks/clear": "Очистить",
				"sidebar/annotations": "Аннотации",
				"sidebar/annotations/add": "Добавить",
				"sidebar/annotations/clear": "Очистить",
				"sidebar/annotations/anchor": "Метка",
				"sidebar/annotations/cancel": "Отмена",
				"sidebar/search": "Поиск",
				"sidebar/search/placeholder": "Поиск",
				"sidebar/settings": "Настройки",
				"sidebar/settings/language": "Язык",
				"sidebar/settings/fontsize": "Размер шрифта",
				"sidebar/settings/layout": "Макет",
				"sidebar/settings/pagination": ["Нумерация страниц", "Генерировать нумерацию страниц"],
				"sidebar/settings/spread": "Разворот",
				"sidebar/settings/spread/pagewidth": "Ширина страницы"
			}
		};

		reader.on("languagechanged", (value) => {
			this.language = value;
		});
	}

	get(key) { return this.values[this.language][key] || "???"; }
}

;// CONCATENATED MODULE: ./src/reader.js







class Reader {

	constructor(bookPath, _options) {

		this.settings = undefined;
		this.cfgInit(bookPath, _options);

		this.strings = new Strings(this);
		this.toolbar = new Toolbar(this);
		this.sidebar = new Sidebar(this);
		this.content = new Content(this);

		this.book = undefined;
		this.rendition = undefined;
		this.displayed = undefined;

		this.init();

		window.onbeforeunload = this.unload.bind(this);
		window.onhashchange = this.hashChanged.bind(this);
		window.onkeydown = this.keyboardHandler.bind(this);
		window.onwheel = (e) => {
			if (e.ctrlKey) {
				e.preventDefault();
			}
		};
	}

	/**
	 * Initialize book.
	 * @param {*} bookPath
	 * @param {*} _options
	 */
	init(bookPath, _options) {

		this.emit("viewercleanup");
		this.navItems = {};

		if (arguments.length > 0) {

			this.cfgInit(bookPath, _options);
		}

		this.book = ePub(this.settings.bookPath);
		this.rendition = this.book.renderTo("viewer", {
			flow: this.settings.flow,
			spread: this.settings.spread.mod,
			minSpreadWidth: this.settings.spread.min,
			width: "100%",
			height: "100%"
		});

		const cfi = this.settings.previousLocationCfi;
		if (cfi) {
			this.displayed = this.rendition.display(cfi);
		} else {
			this.displayed = this.rendition.display();
		}

		this.displayed.then((renderer) => {
			this.emit("displayed", renderer, this.settings);
		});

		this.book.ready.then(() => {
			this.emit("bookready", this.settings);
		}).then(() => {
			this.emit("bookloaded");
		});

		this.book.loaded.metadata.then((meta) => {
			this.emit("metadata", meta);
		});

		this.book.loaded.navigation.then((toc) => {
			this.emit("navigation", toc);
		});

		this.rendition.on("click", (e) => {
			const selection = e.view.document.getSelection();
			if (selection.type !== "Range") {
				this.emit("unselected");
			}
		});

		this.rendition.on("layout", (props) => {
			this.emit("layout", props);
		});

		this.rendition.on("selected", (cfiRange, contents) => {
			this.setLocation(cfiRange);
			this.emit("selected", cfiRange, contents);
		});

		this.rendition.on("relocated", (location) => {
			this.setLocation(location.start.cfi);
			this.emit("relocated", location);
		});

		this.on("prev", () => {
			if (this.book.package.metadata.direction === 'rtl') {
				this.rendition.next();
			} else {
				this.rendition.prev();
			}
		});

		this.on("next", () => {
			if (this.book.package.metadata.direction === 'rtl') {
				this.rendition.prev();
			} else {
				this.rendition.next();
			}
		});

		this.on("sidebarreflow", () => {
			// no implementation sidebarReflow setting
			//this.rendition.resize();
		});

		this.on("languagechanged", (value) => {
			this.settings.language = value;
		});

		this.on("flowchanged", (value) => {
			this.settings.flow = value;
			this.rendition.flow(value);
		});
		
		this.on("spreadchanged", (value) => {
			const mod = value.mod || this.settings.spread.mod;
			const min = value.min || this.settings.spread.min;
			this.settings.spread.mod = mod;
			this.settings.spread.min = min;
			this.rendition.spread(mod, min);
		});

		this.on("styleschanged", (value) => {
			const fontSize = value.fontSize;
			this.settings.styles.fontSize = fontSize;
			this.rendition.themes.fontSize(fontSize + "%");
		});
	}

	/* ------------------------------- Common ------------------------------- */

	defaults(obj) {

		for (let i = 1, length = arguments.length; i < length; i++) {
			const source = arguments[i];
			for (let prop in source) {
				if (obj[prop] === void 0)
					obj[prop] = source[prop];
			}
		}
		return obj;
	}

	uuid() {

		let d = new Date().getTime();
		const uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
			let r = (d + Math.random() * 16) % 16 | 0;
			d = Math.floor(d / 16);
			return (c === 'x' ? r : (r & 0x7 | 0x8)).toString(16);
		});
		return uuid;
	}

	navItemFromCfi(cfi) {

		const range = this.rendition.getRange(cfi);
		const idref = range.startContainer.parentNode.id;
		const location = this.rendition.currentLocation();
		const href = location.start.href;
		return this.navItems[href + "#" + idref] || this.navItems[href];
	}

	/* ------------------------------ Bookmarks ----------------------------- */

	/**
	 * Verifying the current page in bookmarks.
	 * @param {*} cfi
	 * @returns The index of the bookmark if it exists, or -1 otherwise.
	 */
	isBookmarked(cfi) {

		return this.settings.bookmarks.indexOf(cfi);
	}

	/* ----------------------------- Annotations ---------------------------- */

	isAnnotated(note) {

		return this.settings.annotations.indexOf(note);
	}

	/* ------------------------------ Settings ------------------------------ */

	/**
	 * Initialize book settings.
	 * @param {*} bookPath
	 * @param {*} _options
	 */
	cfgInit(bookPath, _options) {

		this.entryKey = md5(bookPath).toString();
		this.settings = this.defaults(_options || {}, {
			bookPath: bookPath,
			flow: undefined,
			restore: false,
			history: true,
			reload: false, // ??
			bookmarks: undefined,
			annotations: undefined,
			contained: undefined,
			sectionId: undefined,
			spread: undefined,
			styles: undefined,
			pagination: false, // ??
			language: undefined
		});

		if (this.settings.restore && this.isSaved()) {
			this.applySavedSettings();
		}

		if (this.settings.bookmarks === undefined) {
			this.settings.bookmarks = [];
		}

		if (this.settings.annotations === undefined) {
			this.settings.annotations = [];
		}

		if (this.settings.flow === undefined) {
			this.settings.flow = "paginated";
		}

		if (this.settings.spread === undefined) {
			this.settings.spread = {
				mod: "auto",
				min: 800
			};
		}

		if (this.settings.styles === undefined) {
			this.settings.styles = {
				fontSize: 100
			};
		}

		if (this.settings.language === undefined) {
			this.settings.language = "en";
		}
	}

	/**
	 * Checks if the book setting can be retrieved from localStorage.
	 * @returns true if the book key exists, or false otherwise.
	 */
	isSaved() {

		if (!localStorage)
			return false;

		return localStorage.getItem(this.entryKey) !== null;
	}

	/**
	 * Removing the current book settings from local storage.
	 * @returns true if the book settings were deleted successfully, or false
	 * otherwise.
	 */
	removeSavedSettings() {

		if (!this.isSaved())
			return false;

		localStorage.removeItem(this.entryKey);
		return true;
	}

	applySavedSettings() {

		if (!localStorage)
			return false;

		let stored;
		try {
			stored = JSON.parse(localStorage.getItem(this.entryKey));
		} catch (e) { // parsing error of localStorage
			console.exception(e);
		}

		if (stored) {
			// Merge spread
			if (stored.spread) {
				this.settings.spread = this.defaults(this.settings.spread || {}, 
					stored.spread);
			}
			// Merge styles
			if (stored.styles) {
				this.settings.styles = this.defaults(this.settings.styles || {},
					stored.styles);
			}
			// Merge the rest
			this.settings = this.defaults(this.settings, stored);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Saving the current book settings in local storage.
	 * @returns
	 */
	saveSettings() {

		this.settings.previousLocationCfi = this.rendition.location.start.cfi;
		localStorage.setItem(this.entryKey, JSON.stringify(this.settings));
	}

	setLocation(cfi) {

		const baseUrl = this.book.archived ? undefined : this.book.url;
		const url = new URL(window.location, baseUrl);
		url.hash = "#" + cfi;

		// Update the History Location
		if (this.settings.history && window.location.hash !== url.hash) {
			// Add CFI fragment to the history
			window.history.pushState({}, "", url);
			this.currentLocationCfi = cfi;
		}
	}

	//-- event handlers --//

	unload() {

		if (this.settings.restore && localStorage) {
			this.saveSettings();
		}
	}

	hashChanged() {

		const hash = window.location.hash.slice(1);
		this.rendition.display(hash);
	}

	keyboardHandler(e) {

		const MOD = (e.ctrlKey || e.metaKey);

		if (MOD) {

			const step = 2;
			let value = this.settings.styles.fontSize;

			switch (e.key) {

				case '=':
					e.preventDefault();
					value += step;
					this.emit("styleschanged", { fontSize: value });
					break;
				case '-':
					e.preventDefault();
					value -= step;
					this.emit("styleschanged", { fontSize: value });
					break;
				case '0':
					e.preventDefault();
					value = 100;
					this.emit("styleschanged", { fontSize: value });
					break;
			}
		} else {

			switch (e.key) {
				case 'ArrowLeft':
					this.emit('prev');
					e.preventDefault();
					break;
				case 'ArrowRight':
					this.emit('next');
					e.preventDefault();
					break;
			}
		}
	}
}

event_emitter_default()(Reader.prototype);

;// CONCATENATED MODULE: ./src/storage.js
class Storage {

	constructor() {

		this.name = 'epubjs-reader';
		this.version = 1.0;
		this.database;
		this.indexedDB = window.indexedDB ||
			window.webkitIndexedDB ||
			window.mozIndexedDB ||
			window.OIndexedDB ||
			window.msIndexedDB;

		if (this.indexedDB === undefined) {

			alert('The IndexedDB API not available in your browser.');
		}
	}

	init(callback) {

		if (this.indexedDB === undefined) {
			callback();
			return;
		}

		const scope = this;
		const request = indexedDB.open(this.name, this.version);
		request.onupgradeneeded = function (event) {

			const db = event.target.result;
			if (db.objectStoreNames.contains('entries') === false) {

				db.createObjectStore("entries");
			}
		};

		request.onsuccess = function (event) {

			scope.database = event.target.result;
			scope.database.onerror = function (event) {

				console.error('IndexedDB', event);
			};
			callback();
		}

		request.onerror = function (event) {

			console.error('IndexedDB', event);
		};
	}

	get(callback) {

		if (this.database === undefined) {
			callback();
			return;
		}

		const transaction = this.database.transaction(['entries'], 'readwrite');
		const objectStore = transaction.objectStore('entries');
		const request = objectStore.get(0);
		request.onsuccess = function (event) {

			callback(event.target.result);
			console.log('storage.get');
		};
	}

	set(data, callback) {

		if (this.database === undefined) {
			callback();
			return;
		}

		const transaction = this.database.transaction(['entries'], 'readwrite');
		const objectStore = transaction.objectStore('entries');
		const request = objectStore.put(data, 0);
		request.onsuccess = function () {

			callback();
			console.log('storage.set');
		};
	}

	clear() {

		if (this.database === undefined)
			return;

		const transaction = this.database.transaction(['entries'], 'readwrite');
		const objectStore = transaction.objectStore('entries');
		const request = objectStore.clear();
		request.onsuccess = function () {

			console.log('storage.clear');
		};
	}
}

;// CONCATENATED MODULE: ./src/main.js



"use strict";

window.ResizeObserver = undefined;
window.onload = function () {

	const storage = new Storage();
	const url = new URL(window.location);
	const path = (window.bookPath !== undefined)
		? window.bookPath
		: ((url.search.length > 0) ? url.searchParams.get("bookPath") : "https://s3.amazonaws.com/moby-dick/");

	storage.init(function () {

		storage.get(function (data) {

			if (data !== undefined && url.search.length === 0) {

				window.reader = new Reader(data, { restore: true });

			} else {

				window.reader = new Reader(path, { restore: true });
			}
		});
	});

	window.storage = storage;
};

})();

/******/ })()
;
//# sourceMappingURL=epubreader.js.map