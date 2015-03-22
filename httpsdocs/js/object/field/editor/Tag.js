Ext.namespace('CB.object.field.editor');

Ext.define('CB.object.field.editor.Tag', {
    extend: 'Ext.form.field.Tag'

    ,xtype: 'CBObjectFieldEditorTag'

    ,layout: 'border'

    ,constructor: function(config) {
        this.objData = config.objData;

        this.cfg = this.objData.fieldRecord
            ? Ext.apply({}, Ext.valueFrom(this.objData.fieldRecord.data.cfg, {}))
            : Ext.applyIf(
                Ext.valueFrom(config.config, {})
                ,{multiValued: false}
            );

        this.detectStore();

        this.callParent(arguments);
    }

    ,initComponent: function(){
        Ext.apply(this, {
            listeners: {
                scope: this
                ,destroy: function() {
                    Ext.destroy(this.store);
                }
            }
        });

        this.callParent(arguments);
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
                            var d = this.objData;
                            if(d){
                                if(!Ext.isEmpty(d.fieldRecord)) {
                                    store.proxy.extraParams.fieldId = d.fieldRecord.get('id');
                                }
                                if(!Ext.isEmpty(d.objectId)) {
                                    store.proxy.extraParams.objectId = d.objectId;
                                }
                                if(!Ext.isEmpty(d.pidValue)) {
                                    store.proxy.extraParams.pidValue = d.pidValue;
                                }
                                if(!Ext.isEmpty(d.path)) {
                                    store.proxy.extraParams.path = d.path;
                                }
                                store.proxy.extraParams.objFields = d.objFields;
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

    ,collapse: function() {
        var ev = Ext.EventObject
            ,eventTime = ev.getTime();

        switch(ev.type) {
            case 'mousedown':
                //check if clicked inside the editor or picker
                if(
                    !ev.within(this.getEl(), false, true) &&
                    (!this.picker  || (this.picker && !ev.within(this.picker.getEl(), false, true)))
                ) {
                    delete this.preventEditComplete;
                } else {
                    this.preventEditComplete = true;
                }
                break;

            case 'keydown':
                switch(ev.getKey()) {

                    case ev.ESC: // doesnt work ok for esc (looses focus)
                                // will investigate later, maybe dig into keyNav direction
                    case ev.ENTER:
                        if(this.isExpanded) {
                            this.collapsedTime = eventTime;
                            this.preventEditComplete = true;

                        } else {
                            if(this.collapsedTime !== eventTime) {
                                delete this.preventEditComplete;
                            }
                        }
                }

                break;
        }

        this.callParent(arguments);
    }
});
