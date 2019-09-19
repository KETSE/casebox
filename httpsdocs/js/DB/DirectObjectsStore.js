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

        this.getTexts = getStoreNames;
    }
});

CB.DB.DirectObjectsStore.borrow(
    CB.DB.ObjectsStore
    ,[
        'checkRecordExistance'
        ,'checkRecordsExistance'
    ]
);
