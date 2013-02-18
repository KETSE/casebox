Ext.onReady(function(){

	var s = new Ext.ux.form.Spinner({
		//strategy: new Ext.ux.form.Spinner.DateStrategy()
	});
	s.applyToMarkup('t');
	
	Ext.get('frm').boxWrap().applyStyles('width:210px; margin:2em 0;');

	btnReset = new Ext.Button({renderTo:'container', text:'Reset'});
	btnReset.on('click', function(){
		s.reset();
		s.focus();
	});

	btnDisable = new Ext.Button({renderTo:'container', text:'Toggle disable'});
	btnDisable.on('click', function(){
		(s.disabled == true) ? s.enable() : s.disable();
		s.focus();
	});

	btnReadOnly = new Ext.Button({renderTo:'container', text:'Toggle readOnly'});
	btnReadOnly.on('click', function(){
		s.getEl().dom.readOnly = (s.getEl().dom.readOnly == true) ? false : true;
	});

	btnStgNumber = new Ext.Button({renderTo:'container', text:'Convert to Number'});
	btnStgNumber.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.NumberStrategy();
		s.focus();
	});

	btnStgNumberLimited = new Ext.Button({renderTo:'container', text:'Convert to Number: Limited[0-10]'});
	btnStgNumberLimited.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.NumberStrategy({minValue:0, maxValue:10});
		s.focus();
	});

	btnStgNumberIncrementLimited = new Ext.Button({renderTo:'container', text:'Convert to Number: Limited[100-200] & Increment[5] & Alternate Increment[10]'});
	btnStgNumberIncrementLimited.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.NumberStrategy({minValue:100, maxValue:200, incrementValue:5, alternateIncrementValue:10});
		s.focus();
	});

	btnStgDate = new Ext.Button({renderTo:'container', text:'Convert to Date'});
	btnStgDate.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.DateStrategy();
		s.focus();
	});

	btnStgDateLimited = new Ext.Button({renderTo:'container', text:'Convert to Date: Limited[2008-01-01 - 2008-12-31]'});
	btnStgDateLimited.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.DateStrategy({minValue:'2008-01-01', maxValue:'2008-12-31'});
		s.focus();
	});

	btnStgDateFormat = new Ext.Button({renderTo:'container', text:'Convert to Date: Format[m-d-Y]'});
	btnStgDateFormat.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.DateStrategy({format:'m-d-Y'});
		s.focus();
	});

	btnStgDateIncrement = new Ext.Button({renderTo:'container', text:'Convert to Date: Increment 7 Days, Alt 3 Months'});
	btnStgDateIncrement.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.DateStrategy({
			incrementValue : 7,
			incrementConstant : Date.DAY,
			alternateIncrementValue : 3,
			alternateIncrementConstant : Date.MONTH
		});
		s.focus();
	});

	btnStgTime = new Ext.Button({renderTo:'container', text:'Convert to Time'});
	btnStgTime.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.TimeStrategy();
		s.focus();
	});

	btnStgTimeLimited = new Ext.Button({renderTo:'container', text:'Convert to Time: Limited[09:00 - 17:00]'});
	btnStgTimeLimited.on('click', function(){
		s.reset();
		s.strategy = new Ext.ux.form.Spinner.TimeStrategy({minValue:'09:00', maxValue:'17:00'});
		s.focus();
	});

	
	/****/
    var simple = new Ext.FormPanel({
        labelWidth: 40, // label settings here cascade unless overridden
        url:'save-form.php',
        frame: true,
        title: 'Simple Form',
        bodyStyle:'padding:5px 5px 0',
        width: 210,
        defaults: {width: 135},
        defaultType: 'textfield',

        items: [
            new Ext.ux.form.Spinner({
                fieldLabel: 'Age',
                name: 'age',
                strategy: new Ext.ux.form.Spinner.NumberStrategy({minValue:'0', maxValue:'130'})
            }),
            new Ext.ux.form.Spinner({
                fieldLabel: 'Time',
                name: 'time',
                strategy: new Ext.ux.form.Spinner.TimeStrategy()
            }),
            new Ext.ux.form.Spinner({
                fieldLabel: 'Date',
                name: 'date',
                strategy: new Ext.ux.form.Spinner.DateStrategy()
            })
        ],

        buttons: [{
            text: 'Save'
        },{
            text: 'Cancel'
        }]
    });

    simple.render('form-ct');
    
    /*
    var tf = new Ext.form.TextField({
        plugins: [new Ext.ux.SpinnerPlugin()]
    });
    tf.render('plugin-ct');
    */

});
