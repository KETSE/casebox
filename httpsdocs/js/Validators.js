Ext.namespace('CB.Validators');

CB.Validators.json = function (jsonString){
    try {
        // var o = Ext.decode(jsonString);
        var o = JSON.parse(jsonString);

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

CB.Validators.geoPoint = function (value){
    // Point must be in 'lat,​ lon' or 'x y'
    var re = /^-?\d+\.?\d*\,-?\d+\.?\d*$/;

    rez = !Ext.isEmpty(re.exec(value));

    //check if in correct diapazon
    if(rez) {
        var a = value.split(',')
            ,y = parseFloat(a[0])
            ,x = parseFloat(a[1]);

        rez = ((y >= -90) && (y <= 90) && (x >= -180) && (x <= 180));
    }

    return rez;
};
