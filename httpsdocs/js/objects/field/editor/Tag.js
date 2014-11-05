Ext.namespace('CB.objects.field.editor');

Ext.define('CB.objects.field.editor.Tag', {
    extend: 'Ext.form.field.Tag'
    ,xtype: 'CBObjectsFieldEditorTag'

    ,layout: 'border'

    ,constructor: function(config) {
        this.data = config.data;

        this.cfg = this.data.fieldRecord
            ? Ext.apply({}, Ext.valueFrom(this.data.fieldRecord.data.cfg, {}))
            : Ext.applyIf(
                Ext.valueFrom(config.config, {})
                ,{multiValued: false}
            );

        this.callParent(arguments);

        // this.setValue(config.value);
    }

    ,initComponent: function(){
        // this.detectStore();

        Ext.apply(this, {
            listeners: {
                scope: this
            }
        });
        this.callParent(arguments);

        // this.store.on('load', this.onLoad, this);
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

    ,setValue: function(value) {
        if(Ext.isEmpty(value)) {
            value = null;
        } else {
            value = {id: value, name: value};
        }
        clog('setting value', value, this.store.getCount());

        this.callParent([value]);

        // this.fireEvent('change', this, value);
    }

    ,checkChange: function() {
        clog('checkChange', this.value);
        if(Ext.isEmpty(this.value)) {
            return;
        }

        this.callParent();
    }

    // ,getValue: function() {
    //     return this.value;
    // }

    // ,getSearchParams: function(){
    //     result = Ext.apply({}, this.cfg);
    //     result.query = this.triggerField.getValue();
    //     if(!Ext.isEmpty(this.data.objectId)) {
    //         result.objectId = this.data.objectId;
    //     }
    //     if(!Ext.isEmpty(this.data.path)) {
    //         result.path = this.data.path;
    //     }

    //     return result;
    // }
});
