Ext.namespace('CB.DB');

/**
* generic JsonStore class for objects store, used in different components
**/

Ext.define('CB.DB.ObjectsStore', {
    extend: 'Ext.data.JsonStore'

    ,constructor: function(){
        this.callParent([{
            model: 'ObjectsRecord'
            ,proxy: {
                type: 'memory'
                ,reader: {
                    type: 'json'
                }
            }
            // fields: [
            //     {name: 'id', type: 'int'}
            //     ,'name'
            //     ,{name: 'date', type: 'date'}
            //     ,{name: 'type', type: 'int'}
            //     ,{name: 'subtype', type: 'int'}
            //     ,{name: 'template_id', type: 'int'}
            //     ,{name: 'status', type: 'int'}
            //     ,'iconCls'
            // ]
        }]);

        this.getTexts = getStoreNames;
    }
    // ,getData: function(v){  // this function conflicts with new getData method of stores
    //     if(Ext.isEmpty(v)) return [];
    //     ids = String(v).split(',');
    //     data = [];
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

        if (idx < 0) {
            data = Ext.apply({}, data);
            data.id = id;
            var r = Ext.create(
                this.getModel().getName()
                ,data
            );

            r.set('iconCls', getItemIcon(data));
            this.add(r);
        }
    }
});
