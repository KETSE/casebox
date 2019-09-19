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

    /**
     * check record existance for a record data
     * @param  array data record data
     * @return void
     */
    ,checkRecordExistance: function(data){
        if(Ext.isEmpty(data)) {
            return false;
        }

        var id = Ext.valueFrom(data.nid, data.id);
        if(Ext.isEmpty(id)) {
            return false;
        }

        var rec = this.findRecord('id', id, 0, false, false, true);

        if (!rec) {
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

    /**
     * check record existance for a set of records
     * @param  array arr
     * @return void
     */
    ,checkRecordsExistance: function(arr){
        Ext.each(
            arr
            ,function(d){
                this.checkRecordExistance(d);
            }
            ,this
        );
    }
});
