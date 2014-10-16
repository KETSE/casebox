Ext.namespace('CB');

Ext.define('CB.window.edit.Object', {
    extend: 'Ext.Window'
    ,alias: 'CBObjectEditWindow'

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
    ,autoScroll: true

    ,initComponent: function() {

        this.data = Ext.apply({}, this.config.data);
        delete this.data.html;

        this.actions = {
            save: new Ext.Action({
                text: L.Save
                ,iconCls: 'icon-save'
                ,disabled: true
                ,scope: this
                ,handler: this.onSaveClick
            })
            ,cancel: new Ext.Action({
                text: Ext.MessageBox.buttonText.cancel
                ,iconCls: 'i-cancel'
                ,scope: this
                ,handler: this.close
            })
        };

        Ext.apply(this, {
            cls: 'x-panel-white'
            ,bodyStyle: 'border: 0'
            ,tbar: [
                this.actions.save
                ,this.actions.cancel
            ]

            ,items: [
                {
                    xtype: 'CBEditObject'
                    ,hideTitle: true
                    ,autoScroll: true
                    ,listeners: {
                        scope: this
                        ,loaded: this.onLoaded
                        ,changed: function(){
                            this.actions.save.setDisabled(!this.editForm.isValid() || !this.editForm._isDirty);
                            this.updateLayout();
                        }
                        ,clear: function(){
                            this.actions.save.setDisabled(!this.editForm.isValid() || !this.editForm._isDirty);
                        }
                        ,saveobject: this.onSaveObjectEvent
                        // ,loaded: this.onCardItemLoaded
                        ,'iconclschange': this.onIconChange
                        ,'resize': this.updateLayout
                    }
                }
            ]
            ,listeners: {
                scope: this
                ,'afterrender': this.onAfterRender
                ,'beforeclose': this.onBeforeClose
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
            ,icon:  'ext-mb-question'
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
        });

        return false;
    }

});
