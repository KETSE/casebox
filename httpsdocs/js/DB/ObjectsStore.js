Ext.namespace('CB.DB');

/**
* generic JsonStore class for objects store, used in different components
**/

CB.DB.ObjectsStore = Ext.extend(Ext.data.JsonStore, {
    constructor: function(){
        CB.DB.ObjectsStore.superclass.constructor.call(this, {
            fields: [
                {name: 'id', type: 'int'}
                ,'name'
                ,{name: 'date', type: 'date'}
                ,{name: 'type', type: 'int'}
                ,{name: 'subtype', type: 'int'}
                ,{name: 'template_id', type: 'int'}
                ,{name: 'status', type: 'int'}
                ,'iconCls'
            ]
        });
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
        if(Ext.isEmpty(data)) return false;
        idx = this.findExact('id', parseInt(data.id, 10));
        if (idx < 0) {
            r = new this.recordType(data);
            r.set('iconCls', getItemIcon(data));
            this.add(r);
        }
    }
});

Ext.reg('CBDBObjectsStore', CB.DB.ObjectsStore);
