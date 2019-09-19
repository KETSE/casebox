Ext.namespace('CB.plugin.task');

Ext.define('CB.plugin.task.DateChangeSync', {
    extend: 'CB.plugin.CustomInterface'
    ,alias: 'plugin.CBPluginTaskDateChangeSync'

    ,init: function(owner) {
        this.historyData = {};

        this.callParent(arguments);

        this.owner = owner;

        if(
            owner &&
            owner.refOwner &&
            owner.refOwner.data &&
            (CB.DB.templates.getType(owner.refOwner.data.template_id) === 'task')
        ) {
            owner.on('change', this.onFieldChange, this);
        }
    }

    ,onFieldChange: function(fieldName, newValue, oldValue) {
        switch(fieldName) {
            case 'allday':
                if((oldValue == 1) && (newValue == -1)) { //change from allday to time period
                    this.setDateTimeFromDateFields();
                } else if((oldValue == -1) && (newValue == 1)) {
                    this.setDateFromDateTimeFields();
                }
                break;

            case 'date_start':
            case 'date_end':
            case 'datetime_start':
            case 'datetime_end':
                this.dateShift(fieldName, newValue, oldValue);
                break;
        }
    }

    ,setDateTimeFromDateFields: function() {
        var ht = this.owner.helperTree
            ,sd1, sd2
            ,td1 = ht.getNodesByFieldName('datetime_start')[0]
            ,td2 = ht.getNodesByFieldName('datetime_end')[0];

        //change date time start only if its not set already
        if(Ext.isEmpty(td1.data.value.value)) {
            sd1 = ht.getNodesByFieldName('date_start')[0];
            //check if we have a date_start value
            if(!Ext.isEmpty(sd1.data.value.value)) {
                td1.data.value.value = new Date(
                    Date.parse(
                        Ext.Date.format(sd1.data.value.value, 'Y-m-d') +
                        ' ' +
                        Ext.Date.format(new Date(), 'H:i:s')
                    )
                );
            }
        }

        //change date time end only if its not set already
        if(Ext.isEmpty(td2.data.value.value)) {
            sd2 = ht.getNodesByFieldName('date_end')[0];
            //check if we have a date_end value
            if(!Ext.isEmpty(sd2.data.value.value)) {
                td2.data.value.value = new Date(
                    Date.parse(
                        Ext.Date.format(sd2.data.value.value, 'Y-m-d') +
                        ' ' +
                        Ext.Date.format(new Date(), 'H:i:s')
                    )
                );
            }
        }
    }

    ,setDateFromDateTimeFields: function() {
       var ht = this.owner.helperTree
            ,sd1, sd2
            ,td1 = ht.getNodesByFieldName('date_start')[0]
            ,td2 = ht.getNodesByFieldName('date_end')[0];

        //change date time start only if its not set already
        if(Ext.isEmpty(td1.data.value.value)) {
            sd1 = ht.getNodesByFieldName('datetime_start')[0];
            //check if we have a datetime_start value
            if(!Ext.isEmpty(sd1.data.value.value)) {
                td1.data.value.value = new Date(
                    Date.parse(
                        Ext.Date.format(sd1.data.value.value, 'Y-m-d') + ' 00:00:00'
                    )
                );
            }
        }

        //change date time end only if its not set already
        if(Ext.isEmpty(td2.data.value.value)) {
            sd2 = ht.getNodesByFieldName('datetime_end')[0];
            //check if we have a datetime_end value
            if(!Ext.isEmpty(sd2.data.value.value)) {
                td2.data.value.value = new Date(
                    Date.parse(
                        Ext.Date.format(sd2.data.value.value, 'Y-m-d') + ' 00:00:00'
                    )
                );
            }
        }
    }

    ,dateShift: function(fieldName, newValue, oldValue) {
        var ht = this.owner.helperTree
            ,arr = fieldName.split('_')
            ,fieldType = arr[0]
            ,fieldSuffix = arr[1]
            ,pairFieldName = fieldType + '_' + (
                (fieldSuffix === 'start')
                ? 'end'
                : 'start'
            )
            ,pairField = ht.getNodesByFieldName(pairFieldName)[0]
            ,pairValue = pairField.data.value.value
            ,shiftDays = 0;

        if(Ext.isEmpty(newValue) || Ext.isEmpty(pairValue)) {
            return;
        }


        switch(fieldSuffix) {
            case 'start':
                //if old value not empty we'll shift end date according to start date
                if(!Ext.isEmpty(oldValue)) {
                    pairField.data.value.value = Ext.Date.add(
                        pairValue
                        ,'s'
                        ,Ext.Date.diff(
                            oldValue
                            ,newValue
                            ,'s'
                        )
                    );

                } else if(pairValue < newValue) {
                    pairField.data.value.value = Ext.Date.clone(newValue);
                }
                break;

            case 'end':
                if(pairValue > newValue) {
                    pairField.data.value.value = Ext.Date.clone(newValue);
                }
                break;
        }
    }
});

Ext.onReady(function(){
    var plugins = CB.VerticalEditGrid.prototype.plugins || [];
    plugins.push({
        ptype: 'CBPluginTaskDateChangeSync'
    });

    CB.VerticalEditGrid.prototype.plugins = plugins;
});
