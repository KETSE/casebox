Ext.namespace('CB.form.edit');

Ext.define('CB.object.edit.Form', {
    extend: 'Ext.Panel'

    ,alias: 'widget.CBEditObject'

    ,tbarCssClass: 'x-panel-white'
    ,padding: 0
    ,scrollable: false
    ,layout: 'anchor'
    ,data: {}

    ,initComponent: function(){

        this.data = Ext.apply({}, this.config.data);
        this.objectsStore = new CB.DB.DirectObjectsStore({
            listeners:{
                scope: this
                ,add: this.onObjectsStoreChange
                ,load: this.onObjectsStoreLoad
            }
        });

        this.titleView = new Ext.DataView({
            autoHeight: true
            ,hidden: (this.hideTitle === true)
            ,cls: 'obj-plugin-title'
            ,tpl: [
                '<tpl for=".">'
                ,'<div class="obj-header">{[ Ext.util.Format.htmlEncode(Ext.valueFrom(values.name, \'\')) ]}</div>'
                ,'</tpl>'
            ]
            ,itemSelector: 'div'
            ,data: {}
        });

        this.fieldsZone = new Ext.form.FormPanel({
            title: L.Fields
            ,header: false
            ,border: false
            ,autoHeight: true
            ,labelAlign: 'top'
            ,bodyStyle: 'margin:0; padding: 0 7px'
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
            ,items: [
                this.titleView
                ,{
                    xtype: 'panel'
                    ,autoHeight: true
                    ,border: false
                    ,items: []
                }
                ,this.fieldsZone
            ]
            ,listeners: {
                scope: this
                ,change: this.onChange
                ,afterrender: this.onAfterRender
            }
        });

        this.callParent(arguments);

        this.enableBubble(['saveobject']);
    }

    ,onChange: function(fieldName, newValue, oldValue){
        this._isDirty = true;

        if(!Ext.isEmpty(fieldName) && Ext.isString(fieldName)) {
            this.fireEvent('fieldchange', fieldName, newValue, oldValue);
        }
        // this.updateLayout();

        //fire event after change event process
        this.fireEvent('changed', this);
    }

    ,onAfterRender: function(c) {

        // map multiple keys to multiple actions by strings and array of codes
        var map = new Ext.KeyMap(
            c.getEl()
            ,[{
                key: "s"
                ,ctrl:true
                ,shift:false
                ,stopEvent: true
                ,scope: this
                ,fn: this.onSaveObjectEvent
            }]
        );
    }

    ,load: function(objectData) {
        if(Ext.isEmpty(objectData)) {
            return;
        }

        if(!isNaN(objectData)) {
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

        if(isNaN(objectData.id)) {

            if(Ext.isEmpty(objectData.name)) {
                objectData.name = L.New + ' ' + CB.DB.templates.getName(objectData.template_id);
            }

            this.processLoadData({
                    success: true
                    ,data: objectData
                }
            );
        } else {
            CB_Objects.load(
                {id: objectData.id}
                ,this.processLoadData
                ,this
            );
        }
    }

    ,processLoadData: function(r, e) {
        this.getEl().unmask();
        if (!r || (r.success !== true)) {
            return;
        }
        this.data = r.data;
        if(Ext.isEmpty(this.data.data)) {
            this.data.data = {};
        }

        this.titleView.update(this.data);

        this.objectsStore.proxy.extraParams = {
            id: r.data.id
            ,template_id: r.data.template_id
            ,data: r.data.data
        };

        this.startEditAfterObjectsStoreLoadIfNewObject = true;
        this.objectsStore.reload();

        /* detect template type of the opened object and create needed grid */
        var gridType = (CB.DB.templates.getType(this.data.template_id) === 'search')
            ? 'CBVerticalSearchEditGrid'
            : 'CBVerticalEditGrid';

        if(this.lastgGridType != gridType) {
            this.items.getAt(1).removeAll(true);
            this.grid = Ext.create(
                gridType
                ,{
                    title: L.Details
                    ,autoHeight: true
                    ,hidden: true
                    ,refOwner: this
                    ,includeTopFields: true
                    ,stateId: 'oevg' //object edit vertical grid
                    ,autoExpandColumn: 'value'
                    ,scrollable: false
                    ,keys: [{
                        key: "s"
                        ,ctrl:true
                        ,shift:false
                        ,scope: this
                        ,stopEvent: true
                        ,fn: this.onSaveObjectEvent
                    }]
                    ,viewConfig: {
                        forceFit: true
                        ,autoFill: true
                    }
                    ,listeners: {
                        scope: this

                        ,beforeedit: this.saveScroll
                        ,edit: this.restoreScroll

                        ,savescroll: this.saveScroll
                        ,restorescroll: this.restoreScroll
                    }
                }
            );
            this.lastgGridType = gridType;

            this.items.getAt(1).add(this.grid);
        }

        this.grid.reload();

        if(this.grid.store.getCount() > 0) {
            this.grid.show();

            if(this.grid.rendered) {
                this.grid.getView().refresh(true);
            }
        }

        if(this.grid.templateStore) {
            var fields = [];
            this.grid.templateStore.each(
                function(r) {
                    if(r.get('cfg').showIn === 'tabsheet') {
                        var cfg = {
                            border: false
                            ,title: r.get('title')
                            ,isTemplateField: true
                            ,name: r.get('name')
                            ,value: this.data.data[r.get('name')]
                            ,height: Ext.valueFrom(r.get('cfg').height, 200)
                            ,anchor: '100%'
                            // ,style: 'resize: vertical'
                            ,grow: true
                            ,fieldLabel: r.get('title')
                            ,labelAlign: 'top'
                            ,labelCls: 'fwB ttU'
                            ,listeners: {
                                scope: this
                                ,change: function(field, newValue, oldValue) {
                                    this.fireEvent('change', field.name, newValue, oldValue);
                                }
                                ,sync: function(){
                                    this.fireEvent('change');
                                }
                            }
                            ,xtype: (r.get('type') === 'html')
                                ? 'CBHtmlEditor'
                                : 'textarea'
                        };
                        this.fieldsZone.add(cfg);
                    }
                }
                ,this
            );
        }
        this._isDirty = false;

        if(!this.hasLayout && this.doLayout) {
            this.doLayout();
            // this.syncSize();
        }

        this.fireEvent('loaded', this);
    }

    ,saveScroll: function() {
        this.lastScroll = this.body.getScroll();

        return this.lastScroll;
    }

    ,restoreScroll: function() {
        this.body.setScrollLeft(this.lastScroll.left);
        this.body.setScrollTop(this.lastScroll.top);
    }

    /**
     * focus value column in first row, and start editing if it's a new object
     * @return void
     */
    ,focusDefaultCell: function() {
        if(this.grid &&
            !this.grid.editing &&
            this.grid.getEl() &&
            (this.grid.store.getCount() > 0)
        ) {
            var valueCol = this.grid.headerCt.child('[dataIndex="value"]');
            var colIdx = valueCol.getVisibleIndex();

            this.grid.getSelectionModel().select({row: 0, column: colIdx});
            this.grid.getNavigationModel().setPosition(0, colIdx);

            if(this.startEditAfterObjectsStoreLoadIfNewObject && isNaN(this.data.id)) {
                this.grid.editingPlugin.startEditByPosition({row: 0, column: colIdx});
            }

            delete this.startEditAfterObjectsStoreLoadIfNewObject;
        }

    }

    ,onObjectsStoreLoad: function(store, records, options) {
        this.onObjectsStoreChange(store, records, options);

        if(!this.grid.editing) {
            this.grid.getView().refresh();

            if(this.startEditAfterObjectsStoreLoadIfNewObject === true) {
                this.focusDefaultCell();
            }
        }
    }

    ,onObjectsStoreChange: function(store, records, options){
        Ext.each(
            records
            ,function(r){
                r.set('iconCls', getItemIcon(r.data));
            }
            ,this
        );
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
            ,icon:  Ext.Msg.QUESTION
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

    ,readValues: function() {
        this.grid.readValues();
        this.data.data = Ext.apply(this.data.data, this.fieldsZone.getForm().getFieldValues());
        return this.data;
    }

    /**
     * set value for a field
     *
     * TODO: review for duplicated fields, and for fields outside of the grid
     *
     * @param varchar fieldName
     * @param variant value
     */
    ,setFieldValue: function (fieldName, value) {
        if(this.grid) {
            this.grid.setFieldValue(fieldName, value);
        }
    }

    ,save: function(callback, scope) {
        if(!this._isDirty) {
            return;
        }

        this.readValues();

        if(callback) {
            this.saveCallback = callback.bind(scope || this);
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
        if (!r || (r.success !== true)) {
            delete this.saveCallback;
            return;
        }
        this._isDirty = false;
        if(this.saveCallback) {
            this.saveCallback(this, form, action);
            delete this.saveCallback;
        }

        App.fireEvent('objectchanged', r.data, this);
    }

    ,clear: function(){
        this.data = {};
        this.titleView.update(this.data);
        if(this.grid) {
            this.grid.hide();
        }
        this.fieldsZone.removeAll(true);
        this._isDirty = false;
        this.fireEvent('clear', this);
    }

    ,onSaveObjectEvent: function(key, ev) {
        this.fireEvent('saveobject', this, ev);
    }

    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {}
        };

        if(CB.DB.templates.getType(this.data.template_id) === 'search') {
            rez.tbar['search'] = {};
            rez.menu['save'] = {};
        } else {
            rez.tbar['save'] = {};
            rez.tbar['cancel'] = {};
            rez.tbar['openInTabsheet'] = {};
        }

        return rez;
    }

    /**
     * check if all fields are valid in current object form
     * For now only return vertical grid status
     * Later validation of separate fields should be made if needed
     * @return bool
     */
    ,isValid: function() {
        var rez = true;

        if(this.grid && this.grid.isValid) {
            rez = this.grid.isValid();
        }

        return rez;
    }

});
