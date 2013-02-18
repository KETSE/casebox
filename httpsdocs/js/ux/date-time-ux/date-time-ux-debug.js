/*!
 * Copyright (c) 2011 Andrew Pleshkov andrew.pleshkov@gmail.com
 * 
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */
Ext.namespace('Ext.ux');

(function () {

    var UX = Ext.ux;

    UX.BaseTimePicker = Ext.extend(Ext.Panel, {

        format: 'g:i A',

        header: true,

        nowText: 'Now',

        doneText: 'Done',

        hourIncrement: 1,

        minIncrement: 1,

        hoursLabel: 'Hours',

        minsLabel: 'Minutes',

        cls: 'ux-base-time-picker',

        width: 210,

        layout: 'form',

        labelAlign: 'top',

        initComponent: function () {
            this.addEvents('select');

            this.hourSlider = new Ext.slider.SingleSlider({
                increment: this.hourIncrement,
                minValue: 0,
                maxValue: 23,
                fieldLabel: this.hoursLabel,
                listeners: {
                    change: this._updateTimeValue,
                    scope: this
                },
                plugins: new Ext.slider.Tip()
            });

            this.minSlider = new Ext.slider.SingleSlider({
                increment: this.minIncrement,
                minValue: 0,
                maxValue: 59,
                fieldLabel: this.minsLabel,
                listeners: {
                    change: this._updateTimeValue,
                    scope: this
                },
                plugins: new Ext.slider.Tip()
            });

            this.setCurrentTime(false);

            this._initItems();

            this.bbar = [
                {
                    text: this.nowText,
                    handler: this.setCurrentTime,
                    scope: this
                },
                '->',
                {
                    text: this.doneText,
                    handler: this.onDone,
                    scope: this
                }
            ];

            UX.BaseTimePicker.superclass.initComponent.call(this);
        },

        _initItems: function () {
            this.items = [
                this.hourSlider,
                this.minSlider
            ];
        },

        setCurrentTime: function (animate) {
            this.setValue(new Date(), !!animate);
        },

        onDone: function () {
            this.fireEvent('select', this, this.getValue());
        },

        setValue: function (value, animate) {
            this.hourSlider.setValue(value.getHours(), animate);
            this.minSlider.setValue(value.getMinutes(), animate);

            this._updateTimeValue();
        },

        _extractValue: function () {
            var v = new Date();
            v.setHours(this.hourSlider.getValue());
            v.setMinutes(this.minSlider.getValue());
            return v;
        },

        getValue: function () {
            return this._extractValue();
        },

        _updateTimeValue: function () {
            var v = this._extractValue().format(this.format);

            if (this.rendered) {
                this.setTitle(v);
            }
        },

        afterRender: function () {
            UX.BaseTimePicker.superclass.afterRender.call(this);

            this._updateTimeValue();
        },

        destroy: function () {
            this.purgeListeners();

            this.hourSlider = null;
            this.minSlider = null;

            UX.BaseTimePicker.superclass.destroy.call(this);
        }

    });

    Ext.reg('basetimepicker', UX.BaseTimePicker);

})();
Ext.namespace('Ext.ux');

(function () {

    var UX = Ext.ux;

    UX.ExBaseTimePicker = Ext.extend(Ext.ux.BaseTimePicker, {

        format: 'g:i:s A',

        secIncrement: 1,

        secsLabel: 'Seconds',

        initComponent: function () {
            this.secSlider = new Ext.slider.SingleSlider({
                increment: this.secIncrement,
                minValue: 0,
                maxValue: 59,
                fieldLabel: this.secsLabel,
                listeners: {
                    change: this._updateTimeValue,
                    scope: this
                },
                plugins: new Ext.slider.Tip()
            });

            UX.ExBaseTimePicker.superclass.initComponent.call(this);
        },

        _initItems: function () {
            UX.ExBaseTimePicker.superclass._initItems.call(this);

            this.items.push(this.secSlider);
        },

        setValue: function (value, animate) {
            this.secSlider.setValue(value.getSeconds(), animate);

            UX.ExBaseTimePicker.superclass.setValue.call(this, value, animate);
        },

        _extractValue: function () {
            var v = UX.ExBaseTimePicker.superclass._extractValue.call(this);

            v.setSeconds(this.secSlider.getValue());
            return v;
        },

        destroy: function () {
            this.secSlider = null;

            UX.ExBaseTimePicker.superclass.destroy.call(this);
        }

    });

    Ext.reg('exbasetimepicker', UX.ExBaseTimePicker);

})();
Ext.namespace('Ext.ux');

(function () {

    var UX = Ext.ux;

    var CLS = 'ux-date-time-picker';

    UX.DateTimePicker = Ext.extend(Ext.BoxComponent, {

        timeLabel: 'Time',

        changeTimeText: 'Change...',

        doneText: 'Done',

        initComponent: function () {
            UX.DateTimePicker.superclass.initComponent.call(this);

            this.addEvents('select');

            this.timePickerButton = new Ext.Button({
                text: this.changeTimeText,
                handler: this._showTimePicker,
                scope: this
            });

            this._initDatePicker();
            this._initTimePicker();

            this.timeValue = new Date();

            if (this.value) {
                this.setValue(this.value);
                delete this.value;
            }
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

            var bottomEl = picker.getEl().child('.x-date-bottom');

            var size = bottomEl.getSize(true);
            var style = [
                'position: absolute',
                'bottom: 0',
                'left: 0',
                'overflow: hidden',
                'width: ' + size.width + 'px',
                'height: ' + size.height + 'px'
            ].join(';');

            var div = ct.createChild({
                tag: 'div',
                cls: 'x-date-bottom',
                style: style,
                children: [
                    {
                        tag: 'table',
                        cellspacing: 0,
                        style: 'width: 100%',
                        children: [
                            {
                                tag: 'tr',
                                children: [
                                    {
                                        tag: 'td',
                                        align: 'left'
                                    },
                                    {
                                        tag: 'td',
                                        align: 'right'
                                    }
                                ]
                            }
                        ]
                    }
                ]
            });

            if (picker.showToday) {
                var todayConfig = {};
                Ext.each(['text', 'tooltip', 'handler', 'scope'], function (key) {
                    todayConfig[key] = picker.todayBtn.initialConfig[key];
                });
                this.todayBtn = new Ext.Button(todayConfig).render(div.child('td:first'));
            }

            this.doneBtn = new Ext.Button({
                text: this.doneText,
                handler: this.onDone,
                scope: this
            }).render(div.child('td:last'));
        },

        _getFormattedTimeValue: function (date) {
            return date.format(this.timeMenu.picker.format);
        },

        _renderValueField: function (ct) {
            var cls = CLS + '-value-ct';

            var timeLabel = !Ext.isEmpty(this.timeLabel)
                    ? '<span class="' + cls + '-value-label">' + this.timeLabel + ':</span>&nbsp;'
                    : '';

            var div = ct.insertFirst({
                tag: 'div',
                cls: [cls, 'x-date-bottom'].join(' ')
            });

            var table = div.createChild({
                tag: 'table',
                cellspacing: 0,
                style: 'width: 100%',
                children: [
                    {
                        tag: 'tr',
                        children: [
                            {
                                tag: 'td',
                                align: 'left',
                                cls: cls + '-value-cell',
                                html: '<div class="' + cls + '-value-wrap">'
                                        + timeLabel
                                        + '<span class="' + cls + '-value">'
                                        + this._getFormattedTimeValue(this.timeValue)
                                        + '</span>'
                                        + '</div>'
                            },
                            {
                                tag: 'td',
                                align: 'right',
                                cls: cls + '-btn-cell'
                            }
                        ]
                    }
                ]
            });

            this.timeValueEl = table.child('.' + cls + '-value');
            this.timeValueEl.on('click', this._showTimePicker, this);

            this.timePickerButton.render(table.child('td:last'));
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
        },

        _updateTimeValue: function (date) {
            this.timeValue = date;
            if (this.timeValueEl != null) {
                this.timeValueEl.update(this._getFormattedTimeValue(date));
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

        onTimeSelect: function (menu, picker, value) {
            this._updateTimeValue(value);
        },

        _showTimePicker: function () {
            this.timeMenu.getPicker().setValue(this.timeValue, false);

            if (this.timeMenu.isVisible()) {
                this.timeMenu.hide();
            } else {
                this.timeMenu.show(this.timePickerButton.el, null, this.parentMenu);
            }
        },

        onDone: function () {
            this.fireEvent('select', this, this.getValue());
        },

        destroy: function () {
            Ext.destroy(this.timePickerButton);
            this.timePickerButton = null;

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

            if (this.todayBtn) {
                Ext.destroy(this.todayBtn);
                this.todayBtn = null;
            }

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

})();Ext.namespace('Ext.ux.menu');

(function () {

    var M = Ext.ux.menu;

    var isStrict = Ext.isIE7 && Ext.isStrict;

    M.DateTimeMenu = Ext.extend(Ext.menu.Menu, {

        enableScrolling : false,

        plain: true,

        showSeparator: false,

        hideOnClick : true,

        pickerId : null,

        cls : 'x-date-menu x-date-time-menu',

        constructor: function (config) {
            this.picker = this._createPicker(config || {});
            delete config.picker;

            M.DateTimeMenu.superclass.constructor.call(this, Ext.applyIf({
                items: this.picker
            }, config || {}));

            this.picker.parentMenu = this;

            this.on('beforeshow', this.onBeforeShow, this);

            if (isStrict) {
                this.on('show', this.onShow, this, { single: true, delay: 20 });
            }

            this.relayEvents(this.picker, ['select']);
            this.on('show', this.picker.focus, this.picker);
            this.on('select', this.menuHide, this);

            if (this.handler) {
                this.on('select', this.handler, this.scope || this);
            }
        },

        _createPicker: function (initialConfig) {
            var picker = initialConfig.picker;

            var defaultConfig = {
                ctCls: 'x-menu-date-item',
                internalRender: isStrict || !Ext.isIE
            };

            if (typeof picker === 'object') {
                if (picker.render) {
                    return picker;
                } else {
                    return Ext.create(Ext.apply(defaultConfig, picker), 'datetimepicker');
                }
            } else {
                return Ext.create(defaultConfig, 'datetimepicker');
            }
        },

        menuHide : function () {
            if (this.hideOnClick) {
                this.hide(true);
            }
        },

        onBeforeShow : function () {
            if (this.picker.datePicker) {
                this.picker.datePicker.hideMonthPicker(true);
            }
        },

        onShow : function () {
            var el = this.picker.datePicker.getEl();
            el.setWidth(el.getWidth()); // nasty hack for IE7 strict mode
        },

        destroy: function () {
            this.picker.destroy();
            this.picker = null;

            M.DateTimeMenu.superclass.destroy.call(this);
        }

    });

    Ext.reg('datetimemenu', M.DateTimeMenu);

})();
Ext.namespace('Ext.ux.form');

(function () {

    var UX = Ext.ux;
    var F = UX.form;

    F.DateTimeField = Ext.extend(Ext.form.DateField, {

        timeFormat: 'g:i A',

        defaultAutoCreate: {
            tag: 'input',
            type: 'text',
            size: '22',
            autocomplete: 'off'
        },

        initComponent: function () {
            F.DateTimeField.superclass.initComponent.call(this);

            this.dateFormat = this.dateFormat || this.format;
            this.format = this.dateFormat + ' ' + this.timeFormat;

            var pickerConfig = Ext.apply(this.picker || {}, {
                dateFormat: this.dateFormat,
                timeFormat: this.timeFormat
            });

            delete this.picker;
            delete this.initialConfig.picker;

            this.menu = new UX.menu.DateTimeMenu({
                picker: pickerConfig,
                hideOnClick: false
            });
        },

        onTriggerClick: function () {
            F.DateTimeField.superclass.onTriggerClick.call(this);

            this.menu.picker.setValue(this.getValue() || new Date());
        }

    });

    Ext.reg('datetimefield', F.DateTimeField);

})();