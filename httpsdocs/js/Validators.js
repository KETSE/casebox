Ext.namespace('CB.Validators');

CB.Validators.json = function (jsonString){
    try {
        var o = Ext.decode(jsonString);

        // Handle non-exception-throwing cases:
        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
        // but... JSON.parse(null) returns 'null', and typeof null === "object",
        // so we must check for that, too.
        if (o && typeof o === "object" && o !== null) {
            return true;
        }
    }
    catch (e) { }

    return false;
};
