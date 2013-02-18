Ext.namespace('Ext.ux.menu');

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
