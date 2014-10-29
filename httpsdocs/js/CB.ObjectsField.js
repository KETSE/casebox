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
                                if(!Ext.isEmpty(this.data.fieldRecord)) store.proxy.extraParams.fieldId = this.data.fieldRecord.get('id');
                                if(!Ext.isEmpty(this.data.objectId)) store.proxy.extraParams.objectId = this.data.objectId;
                                if(!Ext.isEmpty(this.data.pidValue)) store.proxy.extraParams.pidValue = this.data.pidValue;
                                if(!Ext.isEmpty(this.data.path)) store.proxy.extraParams.path = this.data.path;
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
    }

    ,getObjectsStore: function(){
        if(this.cfg.source == 'thesauri') {
            return this.getThesauriStore();
        }

        if(Ext.isEmpty(this.data)) return;
        if(this.data.ownerCt) {
            return this.data.ownerCt.objectsStore;
        }
        if(this.data.grid) {
            a = this.data.grid.refOwner || this.data.grid.findParentByType(CB.Objects);
            if(!Ext.isEmpty(a)) {
                return a.objectsStore;
            }
        }
    }
    ,getThesauriStore: function(){
        thesauriId = this.cfg.thesauriId;
        if(this.cfg.thesauriId == 'dependent'){
            fieldName = this.data.record.store.fields.findIndex('name', 'field_id');
            fieldName = (fieldName < 0) ? 'id': 'field_id';
            pri = this.data.record.store.findBy(function(r){
                return ( (r.get(fieldName) == this.data.record.get('pid')) && (r.get('duplicate_id') == this.data.record.get('duplicate_id')) );
            }, this);
            if(pri > -1) thesauriId = this.data.pidValue;
        }
        if(!isNaN(thesauriId)) return getThesauriStore(thesauriId);
    }

};

Ext.define('CB.ObjectsComboField', {
    extend: 'Ext.form.ComboBox'
    ,forceSelection: true
    ,triggerAction: 'all'
    ,lazyRender: true
    ,mode: 'remote'
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

        this.callParent(arguments);
    }

    ,initComponent: function(){
        //CB.ObjectsComboField.superclass.initComponent.call(this);
        var mode = 'local';

        if(this.store.proxy){
            mode = 'remote';

            this.store.proxy.extraParams = Ext.apply({}, this.cfg);
            if(!Ext.isEmpty(this.data.objectId)) this.store.proxy.extraParams.objectId = this.data.objectId;
            if(!Ext.isEmpty(this.data.pidValue)) this.store.proxy.extraParams.pidValue = this.data.pidValue;
            if(!Ext.isEmpty(this.data.path)) this.store.proxy.extraParams.path = this.data.path;

            this.store.on('beforeload', this.onBeforeLoadStore, this);
            this.store.on('load', this.onStoreLoad, this);
            this.store.load();
        }
        var customIcon = (this.cfg.renderer == 'listGreenIcons') ? 'icon-element' : '';
        var plugins = Ext.isEmpty(this.cfg.renderer)
            ? []
            : []; //[new Ext.ux.plugins.IconCombo()];
        Ext.apply(this, {
            mode: mode
            ,store: this.store
            ,iconClsField: 'iconCls'
            ,customIcon: customIcon
            // ,plugins: plugins
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

        this._setValue = this.setValue;
        this.setValue = function(v){
            var value, values = Ext.Array.from(v);
            v = [];
            for (var i = 0; i < values.length; i++) {
                value = (values[i] && values[i].isModel)
                    ? values[i].get(this.valueField)
                    : values[i];
                v.push(parseInt(value, 10));
            }

            this._setValue(v);
            var text = this.store.getTexts(v);

            //delete this.customIcon;
            if(Ext.isEmpty(text) && this.objectsStore){
                var idx = this.objectsStore.findExact('id', v);

                if(idx > 0){
                    r = this.objectsStore.getAt(idx);
                    if(this.icon) {
                        this.icon.className = 'ux-icon-combo-icon ' + r.get('iconCls');
                    }
                    text = this.objectsStore.getTexts(v);
                }
            }

            this.setRawValue(text);
        };

        this.callParent(arguments);

        // CB.ObjectsComboField.superclass.initComponent.apply(this, arguments);
    }

    ,onBeforeLoadStore: function(st, options){
        options.params = Ext.apply({}, this.cfg, options.params);
    }

    ,onStoreLoad: function(store, recs, options) {
        Ext.each(
            recs
            ,function(r){
                r.set('iconCls', getItemIcon(r.data));
            }
            ,this
        );

        store.insert(
            0
            ,Ext.create(
                store.getModel().getName()
                ,{
                    id: null
                    ,name:''
                }
            )
        );

        if(Ext.isEmpty(this.lastQuery)) {
            this.setValue(this.value);
        }
    }

    ,updateStore: function(){
        oldStore = this.store;
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
        tpl = '<tpl for=".">{[ (xindex == 0) ? "" : "'+this.delimiter+'"]}{name}</tpl>';
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

        CB.ObjectsTriggerField.superclass.initComponent.apply(this, arguments);
    }
    ,afterrender: function(){
        this.setValue(this.value);
    }
    ,setValue: function(v){
        this.value = [];
        var store = this.getObjectsStore();
        if(!Ext.isEmpty(v)){
            if(!Ext.isArray(v)) v = String(v).split(',');
            for(i = 0; i < v.length; i++) {
                this.value.push(parseInt(v[i], 10));
            }
        }
        data = [];
        if(store) //check if store is set cause it could not be determined due to field configuration errors
        for (var i = 0; i < this.value.length; i++) {
            idx = store.findExact('id', this.value[i]);
            if(idx >=0){
                r = store.getAt(idx);
                data.push(r.data);
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
            selectedValue = [];
            Ext.each( data, function(i){
                selectedValue.push(i.id);
            }, this );
            data = selectedValue.join(',');

        }

        oldValue = this.getValue();
        if(data == oldValue) {
            return;
        }
        this.setValue(data);
        this.fireEvent('change', data, oldValue);
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
                ,{text: Ext.MessageBox.buttonText.cancel, handler: this.doClose, scope: this, tabIndex: 4}
            ]
        });
        CB.ObjectsSelectionPopupList.superclass.initComponent.apply(this, arguments);

        this.on('beforeshow', this.onBeforeShowEvent, this);
        this.on('resize', function(win, w, h){this.trigger.setWidth(w - 17);});
    }
    ,focusGrid: function(){
        this.grid.focus();
        if(this.grid.getStore().getCount() > 0){
            r = this.grid.getSelectionModel().getSelected();
            if(!r) r = this.grid.getStore().getAt(0);
            this.grid.getSelectionModel().select([r]);
            this.grid.getView().focusRow(this.grid.getStore().indexOf(r));
        }
    }
    ,toggleElementSelection: function(g, ri, e){
        r = this.grid.getSelectionModel().getSelected();
        if(!r || (r.get('header_row') == 1)) return;
        id = r.get('id') + '';
        if(this.value.indexOf(id) < 0 ) this.value.push(id);
        else this.value.remove(id);
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
    },doFilter: function(e){
        criterias = [{fn: function(rec){return !Ext.isEmpty(rec.get('id'));}, scope: this}];
        v = this.trigger.getValue();
        if(!Ext.isEmpty(v)) criterias.push({ property: 'name', value: v, anyMatch: true, caseSensitive: false });
        if(Ext.isEmpty(criterias)) this.grid.store.clearFilter(); else this.grid.store.filter(criterias);
    },doClearSelection: function(){
        this.value = [];
        this.grid.getView().refresh(false);
    },doSubmit: function(){
        this.grid.store.clearFilter();
        newValue = this.value.join(',');
        this.fireEvent('setvalue', newValue, this);
        this.close();
    }

});
