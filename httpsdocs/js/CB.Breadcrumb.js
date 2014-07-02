Ext.namespace('CB');
CB.Breadcrumb = Ext.extend( Ext.DataView, {
    border: false
    ,bodyStyle: 'background: none'
    ,initComponent: function(){
        this.tpl = new Ext.XTemplate(
            '<ul class="breadcrumb"><tpl for=".">'
            ,'<li><a href="#">{[values.name.substring(0,30)]}</a>'
            ,'{[(xindex < xcount) ? \'</li><li>/\': \'\']}'
            ,'</li></tpl></ul>'
            ,{compiled: true}
        );

        this.store = new Ext.data.JsonStore({
            fields: ['id', 'name']
        });

        Ext.apply(this, {
            itemSelector: 'a'
            ,overClass:'item-over'
            ,emptyText: '-'
        });

        CB.Breadcrumb.superclass.initComponent.apply(this, arguments);
    }
    ,onItemClick: function (el, idx, ev){
    }
    ,setValue: function(dataArray) {
        var data = [];
        for (var i = 0; i < dataArray.length; i++) {
            if(Ext.isObject(dataArray[i])) {
                data.push(dataArray[i]);
            } else {
                data.push({
                    id: Ext.id()
                    ,name: Ext.util.Format.htmlEncode(dataArray[i])
                });
            }
        }
        this.store.loadData(data);
    }
}
);

Ext.reg('CBBreadcrumb', CB.Breadcrumb);
