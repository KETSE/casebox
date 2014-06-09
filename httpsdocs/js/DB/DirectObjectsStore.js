Ext.namespace('CB.DB');

/**
* generic DirectStore class for objects store, used in different components
**/

CB.DB.DirectObjectsStore = Ext.extend(Ext.data.DirectStore, {
    autoLoad: false
    ,restful: false
    ,constructor: function(){
        var params = arguments[0];
        params = Ext.apply(
            params
            ,{
                proxy: new  Ext.data.DirectProxy({
                    paramsAsHash: true
                    ,api: { read: CB_Objects.getAssociatedObjects }
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
                })
                ,reader: new Ext.data.JsonReader({
                    successProperty: 'success'
                    ,root: 'data'
                    ,messageProperty: 'msg'
                },[
                    {name: 'id', type: 'int'}
                    ,'name'
                    ,{name: 'date', type: 'date'}
                    ,{name: 'template_id', type: 'int'}
                    ,{name: 'status', type: 'int'}
                    , 'iconCls'
                    , 'cfg'
                ]
                )
            }
        );

        CB.DB.DirectObjectsStore.superclass.constructor.call(this, params);
        this.getTexts = getStoreNames;
    }
    ,getData: function(v){
        if(Ext.isEmpty(v)) return [];
        ids = String(v).split(',');
        data = [];
        Ext.each(ids, function(id){
             idx = this.findExact('id', parseInt(id, 10));
            if(idx >= 0) data.push(this.getAt(idx).data);
        }, this);
        return data;
    }
    ,checkRecordExistance: function(data){
        if(Ext.isEmpty(data) || isNaN(data.id)) {
            return false;
        }

        data.id = parseInt(data.id, 10);

        var idx = this.findExact('id', data.id, 10);

        if(idx < 0){
            r = new this.recordType(data);
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

Ext.reg('CBDBDirectObjectsStore', CB.DB.DirectObjectsStore);
