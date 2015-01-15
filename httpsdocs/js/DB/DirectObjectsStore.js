Ext.namespace('CB.DB');

/**
* generic DirectStore class for objects store, used in different components
**/

Ext.define('CB.DB.DirectObjectsStore', {
    extend: 'Ext.data.DirectStore'

    ,autoLoad: false
    ,restful: false

    ,constructor: function(){
        var params = arguments[0];

        params = Ext.apply(
            params
            ,{
                model: 'ObjectsRecord'
                ,proxy: {
                    type: 'direct'
                    ,paramsAsHash: true
                    ,api: { read: CB_Objects.getAssociatedObjects }
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
                                if(!Ext.isEmpty(obj.result.data[i].cfg.iconCls)) {
                                    obj.result.data[i].iconCls = obj.result.data[i].cfg.iconCls;
                                }
                            }
                        }
                    }
                }
            }
        );

        // Ext.apply(this, params);
        this.callParent([params]);
        // CB.DB.DirectObjectsStore.superclass.constructor.call(this, params);

        this.getTexts = getStoreNames;
    }

    // ,getData: function(v){ // this function conflicts with new getData method of stores
    //     if(Ext.isEmpty(v)) {
    //         return [];
    //     }
    //     var ids = String(v).split(',')
    //         ,data = [];

    //     Ext.each(ids, function(id){
    //          idx = this.findExact('id', parseInt(id, 10));
    //         if(idx >= 0) data.push(this.getAt(idx).data);
    //     }, this);
    //     return data;
    // }

    ,checkRecordExistance: function(data){
        if(Ext.isEmpty(data)) {
            return false;
        }

        var id = Ext.Number.from(data.nid, data.id);
        if(isNaN(id)) {
            return false;
        }

        var idx = this.findExact('id', id);

        if(idx < 0){
            data = Ext.apply({}, data);
            data.id = id;
            r = Ext.create(
                this.getModel().getName()
                ,data
            );

            var icon = null;
            if(!Ext.isEmpty(data.cfg)) {
                icon = data.cfg.iconCls;
            }
            if(Ext.isEmpty(icon)) {
                icon = getItemIcon(data);
            }
            r.set('iconCls', icon);
            this.add(r);
        }
    }
});
