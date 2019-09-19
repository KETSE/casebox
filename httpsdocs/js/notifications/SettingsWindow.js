Ext.namespace('CB.notifications');

Ext.define('CB.notifications.SettingsWindow', {
    extend: 'Ext.Window'

    ,alias: 'widget.CBNotificationsSettingsWindow'

    ,border: false
    ,modal: true
    ,autoHeight: true
    ,autoWidth: true
    ,layout: 'fit'
    ,minWidth: 250
    ,minHeight: 150
    ,width: 300

    ,initComponent: function(){

        Ext.apply(this, {
            title: L.NotificationsSettings
            ,items: {
                xtype: 'form'
                ,forceLayout: true
                ,bodyPadding: 10
                ,defaults: {
                    labelAlign: 'top'
                }
                ,items: [
                    {
                        xtype: 'displayfield'
                        ,cls: 'fs20'
                        ,value: 'Push notifications'
                    },{
                        xtype: 'combo'
                        ,itemId: 'cbNotifyFor'
                        ,fieldLabel: 'Send me email notifications for'
                        ,anchor: '100%'
                        ,store: CB.DB.notifyFor
                        ,lazyRender: true
                        ,forceSelection: true
                        ,triggerAction: 'all'
                        ,queryMode: 'local'
                        ,editable: false
                        ,displayField: 'name'
                        ,valueField: 'id'
                        // ,value: 'mentioned'
                        ,listeners: {
                            scope: this
                            ,change: this.updateAvailability
                        }
                    },{
                        xtype: 'displayfield'
                        ,cls: 'fs20 pt10'
                        ,itemId: 'dfTimings'
                        ,disabled: true
                        ,value: 'Push notifications timing'
                    },{
                        xtype: 'radiogroup'
                        ,itemId: 'rgTimings'
                        ,columns: 1
                        ,vertical: true
                        ,items: [
                            {
                                boxLabel: 'Send instantly'
                                ,name: 'rb'
                                ,inputValue: 1
                            },{
                                boxLabel: 'After being idle for:'
                                ,name: 'rb'
                                ,inputValue: 2
                                ,checked: true
                            }
                        ]
                        ,listeners: {
                            scope: this
                            ,change: this.updateAvailability
                        }
                    },{
                        xtype: 'combo'
                        ,anchor: '100%'
                        ,itemId: 'cbTimings'
                        ,disabled: true
                        ,editable: false
                        ,store: CB.DB.idleTimings
                        ,lazyRender: true
                        ,forceSelection: true
                        ,triggerAction: 'all'
                        ,queryMode: 'local'
                        ,displayField: 'name'
                        ,valueField: 'id'
                        ,value: 2
                        ,listeners: {
                            scope: this
                            ,change: this.updateAvailability
                        }
                    }
                ]
                ,buttons: [
                    {
                        text: 'Ok'
                        ,itemId: 'btnOk'
                        ,disabled: true
                        ,formBind: true
                        ,scope: this
                        ,handler: this.onOkClick
                    }
                ]
            }
        });

        this.callParent(arguments);

        this.notifyCombo = this.down('[itemId="cbNotifyFor"]');
        this.timingsHeader = this.down('[itemId="dfTimings"]');
        this.timingsRadio = this.down('[itemId="rgTimings"]');
        this.timingsCombo = this.down('[itemId="cbTimings"]');

        this.reload();
    }

    ,reload: function() {
        CB_User.getNotificationSettings(
            this.processGetNotificationSettings
            ,this
        );
    }

    ,processGetNotificationSettings: function(r, e) {
        if(!r || (r.success !== true)) {
            return;
        }
        var d = r.data;

        this.notifyCombo.originalValue = d.notifyFor;
        this.notifyCombo.setValue(d.notifyFor);

        this.timingsRadio.originalValue = d.delay;
        this.timingsRadio.setValue({'rb' : d.delay});

        this.timingsCombo.originalValue = d.delaySize;
        this.timingsCombo.setValue(d.delaySize);

        this.updateAvailability();
        var okb = this.down('[itemId="btnOk"]');
        Ext.Function.defer(okb.disable, 10, okb);
    }

    ,updateAvailability: function() {
        var enableDelay = (this.notifyCombo.getValue() !== 'none')
            ,enableDelaySize = (this.timingsRadio.getValue().rb == 2);

        this.timingsHeader.setDisabled(!enableDelay);

        if(enableDelay) {
            this.timingsRadio.setDisabled(false);
            var r = this.timingsRadio.items.getAt(0);
            if(r && r.el) {
                r.unmask();
                this.timingsRadio.items.getAt(1).unmask();
            }
        }  else {
            this.timingsRadio.setDisabled(true);
        }

        this.timingsCombo.setDisabled(!enableDelay || !enableDelaySize);

        this.down('[itemId="btnOk"]').setDisabled(!this.items.getAt(0).isDirty());
    }

    ,onOkClick: function(b, e) {
        var rez = {
            notifyFor : this.notifyCombo.getValue()
            ,delay: this.timingsRadio.getValue().rb
            ,delaySize: this.timingsCombo.getValue()
        };

        CB_User.setNotificationSettings(rez, this.close, this);
    }
});
