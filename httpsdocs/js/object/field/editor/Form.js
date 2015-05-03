Ext.namespace('CB.object.field.editor');

Ext.define('CB.object.field.editor.Form', {
    extend: 'Ext.Window'

    ,xtype: 'CBObjectFieldEditorForm'

    ,height: 500
    ,width: 600
    ,minHeight: 400
    ,minWidth: 600
    ,modal: true
    ,layout: 'border'
    ,title: L.Associate
    ,closeAction: 'destroy'
    ,selectedRecordsData: []

    ,constructor: function(config) {
        this.data = config.data;

        //make shortcut, because grid view tries to acces folderProperties of refOwner
        //shold be refactored
        this.folderProperties = this.data;

        this.cfg = this.data.fieldRecord
            ? Ext.apply({}, Ext.valueFrom(this.data.fieldRecord.data.cfg, {}))
            : Ext.applyIf(
                Ext.valueFrom(config.config, {})
                ,{multiValued: false}
            );

        this.callParent(arguments);

        this.setValue(config.value);
    }

    ,initComponent: function(){
        this.detectStore();

        this.actions = {
            showSelection: new Ext.Action({
                text: L.ShowSelection
                ,enableToggle: true
                ,disabled: true
                ,scope: this
                ,handler: this.onShowSelectionClick
            })
        };

        //set title from fieldRecord if set
        if(this.data.fieldRecord) {
            this.title = Ext.valueFrom(
                this.data.fieldRecord.get('title')
                ,this.title
            );
        }

        this.gridView = new CB.browser.view.Grid({
            border: false
            ,region: 'center'
            ,refOwner: this
            ,store: this.store
            ,getProperty: this.getProperty.bind(this)
            ,saveGridState: Ext.emptyFn

            ,selModel: {
                selType: 'checkboxmodel'
                ,injectCheckbox: 'first'
                ,checkOnly: true
                ,toggleOnClick: true
                ,mode: (this.cfg.multiValued ? 'SIMPLE': 'SINGLE')
                ,listeners: {
                    scope: this
                    ,select: this.onRowSelect
                    ,deselect: this.onRowDeselect
                }
            }
        });

        Ext.apply(this, {
            defaults: {
                border: false
            }
            ,border: false
            ,buttonAlign: 'left'
            ,layout: 'fit'
            ,items:[
                {
                    xtype: 'panel'
                    ,region: 'center'
                    ,layout: 'border'
                    ,cls: 'x-panel-white'
                    ,items: [
                        {
                            xtype: 'panel'
                            ,region: 'north'
                            ,autoHeight: true
                            ,layout: 'hbox'
                            ,border: false
                            ,items: [
                                {
                                    xtype: 'textfield'
                                    ,anchor: '100%'
                                    ,flex: 1
                                    ,emptyText: L.Search
                                    ,triggers: {
                                        search: {
                                            cls: 'x-form-search-trigger'
                                            ,scope: this
                                            ,handler: this.onGridReloadTask
                                        }
                                    }
                                    ,enableKeyEvents: true

                                    ,listeners: {
                                        scope: this
                                        ,specialkey: function(ed, ev){
                                            if(ev.getKey() == ev.ENTER) {
                                                this.onGridReloadTask();
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                        ,this.gridView
                    ]
                }
            ]
            ,listeners: {
                scope: this
                ,show: function(){
                    this.store.removeAll();
                    if((!Ext.isDefined(this.cfg.autoLoad)) || (this.cfg.autoLoad === true)) {
                        this.onGridReloadTask();
                    }
                    this.triggerField.focus(false, 400);
                }
                ,change: this.onChange
                // ,beforedestroy: function(){
                //     if(this.qt) {
                //         this.qt.destroy();
                //     }
                // }
            }
            ,buttons:[
                ,this.actions.showSelection
                ,'->'
                ,{
                    text: Ext.MessageBox.buttonText.ok
                    ,scope: this
                    ,handler: this.onOkClick
                },{
                    text: Ext.MessageBox.buttonText.cancel
                    ,scope: this
                    ,handler: this.destroy
                }
            ]
        });
        this.callParent(arguments);

        this.gridView.addCls('view-loading');

        this.store.on('load', this.onLoad, this);

        this.triggerField = this.query('textfield')[0];
    }

    /**
     * detect store used, based on configuration
     * @return store
     */
    ,detectStore: function(){
        var source = Ext.valueFrom(this.cfg.source, 'tree');

        switch(source){
            case 'users':
            case 'groups':
            case 'usersgroups':
                this.store = CB.DB.usersGroupsSearchStore;
                break;
            default:
                this.store = new Ext.data.DirectStore({
                    autoLoad: false //true
                    ,autoDestroy: true
                    ,restful: false
                    ,remoteSort: true
                    ,model: 'FieldObjects'
                    ,proxy: {
                        type: 'direct'
                        ,paramsAsHash: true
                        ,api: {
                            read: CB_Browser.getObjectsForField
                        }
                        ,reader: {
                            type: 'json'
                            ,successProperty: 'success'
                            ,rootProperty: 'data'
                            ,messageProperty: 'msg'
                        }
                        ,listeners:{
                            load: function(proxy, obj, opt){
                                for (var i = 0; i < obj.result.data.length; i++) {
                                    obj.result.data[i].date = date_ISO_to_local_date(obj.result.data[i].date);
                                }
                            }
                        }
                    }

                    ,sortInfo: {
                        field: 'name'
                        ,direction: 'ASC'
                    }

                    ,listeners: {
                        scope: this
                        ,beforeload: function(store, o ){
                            if(this.data){
                                if(!Ext.isEmpty(this.data.fieldRecord)) {
                                    store.proxy.extraParams.fieldId = this.data.fieldRecord.get('id');
                                }
                                if(!Ext.isEmpty(this.data.objectId)) {
                                    store.proxy.extraParams.objectId = this.data.objectId;
                                }
                                if(!Ext.isEmpty(this.data.pidValue)) {
                                    store.proxy.extraParams.pidValue = this.data.pidValue;
                                }
                                if(!Ext.isEmpty(this.data.path)) {
                                    store.proxy.extraParams.path = this.data.path;
                                }
                                store.proxy.extraParams.objFields = this.data.objFields;
                            }
                        }
                        ,load:  function(store, recs, options) {
                            Ext.each(
                                recs
                                ,function(r){
                                    r.set('iconCls', getItemIcon(r.data));
                                }
                                ,this
                            );
                        }
                    }
                });
        }

        //set an empty store if none detected
        if(Ext.isEmpty(this.store)) {
            this.store = new Ext.data.ArrayStore({
                idIndex: 0
                ,model: 'Generic'
                ,data:  []
            });
        }
        if(Ext.isEmpty(this.store.getTexts)) {
            this.store.getTexts = getStoreNames;
        }

        //set default sorting
        if(this.cfg.sort){
            field = 'order';
            dir = 'asc';
            switch(this.cfg.sort){
                case 'asc':
                    field = 'name';
                    break;
                case 'desc':
                    field = 'name';
                    dir = 'desc';
                    break;
            }
            this.store.sort(field, dir);
        }

        return this.store;
    }

    /**
     * get a property from current class
     * used by grid when needed
     * @param  varchar propertyName
     * @return variant
     */
    ,getProperty: function(propertyName){
        if(propertyName == 'nid') {
            propertyName = 'id';
        }
        if(this.data && this.data[propertyName]) {
            return this.data[propertyName];
        }

        return null;
    }

    ,setValue: function(value) {
        this.value = toNumericArray(value);
        this.fireEvent('change', this, this.value);
    }

    ,getValue: function() {
        return this.value;
    }

    /**
     * handler for selectiong a new row
     * update value with id of new row
     * @param  selModel
     * @param  record
     * @param  index
     * @param  eOpts
     * @return void
     */
    ,onRowSelect: function(selModel, record, index, eOpts) {
        var id = record.get('id');

        if(this.cfg.multiValued !== true) {
            this.value = [];
            this.selectedRecordsData = [];
        }

        if(this.value.indexOf(id) < 0) {
            this.value.push(id);
            this.fireEvent('change', this, this.value);
            //keep selected records data for later usage (add to objectsStore of object edit window)
            this.selectedRecordsData[id] = Ext.apply({}, record.data);
        }
    }

    /**
     * handler for deselectiong a row
     * update value by removing id of new row
     * @param  selModel
     * @param  record
     * @param  index
     * @param  eOpts
     * @return void
     */
    ,onRowDeselect: function(selModel, record, index, eOpts) {
        var id = record.get('id')
            ,idx = this.value.indexOf(id);

        if(idx > -1) {
            this.value.splice(idx, 1);
            this.fireEvent('change', this, this.value);
        }
    }

    ,onChange: function(ed, value) {
        this.actions.showSelection.setDisabled(Ext.isEmpty(value));
    }

    ,onGridReloadTask: function(){
        if(!this.gridReloadTask) {
            this.gridReloadTask = new Ext.util.DelayedTask(this.doReloadGrid, this);
        }
        this.gridReloadTask.delay(500);
    }

    ,doReloadGrid: function(params){
        if(Ext.isEmpty(params)) {
            params = this.getSearchParams();
        }

        params.from = 'formEditor';
        this.store.proxy.extraParams = params;
        this.store.reload(params);
    }

    ,getSearchParams: function(){
        result = Ext.apply({}, this.cfg);
        result.query = this.triggerField.getValue();
        result.value = this.getValue();

        if(!Ext.isEmpty(this.data.objectId)) {
            result.objectId = this.data.objectId;
        }
        if(!Ext.isEmpty(this.data.path)) {
            result.path = this.data.path;
        }

        return result;
    }

    /**
     * handler for load store event
     * used to :
     *     - mask/unmask gridview on no data
     *     - set icons for new records
     *     - select rows according to current value
     * @param  object store
     * @param  array records
     * @param  object options
     * @return void
     */
    ,onLoad: function(store, records, options){
        if(Ext.isEmpty(records)) {
            this.gridView.getEl().mask(L.noData);
        } else {
            var el = this.gridView.getEl();
            if(el) {
                this.gridView.getEl().unmask();
            }

            var selectedRecords = [];
            Ext.each(
                records
                ,function(r){
                    //set icon
                    r.set('iconCls', getItemIcon(r.data));

                    //collect records that should be selected
                    if(this.value.indexOf(r.get('id')) >= 0) {
                        selectedRecords.push(r);
                    }
                }
                ,this
            );

            //select collected records
            if(!Ext.isEmpty(selectedRecords)) {
                this.gridView.grid.getSelectionModel().select(selectedRecords);
            }
        }
        // this.triggerField.setValue(options.params.query);

        this.gridView.removeCls('view-loading');
    }

    ,onShowSelectionClick: function(b, e) {
        var params = this.getSearchParams();

        if(b.pressed) {
            delete params.query;
            params.ids = this.value;
        }

        this.doReloadGrid(params);
    }

    /**
     * set value and close form
     * @return {[type]} [description]
     */
    ,onOkClick: function(){
        this.fireEvent('setvalue', this.value, this);
        this.close();
    }
});
