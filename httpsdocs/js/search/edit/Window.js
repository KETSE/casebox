Ext.namespace('CB');

Ext.define('CB.search.edit.Window', {
    extend: 'Ext.Window'
    ,alias: 'CBSearchEditWindow'

    ,xtype: 'CBSearchEditWindow'

    ,closable: true
    ,minimizable: true
    ,maximizable: true
    ,layout: 'fit'
    ,border: false
    ,minWidth: 200
    ,minHeight: 200
    ,width: 400
    ,height: 450
    ,iconCls: 'icon-none'
    ,scrollable: true

    ,initComponent: function() {
        this.data = Ext.apply({}, this.config.data);

        Ext.apply(this, {
            cls: 'x-panel-white'
            ,bodyStyle: 'border: 0'

            ,items: [
                {
                    xtype: 'CBSearchPanel'
                    ,hideTitle: true
                    ,scrollable: true
                    ,listeners: {
                        scope: this
                        ,loaded: this.onLoaded
                    }
                }
            ]
            ,listeners: {
                scope: this
                ,'afterrender': this.onAfterRender
                // ,'beforeclose': this.onBeforeClose
            }
        });

        this.callParent(arguments);

        this.editForm = this.items.getAt(0);
    }

    ,onAfterRender: function() {
        this.editForm.load(this.data);
    }

    ,onLoaded: function(editForm) {
        var title = Ext.valueFrom(editForm.data.name, '');

        this.setTitle(Ext.util.Format.htmlEncode(title));
        this.setIconCls(getItemIcon(editForm.data));
        this.updateLayout();
    }

    ,onSaveObjectEvent: function(objComp, ev) {
        ev.stopPropagation();
        ev.preventDefault();
        if(this.actions.save.isDisabled()) {
            return false;
        }
        this.onSaveClick();
    }

    ,onSaveClick: function() {
        this.editForm.save(
            //callback function
            function(component, form, action){
                if(action.result.success !== true) {
                    App.showException(action.result);
                } else {
                    this.actions.save.setDisabled(true);
                    this.close();
                }
            }
            ,this
        );
    }

    ,onIconChange: function(f, newIconCls, oldIconCls, eOpts) {
        this.setIconCls(newIconCls);
    }

    ,onBeforeClose: function(){
        if(this._confirmedClosing || !this.editForm._isDirty){
            return true;
        }

        Ext.Msg.show({
            title:  L.Confirmation
            ,msg:   L.SavingChangedDataMessage
            ,icon:  Ext.Msg.QUESTION
            ,buttons: Ext.Msg.YESNOCANCEL
            ,scope: this
            ,fn: function(b, text, opt){
                switch(b){
                case 'yes':
                    this._confirmedClosing = true;
                    this.editForm.save(this.close, this);
                    break;
                case 'no':
                    this._confirmedClosing = true;
                    this.close();
                    break;
                }
            }
        }).getEl().center(this);

        return false;
    }
});
