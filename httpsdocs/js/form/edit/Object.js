Ext.namespace('CB.form.edit');

CB.form.edit.Object = Ext.extend(Ext.Container, {
    xtype: 'panel'
    ,tbarCssClass: 'x-panel-white'
    ,padding:0
    ,autoScroll: true
    ,layout: 'anchor'
    ,data: {}
    ,initComponent: function(){

        this.data = Ext.apply({}, this.data);

        this.objectsStore = new CB.DB.DirectObjectsStore({
            listeners:{
                scope: this
                ,add: this.onObjectsStoreChange
                ,load: this.onObjectsStoreChange
            }
        });

        this.grid = new CB.VerticalEditGrid({
            title: L.Details
            ,autoHeight: true
            ,hidden: true
            ,refOwner: this
            ,includeTopFields: true
            ,viewConfig: {
                // forceFit: true
                // ,autoFill: true
            }
        });

        this.fieldsZone = new Ext.form.FormPanel({
            title: L.Fields
            ,header: false
            ,border: false
            ,autoHeight: true
            ,labelAlign: 'top'
            ,bodyStyle: 'margin:0; padding:0'
            ,items: []
            ,api: {
                submit: CB_Objects.save
            }
        });

        Ext.apply(this, {
            defaults: {
                anchor: '-1'
                ,style: 'margin: 0 0 15px 0'
            }
            ,items: [ {
                    xtype: 'panel'
                    ,layout: 'fit'
                    ,autoHeight: true
                    ,autoScroll: true
                    ,border: false
                    ,items: this.grid
                }
                ,this.fieldsZone
            ]
            ,listeners: {
                scope: this
                ,change: this.onChange
            }
        });
        CB.form.edit.Object.superclass.initComponent.apply(this, arguments);
    }

    ,onChange: function(){
        this._isDirty = true;
    }

    ,load: function(objectData) {
        if(Ext.isEmpty(objectData)) {
            return;
        }

        if(Ext.isNumber(objectData)) {
            objectData = {id: objectData};
        }
        this.loadData(objectData);
    }

    ,loadData: function(objectData) {
        this.requestedLoadData = objectData;
        if(this._isDirty) {
            this.confirmDiscardChanges();
            return;
        }


        this.clear();
        // this.getEl().mask(L.LoadingData + ' ...', 'x-mask-loading');

        if(Ext.isNumber(objectData.id)) {
            CB_Objects.load(
                {id: objectData.id}
                ,this.processLoadData
                ,this
            );
        } else {
            this.processLoadData({
                    success: true
                    ,data: objectData
                }
            );
        }
    }
    ,processLoadData: function(r, e) {
        this.getEl().unmask();
        if(r.success !== true) {
            return;
        }
        this.data = r.data;
        if(Ext.isEmpty(this.data.data)) {
            this.data.data = {};
        }

        this.objectsStore.baseParams = {
            id: r.data.id
            ,template_id: r.data.template_id
        };
        this.objectsStore.reload();

        this.grid.reload();
        if(this.grid.store.getCount() > 0) {
            this.grid.show();
            this.grid.getView().refresh(true);
            this.grid.doLayout();
            this.grid.focus();
            this.grid.getSelectionModel().select(0, 1);
        }

        if(this.grid.templateStore) {
            var fields = [];
            this.grid.templateStore.each(
                function(r) {
                    if(r.get('cfg').showIn == 'tabsheet') {
                        var cfg = {
                            border: false
                            ,hideBorders: true
                            ,title: r.get('title')
                            ,isTemplateField: true
                            ,name: r.get('name')
                            ,value: this.data.data[r.get('name')]
                            ,height: Ext.value(r.get('cfg').height, 200)
                            ,anchor: '100%'
                            ,style: 'resize: vertical'
                            ,grow: true
                            ,fieldLabel: r.get('title')
                            ,listeners: {
                                scope: this
                                ,change: function(){ this.fireEvent('change'); }
                                ,sync: function(){ this.fireEvent('change'); }
                            }
                            ,xtype: (r.get('type') == 'html')
                                ? 'CBHtmlEditor'
                                : 'textarea'
                        };
                        this.fieldsZone.add(cfg);
                    }
                }
                ,this
            );

            // if(this.grid.rendered) {
            //     this.grid.doLayout();
            //     this.grid.syncSize();
            // }
            // if(this.fieldsZone.rendered) {
            //     this.fieldsZone.doLayout();
            //     this.fieldsZone.syncSize();
            // }
        }
        this._isDirty = false;

        this.doLayout();
        this.syncSize();
    }

    ,onObjectsStoreChange: function(store, records, options){
        Ext.each(
            records
            ,function(r){
                r.set('iconCls', getItemIcon(r.data));
            }
            ,this
        );
        if(this.grid && !this.grid.editing && this.grid.getEl()) {
            var sc = this.grid.getSelectionModel().getSelectedCell();
            this.grid.getView().refresh(true);
            if(sc) {
                this.grid.getSelectionModel().select(sc[0], sc[1]);
                this.grid.getView().focusCell(sc[0], sc[1]);
            }
        }
    }

    ,confirmDiscardChanges: function(){
        //if confirmed
        //save
        //  save and load new requested data
        //no
        //  load new requested data
        //  cancel
        //      discard requested data
        //
        Ext.Msg.show({
            title:  L.Confirmation
            ,msg:   L.SavingChangedDataMessage
            ,icon:  'ext-mb-question'
            ,buttons: Ext.Msg.YESNOCANCEL
            ,scope: this
            ,fn: function(b, text, opt){
                switch(b){
                    case 'yes':
                        this.save();
                        break;
                    case 'no':
                        this.clear();
                        this.loadData(this.requestedLoadData);
                        break;
                    default:
                        delete this.requestedLoadData;
                }
            }
        });
    }
    ,save: function(callback, scope) {
        if(!this._isDirty) {
            return;
        }
        this.grid.readValues();
        this.data.data = Ext.apply(this.data.data, this.fieldsZone.getForm().getFieldValues());

        if(callback) {
            this.saveCallback = callback.createDelegate(scope || this);
        }

        this.getEl().mask(L.Saving + ' ...', 'x-mask-loading');

        this.fieldsZone.getForm().submit({
            clientValidation: true
            ,loadMask: false
            ,params: {
                data: Ext.encode(this.data)
            }
            ,scope: this
            ,success: this.processSave
            ,failure: this.processSave
        });

    }
    ,processSave: function(form, action) {
        this.getEl().unmask();
        var r = action.result;
        if(r.success !== true) {
            delete this.saveCallback;
            return;
        }
        this._isDirty = false;
        if(this.saveCallback) {
            this.saveCallback(this);
            delete this.saveCallback;
        }
        App.fireEvent('objectchanged', r.data);

    }
    ,clear: function(){
        this.data = {};
        this.grid.hide();
        this.fieldsZone.removeAll(true);
        this._isDirty = false;
        this.fireEvent('clear', this);
    }

});

Ext.reg('CBEditObject', CB.form.edit.Object);
