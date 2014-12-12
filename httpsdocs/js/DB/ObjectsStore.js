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
        }]);

        this.getTexts = getStoreNames;
    }

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
