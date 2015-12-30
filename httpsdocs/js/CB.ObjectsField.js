//ObjectsComboField
//ObjectsTriggerField
    //ObjectsSelectionForm
    //ObjectsSelectionPopupList

CB.ObjectsFieldCommonFunctions = {
    detectStore: function(){
        var source = Ext.isEmpty(this.cfg.source) ? 'tree': this.cfg.source;

        switch(source){
            case 'thesauri':
                this.store = this.getThesauriStore();
                break;
            case 'users':
            case 'groups':
            case 'usersgroups':
                this.store = CB.DB.usersGroupsSearchStore;
                break;
            default:
                //try to access object window to locate objects store
                this.objectsStore = this.getObjectsStore();

                this.store = new Ext.data.DirectStore({
                    autoLoad: false //true
                    ,autoDestroy: true
                    ,restful: false
                    ,remoteSort: true
                    ,model: 'FieldObjects'
                    ,pageSize: Ext.valueFrom(this.cfg.rows, 50)
                    ,proxy: {
                        type: 'direct'
                        ,paramsAsHash: true
                        ,api: { read: CB_Browser.getObjectsForField }
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
                                if (!Ext.isEmpty(this.data.fieldRecord)) {
                                    store.proxy.extraParams.fieldId = this.data.fieldRecord.get('id');
                                }

                                if (!Ext.isEmpty(this.data.objectId)) {
                                    store.proxy.extraParams.objectId = this.data.objectId;
                                }

                                if (!Ext.isEmpty(this.data.pidValue)) {
                                    store.proxy.extraParams.pidValue = this.data.pidValue;
                                }

                                if (!Ext.isEmpty(this.data.path)) {
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

        if(this.cfg.sort){
            var field = 'order'
                ,dir = 'asc';

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
    }

    ,getObjectsStore: function(){
        if(this.cfg.source == 'thesauri') {
            return this.getThesauriStore();
        }

        if(Ext.isEmpty(this.data)) {
            return;
        }

        if(this.data.ownerCt) {
            return this.data.ownerCt.objectsStore;
        }

        if(this.data.grid) {
            var a = this.data.grid.refOwner || this.data.grid.findParentByType(CB.Objects);
            if(!Ext.isEmpty(a)) {
                return a.objectsStore;
            }
        }
    }
    ,getThesauriStore: function(){
        var thesauriId = this.cfg.thesauriId;

        if(this.cfg.thesauriId == 'dependent'){
            fieldName = this.data.record.store.fields.findIndex('name', 'field_id');
            fieldName = (fieldName < 0) ? 'id': 'field_id';

            var pri = this.data.record.store.findBy(
                function(r){
                    return ( (r.get(fieldName) == this.data.record.get('pid')) && (r.get('duplicate_id') == this.data.record.get('duplicate_id')) );
                }
                ,this
            );

            if(pri > -1) {
                thesauriId = this.data.pidValue;
            }
        }

        if(!isNaN(thesauriId)) {
            return getThesauriStore(thesauriId);
        }
    }

};

Ext.define('CB.ObjectsComboField', {
    extend: 'Ext.form.ComboBox'
    ,forceSelection: true
    ,triggerAction: 'all'
    ,lazyRender: true
    ,queryMode: 'remote'
    ,editable: true
    ,displayField: 'name'
    ,valueField: 'id'
    ,minChars: 3

    ,constructor: function(config) {
        this.data = Ext.valueFrom(config.data, {});

        this.cfg = this.data.fieldRecord
            ? Ext.apply({}, Ext.valueFrom(this.data.fieldRecord.data.cfg, {}))
            : Ext.valueFrom(this.config.config, {});

        this.store = [];

        Ext.apply(this, CB.ObjectsFieldCommonFunctions);

        this.detectStore();

        //set template for item list
        switch(this.cfg.renderer) {
            case 'listObjIcons':
                this.tpl = Ext.create('Ext.XTemplate',
                    '<tpl for=".">'
                    ,'<div class="x-boundlist-item icon-padding {iconCls} bgpLT">{name}</div>'
                    ,'</tpl>'
                );

                break;

            case 'listGreenIcons':
                this.tpl = Ext.create('Ext.XTemplate',
                    '<tpl for=".">'
                    ,'<div class="x-boundlist-item icon-padding {[ values.id ? \'icon-element\': \'\' ]}">{name}</div>'
                    ,'</tpl>'
                );

                break;
        }

        this.callParent(arguments);
    }

    ,initComponent: function(){
        var mode = 'local';

        if(this.store.proxy){
            mode = 'remote';

            this.store.proxy.extraParams = Ext.clone(this.cfg);

            if(!Ext.isEmpty(this.data.objectId)) {
                this.store.proxy.extraParams.objectId = this.data.objectId;
            }

            if(!Ext.isEmpty(this.data.pidValue)) {
                this.store.proxy.extraParams.pidValue = this.data.pidValue;
            }

            if(!Ext.isEmpty(this.data.path)) {
                this.store.proxy.extraParams.path = this.data.path;
            }

            this.store.on('beforeload', this.onBeforeLoadStore, this);
            this.store.on('load', this.onStoreLoad, this);
            this.store.load();
        }
        var customIcon = (this.cfg.renderer == 'listGreenIcons') ? 'icon-element' : '';
        var plugins = [];

        Ext.apply(this, {
            mode: mode
            ,store: this.store
            ,iconClsField: 'iconCls'
            ,customIcon: customIcon

            ,listeners: {
                scope: this
                ,beforeselect: function(combo, record, index){
                    if(Ext.isEmpty(this.objectsStore)) {
                        return;
                    }
                    this.objectsStore.checkRecordExistance(record.data);
                }
                // ,blur: function(field){
                //     this.setValue(this.value);
                // }
                ,beforedestroy: function(){
                    this.store.un('beforeload', this.onBeforeLoadStore, this);
                    this.store.un('load', this.onStoreLoad, this);
                }
                ,expand: function(c){
                    // var idx = c.store.findExact('id', c.getValue()) -1;
                    // c.select(idx, true);
                }
            }
        });

        this.callParent(arguments);
    }

    ,onBeforeLoadStore: function(st, options){
        options.params = Ext.apply({}, this.cfg, options.params);
    }

    ,setValue: function(v){
        var value, values = Ext.Array.from(v);

        if(v == this.nullRecord) {
            this.callParent([null]);
            return;
        }

        v = [];
        for (var i = 0; i < values.length; i++) {
            if(!Ext.isEmpty(values[i])) {
                value = (values[i] && values[i].isModel)
                    ? values[i].get(this.valueField)
                    : values[i];
                v.push(value);
            }
        }

        this.callParent([v]);
        var text = this.store.getTexts(v);

        //delete this.customIcon;
        if(Ext.isEmpty(text) && this.objectsStore){
            var r = this.objectsStore.findRecord('id', v, 0, false, false, true);

            if(r){
                if(this.icon) {
                    this.icon.className = 'ux-icon-combo-icon ' + r.get('iconCls');
                }
                text = this.objectsStore.getTexts(v);
            }
        }

        this.setRawValue(text);
    }

    ,onStoreLoad: function(store, recs, options) {
        Ext.each(
            recs
            ,function(r){
                r.set('iconCls', getItemIcon(r.data));
            }
            ,this
        );

        this.nullRecord = Ext.create(
            store.getModel().getName()
            ,{
                id: 0
                ,name: ''
            }
        );

        store.insert(
            0
            ,this.nullRecord
        );

        if(Ext.isEmpty(this.lastQuery) && !Ext.isEmpty(this.value)) {
            this.setValue(this.value);
        }
    }

    ,updateStore: function(){
        var oldStore = this.store;
        this.detectStore();
        this.bindStore(this.store);
        if(oldStore && oldStore.autoDestroy) {
            oldStore.destroy();
        }
    }
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Ext.define('CB.ObjectsTriggerField', {
    extend: 'Ext.Panel'
    ,bodyStyle: 'border: 1px solid #b5b8c8'
    ,cls: 'x-form-field'
    ,isFormField: true
    ,delimiter: '<br />'

    ,initComponent: function(){
        if(Ext.isEmpty(this.config)) {
            this.config = {};
        }

        this.cfg = this.data.fieldRecord
            ? Ext.apply({}, Ext.valueFrom(this.data.fieldRecord.data.cfg, {}))
            : Ext.valueFrom(this.config.config, {});

        this.triggerIconCls = 'icon-element';
        var tpl = '<tpl for=".">{[ (xindex == 0) ? "" : "'+this.delimiter+'"]}{name}</tpl>';

        switch(this.cfg.renderer){
            case 'listGreenIcons':
                    tpl = '<ul><tpl for="."><li class="icon-padding16 icon-element">{name}</li></tpl></ul>';
                    this.triggerIconCls = 'icon-element';
                    break;
            case 'listObjIcons':
                    tpl = '<ul><tpl for="."><li class="icon-padding16 {iconCls}">{name}</li></tpl></ul>';
                    this.triggerIconCls = 'icon-arrow-split-090';
                    break;
        }

        Ext.apply(this, CB.ObjectsFieldCommonFunctions);

        this.trigger = new Ext.Button({
            iconCls: this.triggerIconCls
            ,cls:'fr '
            ,style: 'margin:-1px -2px '
            ,scope: this
            ,handler: this.onTriggerClick
        });


        this.dataView = new Ext.DataView({
            emptyText: L.empty
            ,overItemCls: 'field-over'
            ,itemSelector: 'li'
            ,style: 'margin: 3px; white-space: normal'
            ,tpl: tpl
            ,data: []
        });

        Ext.apply(this, {
            items: [this.trigger, this.dataView]
            ,listeners:{
                scope: this
                ,afterrender: this.afterrender
            }
        });

        this.callParent(arguments);
    }
    ,afterrender: function(){
        this.setValue(this.value);
    }
    ,setValue: function(v){
        this.value = [];

        var store = this.getObjectsStore()
            ,data = []
            ,i;

        if(!Ext.isEmpty(v)){
            if(!Ext.isArray(v)) {
                v = String(v).split(',');
            }

            for(i = 0; i < v.length; i++) {
                this.value.push(v[i]);
            }
        }

        //check if store is set cause it could not be determined due to field configuration errors
        if(store) {
            for (i = 0; i < this.value.length; i++) {
                var r = store.findRecord('id', this.value[i], 0, false, false, true);
                if(r){
                    data.push(r.data);
                }
            }
        }

        if(this.dataView.rendered) {
            this.dataView.update(data);
        } else {
            this.dataView.data = data;
        }
    }

    ,getValue: function(){
        return this.value.join(',');
    }

    ,onTriggerClick: function(e){
        if(this.cfg.source == 'thesauri'){
            this.form = new CB.ObjectsSelectionPopupList({
                data: this.data
                ,value: this.getValue()
                ,listeners:{
                    scope: this
                    ,setvalue : this.onSetValue
                }
            });
        } else {
            this.form = new CB.ObjectsSelectionForm({
                data: this.data
                ,value: this.getValue()
                ,listeners:{
                    scope: this
                    ,setvalue : this.onSetValue
                }
            });
        }
        this.form.show();
    }

    ,onSetValue: function(data){
        if(!Ext.isString(data)){
            var selectedValue = [];
            Ext.each( data, function(i){
                selectedValue.push(i.id);
            }, this );
            data = selectedValue.join(',');

        }

        var oldValue = this.getValue();
        if(data == oldValue) {
            return;
        }
        this.setValue(data);
        this.fireEvent('change', data, oldValue);
    }
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Ext.define('CB.ObjectsSelectionForm', {
    extend: 'Ext.Window'
    ,height: 400
    ,width: 500
    ,modal: true
    ,layout: 'border'
    ,title: L.Associate

    ,constructor: function(config) {
        this.data = config.data;
        this.cfg = this.data.fieldRecord
            ? Ext.apply({}, Ext.valueFrom(this.data.fieldRecord.data.cfg, {}))
            : Ext.applyIf(Ext.valueFrom(config.config, {}), { multiValued: false });

        this.callParent(arguments);
    }

    ,initComponent: function(){

        Ext.apply(this, CB.ObjectsFieldCommonFunctions);
        this.detectStore();

        //set title
        if(this.data.fieldRecord) {
            this.title = Ext.valueFrom(
                this.data.fieldRecord.get('title')
                ,this.title
            );
        }

        var sm = new Ext.selection.CheckboxModel({
            injectCheckbox: 'first'
            ,checkOnly: true
            ,toggleOnClick: true
            ,mode: (this.cfg.multiValued ? 'SIMPLE': 'SINGLE')
            ,listeners: {
                scope: this
                ,select: this.onRowSelect
                ,deselect: this.onRowDeselect
            }
        });

        var columns = [
            {   dataIndex: 'name'
                ,header: L.Name
                ,width: 300
                ,scope: this

                ,renderer: function(v, m, r, ri, ci, s){
                    var selected = !Ext.isEmpty(this.resultPanel.config.store.findRecord('id', r.get('id'), 0, false, false, true));
                    switch(this.cfg.renderer){
                        case 'listGreenIcons':
                            m.css = 'icon-grid-column ' + ((!selected) ? 'icon-element-off' : 'icon-element');
                            break;
                        case 'listObjIcons':
                            m.css = 'icon-grid-column ' + r.get('iconCls');
                            break;
                    }

                    return v;
                }
            }
        ];

        if(!Ext.isEmpty(this.cfg.fields)){
            if(!Ext.isArray(this.cfg.fields)) {
                this.cfg.fields = this.cfg.fields.split(',');
            }
            for (var i = 0; i < this.cfg.fields.length; i++) {
                var fieldName = this.cfg.fields[i].trim();
                switch(fieldName){
                    case 'name': break;
                    case 'date':
                        columns.push( {
                            header: L.Date
                            ,width: 120
                            ,dataIndex: 'date'
                            ,format: App.dateFormat + ' ' + App.timeFormat
                            ,renderer: App.customRenderers.datetime
                        });
                        this.width += 120;
                        break;
                    case 'path':
                        columns.push({
                            header: L.Path
                            ,width: 150
                            ,dataIndex: 'path'
                            ,renderer: App.customRenderers.titleAttribute
                        });
                        this.width += 150;
                        break;
                    case 'project':
                        columns.push({
                            header: L.Project
                            ,width: 150
                            ,dataIndex: 'case'
                            ,renderer: App.customRenderers.titleAttribute
                        });
                        break;
                    case 'size':
                        columns.push({ header: L.Size, width: 80, dataIndex: 'size', renderer: App.customRenderers.filesize});
                        this.width += 80;
                        break;
                    case 'cid':
                        columns.push({ header: L.Creator, width: 200, dataIndex: 'cid', renderer: App.customRenderers.userName});
                        this.width += 200;
                        break;
                    case 'oid':
                        columns.push({ header: L.Owner, width: 200, dataIndex: 'oid', renderer: App.customRenderers.userName});
                        this.width += 200;
                        break;
                    case 'cdate':
                        columns.push({ header: L.CreatedDate, width: 120, dataIndex: 'cdate', xtype: 'datecolumn', format: App.dateFormat+' '+App.timeFormat});
                        this.width += 120;
                        break;
                    case 'udate':
                        columns.push({ header: L.UpdatedDate, width: 120, dataIndex: 'udate', xtype: 'datecolumn', format: App.dateFormat+' '+App.timeFormat});
                        this.width += 120;
                        break;
                }
            }
        }
        this.width = Math.min(this.width, 1024);

        if(this.cfg.showDate === true) {
            columns.push({dataIndex: 'date', width: 50, renderer: App.customRenderers.datetime});
        }

        this.grid = new Ext.grid.GridPanel({
            region: 'center'
            ,border: false
            ,store: this.store
            ,scrollable: true
            ,columns: columns
            // ,colModel: new Ext.grid.ColumnModel({
            //     defaults: { sortable: true }
            // })
            ,viewConfig: {
                markDirty: false
                ,stripeRows: false
            }
            ,selModel: sm
            ,listeners: {
                scope: this
                ,rowclick: this.onRowClick
                ,rowdblclick: this.onRowDblClick
            }
            ,bbar: new Ext.PagingToolbar({
                store: this.store       // grid and PagingToolbar using same store
                ,displayInfo: true
                ,hidden: true
            })
        });

        this.resultPanel = new Ext.DataView({
            region: 'south'
            ,border: false
            ,cls: 'bgcW btg p10'
            ,autoHeight: true
            ,hidden: !this.cfg.multiValued
            ,tpl: new Ext.XTemplate(
                '<span class="fwB">'+L.Value+':</span><ul class="clean"><tpl for=".">'
                ,'<li class="lh20 icon-padding16 '+ ((this.cfg.renderer == 'listGreenIcons') ? 'icon-element' : '{iconCls}') + '"> &nbsp; {name} <span style="display: inline-block; width: 14px"><span class="buttons"><a href="#" class="icon-close-light" style="display:inline-block; width: 20px;text-decoration: none" title="'+L.Remove+'">&nbsp; &nbsp;</a></span></span></li>'
                ,'</tpl></ul>'
                ,{compiled: true}
            )
            ,store: new Ext.data.JsonStore({
                model: 'Generic'
                ,proxy: {
                    type: 'memory'
                    ,reader: {
                        type: 'json'
                    }
                }
            })
            ,itemSelector: 'li'
            ,overItemCls:'item-over'
            ,listeners: {
                scope: this
                ,itemclick: this.onRemoveItemClick
            }
        });

        Ext.apply(this, {
            defaults: {border: false}
            ,border: false
            ,buttonAlign: 'left'
            ,items:[
                { xtype: 'panel'
                    ,region: 'center'
                    ,layout: 'border'
                    ,items: [
                        {
                            xtype: 'panel'
                            ,region: 'north'
                            ,height: 22
                            ,layout: 'hbox'
                            ,border: false
                            ,items: [
                                {
                                    xtype: 'textfield'
                                    ,anchor: '100%'
                                    ,flex: 1
                                    ,emptyText: L.Search
                                    ,triggerClass: 'x-form-search-trigger'
                                    ,enableKeyEvents: true
                                    ,scope: this
                                    ,onTriggerClick: function(){
                                        this.scope.onGridReloadTask();
                                    }
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
                        ,this.grid
                        ,this.resultPanel
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

                ,facetchange: function(o, ev){
                    ev.stopPropagation();
                    this.onGridReloadTask();
                }

                ,beforedestroy: function(){
                    if(this.qt) this.qt.destroy();
                }
            }
            ,buttons:[
                '->'
                ,{text: Ext.MessageBox.buttonText.ok, iconCls: 'icon-tick', scope: this, handler: this.onOkClick}
                ,{text: L.Cancel, iconCls: 'icon-cancel', scope: this, handler: this.destroy}]
        });

        this.callParent(arguments);

        this.store.on('load', this.onLoad, this);

        this.triggerField = this.query('textfield')[0];
    }

    ,onGridReloadTask: function(){
        if(!this.gridReloadTask) {
            this.gridReloadTask = new Ext.util.DelayedTask(this.processGridReload, this);
        }
        this.gridReloadTask.delay(500);
    }

    ,processGridReload: function(){
        this.store.proxy.extraParams = this.getSearchParams();
        this.store.reload(this.store.extraParams);
    }

    ,onBeforeLoad: function(store, records, options){
        // options = this.getSearchParams();
        // store.extraParams = options
    }

    ,getSearchParams: function(){
        var result = Ext.clone(this.cfg);

        result.query = this.triggerField.getValue();

        if(!Ext.isEmpty(this.data.objectId)) {
            result.objectId = this.data.objectId;
        }

        if(!Ext.isEmpty(this.data.path)) {
            result.path = this.data.path;
        }

        return result;
    }

    ,onLoad: function(store, records, options){
        var el = this.getEl();

        if(Ext.isEmpty(records)) {
            this.grid.getEl().mask(L.noData);
        } else {
            el = this.grid.getEl();
            if(el) {
                this.grid.getEl().unmask();
            }

            var currentValue = this.getValue();
            var selectedRecords = [];
            this.selectValueOnLoad = true;
            currentValue = currentValue.split(',');
            Ext.each(
                records
                ,function(r){
                    r.set('iconCls', getItemIcon(r.data));
                    if(currentValue.indexOf(r.get('id')+'') >= 0) {
                        selectedRecords.push(r);
                    }
                }
                ,this
            );
            if(!Ext.isEmpty(selectedRecords)) {
                this.grid.getSelectionModel().select(selectedRecords);
            }
            this.selectValueOnLoad = false;
        }
        // this.triggerField.setValue(options.params.query);
        // this.grid.getBottomToolbar().setVisible(store.reader.jsonData.total > store.reader.jsonData.data.length);
    }

    ,onSelectionChange: function(sm, selection){
        //this.buttons[0].setDisabled(!sm.hasSelection());
    }

    ,onRowClick: function(g, record, tr, ri, e, eOpts){ //g, ri, e
        var el = Ext.get(e.getTarget());
        if(!el || !el.hasCls('open-object')) {
            return;
        }

        var r = g.getStore().getAt(ri);

        if(!this.qt) {
            this.qt = new Ext.QuickTip({
                autoHeight: true
                ,autoWidth: true
                ,autoHide: true
                ,dismissDelay: 0
                ,closable: true
                ,draggable: true
                ,target: this
                ,cls: 'fs11'
                ,iconCls: r.get('iconCls')
                ,headerCfg:{
                    cls: 'icon-padding'
                    ,style:'height:20px'
                }
                ,title: r.get('name')
                ,html: '<span class="icon-padding icon-loading">' + L.LoadingData + '</span>'
            });

        } else {
            this.qt.hide();
            this.qt.setTitle(r.get('name'), r.get('iconCls'));
            if(this.qt.contact_id != r.get('id')) {
                this.qt.update('<span class="icon-padding icon-loading">'+L.LoadingData+'</span>');
            }
        }

        this.qt.showAt(e.getXY());
    }

    ,onRowDblClick: function(g, record, tr, ri, e, eOpts){ //g, ri, e

        var sm = this.grid.getSelectionModel();
        if(sm.isSelected(record)) {
            sm.deselect(record);
        } else {
            sm.select(ri, this.cfg.multiValued);
        }
    }
    ,onRowSelect: function (sm, record, index, eOpts) { //sm, ri, r
        if(!this.selectValueOnLoad) {
            this.resultPanel.config.store.loadData([record.data], true);
        }
    }
    ,onRowDeselect: function (sm, record, index, eOpts) {//sm, ri, r
        var r = this.resultPanel.config.store.findRecord('id', record.get('id'), 0, false, false, true);

        if(r) {
            this.resultPanel.config.store.remove(r);
        }
    }

    ,onRemoveItemClick: function(cmp, record, item, index, e, eOpts){//b, idx, oel, e
        var el = Ext.get(e.getTarget());
        if(!el.dom.classList.contains('icon-close-light')) {
            return;
        }
        var r = this.resultPanel.config.store.getAt(index)
            ,gridRecord = this.grid.store.findRecord('id', r.get('id'), 0, false, false, true);

        this.resultPanel.config.store.removeAt(index);
        if(gridRecord) {
            this.grid.getSelectionModel().deselect([gridRecord]);
        }
    }

    ,getValue: function(){
        var rez = [];
        if(this.resultPanel && this.resultPanel.config.store) {
            this.resultPanel.config.store.each(
                function(r){
                    rez.push(r.data.id);
                }
                ,this
            );
        }
        return rez.join(',');
    }

    ,setData: function(data){
        if(!this.cfg.multiValued) {
            return;
        }
        if(Ext.isEmpty(data)) {
            data = [];
        }
        if(this.resultPanel) {
            this.resultPanel.config.store.removeAll();
            Ext.each(data, function(d){
                d.id = parseInt(d.id, 10);
                var u = Ext.create(
                    this.resultPanel.config.store.getModel().getName()
                    ,d
                );
                this.resultPanel.config.store.add(u);
            }, this);
        }

        // if(this.rendered) {
        //     this.items.last().syncSize();
        // }
    }
    ,getData: function(){
        var rez = [];
        this.resultPanel.config.store.each(
            function(r){
                rez.push(r.data);
            }
            ,this
        );

        return rez;
    }
    ,onOkClick: function(){
        if(!this.cfg.multiValued){
            this.resultPanel.config.store.removeAll();
            var s = this.grid.getSelectionModel().getSelection();

            if(s && (s.length > 0)){
                var r = s[0]
                    ,u = Ext.create(
                        this.resultPanel.config.store.getModel().getName()
                        ,r.data
                    );

                this.resultPanel.config.store.add(u);
            }
        }

        var newValue = this.getData();
        var objStore = this.getObjectsStore();
        if(objStore) {
            Ext.each(
                newValue
                ,function(d){
                    objStore.checkRecordExistance(d);
                }
                ,this
            );
        }
        this.fireEvent('setvalue', newValue, this);
        this.close();
    }
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Ext.define('CB.ObjectsSelectionPopupList', {
    extend: 'Ext.Window'
    ,bodyBorder: false
    ,closable: true
    ,closeAction: 'destroy'
    ,hideCollapseTool: true
    ,layout: 'fit'
    ,maximizable: false
    ,minimizable: false
    ,modal: true
    ,plain: true
    ,stateful: true
    ,value: []
    ,title: L.ChooseValues
    ,store: new Ext.data.ArrayStore({
        autoDestory: true
        ,idIndex: 0
        ,model: 'Generic2'
        ,data: []
    })
    ,minWidth: 350
    ,minHeight: 250
    ,height: 350

    ,initComponent: function(){
        if(Ext.isEmpty(this.config.config)) {
            this.config.config = {};
        }

        this.data = this.config.data;

        this.cfg = this.data.fieldRecord
            ? Ext.apply({}, Ext.valueFrom(this.data.fieldRecord.data.cfg, {}))
            : this.config.config;

        Ext.apply(this, CB.ObjectsFieldCommonFunctions);
        this.detectStore();
        this.cm = [{
                header:' '
                ,dataIndex: 'id'
                ,width: 15
                ,fixed: true
                ,resizable: false
                ,scope: this
                ,renderer: function(value, metaData, record, rowIndex, colIndex, store){
                    if(record.get('header_row') == 1) return;
                    metaData.css = (this.value.indexOf(value+'') >= 0)
                        ? 'icon-element'
                        : 'icon-element-off';
                }
            },{
                header: L.Value
                ,dataIndex: 'name'
                ,width: 270
                ,renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    metaData.css = 'wsn '+ (record.get('icon') ? record.get('icon') + ' icon-padding' : '');
                    return value;
                }
            }
        ];
        if(!Ext.isEmpty(this.cfg.showDate))
            this.cm.push({
                header: L.Date
                ,width: 60
                ,dataIndex: this.cfg.showDate
                ,format: App.dateFormat
                ,renderer: App.customRenderers.date
            });

        this.trigger = new Ext.form.field.Text({
                triggerClass: 'x-form-search-trigger'
                ,border: false
                ,emptyText: L.Filter
                ,enableKeyEvents: true
                ,onTriggerClick: function(e){
                    this.doFilter(e);
                }.bind(this)
                ,tabIndex: 1
                ,listeners: {
                    scope: this
                    ,keyup: function(f,e){
                        this.doFilter(e);
                    }
                    ,specialkey: function(f,e){
                        switch(e.getKey()){
                            case e.DOWN:
                            case e.TAB: this.focusGrid(); break;
                            case e.ENTER:  f.onTriggerClick();
                        }
                    }
                }
            });

        this.grid = new Ext.grid.GridPanel({
            border: false
            ,style: 'background-color: white'
            ,stripeRows: true
            ,store: this.store
            ,minColumnWidth: 5
            ,columns: this.cm
            ,tbar: [this.trigger]
            ,viewConfig: {
                forceFit: true
                ,enableRowBody: true
                ,getRowClass: function(r, rowIndex, rp, ds){
                    rp.body = (r.get('header_row') == 1) ? r.get('name') : '';
                    return (rp.body ? 'x-grid3-row-with-body' : '');
                }
            }
            ,hideHeaders: true
            ,selModel: new Ext.selection.RowModel({singleSelect: true})
            ,tabIndex: 2
            ,listeners:{
                scope: this
                ,rowclick: this.toggleElementSelection
                ,keypress: function(e){
                    if( (e.getKey() == e.SPACE) && (!e.hasModifier())){
                        e.stopPropagation();
                        this.toggleElementSelection();
                    }
                }
            }
         });

        Ext.apply(this, {
            buttonAlign: 'left'
            ,items: this.grid
            ,keys:[{
                    key: "\r\n"
                    ,fn: this.doSubmit
                    ,scope: this
                },{
                    key: Ext.event.Event.ESC
                    ,fn: this.doClose
                    ,scope: this
                }
            ]
            ,buttons: [
                {text: L.ClearSelection, handler: this.doClearSelection, scope: this, tabIndex: 6}
                ,'->'
                ,{text: Ext.MessageBox.buttonText.ok, handler: this.doSubmit, scope: this, tabIndex: 3}
                ,{text: L.Cancel, handler: this.doClose, scope: this, tabIndex: 4}
            ]
        });

        this.callParent(arguments);

        this.on('beforeshow', this.onBeforeShowEvent, this);
        this.on('resize', function(win, w, h){this.trigger.setWidth(w - 17);});
    }

    ,focusGrid: function(){
        this.grid.focus();
        if(this.grid.getStore().getCount() > 0){
            var r = this.grid.getSelectionModel().getSelected();
            if(!r) {
                r = this.grid.getStore().getAt(0);
            }
            this.grid.getSelectionModel().select([r]);
            this.grid.getView().focusRow(this.grid.getStore().indexOf(r));
        }
    }

    ,toggleElementSelection: function(g, ri, e){
        var r = this.grid.getSelectionModel().getSelected();

        if(!r || (r.get('header_row') == 1)) {
            return;
        }

        var id = r.get('id') + '';
        if(this.value.indexOf(id) < 0) {
            this.value.push(id);
        } else {
            this.value.remove(id);
        }

        this.grid.getView().refresh(false);
        this.grid.getView().focusRow(this.grid.getStore().indexOf(r));
    }

    ,onBeforeShowEvent: function(){
        this.trigger.setValue('');
        this.trigger.focus(true, 350);
        if(!Ext.isArray(this.value)) this.value = Ext.isEmpty(this.value) ? [] : String(this.value).split(',');
        this.doFilter();
        this.setTitle(this.title);
        if(this.iconCls)  this.setIconCls(this.iconCls);
        this.width = 350 + (this.grid.getColumnModel().getColumnCount() - 2) * 100;
        this.setWidth(this.width);
    }

    ,doFilter: function(e){
        var criterias = [
            {
                fn: function(rec){
                    return !Ext.isEmpty(rec.get('id'));
                }
                ,scope: this
            }
        ]
        ,v = this.trigger.getValue();

        if(!Ext.isEmpty(v)) {
            criterias.push({ property: 'name', value: v, anyMatch: true, caseSensitive: false });
        }

        if(Ext.isEmpty(criterias)) {
            this.grid.store.clearFilter();
        } else {
            this.grid.store.filter(criterias);
        }
    }

    ,doClearSelection: function(){
        this.value = [];
        this.grid.getView().refresh(false);
    }

    ,doSubmit: function(){
        this.grid.store.clearFilter();
        var newValue = this.value.join(',');
        this.fireEvent('setvalue', newValue, this);
        this.close();
    }
});
