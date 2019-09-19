Ext.namespace('CB.DB');

/**
* generic JsonStore class for template stores
**/

Ext.define('CB.DB.TemplateStore', {
    extend: 'Ext.data.JsonStore'

    ,defaultParams: {
        autoLoad: true
        ,model: 'Template'
        // ,fields: [
        //     {name: 'id', type: 'int'}
        //     ,{name:'pid', type: 'int'}
        //     ,'tag'
        //     ,'name'
        //     ,'title'
        //     ,'type'
        //     ,{name: 'order', type: 'int'}
        //     ,{name: 'cfg', convert: function(v, r){ return Ext.isEmpty(v) ? {} : v;} }
        // ]
    }

    ,constructor: function(params){
        Ext.applyIf(params, this.defaultParams);
        if(Ext.isEmpty(params.proxy)) {
            params.proxy = new Ext.data.MemoryProxy(params.data || []);
        }

        this.callParent(arguments);
        // CB.DB.TemplateStore.superclass.constructor.call(this, params);
    }
});
