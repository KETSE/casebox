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