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
