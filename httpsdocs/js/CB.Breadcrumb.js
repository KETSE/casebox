Ext.namespace('CB');
Ext.define('CB.Breadcrumb', {
    extend: 'Ext.DataView'
    ,border: false
    ,bodyStyle: 'background: none'
    ,height: 28
    ,initComponent: function(){
        this.tpl = new Ext.XTemplate(
            '<ul class="breadcrumb"><tpl for=".">'
            ,'<li><a href="#">{[values.name.substring(0,30)]}</a>'
            ,'{[(xindex < xcount) ? \'</li><li>/\': \'\']}'
            ,'</li></tpl></ul>'
            ,{compiled: true}
        );

        this.store = new Ext.data.JsonStore({
            model: 'Generic2'
            ,proxy: {
                type: 'memory'
            }
        });

        Ext.apply(this, {
            itemSelector: 'a'
            ,overItemCls:'item-over'
            ,emptyText: '-'
        });

        CB.Breadcrumb.superclass.initComponent.apply(this, arguments);
    }

    ,setValue: function(dataArray) {
        var data = [];
        for (var i = 0; i < dataArray.length; i++) {
            if(Ext.isObject(dataArray[i])) {
                data.push(dataArray[i]);
            } else {
                data.push({
                    id: Ext.id()
                    ,name: dataArray[i].replace(/&amp;#47;/g, '&#47;')
                });
            }
        }
        this.store.loadData(data);
    }
}
);
