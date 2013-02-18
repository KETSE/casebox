Ext.namespace('Ext.ux');

(function () {

    var UX = Ext.ux;

    var CLS = 'ux-date-time-picker';

    UX.DateTimePicker = Ext.extend(Ext.BoxComponent, {
        doneText: 'Done',
	todayBtnText: 'Now',
	doneTip: '{0}',
	doneBtnInvalidText: 'Invalid date',
				   timeConfig: {},
        initComponent: function () {
            UX.DateTimePicker.superclass.initComponent.call(this);

            this.addEvents('select');

            this._initDatePicker();
            this._initTimePicker();

            this.timeValue = new Date();

            if (this.value) {
                this.setValue(this.value);
                delete this.value;
            }
            
            var strategyConfig = {
		format : "H:i",
		incrementValue : 1,
		incrementConstant : Date.MINUTE,
		alternateIncrementValue : 1,
		alternateIncrementConstant : Date.HOUR
	    };

	    strategyConfig = Ext.apply(strategyConfig, this.timeConfig.strategyConfig);
	    
	    var timeConfig = {
		allowBlank: false,
		fieldLabel: 'Time',
		labelStyle: 'font-weight:bold;',
		selectOnFocus: true,
		strategy: new Ext.ux.form.Spinner.TimeStrategy(strategyConfig)
	    };
	    
	    if( this.timeConfig.allowBlank){
	      delete this.timeConfig.allowBlank;
	    }

	    timeConfig = Ext.apply(timeConfig, this.timeConfig);
	    
	    this.timePicker = new Ext.ux.form.Spinner(timeConfig);
	    this.timePicker.on('spin', this.onTimeSelect, this);
	    this.datePicker.format = this.format || this.datePicker.format;
        },

        _initTimePicker: function () {
            if (!this.timeMenu) {
                var menuConfig = this.initialConfig.timeMenu;

                if (menuConfig && menuConfig.xtype) {
                    this.timeMenu = Ext.create(menuConfig);
                } else {
                    var pickerConfig = this.initialConfig.timePicker || {};
                    if (this.timeFormat) {
                        pickerConfig.format = this.timeFormat;
                    }
                    var picker = Ext.create(pickerConfig, 'basetimepicker');
                    this.timeMenu = new Menu(picker, menuConfig || {});
                }

                if (!Ext.isFunction(this.timeMenu.getPicker)) {
                    throw 'Your time menu must provide the getPicker() method';
                }

                this.timeMenu.on('timeselect', this.onTimeSelect, this);
            }
        },

        _initDatePicker: function () {
            var config = this.initialConfig.datePicker || {};

            config.internalRender = this.initialConfig.internalRender;

            Ext.applyIf(config, {
                format: this.dateFormat || Ext.DatePicker.prototype.format
            });

            var picker = this.datePicker = Ext.create(config, 'datepicker');

            picker.update = picker.update.createSequence(function () {
                if (this.el != null && this.datePicker.rendered) {
                    var width = this.datePicker.el.getWidth();
                    this.el.setWidth(width + this.el.getBorderWidth('lr') + this.el.getPadding('lr'));
                }
            }, this);
        },

        _renderDatePicker: function (ct) {
            var picker = this.datePicker;

            picker.render(ct);

            this.todayBtn = picker.todayBtn;

            var bottomEl = picker.getEl().child('.x-date-bottom');
            this.todayBtn.addClass('ux-datetime-today-btn');
            this.doneBtn = new Ext.Button({
            	cls: 'ux-datetime-done-btn',
                text: this.doneText,
                handler: this.onDone,
                scope: this
            }).render(bottomEl);
            this.updateDoneBtnTooltip();
        },

        _getFormattedTimeValue: function (date) {
			return date.format(this.timePicker.strategy.format);
        },

        _renderValueField: function (ct) {
			var cls = CLS + '-value-ct';

			var div = ct.insertFirst({
				tag : 'div',
				cls : [ cls, 'x-date-bottom' ].join(' ')
			});
			
			var timePanel = new Ext.Container({
				layout: 'form',
				labelWidth: 75,
				labelAlign: 'left',
				defaults: {
					anchor: '100%'
				},
				items : [this.timePicker]
			});
			timePanel.render(div);
        },
		updateDoneBtnTooltip : function() {
			var date = this.getValue();
			var txt = this.doneBtnInvalidText;
			if( date ){
				txt = String.format(this.doneTip, date.format(this.format));
			}
			this.doneBtn.setTooltip(txt);
		},

        onRender: function (ct, position) {
            this.el = ct.createChild({
                tag: 'div',
                cls: CLS,
                children: [
                    {
                        tag: 'div',
                        cls: CLS + '-inner'
                    }
                ]
            }, position);

            UX.DateTimePicker.superclass.onRender.call(this, ct, position);

            var innerEl = this.el.first();

            this._renderDatePicker(innerEl);

            this._renderValueField(innerEl);
            this.todayBtn.on('click', this.onTodayClick, this);
            this.todayBtn.setText(this.todayBtnText);
            
            this.datePicker.on('select', this.updateDoneBtnTooltip, this);
            this.datePicker.keyNav.enter = false;
            this.keyNav = new Ext.KeyNav(this.el, {
                'enter' : this.onEnterKey,
                'tab' : this.onTabKey,
                scope : this
            });
            this.datePicker.focus();
            this.focused = this.datePicker;
        },
        // toggle focus between time and date pickers
        onTabKey : function(){
        	if( this.focused === this.timePicker){
	        	this.datePicker.focus();
	        	this.focused = this.datePicker;
        	} else {
        		this.timePicker.focus();
                this.focused = this.timePicker;
        	}
        },
        onEnterKey : function(){
        	if( this.timePicker.isValid()){
	        	this.datePicker.setValue(this.datePicker.activeDate);
	        	this.onDone();
        	}
        },
        
		onTodayClick: function(c,e) {
			this._updateTimeValue(new Date());
		},

        _updateTimeValue: function (date) {
			if( date ){
				this.timeValue = date;
				this.timePicker.setValue(date.format(this.timePicker.strategy.format));
			}
        },

        setValue: function (value) {
            this._updateTimeValue(value);
            this.datePicker.setValue(value.clone());
        },

        getValue: function () {
            var date = this.datePicker.getValue();

            var time = this.timeValue.getElapsed(this.timeValue.clone().clearTime());

            return new Date(date.getTime() + time);
        },

		onTimeSelect : function(timeField, record, index) {
			this._updateTimeValue(Date.parseDate(timeField.getValue(), timeField.strategy.format));
            this.updateDoneBtnTooltip();
        },

        onDone: function () {
        	if( this.timePicker.isValid()){
        		this.fireEvent('select', this, this.getValue());
        	}
        },

        destroy: function () {
			Ext.destroy(this.timePicker);
			this.timePicker= null;

            if (this.timeValueEl) {
                this.timeValueEl.remove();
                this.timeValueEl = null;
            }

            Ext.destroy(this.datePicker);
            this.datePicker = null;

            if (this.timeMenu) {
                Ext.destroy(this.timeMenu);
                this.timeMenu = null;
            }
            this.todayBtn = null;

            if (this.doneBtn) {
                Ext.destroy(this.doneBtn);
                this.doneBtn = null;
            }

            this.parentMenu = null;

            UX.DateTimePicker.superclass.destroy.call(this);
        }

    });

    Ext.reg('datetimepicker', UX.DateTimePicker);

    //

    var Menu = UX.DateTimePicker.Menu = Ext.extend(Ext.menu.Menu, {
        enableScrolling : false,
        hideOnClick: false,
        plain: true,
        showSeparator: false,
        constructor: function (picker, config) {
            config = config || {};

            if (config.picker) {
                delete config.picker;
            }

            this.picker = Ext.create(picker);

            Menu.superclass.constructor.call(this, Ext.applyIf({
                items: this.picker
            }, config));

            this.addEvents('timeselect');

            this.picker.on('select', this.onTimeSelect, this);
        },

        getPicker: function () {
            return this.picker;
        },

        onTimeSelect: function (picker, value) {
            this.hide();
            this.fireEvent('timeselect', this, picker, value);
        },

        destroy: function () {
            this.purgeListeners();

            this.picker = null;

            Menu.superclass.destroy.call(this);
        }

    });

})();