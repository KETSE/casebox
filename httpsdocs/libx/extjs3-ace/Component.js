if (typeof bravojs == 'undefined') { bravojs = {}; }
if (typeof window != 'undefined' && typeof bravojs.url == 'undefined') {
bravojs.url = window.location.protocol + '//' + window.location.host + '/extjs3-ace/Component.js';
} else if(typeof importScripts != 'undefined' && typeof bravojs.url == 'undefined') {
bravojs.url = location;
}
bravojs.mainModuleDir = /^(https?|resource):\/(.*?)\.js$/.exec(bravojs.url)[2];
bravojs.mainContext = bravojs.mainModuleDir + '/75facf7e3e2e534c307f54f259c3e920b1dc6965';
bravojs.platform = 'browser';
function dump() { (bravojs.dump || bravojs.print).apply(null, arguments); };

// -- kriskowal Kris Kowal Copyright (C) 2009-2010 MIT License
// -- tlrobinson Tom Robinson
// dantman Daniel Friesen

/*!
    Copyright (c) 2009, 280 North Inc. http://280north.com/
    MIT License. http://github.com/280north/narwhal/blob/master/README.md
*/

// Brings an environment as close to ECMAScript 5 compliance
// as is possible with the facilities of erstwhile engines.

// ES5 Draft
// http://www.ecma-international.org/publications/files/drafts/tc39-2009-050.pdf

// NOTE: this is a draft, and as such, the URL is subject to change.  If the
// link is broken, check in the parent directory for the latest TC39 PDF.
// http://www.ecma-international.org/publications/files/drafts/

// Previous ES5 Draft
// http://www.ecma-international.org/publications/files/drafts/tc39-2009-025.pdf
// This is a broken link to the previous draft of ES5 on which most of the
// numbered specification references and quotes herein were taken.  Updating
// these references and quotes to reflect the new document would be a welcome
// volunteer project.

//
// Array
// =====
//

// ES5 15.4.3.2 
if (!Array.isArray) {
    Array.isArray = function(obj) {
        return Object.prototype.toString.call(obj) == "[object Array]";
    };
}

// ES5 15.4.4.18
if (!Array.prototype.forEach) {
    Array.prototype.forEach =  function(block, thisObject) {
        var len = this.length >>> 0;
        for (var i = 0; i < len; i++) {
            if (i in this) {
                block.call(thisObject, this[i], i, this);
            }
        }
    };
}

// ES5 15.4.4.19
// https://developer.mozilla.org/en/Core_JavaScript_1.5_Reference/Objects/Array/map
if (!Array.prototype.map) {
    Array.prototype.map = function(fun /*, thisp*/) {
        var len = this.length >>> 0;
        if (typeof fun != "function")
          throw new TypeError();

        var res = new Array(len);
        var thisp = arguments[1];
        for (var i = 0; i < len; i++) {
            if (i in this)
                res[i] = fun.call(thisp, this[i], i, this);
        }

        return res;
    };
}

// ES5 15.4.4.20
if (!Array.prototype.filter) {
    Array.prototype.filter = function (block /*, thisp */) {
        var values = [];
        var thisp = arguments[1];
        for (var i = 0; i < this.length; i++)
            if (block.call(thisp, this[i]))
                values.push(this[i]);
        return values;
    };
}

// ES5 15.4.4.16
if (!Array.prototype.every) {
    Array.prototype.every = function (block /*, thisp */) {
        var thisp = arguments[1];
        for (var i = 0; i < this.length; i++)
            if (!block.call(thisp, this[i]))
                return false;
        return true;
    };
}

// ES5 15.4.4.17
if (!Array.prototype.some) {
    Array.prototype.some = function (block /*, thisp */) {
        var thisp = arguments[1];
        for (var i = 0; i < this.length; i++)
            if (block.call(thisp, this[i]))
                return true;
        return false;
    };
}

// ES5 15.4.4.21
// https://developer.mozilla.org/en/Core_JavaScript_1.5_Reference/Objects/Array/reduce
if (!Array.prototype.reduce) {
    Array.prototype.reduce = function(fun /*, initial*/) {
        var len = this.length >>> 0;
        if (typeof fun != "function")
            throw new TypeError();

        // no value to return if no initial value and an empty array
        if (len == 0 && arguments.length == 1)
            throw new TypeError();

        var i = 0;
        if (arguments.length >= 2) {
            var rv = arguments[1];
        } else {
            do {
                if (i in this) {
                    rv = this[i++];
                    break;
                }

                // if array contains no values, no initial value to return
                if (++i >= len)
                    throw new TypeError();
            } while (true);
        }

        for (; i < len; i++) {
            if (i in this)
                rv = fun.call(null, rv, this[i], i, this);
        }

        return rv;
    };
}

// ES5 15.4.4.22
// https://developer.mozilla.org/en/Core_JavaScript_1.5_Reference/Objects/Array/reduceRight
if (!Array.prototype.reduceRight) {
    Array.prototype.reduceRight = function(fun /*, initial*/) {
        var len = this.length >>> 0;
        if (typeof fun != "function")
            throw new TypeError();

        // no value to return if no initial value, empty array
        if (len == 0 && arguments.length == 1)
            throw new TypeError();

        var i = len - 1;
        if (arguments.length >= 2) {
            var rv = arguments[1];
        } else {
            do {
                if (i in this) {
                    rv = this[i--];
                    break;
                }

                // if array contains no values, no initial value to return
                if (--i < 0)
                    throw new TypeError();
            } while (true);
        }

        for (; i >= 0; i--) {
            if (i in this)
                rv = fun.call(null, rv, this[i], i, this);
        }

        return rv;
    };
}

// ES5 15.4.4.14
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (value /*, fromIndex */ ) {
        var length = this.length;
        if (!length)
            return -1;
        var i = arguments[1] || 0;
        if (i >= length)
            return -1;
        if (i < 0)
            i += length;
        for (; i < length; i++) {
            if (!Object.prototype.hasOwnProperty.call(this, i))
                continue;
            if (value === this[i])
                return i;
        }
        return -1;
    };
}

// ES5 15.4.4.15
if (!Array.prototype.lastIndexOf) {
    Array.prototype.lastIndexOf = function (value /*, fromIndex */) {
        var length = this.length;
        if (!length)
            return -1;
        var i = arguments[1] || length;
        if (i < 0)
            i += length;
        i = Math.min(i, length - 1);
        for (; i >= 0; i--) {
            if (!Object.prototype.hasOwnProperty.call(this, i))
                continue;
            if (value === this[i])
                return i;
        }
        return -1;
    };
}

//
// Object
// ======
// 

// ES5 15.2.3.2
if (!Object.getPrototypeOf) {
    Object.getPrototypeOf = function (object) {
        return object.__proto__;
        // or undefined if not available in this engine
    };
}

// ES5 15.2.3.3
if (!Object.getOwnPropertyDescriptor) {
    Object.getOwnPropertyDescriptor = function (object) {
        return {}; // XXX
    };
}

// ES5 15.2.3.4
if (!Object.getOwnPropertyNames) {
    Object.getOwnPropertyNames = function (object) {
        return Object.keys(object);
    };
}

// ES5 15.2.3.5 
if (!Object.create) {
    Object.create = function(prototype, properties) {
        if (typeof prototype != "object" || prototype === null)
            throw new TypeError("typeof prototype["+(typeof prototype)+"] != 'object'");
        function Type() {};
        Type.prototype = prototype;
        var object = new Type();
        if (typeof properties !== "undefined")
            Object.defineProperties(object, properties);
        return object;
    };
}

// ES5 15.2.3.6
if (!Object.defineProperty) {
    Object.defineProperty = function(object, property, descriptor) {
        var has = Object.prototype.hasOwnProperty;
        if (typeof descriptor == "object" && object.__defineGetter__) {
            if (has.call(descriptor, "value")) {
                if (!object.__lookupGetter__(property) && !object.__lookupSetter__(property))
                    // data property defined and no pre-existing accessors
                    object[property] = descriptor.value;
                if (has.call(descriptor, "get") || has.call(descriptor, "set"))
                    // descriptor has a value property but accessor already exists
                    throw new TypeError("Object doesn't support this action");
            }
            // fail silently if "writable", "enumerable", or "configurable"
            // are requested but not supported
            /*
            // alternate approach:
            if ( // can't implement these features; allow false but not true
                !(has.call(descriptor, "writable") ? descriptor.writable : true) ||
                !(has.call(descriptor, "enumerable") ? descriptor.enumerable : true) ||
                !(has.call(descriptor, "configurable") ? descriptor.configurable : true)
            )
                throw new RangeError(
                    "This implementation of Object.defineProperty does not " +
                    "support configurable, enumerable, or writable."
                );
            */
            else if (typeof descriptor.get == "function")
                object.__defineGetter__(property, descriptor.get);
            if (typeof descriptor.set == "function")
                object.__defineSetter__(property, descriptor.set);
        }
        return object;
    };
}

// ES5 15.2.3.7
if (!Object.defineProperties) {
    Object.defineProperties = function(object, properties) {
        for (var property in properties) {
            if (Object.prototype.hasOwnProperty.call(properties, property))
                Object.defineProperty(object, property, properties[property]);
        }
        return object;
    };
}

// ES5 15.2.3.8
if (!Object.seal) {
    Object.seal = function (object) {
        return object;
    };
}

// ES5 15.2.3.9
if (!Object.freeze) {
    Object.freeze = function (object) {
        return object;
    };
}

// ES5 15.2.3.10
if (!Object.preventExtensions) {
    Object.preventExtensions = function (object) {
        return object;
    };
}

// ES5 15.2.3.11
if (!Object.isSealed) {
    Object.isSealed = function (object) {
        return false;
    };
}

// ES5 15.2.3.12
if (!Object.isFrozen) {
    Object.isFrozen = function (object) {
        return false;
    };
}

// ES5 15.2.3.13
if (!Object.isExtensible) {
    Object.isExtensible = function (object) {
        return true;
    };
}

// ES5 15.2.3.14
if (!Object.keys) {
    Object.keys = function (object) {
        var keys = [];
        for (var name in object) {
            if (Object.prototype.hasOwnProperty.call(object, name)) {
                keys.push(name);
            }
        }
        return keys;
    };
}

//
// Date
// ====
//

// ES5 15.9.5.43
// Format a Date object as a string according to a subset of the ISO-8601 standard.
// Useful in Atom, among other things.
if (!Date.prototype.toISOString) {
    Date.prototype.toISOString = function() {
        return (
            this.getFullYear() + "-" +
            (this.getMonth() + 1) + "-" +
            this.getDate() + "T" +
            this.getHours() + ":" +
            this.getMinutes() + ":" +
            this.getSeconds() + "Z"
        ); 
    }
}

// ES5 15.9.4.4
if (!Date.now) {
    Date.now = function () {
        return new Date().getTime();
    };
}

// ES5 15.9.5.44
if (!Date.prototype.toJSON) {
    Date.prototype.toJSON = function (key) {
        // This function provides a String representation of a Date object for
        // use by JSON.stringify (15.12.3). When the toJSON method is called
        // with argument key, the following steps are taken:

        // 1.  Let O be the result of calling ToObject, giving it the this
        // value as its argument.
        // 2. Let tv be ToPrimitive(O, hint Number).
        // 3. If tv is a Number and is not finite, return null.
        // XXX
        // 4. Let toISO be the result of calling the [[Get]] internal method of
        // O with argument "toISOString".
        // 5. If IsCallable(toISO) is false, throw a TypeError exception.
        if (typeof this.toISOString != "function")
            throw new TypeError();
        // 6. Return the result of calling the [[Call]] internal method of
        // toISO with O as the this value and an empty argument list.
        return this.toISOString();

        // NOTE 1 The argument is ignored.

        // NOTE 2 The toJSON function is intentionally generic; it does not
        // require that its this value be a Date object. Therefore, it can be
        // transferred to other kinds of objects for use as a method. However,
        // it does require that any such object have a toISOString method. An
        // object is free to use the argument key to filter its
        // stringification.
    };
}

// 15.9.4.2 Date.parse (string)
// 15.9.1.15 Date Time String Format
// Date.parse
// based on work shared by Daniel Friesen (dantman)
// http://gist.github.com/303249
if (isNaN(Date.parse("T00:00"))) {
    // XXX global assignment won't work in embeddings that use
    // an alternate object for the context.
    Date = (function(NativeDate) {

        // Date.length === 7
        var Date = function(Y, M, D, h, m, s, ms) {
            var length = arguments.length;
            if (this instanceof NativeDate) {
                var date = length === 1 && String(Y) === Y ? // isString(Y)
                    // We explicitly pass it through parse:
                    new NativeDate(Date.parse(Y)) :
                    // We have to manually make calls depending on argument
                    // length here
                    length >= 7 ? new NativeDate(Y, M, D, h, m, s, ms) :
                    length >= 6 ? new NativeDate(Y, M, D, h, m, s) :
                    length >= 5 ? new NativeDate(Y, M, D, h, m) :
                    length >= 4 ? new NativeDate(Y, M, D, h) :
                    length >= 3 ? new NativeDate(Y, M, D) :
                    length >= 2 ? new NativeDate(Y, M) :
                    length >= 1 ? new NativeDate(Y) :
                                  new NativeDate();
                // Prevent mixups with unfixed Date object
                date.constructor = Date;
                return date;
            }
            return NativeDate.apply(this, arguments);
        };

        // 15.9.1.15 Date Time String Format
        var isoDateExpression = new RegExp("^" +
            "(?:" + // optional year-month-day
                "(" + // year capture
                    "(?:[+-]\\d\\d)?" + // 15.9.1.15.1 Extended years
                    "\\d\\d\\d\\d" + // four-digit year
                ")" +
                "(?:-" + // optional month-day
                    "(\\d\\d)" + // month capture
                    "(?:-" + // optional day
                        "(\\d\\d)" + // day capture
                    ")?" +
                ")?" +
            ")?" + 
            "(?:T" + // hour:minute:second.subsecond
                "(\\d\\d)" + // hour capture
                ":(\\d\\d)" + // minute capture
                "(?::" + // optional :second.subsecond
                    "(\\d\\d)" + // second capture
                    "(?:\\.(\\d\\d\\d))?" + // milisecond capture
                ")?" +
            ")?" +
            "(?:" + // time zone
                "Z|" + // UTC capture
                "([+-])(\\d\\d):(\\d\\d)" + // timezone offset
                // capture sign, hour, minute
            ")?" +
        "$");

        // Copy any custom methods a 3rd party library may have added
        for (var key in NativeDate)
            Date[key] = NativeDate[key];

        // Copy "native" methods explicitly; they may be non-enumerable
        Date.now = NativeDate.now;
        Date.UTC = NativeDate.UTC;
        Date.prototype = NativeDate.prototype;
        Date.prototype.constructor = Date;

        // Upgrade Date.parse to handle the ISO dates we use
        // TODO review specification to ascertain whether it is
        // necessary to implement partial ISO date strings.
        Date.parse = function(string) {
            var match = isoDateExpression.exec(string);
            if (match) {
                match.shift(); // kill match[0], the full match
                // recognize times without dates before normalizing the
                // numeric values, for later use
                var timeOnly = match[0] === undefined;
                // parse numerics
                for (var i = 0; i < 10; i++) {
                    // skip + or - for the timezone offset
                    if (i === 7)
                        continue;
                    // Note: parseInt would read 0-prefix numbers as
                    // octal.  Number constructor or unary + work better
                    // here:
                    match[i] = +(match[i] || (i < 3 ? 1 : 0));
                    // match[1] is the month. Months are 0-11 in JavaScript
                    // Date objects, but 1-12 in ISO notation, so we
                    // decrement.
                    if (i === 1)
                        match[i]--;
                }
                // if no year-month-date is provided, return a milisecond
                // quantity instead of a UTC date number value.
                if (timeOnly)
                    return ((match[3] * 60 + match[4]) * 60 + match[5]) * 1000 + match[6];

                // account for an explicit time zone offset if provided
                var offset = (match[8] * 60 + match[9]) * 60 * 1000;
                if (match[6] === "-")
                    offset = -offset;

                return NativeDate.UTC.apply(this, match.slice(0, 7)) + offset;
            }
            return NativeDate.parse.apply(this, arguments);
        };

        return Date;
    })(Date);
}

// 
// Function
// ========
// 

// ES-5 15.3.4.5
// http://www.ecma-international.org/publications/files/drafts/tc39-2009-025.pdf
var slice = Array.prototype.slice;
if (!Function.prototype.bind) {
    Function.prototype.bind = function (that) { // .length is 1
        // 1. Let Target be the this value.
        var target = this;
        // 2. If IsCallable(Target) is false, throw a TypeError exception.
        // XXX this gets pretty close, for all intents and purposes, letting 
        // some duck-types slide
        if (typeof target.apply != "function" || typeof target.call != "function")
            return new TypeError();
        // 3. Let A be a new (possibly empty) internal list of all of the
        //   argument values provided after thisArg (arg1, arg2 etc), in order.
        var args = slice.call(arguments);
        // 4. Let F be a new native ECMAScript object.
        // 9. Set the [[Prototype]] internal property of F to the standard
        //   built-in Function prototype object as specified in 15.3.3.1.
        // 10. Set the [[Call]] internal property of F as described in
        //   15.3.4.5.1.
        // 11. Set the [[Construct]] internal property of F as described in
        //   15.3.4.5.2.
        // 12. Set the [[HasInstance]] internal property of F as described in
        //   15.3.4.5.3.
        // 13. The [[Scope]] internal property of F is unused and need not
        //   exist.
        var bound = function () {

            if (this instanceof bound) {
                // 15.3.4.5.2 [[Construct]]
                // When the [[Construct]] internal method of a function object,
                // F that was created using the bind function is called with a
                // list of arguments ExtraArgs the following steps are taken:
                // 1. Let target be the value of F's [[TargetFunction]]
                //   internal property.
                // 2. If target has no [[Construct]] internal method, a
                //   TypeError exception is thrown.
                // 3. Let boundArgs be the value of F's [[BoundArgs]] internal
                //   property.
                // 4. Let args be a new list containing the same values as the
                //   list boundArgs in the same order followed by the same
                //   values as the list ExtraArgs in the same order.

                var self = Object.create(target.prototype);
                target.apply(self, args.concat(slice.call(arguments)));
                return self;

            } else {
                // 15.3.4.5.1 [[Call]]
                // When the [[Call]] internal method of a function object, F,
                // which was created using the bind function is called with a
                // this value and a list of arguments ExtraArgs the following
                // steps are taken:
                // 1. Let boundArgs be the value of F's [[BoundArgs]] internal
                //   property.
                // 2. Let boundThis be the value of F's [[BoundThis]] internal
                //   property.
                // 3. Let target be the value of F's [[TargetFunction]] internal
                //   property.
                // 4. Let args be a new list containing the same values as the list
                //   boundArgs in the same order followed by the same values as
                //   the list ExtraArgs in the same order. 5.  Return the
                //   result of calling the [[Call]] internal method of target
                //   providing boundThis as the this value and providing args
                //   as the arguments.

                // equiv: target.call(this, ...boundArgs, ...args)
                return target.call.apply(
                    target,
                    args.concat(slice.call(arguments))
                );

            }

        };
        // 5. Set the [[TargetFunction]] internal property of F to Target.
        // extra:
        bound.bound = target;
        // 6. Set the [[BoundThis]] internal property of F to the value of
        // thisArg.
        // extra:
        bound.boundTo = that;
        // 7. Set the [[BoundArgs]] internal property of F to A.
        // extra:
        bound.boundArgs = args;
        bound.length = (
            // 14. If the [[Class]] internal property of Target is "Function", then
            typeof target == "function" ?
            // a. Let L be the length property of Target minus the length of A.
            // b. Set the length own property of F to either 0 or L, whichever is larger.
            Math.max(target.length - args.length, 0) :
            // 15. Else set the length own property of F to 0.
            0
        )
        // 16. The length own property of F is given attributes as specified in
        //   15.3.5.1.
        // TODO
        // 17. Set the [[Extensible]] internal property of F to true.
        // TODO
        // 18. Call the [[DefineOwnProperty]] internal method of F with
        //   arguments "caller", PropertyDescriptor {[[Value]]: null,
        //   [[Writable]]: false, [[Enumerable]]: false, [[Configurable]]:
        //   false}, and false.
        // TODO
        // 19. Call the [[DefineOwnProperty]] internal method of F with
        //   arguments "arguments", PropertyDescriptor {[[Value]]: null,
        //   [[Writable]]: false, [[Enumerable]]: false, [[Configurable]]:
        //   false}, and false.
        // TODO
        // NOTE Function objects created using Function.prototype.bind do not
        // have a prototype property.
        // XXX can't delete it in pure-js.
        return bound;
    };
}

//
// String
// ======
//

// ES5 15.5.4.20
if (!String.prototype.trim) {
    // http://blog.stevenlevithan.com/archives/faster-trim-javascript
    var trimBeginRegexp = /^\s\s*/;
    var trimEndRegexp = /\s\s*$/;
    String.prototype.trim = function () {
        return String(this).replace(trimBeginRegexp, '').replace(trimEndRegexp, '');
    };
}


/**
 *  This file implements BravoJS, a CommonJS Modules/2.0 environment.
 *
 *  Copyright (c) 2010, PageMail, Inc.
 *  Wes Garland, wes@page.ca
 *  MIT License
 *
 *    - Initial implementation
 *
 *  Copyright (c) 2011, Christoph Dorn
 *  Christoph Dorn, christoph@christophdorn.com
 *  MIT License
 *
 *    - Added package and mappings support
 *    - Various tweaks
 *
 */

function bravojs_init(bravojs,    /**< Namespace object for this implementation */
                      window)
{
try {

bravojs.window = window;

if (!bravojs.hasOwnProperty("errorReporter"))
{
  bravojs.errorReporter = function bravojs_defaultDrrorReporter(e)
  {
    if (typeof alert != "undefined")
        alert(" * BravoJS: " + e + "\n" + e.stack);
    throw(e);
  }
}

/** Reset the environment so that a new main module can be loaded */
bravojs.reset = function bravojs_reset(mainModuleDir, plugins)
{
  if (!mainModuleDir)
  {
    if (typeof bravojs.mainModuleDir != "undefined")
      mainModuleDir = bravojs.mainModuleDir;
    else
      mainModuleDir = bravojs.dirname(bravojs.URL_toId(window.location.href + ".js", true));
  }

  bravojs.requireMemo 			= {};	/**< Module exports, indexed by canonical name */
  bravojs.pendingModuleDeclarations	= {};	/**< Module.declare arguments, indexed by canonical name */
  bravojs.mainModuleDir 		= mainModuleDir;
  bravojs.plugins = plugins || [];
  bravojs.contexts = {};
  bravojs.activeContexts = [];

  delete bravojs.Module.prototype.main;
  delete bravojs.scriptTagMemo;
  delete bravojs.scriptTagMemoIE;

  /* The default context. Needed before bravojs.Module() can be called. */
  bravojs.makeContext("_");

  /** Extra-module environment */
  bravojs.module = window.module = new bravojs.Module('', []);
  bravojs.require = window.require = bravojs.requireFactory(bravojs.mainModuleDir, [], bravojs.module);

  /* Module.declare function which handles main modules inline SCRIPT tags.
   * This function gets deleted as soon as it runs, allowing the module.declare
   * from the prototype take over. Modules created from this function have
   * the empty string as module.id.
   */
  bravojs.module.declare = function main_module_declare(dependencies, moduleFactory)
  {
    if (typeof dependencies === "function")
    {
      moduleFactory = dependencies;
      dependencies = [];
    }

    bravojs.initializeMainModule(dependencies, moduleFactory, '');
  }
}

/** Print to text to stdout */
function bravojs_print()
{
  var output="";
  var i;
  var stdout;

  for (i=0; i < arguments.length; i++)
    output += arguments[i] + (i===arguments.length - 1 ? "" : " ");
  output.replace(/\t/, "        ");

  if (typeof window.document != "undefined" && (stdout = window.document.getElementById('stdout')))
  {
    output += "\n";

    if (typeof stdout.value !== "undefined")
    {
      stdout.value += output;
      if (stdout.focus)
        stdout.focus();

      if (stdout.tagName === "TEXTAREA")
        stdout.scrollTop = stdout.scrollHeight;
    }
    else
    {
      if (typeof stdout.innerText !== "undefined")
      {
        stdout.innerText = stdout.innerText.slice(0,-1) + output + " "; 	/* IE normalizes trailing newlines away */
      }
      else
        stdout.textContent += output;
    }
  }
  else if (typeof console === "object" && console.print)
  {
    console.print(output);
  }
  else if (typeof console === "object" && console.log)
  {
    console.log(output);
  }
  // WebWorker
  else if (typeof importScripts === "function" && typeof postMessage === "function")
  {
      postMessage({type: "log", data: output});
  }
  else
    alert(" * BravoJS stdout: " + output);
}
if (typeof bravojs.print === "undefined")
    bravojs.print = bravojs_print;

bravojs.registerPlugin = function(plugin)
{
    plugin.bravojs = bravojs;
    bravojs.plugins.push(plugin);
    if (typeof plugin.init == "function")
      plugin.init();
}

bravojs.callPlugins = function(method, args)
{
  var i, ret;
  for (i = 0 ; i < bravojs.plugins.length ; i++ )
  {
    if (typeof bravojs.plugins[i][method] != "undefined" &&
        typeof (ret = bravojs.plugins[i][method].apply(bravojs.plugins[i], args)) != "undefined")
        break;
  }
  return ret;
}

/** Canonicalize path, compacting slashes and dots per basic UNIX rules.
 *  Treats paths with trailing slashes as though they end with INDEX instead.
 *  Not rigorous.
 */
bravojs.realpath = function bravojs_realpath(path, index)
{
  if (typeof index === "undefined")
    index = "INDEX";
  if (typeof path !== "string")
    path = path.toString();

  var oldPath = path.split('/');
  var newPath = [];
  var i;

  if (path.charAt(path.length - 1) === '/' && index)
    oldPath.push(index);

  for (i = 0; i < oldPath.length; i++)
  {
    if (oldPath[i] == '.' || !oldPath[i].length)
      continue;
    if (oldPath[i] == '..')
    {
      if (!newPath.length)
	throw new Error("Invalid module path: " + path);
      newPath.pop();
      continue;
    }
    newPath.push(oldPath[i]);
  }

  newPath.unshift('');
  return newPath.join('/');
}

/** Extract the non-directory portion of a path */
bravojs.basename = function bravojs_basename(path)
{
  if (typeof path !== "string")
    path = path.toString();

  var s = path.split('/').slice(-1).join('/');
  if (!s)
    return path;
  return s;
}

/** Extract the directory portion of a path */
bravojs.dirname = function bravojs_dirname(path)
{
  if (typeof path !== "string")
    path = path.toString();

  if (path.charAt(path.length - 1) === '/')
    return path.slice(0,-1);

  var s = path.split('/').slice(0,-1).join('/');
  if (!s)
    return ".";

  return s;
}

/** Turn a module identifier and module directory into a canonical
 *  module.id.
 */
bravojs.makeModuleId = function makeModuleId(relativeModuleDir, moduleIdentifier)
{
  return bravojs.contextForId(relativeModuleDir, true).resolveId(moduleIdentifier, relativeModuleDir);
}

/** Turn a script URL into a canonical module.id */
bravojs.URL_toId = function URL_toId(moduleURL, relaxValidation)
{
  var i;

  /* Treat the whole web as our module repository.
   * 'http://www.page.ca/a/b/module.js' has id '/www.page.ca/a/b/module'. 
   */
  i = moduleURL.indexOf("://");
  if (i == -1)
    throw new Error("Invalid module URL: " + moduleURL);
  id = moduleURL.slice(i + 2);

  id = bravojs.realpath(id);
  if ((i = id.indexOf('?')) != -1)
    id = id.slice(0, i);
  if ((i = id.indexOf('#')) != -1)
    id = id.slice(0, i);

  if (!relaxValidation && (id.slice(-3) != ".js"))
    throw new Error("Invalid module URL: " + moduleURL);
  id = id.slice(0,-3);

  return id;
}

/** Normalize a dependency array so that only unique and previously unprovided 
 *  dependencies appear in the output list. The output list also canonicalizes
 *  the module names relative to the current require. Labeled dependencies are
 *  unboxed.
 *  If relativeModuleDir is set it is used to resolve relative dependencies.
 */
bravojs.normalizeDependencyArray = function bravojs_normalizeDependencyArray(dependencies, relativeModuleDir)
{
  var normalizedDependencies = [];
  var i, label;

  function addNormal(moduleIdentifier)
  {
    var id = moduleIdentifier;

    if (typeof id != "string" || id.charAt(0) != "/")
      id = bravojs.contextForId(relativeModuleDir, true).resolveId(id, relativeModuleDir);

    if (id === null)
      return;

    if (bravojs.requireMemo[id] || bravojs.pendingModuleDeclarations[id])
      return;

    normalizedDependencies.push(id);
  }

  for (i=0; i < dependencies.length; i++)
  {
    if (dependencies[i])
    {
      switch(typeof dependencies[i])
      {
        case "object":
          for (label in dependencies[i])
          {
            if (dependencies[i].hasOwnProperty(label))
              addNormal(dependencies[i][label]);
          }
          break;

        case "string":
          addNormal(dependencies[i]);
          break;

        default:
          throw new Error("Invalid dependency array value at position " + (i+1));
      }
    }
  }

  return normalizedDependencies;
}

/** Get a context for a given module ID used to resolve the ID.
 * Plugins should override this function to provide additional contexts.
 */
bravojs.contextForId = function bravojs_contextForId(id, onlyCreateIfDelimited)
{
  return bravojs.contexts["_"];
}

/** Make a new context used to resolve module IDs. */
bravojs.makeContext = function bravojs_makeContext(id)
{
  return bravojs.contexts[id] = new bravojs.Context(id);
}

/** A Context object used to resolve IDs. */
bravojs.Context = function bravojs_Context(id)
{
  this.id = id;
}

bravojs.Context.prototype.resolveId = function bravojs_Context_resolveId(moduleIdentifier, relativeModuleDir)
{
  var id;

  if (moduleIdentifier === '')  /* Special case for main module */
    return '';

  if (typeof moduleIdentifier !== "string")
    throw new Error("Invalid module identifier: " + moduleIdentifier);

  if (moduleIdentifier.charAt(0) === '/')
  {
    /* Absolute path. Not required by CommonJS but it makes dependency list optimization easier */
    id = moduleIdentifier;
  }
  else
  if ((moduleIdentifier.indexOf("./") == 0) || (moduleIdentifier.indexOf("../") == 0))
  {
    /* Relative module path -- relative to relativeModuleDir */
    id = relativeModuleDir + "/" + moduleIdentifier;
  }
  else
  {
    /* Top-level module. Since we don't implement require.paths,
     *  make it relative to the main module.
     */
    id = bravojs.mainModuleDir + "/" + moduleIdentifier;
  }

  return bravojs.realpath(id);
}

/** Provide a module to the environment 
 *  @param	dependencies		A dependency array
 *  @param	moduleFactoryFunction	The function which will eventually be invoked
 *					to decorate the module's exports. If not specified,
 *					we assume the factory has already been memoized in
 *					the bravojs.pendingModuleDeclarations object.
 *  @param	id			The module.id of the module we're providing
 *  @param	callback		Optional function to run after the module has been
 *					provided to the environment
 */
bravojs.provideModule = function bravojs_provideModule(dependencies, moduleFactory, 
						       id, callback)
{
  /* Memoize the the factory, satistfy the dependencies, and invoke the callback */
  if (moduleFactory)
    bravojs.require.memoize(id, dependencies, moduleFactory);

  if (dependencies)
  {
    bravojs.module.provide(bravojs.normalizeDependencyArray(dependencies, id?bravojs.dirname(id):bravojs.mainModuleDir), callback);
  }
  else
  {
    if (callback)
      callback();
  }
}

/** Initialize a module. This makes the exports object available to require(),
 *  runs the module factory function, and removes the factory function from
 *  the pendingModuleDeclarations object.
 */
bravojs.initializeModule = function bravojs_initializeModule(id)
{
  var moduleDir     = id ? bravojs.dirname(id) : bravojs.mainModuleDir;
  var moduleFactory = bravojs.pendingModuleDeclarations[id].moduleFactory;
  var dependencies  = bravojs.pendingModuleDeclarations[id].dependencies;
  var require, exports, module;

  delete bravojs.pendingModuleDeclarations[id];

  exports = bravojs.requireMemo[id] = {};
  module  = new bravojs.Module(id, dependencies);

  if (typeof module.augment == "function")
    module.augment();

  require = bravojs.requireFactory(moduleDir, dependencies, module);

  moduleFactory(require, exports, module);
}

/** Search the module memo and return the correct module's exports, or throw.
 *  Searching the module memo will initialize a matching pending module factory.
 */
bravojs.requireModule = function bravojs_requireModule(parentModuleDir, moduleIdentifier)
{
  /* Remove all active contexts as they are not needed any more (load cycle complete) */
  bravojs.activeContexts = [];

  var id = bravojs.makeModuleId(parentModuleDir, moduleIdentifier);

  var exports = bravojs.callPlugins("requireModule", [id]);
  if (typeof exports != "undefined")
  {
    if (exports === true)
      return bravojs.requireMemo[id];
    return bravojs.requireMemo[id] = exports;
  }

  /* If id is false the module is not available */
  if (id === false)
    return null;

  if (!bravojs.requireMemo[id] && bravojs.pendingModuleDeclarations[id])
    bravojs.initializeModule(id);

  if (id === null || !bravojs.requireMemo[id])
    throw new Error("Module " + id + " is not available.");

  return bravojs.requireMemo[id];
}

/** Create a new require function, closing over it's path so that relative
 *  modules work as expected.
 */
bravojs.requireFactory = function bravojs_requireFactory(moduleDir, dependencies, module)
{
  var deps, i, label;

  function getContextSensitiveModuleDir()
  {
    var contextId;
    if (bravojs.activeContexts.length > 0)
      contextId = bravojs.activeContexts[bravojs.activeContexts.length-1].id;
    if (typeof contextId == "undefined" || !contextId)
      contextId = moduleDir;
    else
    if (contextId == "_")
      contextId = bravojs.mainModuleDir;
    return contextId;
  }

  function addLabeledDep(moduleIdentifier)
  {
    deps[label] = function bravojs_labeled_dependency() 
    { 
      return bravojs.requireModule(getContextSensitiveModuleDir(), moduleIdentifier);
    }
  }

  if (dependencies)
  {
    for (i=0; i < dependencies.length; i++)
    {
      if (typeof dependencies[i] !== "object")
	continue;

      for (label in dependencies[i])
      {
	if (dependencies[i].hasOwnProperty(label))
	{
	  if (!deps)
	    deps = {};
	  addLabeledDep(dependencies[i][label]);
	}
      }
    }
  }

  var newRequire = function require(moduleIdentifier) 
  {
    if (deps && deps[moduleIdentifier])
      return deps[moduleIdentifier]();
    return bravojs.requireModule(getContextSensitiveModuleDir(), moduleIdentifier);
  };

  var ret = bravojs.callPlugins("newRequire", [{
      module: module,
      deps: deps,
      getContextSensitiveModuleDir: getContextSensitiveModuleDir
  }]);
  if (typeof ret != "undefined")
    newRequire = ret;

  newRequire.paths = [bravojs.mainModuleDir];

  if (typeof bravojs.platform != "undefined")
      newRequire.platform = bravojs.platform;

  newRequire.id = function require_id(moduleIdentifier, unsanitized)
  {
    var contextId = getContextSensitiveModuleDir(),
        context = bravojs.contextForId(contextId, true);
        id = context.resolveId(moduleIdentifier, contextId);
    if (unsanitized)
      return id;
    return bravojs.callPlugins("sanitizeId", [id]) || id;
  }

  newRequire.uri = function require_uri(moduleIdentifierPath)
  {
    var basename = bravojs.basename(moduleIdentifierPath),
        parts = basename.split(".");
    var uri = window.location.protocol + "/" + newRequire.id(moduleIdentifierPath, true);
    if (parts.length > 1)
        uri += "." + parts.slice(1).join(".");
    return uri;
  }

  newRequire.canonicalize = function require_canonicalize(moduleIdentifier)
  {
    var id = bravojs.makeModuleId(getContextSensitiveModuleDir(), moduleIdentifier);

    if (id === '')
      throw new Error("Cannot canonically name the resource bearing this main module");

    return window.location.protocol + "/" + id + ".js";
  }

  newRequire.memoize = function require_memoize(id, dependencies, moduleFactory)
  {
    bravojs.pendingModuleDeclarations[id] = { moduleFactory: moduleFactory, dependencies: dependencies };
  }

  newRequire.isMemoized = function require_isMemoized(id)
  {
    return (bravojs.pendingModuleDeclarations[id] || bravojs.requireMemo[id]) ? true : false;
  }

  newRequire.getMemoized = function require_getMemoized(id)
  {
    return bravojs.pendingModuleDeclarations[id] || bravojs.requireMemo[id];
  }

  bravojs.callPlugins("augmentNewRequire", [newRequire, {
      module: module,
      getContextSensitiveModuleDir: getContextSensitiveModuleDir
  }]);

  return newRequire;
}

/** Module object constructor 
 *
 *  @param	id		The canonical module id
 *  @param	dependencies	The dependency list passed to module.declare
 */
bravojs.Module = function bravojs_Module(id, dependencies)
{
  this._id       = id;
  this.id        = bravojs.callPlugins("sanitizeId", [id]) || id;
  this["protected"] = void 0;
  this.dependencies = dependencies;
  this.print = bravojs.print;

  var i, label;

  /* Create module.deps array */
  this.deps = {};

  for (i=0; i < dependencies.length; i++)
  {
    if (typeof dependencies[i] === "string")
      continue;

    if (typeof dependencies[i] !== "object")
      throw new Error("Invalid " + typeof dependencies[i] + " element in dependency array at position " + i);

    /* Labeled dependency object */
    for (label in dependencies[i])
    {
      if (dependencies[i].hasOwnProperty(label))
      {
        this.deps[label] = function bravojs_lambda_module_deps() 
        {
          bravojs.requireModule(bravojs.dirname(id), dependencies[i][label]);
        };
      }
    }
  }
}

/** A module.declare suitable for use during DOM SCRIPT-tag insertion.
 * 
 *  The general technique described below was invented by Kris Zyp.
 *
 *  In non-IE browsers, the script's onload event fires as soon as the 
 *  script finishes running, so we just memoize the declaration without
 *  doing anything. After the script is loaded, we do the "real" work
 *  as the onload event also supplies the script's URI, which we use
 *  to generate the canonical module id.
 * 
 *  In IE browsers, the event can fire when the tag is being inserted
 *  in the DOM, or sometime thereafter. In the first case, we read a 
 *  memo we left behind when we started inserting the tag; in the latter,
 *  we look for interactive scripts.
 *
 *  Event			Action		
 *  -------------------------   ------------------------------------------------------------------------------------
 *  Inject Script Tag		onload event populated with URI
 *				scriptTagMemo populated with URI
 *  IE pulls from cache		cname derived in module.declare from scriptTagMemo, invoke provideModule
 *  IE pulls from http		cname derived in module.declare from script.src, invoke provideModule
 *  Non-IE loads script		onload event triggered, most recent incomplete module.declare is completed, 
 *				deriving the cname from the onload event.
 */
bravojs.Module.prototype.declare = function bravojs_Module_declare(dependencies, moduleFactory)
{
  var stm;

  if (typeof dependencies === "function")
  {
    moduleFactory = dependencies;
    dependencies = [];
  }

  stm = bravojs.scriptTagMemo;
  if (stm && stm.id === '')		/* Static HTML module */
  {
    delete bravojs.scriptTagMemo;
    bravojs.provideModule(dependencies, moduleFactory, stm.id, stm.callback);    
    return;
  }

  if (stm)
    throw new Error("Bug");

  if (document.addEventListener)	/* non-IE, defer work to script's onload event which will happen immediately */
  {
    bravojs.scriptTagMemo = { dependencies: dependencies, moduleFactory: moduleFactory };
    return;
  }

  stm = bravojs.scriptTagMemoIE;
  delete bravojs.scriptTagMemoIE;

  if (stm && stm.id) 			/* IE, pulling from cache */
  {
    bravojs.provideModule(dependencies, moduleFactory, stm.id, stm.callback);
    return;
  }

  /* Assume IE fetching from remote */
  var scripts = document.getElementsByTagName("SCRIPT");
  var i;

  for (i = 0; i < scripts.length; i++)
  {
    if (scripts[i].readyState === "interactive")
    {
      bravojs.provideModule(dependencies, moduleFactory, bravojs.URL_toId(scripts[i].src), stm.callback);
      return;
    }
  }

  throw new Error("Could not determine module's canonical name from script-tag loader");
}

/** A module.provide suitable for a generic web-server back end.  Loads one module at
 *  a time in continuation-passing style, eventually invoking the passed callback.
 * 
 *  A more effecient function could be written to take advantage of a web server
 *  which might aggregate and transport more than one module per HTTP request.
 *
 *  @param	dependencies	A dependency array
 *  @param	callback	The callback to invoke once all dependencies have been
 *				provided to the environment. Optional.
 */
bravojs.Module.prototype.provide = function bravojs_Module_provide(dependencies, callback)
{
  var self = arguments.callee;

  if ((typeof dependencies !== "object") || (dependencies.length !== 0 && !dependencies.length))
    throw new Error("Invalid dependency array: " + dependencies.toString());

  dependencies = bravojs.normalizeDependencyArray(dependencies, (this._id)?this._id:bravojs.mainModuleDir);

  if (dependencies.length === 0)
  {
    if (callback)
      callback();
    return;
  }

  bravojs.activeContexts.push(bravojs.contextForId(dependencies[0], true));

  bravojs.module.load(dependencies[0], function bravojs_lambda_provideNextDep() { self(dependencies.slice(1), callback) });

  bravojs.activeContexts.pop();
}

/** A module.load suitable for a generic web-server back end. The module is
 *  loaded by injecting a SCRIPT tag into the DOM.
 *
 *  @param	moduleIdentifier	Module to load
 *  @param	callback		Callback to invoke when the module has loaded.
 *
 *  @see	bravojs_Module_declare
 */
bravojs.Module.prototype.load = function bravojs_Module_load(moduleIdentifier, callback)
{
  if (window.module.hasOwnProperty("declare"))
    delete window.module.declare;

  var script = document.createElement('SCRIPT');
  script.setAttribute("type","text/javascript");
  script.setAttribute("src", bravojs.require.canonicalize(moduleIdentifier) + "?1");

  if (document.addEventListener)	/* Non-IE; see bravojs_Module_declare */
  {
    script.onload = function bravojs_lambda_script_onload()
    {
      /* stm contains info from recently-run module.declare() */
      var stm = bravojs.scriptTagMemo;
      if (typeof stm === "undefined")
        throw new Error("Module '" + moduleIdentifier + "' did not invoke module.declare!");

      delete bravojs.scriptTagMemo;

      if (typeof moduleIdentifier == "object")
      {
        /* The id is a mapping locator and needs to be resolved. */
        moduleIdentifier = bravojs.makeModuleId(bravojs.mainModuleDir, moduleIdentifier);
      }

      bravojs.activeContexts.push(bravojs.contextForId(moduleIdentifier, true));

      bravojs.provideModule(stm.dependencies, stm.moduleFactory, bravojs.require.id(moduleIdentifier, true), function()
      {
        callback(moduleIdentifier);
      });

      bravojs.activeContexts.pop();
    }

    script.onerror = function bravojs_lambda_script_onerror() 
    { 
      var id = bravojs.require.id(moduleIdentifier, true);
      bravojs.pendingModuleDeclarations[id] = null;	/* Mark null so we don't try to run, but also don't try to reload */
      callback();
    }
  }
  else
  {
    bravojs.scriptTagMemoIE = { moduleIdentifier: moduleIdentifier, callback: callback };

    script.onreadystatechange = function bravojs_lambda_script_onreadystatechange()
    {
      if (this.readyState != "loaded")
        return;

      /* failed load below */
      var id = bravojs.require.id(moduleIdentifier, true);

      if (!bravojs.pendingModuleDeclarations[id] && !bravojs.requireMemo[id] && id === bravojs.scriptTagMemoIE.moduleIdentifier)
      {
        bravojs.pendingModuleDeclarations[id] = null;	/* Mark null so we don't try to run, but also don't try to reload */
        callback();
      }
    }
  }

  document.getElementsByTagName("HEAD")[0].appendChild(script);
}

bravojs.Module.prototype.eventually = function(cb) { cb(); };

/** Shim the environment to have CommonJS ES-5 requirements (if needed),
 *  the execute the callback
 */
bravojs.es5_shim_then = function bravojs_es5_shim_then(callback)
{
  if (!Array.prototype.indexOf)
  {
    /* Load ES-5 shim into the environment before executing the main module */
    var script = document.createElement('SCRIPT');
    script.setAttribute("type","text/javascript");
    script.setAttribute("src", bravojs.dirname(bravojs.url) + "/global-es5.js?1");

    if (document.addEventListener)
      script.onload = callback;
    else
    {
      script.onreadystatechange = function() 
      {
	if (this.readyState === "loaded")
	  callback();
      }
    }

    document.getElementsByTagName("HEAD")[0].appendChild(script);
  }
  else
  {
    callback();
  }
}

/** Reload a module, violating the CommonJS singleton paradigm and
 *  potentially introducing bugs in to the program using this function --
 *  as references to the previous instance of the module may still be
 *  held by the application program.
 */
bravojs.reloadModule = function(id, callback)
{
  delete bravojs.pendingModuleDeclarations[id];
  delete bravojs.requireMemo[id];
  bravojs.module.provide([id], callback);
}

/** Main module bootstrap */
bravojs.initializeMainModule = function bravojs_initializeMainModule(dependencies, moduleFactory, moduleIdentifier)
{
  if (bravojs.module.hasOwnProperty("declare"))		/* special extra-module environment bootstrap declare needs to go */
    delete bravojs.module.declare;

  if (bravojs.module.constructor.prototype.main)
    throw new Error("Main module has already been initialized!");

  bravojs.es5_shim_then
  (
    (function() 
     {
       bravojs.provideModule(dependencies, moduleFactory, moduleIdentifier, function bravojs_lambda_requireMain() { bravojs.module.constructor.prototype.main = bravojs.require(moduleIdentifier); })
     })
  ); 
}

/** Run a module which is not declared in the HTML document and make it the program module.
 *  @param	dependencies		[optional]	A list of dependencies to sastify before running the mdoule
 *  @param	moduleIdentifier	moduleIdentifier, relative to dirname(window.location.href). This function
 *					adjusts the module path such that the program module's directory is the
 *					top-level module directory before the dependencies are resolved.
 *  @param	callback		[optional]	Callback to invoke once the main module has been initialized
 */
bravojs.runExternalMainModule = function bravojs_runExternalProgram(dependencies, moduleIdentifier, callback)
{
  if (arguments.length === 1 || typeof moduleIdentifier === "function")
  {
    callback = moduleIdentifier;
    moduleIdentifier = dependencies;
    dependencies = [];
  }

  delete bravojs.module.declare;

  if (moduleIdentifier.charAt(0) === '/')
    bravojs.mainModuleDir = bravojs.dirname(moduleIdentifier);
  else
    bravojs.mainModuleDir = bravojs.dirname(bravojs.URL_toId(window.location.href + ".js"), true) + "/" + bravojs.dirname(moduleIdentifier);

  moduleIdentifier = bravojs.basename(moduleIdentifier);

  bravojs.es5_shim_then(
      function() {
	bravojs.module.provide(dependencies.concat([moduleIdentifier]), 
		       function bravojs_runMainModule() {
			 bravojs.initializeMainModule(dependencies, '', moduleIdentifier);
			 if (callback)
			   callback(); 
		       })
	    });
}

bravojs.reset();

if (typeof bravojs.url === "undefined")
{
/** Set the BravoJS URL, so that BravoJS can load components
 *  relative to its install dir.  The HTML script element that
 *  loads BravoJS must either have the ID BravoJS, or be the
 *  very first script in the document.
 */ 
(function bravojs_setURL()
{
  var i;
  var checkBasename = false;
  var script;

  script = document.getElementById("BravoJS");
  if (!script)
  {
    checkBasename = true;
    script = document.getElementsByTagName("SCRIPT")[0];
  }

  bravojs.url = script.src;
  i = bravojs.url.indexOf("?");
  if (i !== -1)
    bravojs.url = bravojs.url.slice(0,i);
  i = bravojs.url.indexOf("#");
  if (i !== -1)
    bravojs.url = bravojs.url.slice(0,i);

  if (checkBasename && bravojs.basename(bravojs.url) !== "bravo.js")
    throw new Error("Could not determine BravoJS URL. BravoJS must be the first script, or have id='BravoJS'");
})();
}

/** Diagnostic Aids */
var print   = bravojs.print;
if (!window.onerror)
{
  window.onerror = function window_onerror(message, url, line) 
  { 
    var scripts, i;

    print("\n * Error: " + message + "\n" + 
          "      in: " + url + "\n" + 
          "    line: " + line);  
  }
}

} catch(e) { bravojs.errorReporter(e); }

}

if (typeof exports !== "undefined")
{
    exports.BravoJS = function(context)
    {
        context = context || {};

        var window = {
            location: {
                protocol: "memory:",
                href: "memory:/" + ((typeof context.mainModuleDir != "undefined")?context.mainModuleDir:"/bravojs/")
            }
        };

        var bravojs = {
            mainModuleDir: context.mainModuleDir || void 0,
            platform: context.platform || void 0,
            url: window.location.href,
            print: (context.api && context.api.system && context.api.system.print) || void 0,
            errorReporter: (context.api && context.api.errorReporter) || void 0,
            XMLHttpRequest: (context.api && context.api.XMLHttpRequest) || void 0,
            DEBUG: context.DEBUG || void 0
        };

        bravojs_init(bravojs, window);

        context.bravojs = bravojs;
    }
}
else
{
    if (typeof bravojs === "undefined")
      bravojs = {};
    bravojs_init(bravojs, (typeof window != "undefined")?window:this);
}

/**
 *  This file implements a bravojs core plugin to add
 *  package and package mappings support.
 *
 *  Copyright (c) 2011, Christoph Dorn
 *  Christoph Dorn, christoph@christophdorn.com
 *  MIT License
 *
 *  To use: Load BravoJS, then layer this plugin in
 *  by loading it into the extra-module environment.
 */

(function packages() {

var Plugin = function()
{
}

Plugin.prototype.init = function()
{
    var bravojs = this.bravojs;

    /** Get a context for a given module ID used to resolve the ID. If a package
     *  prefix is found a context specific to the package is returned, otherwise
     *  the default context is returned.
     */
    bravojs.contextForId = function packages_bravojs_contextForId(id, onlyCreateIfDelimited)
    {
        if (typeof id == "undefined")
            return bravojs.contexts["_"];

        id = id.replace(/^\w*!/, "");

        var parts = id.split("@/"),
            id = parts[0];

        if (/@$/.test(id))
            id = id.substring(0, id.length-1);

        var ret = bravojs.callPlugins("contextForId", [id]);
        if (typeof ret != "undefined")
            id = ret;

        if (parts.length == 1 && typeof bravojs.contexts[id] != "undefined")
            return bravojs.contexts[id];

        if (typeof bravojs.contexts[id] == "undefined")
        {
            if (onlyCreateIfDelimited === true && parts.length == 1)
                return bravojs.contexts["_"];

            bravojs.makeContext(id);
        }

        return bravojs.contexts[id];
    };

    bravojs.hasContextForId = function packages_bravojs_hasContext(id)
    {
        id = id.replace(/^\w*!/, "");
        var parts = id.split("@/");
        if (parts.length == 2)
            id = parts[0];
        if (/@$/.test(id))
            id = id.substring(0, id.length-1);
        return (typeof bravojs.contexts[id] != "undefined");
    }

    bravojs.makeContext = function packages_bravojs_makeContext(id)
    {
        id = id.replace(/^\w*!/, "");
        bravojs.contexts[id] = new bravojs.Context(id);
        /* The id so far is path-based. If the context/package descriptor specifies a UID we map
         * the same context to the UID as well.
         */
        if (typeof bravojs.contexts[id].uid != "undefined")
           bravojs.contexts[bravojs.contexts[id].uid] = bravojs.contexts[id];
        return bravojs.contexts[id];
    }

    bravojs.Context = function packages_bravojs_Context(id)
    {
        this.id = id;

        // We do not need to do anything for the default context
        if (this.id == "_")
            return;

        id = this.id + "@/package.json";

        if (bravojs.require.isMemoized(id))
        {
            this.descriptor = bravojs.require.getMemoized(id).moduleFactory();
        }
        else
        {
            this.descriptor = bravojs.callPlugins("loadPackageDescriptor", [id]);
            var self = this;
            bravojs.require.memoize(id, [], function()
            {
                return self.descriptor;
            });
        }

        this.libDir = this.descriptor.directories && this.descriptor.directories.lib;
        if (typeof this.libDir != "string")
            this.libDir = "lib";
    
        this.uid = this.descriptor.uid || void 0;
        if (typeof this.uid != "undefined")
        {
            var m = this.uid.match(/^https?:\/\/(.*)$/);
            if (!m)
                throw new Error("uid property '" + this.uid + "' must be a non-resolving or resolving URL with http or https protocol in: " + id);
            this.uid = m[1];  // strip the protocol prefix
        }
    }

    /** Get a map where labels point to package IDs for all declared mappings */
    bravojs.Context.prototype.getNormalizedMappings = function packages_bravojs_Context_getNormalizedMappings()
    {
        if (this.id == "_")
            throw new Error("Cannot get mappings for default context");
    
        if (typeof this.normalizedMappings != "undefined")
            return this.normalizedMappings;

        this.normalizedMappings = {};

        if (typeof this.descriptor.mappings != "undefined")
        {
            for (var label in this.descriptor.mappings)
            {
                this.normalizedMappings[label] = bravojs.callPlugins("normalizeLocator", [this.descriptor.mappings[label], this]).location;
            }
        }
        return this.normalizedMappings;
    }

    bravojs.Context.prototype.resolveId = function packages_bravojs_Context_resolveId(moduleIdentifier, relativeModuleDir, descriptor)
    {
        // Pull out plugin if applicable
        var plugin;
        if (typeof moduleIdentifier == "string")
        {
            var m = moduleIdentifier.match(/^(\w*)!(.*)$/);
            if (m)
            {
                plugin = m[1];
                moduleIdentifier = m[2];
            }
        }

        try {
            var ret = bravojs.callPlugins("normalizeModuleIdentifier", [moduleIdentifier, relativeModuleDir, descriptor, this]);
            
            // happens if mapping is ignored
            if (ret === false)
                return false;
            
            if (typeof ret != "undefined")
                moduleIdentifier = ret;
        }
        catch(e)
        {
            var mappings = (typeof this.descriptor != "undefined" && typeof this.descriptor.mappings != "undefined")?JSON.stringify(this.descriptor.mappings):"{}";            
            throw new Error(e + " => " + e.stack + "\nUnable to resolve moduleIdentifier '" + JSON.stringify(moduleIdentifier) + "' against context '" + this.id + "' (mappings: " + mappings + ") and relativeModuleDir '" + relativeModuleDir + "'.");
        }

        if (moduleIdentifier === null || moduleIdentifier === "")
            return moduleIdentifier;

        if (moduleIdentifier.charAt(0) == "/")
            return ((typeof plugin != "undefined")?plugin+"!":"") + moduleIdentifier;

        if (moduleIdentifier.charAt(0) == ".")
            return ((typeof plugin != "undefined")?plugin+"!":"") + bravojs.realpath(relativeModuleDir + "/" + moduleIdentifier);

        if (this.id == "_")
            return ((typeof plugin != "undefined")?plugin+"!":"") + bravojs.realpath(bravojs.mainModuleDir + "/" + moduleIdentifier);

        return ((typeof plugin != "undefined")?plugin+"!":"") + bravojs.realpath(relativeModuleDir + "/" + moduleIdentifier);
    }

    /** Run just before providing Module to moduleFactory function in bravojs.initializeModule() */
    bravojs.Module.prototype.augment = function bravojs_Module_augment()
    {
        if (this._id === "")
            return;
    
        var context = bravojs.contextForId(this._id, true);
        /* Only add extra module properties if context represents a package (i.e. not default '_' context) */
        if (context.id == "_")
            return;

        /* If context supplies a UID use it over the path-based ID for the package ID */
        this.pkgId = context.id;
    
        /* Normalized mappings are simply a map where labels point to package IDs */
        this.mappings = context.getNormalizedMappings();

        this.hashId = calcMD5(this.id);
    }

    // We need to reset bravojs to use the Context object from above (but keep registered plugins)
    bravojs.reset(null, bravojs.plugins);
}

Plugin.prototype.requireModule = function(id)
{
    if (!id)
        return;
    
    // The text plugins need special handeling
    if (id.match(/^text!/))
    {
        if (!bravojs.requireMemo[id] && bravojs.pendingModuleDeclarations[id])
        {
            bravojs.requireMemo[id] = bravojs.pendingModuleDeclarations[id].moduleFactory();
        }
        if (!bravojs.requireMemo[id])
            throw new Error("Module " + id + " is not available.");
        return true;
    }
}

Plugin.prototype.newRequire = function(helpers)
{
    var bravojs = this.bravojs;

    var newRequire = function packages_require(moduleIdentifier) 
    {
        // RequireJS compatibility. Convert require([], callback) to module.load([], callback).
        if (Object.prototype.toString.call(moduleIdentifier) == "[object Array]" && arguments.length == 2)
        {
            if (moduleIdentifier.length > 1)
               throw new Error("require([], callback) with more than one module in [] is not supported yet!");
            if (typeof bravojs.mainContext == "undefined")
                throw new Error("Cannot resolve ID for ASYNC require. bravojs.mainContext used to resolve ID not set!");
            // Load IDs are resolved against the default context. To resolve against a different
            // context use module.load([], callback).
            moduleIdentifier = bravojs.contextForId(bravojs.mainContext).resolveId(moduleIdentifier[0], helpers.getContextSensitiveModuleDir());
            var callback = arguments[1];
            bravojs.module.load(moduleIdentifier, function(id)
            {
                callback(newRequire(id));
            });
            return;
        }
        if (helpers.deps && helpers.deps[moduleIdentifier])
            return helpers.deps[moduleIdentifier]();
        return bravojs.requireModule(helpers.getContextSensitiveModuleDir(), moduleIdentifier);
    };
    return newRequire;
}

Plugin.prototype.augmentNewRequire = function(newRequire, helpers)
{
    var bravojs = this.bravojs;

    newRequire.pkg = function packages_require_pkg(packageIdentifierPath)
    {
        if (typeof helpers.module != "undefined" && typeof helpers.module.mappings != "undefined")
        {
            if (typeof helpers.module.mappings[packageIdentifierPath] != "undefined")
                packageIdentifierPath = helpers.module.mappings[packageIdentifierPath];
        }
        var context = bravojs.contextForId(packageIdentifierPath);
        return {
            id: function(moduleIdentifier, unsanitized)
            {
                if (typeof moduleIdentifier == "undefined")
                    return context.id;
                else
                {
                    var id = context.resolveId(moduleIdentifier, helpers.getContextSensitiveModuleDir());
                    if (unsanitized)
                        return id;
                    return bravojs.callPlugins("sanitizeId", [id]) || id;
                }
            }
        }
    }

    newRequire.canonicalize = function packages_require_canonicalize(moduleIdentifier)
    {
        var id = bravojs.makeModuleId(helpers.getContextSensitiveModuleDir(), moduleIdentifier);

        if (id === '')
            throw new Error("Cannot canonically name the resource bearing this main module");

        /* Remove package/module ID delimiter */
        id = bravojs.callPlugins("sanitizeId", [id]) || id;

        /* Some IDs may refer to non-js files */
        if (bravojs.basename(id).indexOf(".") == -1)
            id += ".js";

        return bravojs.window.location.protocol + "/" + id;
    }

    newRequire.nameToUrl = function(moduleIdentifier)
    {
        if (arguments.length >= 2 && arguments[1] !== null)
            throw new Error("NYI - Second argument to require.nameToUrl() must be 'null'!");
        else
        if (arguments.length >= 3 && arguments[2] != "_")
            throw new Error("NYI - Third argument to require.nameToUrl() must be '_'!");
        throw new Error("NYI - require.nameToUrl()");
/*
        var parts = moduleIdentifier.split("/");
        if (parts.length == 0)
        {
        }
        else
        {
        }
*/
    }
}

Plugin.prototype.sanitizeId = function(id)
{
    return id.replace(/@\//, "/").replace(/@$/, "");
}

/**
 * Load a package descriptor from the server.
 * 
 * NOTE: This function will block until the server returns the response!
 *       Package descriptors should be memoized before booting the program
 *       for better loading performance.
 */
Plugin.prototype.loadPackageDescriptor = function(id)
{
    // NOTE: Do NOT use require.canonicalize(id) here as it will cause an infinite loop!
    var URL = window.location.protocol + "/" + bravojs.realpath(id.replace(/@\/+/g, "\/"));

    // TODO: Get this working in other browsers
    var req = new (this.bravojs.XMLHttpRequest || XMLHttpRequest)();
    req.open("GET", URL, false);
    req.send(null);
    if(req.status == 200)
    {
        try
        {
            return JSON.parse(req.responseText);
        }
        catch(e)
        {
            throw new Error("Error parsing package descriptor from URL '" + URL + "': " + e);
        }
    }
    else
        throw new Error("Error loading package descriptor from URL: " + URL);
}

/**
 * Given a mappings locator normalize it according to it's context by
 * setting an absolute path-based location property.
 */
Plugin.prototype.normalizeLocator = function(locator, context)
{
    if (typeof locator.provider != "undefined")
    {
        // do nothing
//        locator.location = locator.provider;
    }
    else
    if (typeof locator.location != "undefined")
    {
        if ((locator.location.indexOf("./") == 0) || (locator.location.indexOf("../") == 0))
        {
            locator.location = this.bravojs.realpath(((context.id!="_")?context.id:this.bravojs.mainModuleDir) + "/" + locator.location, false) + "/";
        }
    }
    else
    if (typeof locator.id != "undefined")
    {
        if (locator.id.charAt(0) != "/")
            locator.id = this.bravojs.mainModuleDir + "/" + locator.id;
    }
    else
    if (typeof locator.catalog != "undefined" || typeof locator.archive != "undefined")
    {
        if (typeof locator.catalog != "undefined" && typeof locator.name == "undefined")
            throw new Error("Catalog-based mappings locator does not specify 'name' property: " + locator);

        var ret = this.bravojs.callPlugins("resolveLocator", [locator]);
        if (typeof ret == "undefined")
            throw new Error("Unable to resolve package locator: " + JSON.stringify(locator));

        locator.location = ret;

        if (typeof id == "undefined")
            throw new Error("Mappings locator could not be resolved by plugins: " + locator);
    }

    if (typeof locator.location != "undefined" && locator.location.charAt(locator.location.length-1) == "/")
        locator.location = locator.location.substring(0, locator.location.length -1);

    return locator;
}

/**
 * Given a moduleIdentifier convert it to a top-level ID
 */
Plugin.prototype.normalizeModuleIdentifier = function(moduleIdentifier, relativeModuleDir, descriptor, context)
{
    if (moduleIdentifier === '')  /* Special case for main module */
        return '';

    var self = this,
        bravojs = this.bravojs,
        originalModuleIdentifier = moduleIdentifier;

    function finalNormalization(moduleIdentifier)
    {
        moduleIdentifier = moduleIdentifier.replace(/{platform}/g, bravojs.require.platform);

        var parts = moduleIdentifier.replace(/\.js$/, "").split("@/");

        if (parts.length == 1)
            return moduleIdentifier;

        var context = bravojs.contextForId(parts[0]);
        // Resolve mapped modules
        if (typeof context.descriptor.modules != "undefined" && typeof context.descriptor.modules["/" + parts[1]] != "undefined")
        {
            var locator = self.normalizeLocator(context.descriptor.modules["/" + parts[1]], context);
            if (typeof locator.available != "undefined" && locator.available === false)
                return null;

            if (typeof locator.module != "undefined")
                moduleIdentifier = bravojs.contextForId(locator.location).resolveId("./" + locator.module);
        }

        // Give opportunity to verify resolved ID to discover missing mappings for example
        var ret = bravojs.callPlugins("verifyModuleIdentifier", [moduleIdentifier, {
            moduleIdentifier: originalModuleIdentifier,
            relativeModuleDir: relativeModuleDir,
            context: context
        }]);
        if (typeof ret != "undefined")
            moduleIdentifier = ret;
        if (/\.js$/.test(moduleIdentifier))
            moduleIdentifier = moduleIdentifier.substring(0, moduleIdentifier.length-3);
        return moduleIdentifier;
    }

    if (moduleIdentifier === null)
    {
        if (typeof context.descriptor == "undefined" || typeof context.descriptor.main == "undefined")
            throw new Error("'main' property not set in package descriptor for: " + this.id);
        return finalNormalization(context.id + "@/" + context.descriptor.main);
    }
    else
    if (typeof moduleIdentifier === "object")
    {
        // We have a mappings locator object
        moduleIdentifier = this.normalizeLocator(moduleIdentifier, context);

        var id;
        if (typeof moduleIdentifier.location != "undefined")
        {
            id = moduleIdentifier.location;
        }
        else
        if (typeof moduleIdentifier.id != "undefined")
        {
            id = moduleIdentifier.id;
        }
        else
            throw new Error("Invalid mapping: " + moduleIdentifier);

        if (typeof moduleIdentifier.descriptor != "undefined" && typeof moduleIdentifier.descriptor.main != "undefined")
            return finalNormalization(this.bravojs.realpath(id + "@/" + moduleIdentifier.descriptor.main, false));

        var context = this.bravojs.contextForId(id);
        if (typeof context.descriptor == "undefined" || typeof context.descriptor.main == "undefined")
            throw new Error("'main' property not set in package descriptor for: " + context.id);

        return finalNormalization(this.bravojs.realpath(context.id + "@/" + context.descriptor.main, false));
    }

    // See if moduleIdentifier matches a mapping alias exactly
    if (typeof context.descriptor != "undefined" &&
        typeof context.descriptor.mappings != "undefined" &&
        typeof context.descriptor.mappings[moduleIdentifier] != "undefined")
    {
        if (typeof context.descriptor.mappings[moduleIdentifier].available != "undefined" && context.descriptor.mappings[moduleIdentifier].available === false)
        {
            // If mapping is not available we return a null ID
            return null;
        }
        else
        if (typeof context.descriptor.mappings[moduleIdentifier].module != "undefined")
        {
            var mappedContextId = this.normalizeLocator(context.descriptor.mappings[moduleIdentifier], context).location,
                mappedContext = this.bravojs.contextForId(mappedContextId),
                mappedModule = context.descriptor.mappings[moduleIdentifier].module;

            mappedModule = mappedModule.replace(/^\./, "");

            if (mappedModule.charAt(0) == "/")
            {
                return finalNormalization(mappedContext.id + "@" + mappedModule);
            }
            else
            {
                return mappedContext.resolveId("./" + context.descriptor.mappings[moduleIdentifier].module, null);
            }
        }
        else
            throw new Error("Unable to resolve ID '" + moduleIdentifier + "' for matching mapping as 'module' property not defined in mapping locator!");
    }

    var moduleIdentifierParts = moduleIdentifier.split("@/");

    // If module ID is absolute we get appropriate context
    if (moduleIdentifierParts.length == 2)
        context = this.bravojs.contextForId(moduleIdentifierParts[0]);

    // NOTE: relativeModuleDir is checked here so we can skip this if we want a module from the package
    if (typeof context.descriptor != "undefined" &&
        typeof context.descriptor["native"] != "undefined" &&
        context.descriptor["native"] === true &&
        relativeModuleDir)
    {
        return finalNormalization(moduleIdentifierParts.pop());
    }
    else
    if (moduleIdentifier.charAt(0) == "/")
        return finalNormalization(moduleIdentifier);

    // From now on we only deal with the relative (relative to context) ID
    moduleIdentifier = moduleIdentifierParts.pop();

    if (moduleIdentifier.charAt(0) == "." && relativeModuleDir)
        return finalNormalization(this.bravojs.realpath(relativeModuleDir + "/" + moduleIdentifier, false));
    else
    if (context && context.id == "_")
        return finalNormalization(this.bravojs.realpath(this.bravojs.mainModuleDir + "/" + moduleIdentifier, false));

    var parts;
    if (typeof context.descriptor != "undefined" &&
        typeof context.descriptor.mappings != "undefined" &&
        (parts = moduleIdentifier.split("/")).length > 1 &&
        typeof context.descriptor.mappings[parts[0]] != "undefined")
    {
        var normalizedLocator = this.normalizeLocator(context.descriptor.mappings[parts[0]], context),
            mappedContextId;

        if (normalizedLocator.available === false)
            return false;

        if (typeof normalizedLocator.provider != "undefined")
            mappedContextId = normalizedLocator.id;
        else
            mappedContextId = normalizedLocator.location;

        var mappedContext = this.bravojs.contextForId(mappedContextId),
            mappedDescriptor = void 0;

        if (typeof context.descriptor.mappings[parts[0]].descriptor != "undefined")
            mappedDescriptor = context.descriptor.mappings[parts[0]].descriptor;

        // Make ID relative and do not pass relativeModuleDir so ID is resolved against root of package without checking mappings
        parts[0] = ".";
        return mappedContext.resolveId(parts.join("/"), null, mappedDescriptor);
    }

    var libDir = context.libDir;
    if (typeof descriptor != "undefined" && typeof descriptor.directories != "undefined" && typeof descriptor.directories.lib != "undefined")
    {
        libDir = descriptor.directories.lib;
    }

    return finalNormalization(this.bravojs.realpath(context.id + "@/" + ((libDir)?libDir+"/":"") + moduleIdentifier, false));
}

if (typeof bravojs != "undefined")
{
    // In Browser
    bravojs.registerPlugin(new Plugin());
}
else
if (typeof exports != "undefined")
{
    // On Server
    exports.Plugin = Plugin;
}


var calcMD5 = function() {
/*
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
 * Digest Algorithm, as defined in RFC 1321.
 * Copyright (C) Paul Johnston 1999 - 2000.
 * Updated by Greg Holt 2000 - 2001.
 * See http://pajhome.org.uk/site/legal.html for details.
 */

/*
 * Convert a 32-bit number to a hex string with ls-byte first
 */
var hex_chr = "0123456789abcdef";
function rhex(num)
{
  str = "";
  for(j = 0; j <= 3; j++)
    str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) +
           hex_chr.charAt((num >> (j * 8)) & 0x0F);
  return str;
}

/*
 * Convert a string to a sequence of 16-word blocks, stored as an array.
 * Append padding bits and the length, as described in the MD5 standard.
 */
function str2blks_MD5(str)
{
  nblk = ((str.length + 8) >> 6) + 1;
  blks = new Array(nblk * 16);
  for(i = 0; i < nblk * 16; i++) blks[i] = 0;
  for(i = 0; i < str.length; i++)
    blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
  blks[i >> 2] |= 0x80 << ((i % 4) * 8);
  blks[nblk * 16 - 2] = str.length * 8;
  return blks;
}

/*
 * Add integers, wrapping at 2^32. This uses 16-bit operations internally 
 * to work around bugs in some JS interpreters.
 */
function add(x, y)
{
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
  return (msw << 16) | (lsw & 0xFFFF);
}

/*
 * Bitwise rotate a 32-bit number to the left
 */
function rol(num, cnt)
{
  return (num << cnt) | (num >>> (32 - cnt));
}

/*
 * These functions implement the basic operation for each round of the
 * algorithm.
 */
function cmn(q, a, b, x, s, t)
{
  return add(rol(add(add(a, q), add(x, t)), s), b);
}
function ff(a, b, c, d, x, s, t)
{
  return cmn((b & c) | ((~b) & d), a, b, x, s, t);
}
function gg(a, b, c, d, x, s, t)
{
  return cmn((b & d) | (c & (~d)), a, b, x, s, t);
}
function hh(a, b, c, d, x, s, t)
{
  return cmn(b ^ c ^ d, a, b, x, s, t);
}
function ii(a, b, c, d, x, s, t)
{
  return cmn(c ^ (b | (~d)), a, b, x, s, t);
}

/*
 * Take a string and return the hex representation of its MD5.
 */
return function calcMD5(str)
{
  x = str2blks_MD5(str);
  a =  1732584193;
  b = -271733879;
  c = -1732584194;
  d =  271733878;

  for(i = 0; i < x.length; i += 16)
  {
    olda = a;
    oldb = b;
    oldc = c;
    oldd = d;

    a = ff(a, b, c, d, x[i+ 0], 7 , -680876936);
    d = ff(d, a, b, c, x[i+ 1], 12, -389564586);
    c = ff(c, d, a, b, x[i+ 2], 17,  606105819);
    b = ff(b, c, d, a, x[i+ 3], 22, -1044525330);
    a = ff(a, b, c, d, x[i+ 4], 7 , -176418897);
    d = ff(d, a, b, c, x[i+ 5], 12,  1200080426);
    c = ff(c, d, a, b, x[i+ 6], 17, -1473231341);
    b = ff(b, c, d, a, x[i+ 7], 22, -45705983);
    a = ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
    d = ff(d, a, b, c, x[i+ 9], 12, -1958414417);
    c = ff(c, d, a, b, x[i+10], 17, -42063);
    b = ff(b, c, d, a, x[i+11], 22, -1990404162);
    a = ff(a, b, c, d, x[i+12], 7 ,  1804603682);
    d = ff(d, a, b, c, x[i+13], 12, -40341101);
    c = ff(c, d, a, b, x[i+14], 17, -1502002290);
    b = ff(b, c, d, a, x[i+15], 22,  1236535329);    

    a = gg(a, b, c, d, x[i+ 1], 5 , -165796510);
    d = gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
    c = gg(c, d, a, b, x[i+11], 14,  643717713);
    b = gg(b, c, d, a, x[i+ 0], 20, -373897302);
    a = gg(a, b, c, d, x[i+ 5], 5 , -701558691);
    d = gg(d, a, b, c, x[i+10], 9 ,  38016083);
    c = gg(c, d, a, b, x[i+15], 14, -660478335);
    b = gg(b, c, d, a, x[i+ 4], 20, -405537848);
    a = gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
    d = gg(d, a, b, c, x[i+14], 9 , -1019803690);
    c = gg(c, d, a, b, x[i+ 3], 14, -187363961);
    b = gg(b, c, d, a, x[i+ 8], 20,  1163531501);
    a = gg(a, b, c, d, x[i+13], 5 , -1444681467);
    d = gg(d, a, b, c, x[i+ 2], 9 , -51403784);
    c = gg(c, d, a, b, x[i+ 7], 14,  1735328473);
    b = gg(b, c, d, a, x[i+12], 20, -1926607734);
    
    a = hh(a, b, c, d, x[i+ 5], 4 , -378558);
    d = hh(d, a, b, c, x[i+ 8], 11, -2022574463);
    c = hh(c, d, a, b, x[i+11], 16,  1839030562);
    b = hh(b, c, d, a, x[i+14], 23, -35309556);
    a = hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
    d = hh(d, a, b, c, x[i+ 4], 11,  1272893353);
    c = hh(c, d, a, b, x[i+ 7], 16, -155497632);
    b = hh(b, c, d, a, x[i+10], 23, -1094730640);
    a = hh(a, b, c, d, x[i+13], 4 ,  681279174);
    d = hh(d, a, b, c, x[i+ 0], 11, -358537222);
    c = hh(c, d, a, b, x[i+ 3], 16, -722521979);
    b = hh(b, c, d, a, x[i+ 6], 23,  76029189);
    a = hh(a, b, c, d, x[i+ 9], 4 , -640364487);
    d = hh(d, a, b, c, x[i+12], 11, -421815835);
    c = hh(c, d, a, b, x[i+15], 16,  530742520);
    b = hh(b, c, d, a, x[i+ 2], 23, -995338651);

    a = ii(a, b, c, d, x[i+ 0], 6 , -198630844);
    d = ii(d, a, b, c, x[i+ 7], 10,  1126891415);
    c = ii(c, d, a, b, x[i+14], 15, -1416354905);
    b = ii(b, c, d, a, x[i+ 5], 21, -57434055);
    a = ii(a, b, c, d, x[i+12], 6 ,  1700485571);
    d = ii(d, a, b, c, x[i+ 3], 10, -1894986606);
    c = ii(c, d, a, b, x[i+10], 15, -1051523);
    b = ii(b, c, d, a, x[i+ 1], 21, -2054922799);
    a = ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
    d = ii(d, a, b, c, x[i+15], 10, -30611744);
    c = ii(c, d, a, b, x[i+ 6], 15, -1560198380);
    b = ii(b, c, d, a, x[i+13], 21,  1309151649);
    a = ii(a, b, c, d, x[i+ 4], 6 , -145523070);
    d = ii(d, a, b, c, x[i+11], 10, -1120210379);
    c = ii(c, d, a, b, x[i+ 2], 15,  718787259);
    b = ii(b, c, d, a, x[i+ 9], 21, -343485551);

    a = add(a, olda);
    b = add(b, oldb);
    c = add(c, oldc);
    d = add(d, oldd);
  }
  return rhex(a) + rhex(b) + rhex(c) + rhex(d);
}

}();

})();

/**
 *  This file implements a bravojs core plugin to add
 *  dynamic module and package loading support where the server
 *  given a module or package ID will return the requested
 *  module (main module for package) and all dependencies
 *  in a single file.
 *
 *  Copyright (c) 2011, Christoph Dorn
 *  Christoph Dorn, christoph@christophdorn.com
 *  MIT License
 *
 *  To use: Load BravoJS, then layer this plugin in
 *  by loading it into the extra-module environment.
 */

(function packages_loader() {

bravojs.module.constructor.prototype.load = function packages_loader_load(moduleIdentifier, callback)
{
    var uri;
    
    if (typeof moduleIdentifier == "object")
    {
        if (typeof moduleIdentifier.id != "undefined")
        {
            var pkg = bravojs.contextForId(moduleIdentifier.id);
            uri = pkg.resolveId(null);
        }
        else
        if (typeof moduleIdentifier.location != "undefined")
        {
            uri = bravojs.mainModuleDir + moduleIdentifier.location.substring(bravojs.mainModuleDir.length);
        }
        else
            throw new Error("NYI");
    }
    else
    if (moduleIdentifier.charAt(0) != "/")
    {
        if (moduleIdentifier.charAt(0) != ".")
        {
            // resolve mapped ID
            uri = bravojs.contextForId(this._id).resolveId(moduleIdentifier).replace(bravojs.mainModuleDir, bravojs.mainModuleDir);
        }
        else
            throw new Error("Cannot load module by relative ID: " + moduleIdentifier);
    }
    else
    {
        uri = bravojs.mainModuleDir + moduleIdentifier.substring(bravojs.mainModuleDir.length);
    }

    var lookupURI = uri;
    if (/\.js$/.test(lookupURI))
        lookupURI = lookupURI.substring(0, lookupURI.length-3);

    if (bravojs.require.isMemoized(lookupURI))
    {
        callback(lookupURI);
        return;
    }

    if (!/\.js$/.test(uri) && !/\/$/.test(uri))
        uri += ".js";

    // Encode ../ as we need to preserve them (servers/browsers will automatically normalize these directory up path segments)
    uri = uri.replace(/\.{2}\//g, "__/");

    // WebWorker
    if (typeof importScripts === "function")
    {
        // Remove hostname
        uri = uri.replace(/^\/[^\/]*\//, "/");

        importScripts(uri);
        
        if (typeof __bravojs_loaded_moduleIdentifier == "undefined")
            throw new Error("__bravojs_loaded_moduleIdentifier not set by server!");

        var id = __bravojs_loaded_moduleIdentifier;

        delete __bravojs_loaded_moduleIdentifierl

        // all modules are memoized now so we can continue
        callback(id);
        return;
    }

    var URL = window.location.protocol + "/" + uri;

    // We expect a bunch of modules wrapped with:
    //  require.memoize('ID', [], function (require, exports, module) { ... });

    var script = document.createElement('SCRIPT');
    script.setAttribute("type","text/javascript");
    script.setAttribute("src", URL);

    /* Fake script.onload for IE6-8 */
    script.onreadystatechange = function()
    {
        var cb;        
        if (this.readyState === "loaded")
        {
            cb = this.onload;
            this.onload = null;
            setTimeout(cb,0);
        }
    }

    script.onload = function packages_loader_onload()
    {
        this.onreadystatechange = null;
        
        if (typeof window.__bravojs_loaded_moduleIdentifier == "undefined")
            throw new Error("__bravojs_loaded_moduleIdentifier not set by server!");
        
        var id = window.__bravojs_loaded_moduleIdentifier;
        
        delete window.__bravojs_loaded_moduleIdentifierl

        // all modules are memoized now so we can continue
        callback(id);
    }
    
    /* Supply errors on browsers that can */
    script.onerror = function fastload_script_error()
    {
        if (typeof console != "undefined")
            console.error("Error contacting server URL = " + script.src);
        else
            alert("Error contacting server\nURL=" + script.src);
    }

    document.getElementsByTagName("HEAD")[0].appendChild(script);
};

})();

require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/75facf7e3e2e534c307f54f259c3e920b1dc6965@/package.json'), [], function() { return {"uid":"http://github.com/cadorn/ace-extjs/packages/extjs3-ace/","name":"extjs3-ace","main":"lib/component.js","contexts":{"top":{"/lib/component":{"include":{"github.com/cadorn/ace-extjs/packages/extjs-ace/":{}}}}},"mappings":{"extjs-ace":{"location":"" + bravojs.mainModuleDir + "/aae9e0c4eed45f3c888f8e7824a96a83f5f8f861"},"ace":{"location":"" + bravojs.mainModuleDir + "/87749d9714f1925e26afa48a0d592eaa39403858"},"cockpit":{"location":"" + bravojs.mainModuleDir + "/b5bd9e5093176e86aa6f6c4d581342361d8c923f"},"pilot":{"location":"" + bravojs.mainModuleDir + "/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c"}}}; });
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/75facf7e3e2e534c307f54f259c3e920b1dc6965@/lib/component'), ['extjs-ace/component'], function (require, exports, module)
{
    var COMPONENT = require("extjs-ace/component");

    exports.main = function()
    {
        COMPONENT.init();
        
        var component = COMPONENT.getComponent();

        Ext.ux.AceEditor = Ext.extend(Ext.BoxComponent, component);
    }
});
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/aae9e0c4eed45f3c888f8e7824a96a83f5f8f861@/package.json'), [], function() { return {"uid":"http://github.com/cadorn/ace-extjs/packages/extjs-ace/","name":"extjs-ace","main":"","contexts":{"top":{"/":{"load":{"github.com/cadorn/ace-extjs/packages/ace-editor/":{}}}}},"mappings":{"editor":{"location":"" + bravojs.mainModuleDir + "/a3d9ddf257e98144c883cd2dbc03ab62243dbc09"},"ace":{"location":"" + bravojs.mainModuleDir + "/87749d9714f1925e26afa48a0d592eaa39403858"},"cockpit":{"location":"" + bravojs.mainModuleDir + "/b5bd9e5093176e86aa6f6c4d581342361d8c923f"},"pilot":{"location":"" + bravojs.mainModuleDir + "/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c"}}}; });
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/aae9e0c4eed45f3c888f8e7824a96a83f5f8f861@/lib/component'), ['pilot/dom','text!./component.css'], function (require, exports, module)
{
    var DOM = require("pilot/dom");

    exports.init = function()
    {
        DOM.importCssString(require("text!./component.css"));
    }

    exports.getComponent = function()
    {
        return {

            baseCls: "x-ux-ace-editor-panel",
            
            initComponent: function()
            {
                Ext.ux.AceEditor.superclass.initComponent.call(this);
                var self = this;

                self.editorClass = null;
                self.initialized = false;
                self._editorLoaded = false;
                self._afterOnRender = false;

                self.value = null;
                self.editor = null;
                
                self.session = null;

                // async load editor
                module.load("editor/main", function(id)
                {
                    self.editorClass = require(id);

                    // init the editor
                    self.editorClass.init(function(env)
                    {
                        var CANON = self.editorClass.module("pilot/canon");

                        self.addEvents(
                            "editor-save",
                            "editor-saveas"
                        );

                        CANON.addCommand({
                            name: "save",
                            bindKey: {
                                win: "Ctrl-S",
                                mac: "Command-S",
                                sender: "editor|cli"
                            },
                            exec: function()
                            {
                                self.fireEvent('editor-save', self);
                            }
                        });

                        CANON.addCommand({
                            name: "saveas",
                            bindKey: {
                                win: "Ctrl-Shift-S",
                                mac: "Command-Shift-S",
                                sender: "editor|cli"
                            },
                            exec: function()
                            {
                                self.fireEvent('editor-saveas', self);
                            }
                        });

                        self._editorLoaded = true;
                        if (self._afterOnRender && !self.initialized)
                            self.firstRender();
                    });
                });
            },

            firstRender: function()
            {
                var self = this;

                var EDITOR = self.editorClass.module("ace/editor").Editor,
                    RENDERER = self.editorClass.module("ace/virtual_renderer").VirtualRenderer,
                    THEME = self.editorClass.module("ace/theme/textmate");  // default
//                    var EVENT = self.editorClass.module("pilot/event");

                self.el.dom.innerHTML = "";
                
                self.renderer = new RENDERER(self.el.dom, THEME);
                self.editor = new EDITOR(self.renderer);
                self.editor.resize();

                self.initialized = true;

                if(self.value!==null)
                {
                    self.setValue(self.value[0], self.value[1]);
                }
            },

            onRender: function()
            {
                Ext.ux.AceEditor.superclass.onRender.apply(this, arguments);

                if (typeof this.el.addCls != "undefined")
                    this.el.addCls(this.baseCls);   // ExtJS 4
                else
                    this.el.addClass(this.baseCls);   // ExtJS 3

                if (!this.initialized)
                {
                    // TODO: Make this look nicer
                    this.el.dom.innerHTML = "Loading Editor ...";
                }

                this._afterOnRender = true;
                if (this._editorLoaded && !this.initialized)
                    this.firstRender();
            },

            onResize: function( aw, ah )
            {
                if(this.editor) {
                    this.editor.resize();
                }
            },

            getValue: function()
            {
                return this.editor.getDocument().getValue();
            },

            getEditor: function()
            {
                return this.editor;
            },

            getSession: function()
            {
                return this.session;
            },

            getRenderer: function()
            {
                return this.renderer;
            },

            setValue: function(value, options)
            {
                if (!this.initialized)
                {
                    this.value = [value, options];
                    return;
                }
                this.value = null;

                options = options || {};

                var SESSION = this.editorClass.module("ace/edit_session").EditSession,
                    UNDO_MANAGER = this.editorClass.module("ace/undomanager").UndoManager;

                var session = new SESSION(value);
                session.setUndoManager(new UNDO_MANAGER());

                session.getDocument().addEventListener("changeDelta", function()
                {
                    self.fireEvent('editor-changeDelta', self);
                });

                var mode = "text";
                if (typeof options.mode != "undefined")
                {
                    mode = options.mode;
                }
                else
                {
                    if(typeof options.basename != "undefined")
                    {
                        if (/^.*\.js$/i.test(options.basename)) {
                            mode = "javascript";
                        } else if (/^.*\.xml$/i.test(options.basename)) {
                            mode = "xml";
                        } else if (/^.*\.html?$/i.test(options.basename)) {
                            mode = "html";
                        } else if (/^.*\.css$/i.test(options.basename)) {
                            mode = "css";
                        } else if (/^.*\.py$/i.test(options.basename)) {
                            mode = "python";
                        } else if (/^.*\.php$/i.test(options.basename)) {
                            mode = "php";
                          } else if (/^.*\.cs$/i.test(options.basename)) {
                              mode = "csharp";
                        } else if (/^.*\.java$/i.test(options.basename)) {
                            mode = "java";
                        } else if (/^.*\.rb$/i.test(options.basename)) {
                            mode = "ruby";
                        } else if (/^.*\.(c|cpp|h|hpp|cxx)$/i.test(options.basename)) {
                            mode = "c_cpp";
                        } else if (/^.*\.coffee$/i.test(options.basename)) {
                            mode = "coffee";
                        } else if (/^.*\.(pl|pm)$/i.test(options.basename)) {
                            mode = "perl";
                        }
                    }
                }

                var self = this;

                this.setMode(mode, {
                    session: session,
                    callback: function(session)
                    {
                        self.editor.setSession(session);

                        self.session = session;

                        if (typeof options.callback != "undefined")
                        {
                            options.callback(session);
                        }
                    }
                });
            },

            setMode: function(name, options)
            {
                // TODO: display loading message

                var self = this;

                var session = options.session || self.session;
                if (!session)
                    throw new Error("Session required!");

                // async load mode
                module.load("ace/mode/" + name, function(id)
                {
                    var modeObj = new (require(id).Mode);
                    session.setMode(modeObj);

                    if (typeof options.callback != "undefined")
                    {
                        options.callback(session);
                    }
                });                
            },

            setTheme: function(name)
            {
                this.editor.setTheme("ace/theme/" + name);
            },

            setKeyboardHandler: function(name, options)
            {
                if (name === null)
                {
                    this.editor.setKeyboardHandler(null);
                    return;
                }
                var self = this;

                // TODO: This should be done more generically
                var id = "ace/keyboard/keybinding/" + name;
                if (name == "hash_handler") {
                    id = "ace/keyboard/" + name;
                }

                // async load keybinding
                module.load(id, function(id)
                {
                    var obj = require(id);

                    // TODO: This should be done more generically
                    if (name == "vim") {
                        obj = obj.Vim;
                    } else
                    if (name == "emacs") {
                        obj = obj.Emacs;
                    } else
                    if (name == "hash_handler") {
                        obj = new obj.HashHandler(options);
                    }

                    self.editor.setKeyboardHandler(obj);
                });
            },

            setFontSize: function(value)
            {
                this.el.dom.style.fontSize = value;
            }
        };
    }
});
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c@/package.json'), [], function() { return {"name":"pilot","description":"Pilot is a small library used in the creation of Ace editor.","version":"0.1.1","homepage":"http://github.com/ajaxorg/pilot","engines":{"node":">= 0.1.102","teleport":">=0.2.0"},"author":"Fabian Jakobs <fabian@ajax.org>","main":"lib/pilot/index.js","repository":{"type":"git","url":"http://github.com/ajaxorg/ace.git"},"overlay":{"teleport":{"directories":{"lib":"lib/pilot"}}},"licenses":[{"type":"LGPLv3","url":"http://www.gnu.org/licenses/lgpl-3.0.txt"}],"uid":"https://github.com/ajaxorg/pilot/","directories":{"lib":"lib/pilot"},"mappings":{"pilot":{"location":"" + bravojs.mainModuleDir + "/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c"}}}; });
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c@/lib/pilot/dom'), [], function (require, exports, module) {


var XHTML_NS = "http://www.w3.org/1999/xhtml";

exports.createElement = function(tag, ns) {
    return document.createElementNS ?
           document.createElementNS(ns || XHTML_NS, tag) :
           document.createElement(tag);
};

exports.setText = function(elem, text) {
    if (elem.innerText !== undefined) {
        elem.innerText = text;
    }
    if (elem.textContent !== undefined) {
        elem.textContent = text;
    }
};

if (!document.documentElement.classList) {
    exports.hasCssClass = function(el, name) {
        var classes = el.className.split(/\s+/g);
        return classes.indexOf(name) !== -1;
    };

    /**
    * Add a CSS class to the list of classes on the given node
    */
    exports.addCssClass = function(el, name) {
        if (!exports.hasCssClass(el, name)) {
            el.className += " " + name;
        }
    };

    /**
    * Remove a CSS class from the list of classes on the given node
    */
    exports.removeCssClass = function(el, name) {
        var classes = el.className.split(/\s+/g);
        while (true) {
            var index = classes.indexOf(name);
            if (index == -1) {
                break;
            }
            classes.splice(index, 1);
        }
        el.className = classes.join(" ");
    };

    exports.toggleCssClass = function(el, name) {
        var classes = el.className.split(/\s+/g), add = true;
        while (true) {
            var index = classes.indexOf(name);
            if (index == -1) {
                break;
            }
            add = false;
            classes.splice(index, 1);
        }
        if(add)
            classes.push(name);

        el.className = classes.join(" ");
        return add;
    };
} else {
    exports.hasCssClass = function(el, name) {
        return el.classList.contains(name);
    };

    exports.addCssClass = function(el, name) {
        el.classList.add(name);
    };

    exports.removeCssClass = function(el, name) {
        el.classList.remove(name);
    };

    exports.toggleCssClass = function(el, name) {
        return el.classList.toggle(name);
    };
}

/**
 * Add or remove a CSS class from the list of classes on the given node
 * depending on the value of <tt>include</tt>
 */
exports.setCssClass = function(node, className, include) {
    if (include) {
        exports.addCssClass(node, className);
    } else {
        exports.removeCssClass(node, className);
    }
};

exports.importCssString = function(cssText, doc){
    doc = doc || document;

    if (doc.createStyleSheet) {
        var sheet = doc.createStyleSheet();
        sheet.cssText = cssText;
    }
    else {
        var style = doc.createElementNS ?
                    doc.createElementNS(XHTML_NS, "style") :
                    doc.createElement("style");

        style.appendChild(doc.createTextNode(cssText));

        var head = doc.getElementsByTagName("head")[0] || doc.documentElement;
        head.appendChild(style);
    }
};

exports.getInnerWidth = function(element) {
    return (parseInt(exports.computedStyle(element, "paddingLeft"))
            + parseInt(exports.computedStyle(element, "paddingRight")) + element.clientWidth);
};

exports.getInnerHeight = function(element) {
    return (parseInt(exports.computedStyle(element, "paddingTop"))
            + parseInt(exports.computedStyle(element, "paddingBottom")) + element.clientHeight);
};

if (window.pageYOffset !== undefined) {
    exports.getPageScrollTop = function() {
        return window.pageYOffset;
    };

    exports.getPageScrollLeft = function() {
        return window.pageXOffset;
    };
}
else {
    exports.getPageScrollTop = function() {
        return document.body.scrollTop;
    };

    exports.getPageScrollLeft = function() {
        return document.body.scrollLeft;
    };
}

exports.computedStyle = function(element, style) {
    if (window.getComputedStyle) {
        return (window.getComputedStyle(element, "") || {})[style] || "";
    }
    else {
        return element.currentStyle[style];
    }
};

exports.scrollbarWidth = function() {

    var inner = exports.createElement("p");
    inner.style.width = "100%";
    inner.style.height = "200px";

    var outer = exports.createElement("div");
    var style = outer.style;

    style.position = "absolute";
    style.left = "-10000px";
    style.overflow = "hidden";
    style.width = "200px";
    style.height = "150px";

    outer.appendChild(inner);

    var body = document.body || document.documentElement;
    body.appendChild(outer);

    var noScrollbar = inner.offsetWidth;

    style.overflow = "scroll";
    var withScrollbar = inner.offsetWidth;

    if (noScrollbar == withScrollbar) {
        withScrollbar = outer.clientWidth;
    }

    body.removeChild(outer);

    return noScrollbar-withScrollbar;
};

/**
 * Optimized set innerHTML. This is faster than plain innerHTML if the element
 * already contains a lot of child elements.
 *
 * See http://blog.stevenlevithan.com/archives/faster-than-innerhtml for details
 */
exports.setInnerHtml = function(el, innerHtml) {
    var element = el.cloneNode(false);//document.createElement("div");
    element.innerHTML = innerHtml;
    el.parentNode.replaceChild(element, el);
    return element;
};

exports.setInnerText = function(el, innerText) {
    if (document.body && "textContent" in document.body)
        el.textContent = innerText;
    else
        el.innerText = innerText;

};

exports.getInnerText = function(el) {
    if (document.body && "textContent" in document.body)
        return el.textContent;
    else
         return el.innerText || el.textContent || "";
};

exports.getParentWindow = function(document) {
    return document.defaultView || document.parentWindow;
};

exports.getSelectionStart = function(textarea) {
    // TODO IE
    var start;
    try {
        start = textarea.selectionStart || 0;
    } catch (e) {
        start = 0;
    }
    return start;
};

exports.setSelectionStart = function(textarea, start) {
    // TODO IE
    return textarea.selectionStart = start;
};

exports.getSelectionEnd = function(textarea) {
    // TODO IE
    var end;
    try {
        end = textarea.selectionEnd || 0;
    } catch (e) {
        end = 0;
    }
    return end;
};

exports.setSelectionEnd = function(textarea, end) {
    // TODO IE
    return textarea.selectionEnd = end;
};

});
require.memoize('text!'+bravojs.realpath(bravojs.mainModuleDir + '/aae9e0c4eed45f3c888f8e7824a96a83f5f8f861@/lib/component.css'), [], function () {
return ["",".x-ux-ace-editor-panel {","    border-style: solid;","    border-width: 0;","    border-color: #99bbe8;","    background-color: #ffffff;","}",""].join("\n");
});
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/87749d9714f1925e26afa48a0d592eaa39403858@/package.json'), [], function() { return {"name":"ace","description":"Ajax.org Code Editor is a full featured source code highlighting editor that powers the Cloud9 IDE","version":"0.1.6","homepage":"http://github.com/ajaxorg/ace","engines":{"node":">= 0.2.0"},"author":"Fabian Jakobs <fabian@ajax.org>","main":"","repository":{"type":"git","url":"http://github.com/ajaxorg/ace.git"},"overlay":{"teleport":{"directories":{"lib":"lib/ace","dependencies":{"cockpit":">=0.1.1","pilot":">=0.1.1"}}}},"dependencies":[{"id":"github.com/cadorn/ace-extjs/packages/ace-editor/","descriptor":{"directories":{"lib":"modules"}},"location":"/Users/cadorn/pinf/workspaces/github.com/cadorn/ace-extjs/packages/ace-editor"}],"licenses":[{"type":"MPL","url":"http://www.mozilla.org/MPL/"},{"type":"GPL","url":"http://www.gnu.org/licenses/gpl.html"},{"type":"LGPL","url":"http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html"}],"directories":{"lib":"lib/ace"},"uid":"https://github.com/ajaxorg/ace/","contexts":{"top":{"/":{"include":{"/lib/ace/range":{},"/lib/ace/tokenizer":{},"/lib/ace/mode/text":{},"/lib/ace/mode/matching_brace_outdent":{},"/lib/ace/mode/doc_comment_highlight_rules":{}},"load":{"/lib/ace/mode/*":{},"/lib/ace/theme/*":{}}}}},"modules":{"/lib/ace/worker/worker_client":{"module":"modules/ace/worker/worker_client","location":"" + bravojs.mainModuleDir + "/a3d9ddf257e98144c883cd2dbc03ab62243dbc09"}},"mappings":{"ace":{"location":"" + bravojs.mainModuleDir + "/87749d9714f1925e26afa48a0d592eaa39403858"},"pilot":{"location":"" + bravojs.mainModuleDir + "/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c"},"asyncjs":{"available":false}}}; });
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/b5bd9e5093176e86aa6f6c4d581342361d8c923f@/package.json'), [], function() { return {"name":"cockpit","description":"Command line component for Skywriter/Ace/Cloud9/etc","version":"0.1.1","homepage":"http://github.com/joewalker/cockpit","engines":{"node":">= 0.1.102","teleport":">=0.2.0"},"dependencies":{"pilot":">=0.1.1"},"overlay":{"teleport":{"directories":{"lib":"lib/cockpit"}}},"author":"Joe Walker <jwalker@mozilla.com>","main":"lib/cockpit","repository":{"type":"git","url":"http://github.com/joewalker/cockpit.git"},"licenses":[{"type":"LGPLv3","url":"http://www.gnu.org/licenses/lgpl-3.0.txt"}],"uid":"https://github.com/ajaxorg/cockpit/","directories":{"lib":"lib/cockpit"},"mappings":{"cockpit":{"location":"" + bravojs.mainModuleDir + "/b5bd9e5093176e86aa6f6c4d581342361d8c923f"},"pilot":{"location":"" + bravojs.mainModuleDir + "/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c"}}}; });
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/a3d9ddf257e98144c883cd2dbc03ab62243dbc09@/package.json'), [], function() { return {"uid":"http://github.com/cadorn/ace-extjs/packages/ace-editor/","main":"main.js","directories":{"lib":""},"contexts":{"top":{"/main":{"include":{"github.com/ajaxorg/pilot/@/lib/pilot/index":{},"github.com/ajaxorg/ace/@/lib/ace/defaults":{},"github.com/ajaxorg/cockpit/@/lib/cockpit/index":{},"github.com/ajaxorg/ace/":{}}}}},"mappings":{"worker":{"location":"" + bravojs.mainModuleDir + "/b339f0b799f23466a9fb3ac92af4cb7e572604a6"},"ace":{"location":"" + bravojs.mainModuleDir + "/87749d9714f1925e26afa48a0d592eaa39403858"},"cockpit":{"location":"" + bravojs.mainModuleDir + "/b5bd9e5093176e86aa6f6c4d581342361d8c923f"},"pilot":{"location":"" + bravojs.mainModuleDir + "/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c"}}}; });
require.memoize(bravojs.realpath(bravojs.mainModuleDir + '/b339f0b799f23466a9fb3ac92af4cb7e572604a6@/package.json'), [], function() { return {"uid":"http://github.com/cadorn/ace-extjs/packages/ace-worker/","main":"worker.js","directories":{"lib":""},"mappings":{"ace":{"location":"" + bravojs.mainModuleDir + "/87749d9714f1925e26afa48a0d592eaa39403858"},"cockpit":{"location":"" + bravojs.mainModuleDir + "/b5bd9e5093176e86aa6f6c4d581342361d8c923f"},"pilot":{"location":"" + bravojs.mainModuleDir + "/f9a24d6931cb0c0e8264fed132a0ed8c97415c4c"}}}; });
(function() {
var env = {};
module.declare([{"_package-0":{"id":"75facf7e3e2e534c307f54f259c3e920b1dc6965"}}], function(require, exports, module) {
require('_package-0').main(env);
});
})();