Ext.namespace('CB.Validators');

//check if field has validator set and notify if validation not passed
CB.Validators.isValidValue = function (validatorName, value){
    var rez = true;

    if(!Ext.isEmpty(validatorName)) {
        if(!Ext.isDefined(CB.Validators[validatorName])) {
            plog('Undefined field validator: ' + validatorName);

        } else {
            //empty values are considered valid by default
            rez = Ext.isEmpty(value) || (CB.Validators[validatorName](value) === true);
        }
    }

    return rez;
};

CB.Validators.json = function (jsonString){
    if(Ext.isEmpty(jsonString)) {
        return true;
    }

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

    return Ext.form.Field.prototype.invalidText;
};

CB.Validators.geoPoint = function (value){
    if(Ext.isEmpty(value)) {
        return true;
    }

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

    if(!rez) {
        rez = Ext.form.Field.prototype.invalidText;
    }

    return rez;
};
