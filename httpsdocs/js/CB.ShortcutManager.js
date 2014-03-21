Ext.namespace('CB');

window.npup = (function keypressListener() {
    // Object to hold keyCode/handler mappings
    var mappings = {};
    // Default options for additional meta keys
    var defaultOptions = {ctrl:false, alt:false};
    // Flag for if we're running checks or not
    var active = false;

    // The function that gets called on keyup.
    // Tries to find a handler to execute
    function driver(event) {
        var keyCode = event.keyCode, ctrl = !!event.ctrlKey, alt = !!event.altKey;
        var key = buildKey(keyCode, ctrl, alt);
        var handler = mappings[key];
        if (handler) {handler(event);}
    }

    // Take the three props and make a string to use as key in the hash
    function buildKey(keyCode, ctrl, alt) {return (keyCode+'_'+ctrl+'_'+alt);}

    function listen(keyCode, handler, options) {
        // Build default options if there are none submitted
        options = options || defaultOptions;
        if (typeof handler!=='function') {throw new Error('Submit a handler for keyCode #'+keyCode+'(ctrl:'+!!options.ctrl+', alt:'+options.alt+')');}
        // Build a key and map handler for the key combination
        var key = buildKey(keyCode, !!options.ctrl, !!options.alt);
        mappings[key] = handler;
    }

    function unListen(keyCode, options) {
        // Build default options if there are none submitted
        options = options || defaultOptions;
        // Build a key and map handler for the key combination
        var key = buildKey(keyCode, !!options.ctrl, !!options.alt);
        // Delete what was found
        delete mappings[key];
    }

    // Rudimentary attempt att cross-browser-ness
    var xb = {
        addEventListener: function (element, eventName, handler) {
            if (element.attachEvent) {element.attachEvent('on'+eventName, handler);}
            else {element.addEventListener(eventName, handler, false);}
        }
        , removeEventListener: function (element, eventName, handler) {
            if (element.attachEvent) {element.detachEvent('on'+eventName, handler);}
            else {element.removeEventListener(eventName, handler, false);}
        }
    };

    function setActive(activate) {
        activate = (typeof activate==='undefined' || !!activate); // true is default
        if (activate===active) {return;} // already in the desired state, do nothing
        var addOrRemove = activate ? 'addEventListener' : 'removeEventListener';
        xb[addOrRemove](document, 'keyup', driver);
        active = activate;
    }

    // Activate on load
    setActive();

    // export API
    return {
        // Add/replace handler for a keycode.
        // Submit keycode, handler function and an optional hash with booleans for properties 'ctrl' and 'alt'
        listen: listen
        // Remove handler for a keycode
        // Submit keycode and an optional hash with booleans for properties 'ctrl' and 'alt'
        , unListen: unListen
        // Turn on or off the whole thing.
        // Submit a boolean. No arg means true
        , setActive: setActive
        // Keycode constants, fill in your own here
        , key : {
            VK_F1 : 112
            , VK_F2: 113
            , VK_A: 65
            , VK_B: 66
            , VK_C: 67
        }
    };
})();

// Small demo of listen and unListen
// Usage:
//   listen(key, handler [,options])
//   unListen(key, [,options])
npup.listen(npup.key.VK_F1, function (event) {
    console.log('F1, adding listener on \'B\'');
    npup.listen(npup.key.VK_B, function (event) {
        console.log('B');
    });
});
npup.listen(npup.key.VK_F2, function (event) {
    console.log('F2, removing listener on \'B\'');
    npup.unListen(npup.key.VK_B);
});
npup.listen(npup.key.VK_A, function (event) {
    console.log('ctrl-A');
}, {ctrl: true});
npup.listen(npup.key.VK_A, function (event) {
    console.log('ctrl-alt-A');
}, {ctrl: true, alt: true});
npup.listen(npup.key.VK_C, function (event) {
    console.log('ctrl-alt-C => It all ends!');
    npup.setActive(false);
}, {ctrl: true, alt: true});
