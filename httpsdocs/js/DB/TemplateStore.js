Ext.namespace('CB.DB');

/**
* generic JsonStore class for template stores
**/

CB.DB.TemplateStore = Ext.extend(Ext.data.JsonStore, {
    defaultParams: {
        autoLoad: true
        ,fields: [
            {name: 'id', type: 'int'}
            ,{name:'pid', type: 'int'}
            ,'tag'
            ,{name: 'level', type: 'int'}
            ,'name'
            ,'title'
            ,'type'
            ,{name: 'order', type: 'int'}
            ,{name: 'cfg', convert: function(v, r){ return Ext.isEmpty(v) ? {} : v;} }
        ]
    }

    ,constructor: function(params){
        Ext.applyIf(params, this.defaultParams);
        if(Ext.isEmpty(params.proxy)) {
            params.proxy = new Ext.data.MemoryProxy(params.data || []);
        }
        CB.DB.TemplateStore.superclass.constructor.call(this, params);
    }
});

Ext.reg('CBDBTemplateStore', CB.DB.TemplateStore);
