Ext.namespace('CB');
CB.Breadcrumb = Ext.extend( Ext.DataView, {
    border: false
    ,bodyStyle: 'background: none'
    ,initComponent: function(){
        this.tpl = new Ext.XTemplate(
            '<ul class="breadcrumb"><tpl for=".">'
            ,'<li><a href="#">{name}</a>'
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
        clog('breadcrumb click', arguments);
    }
    ,setValue: function(dataArray) {
        clog('breadcrumb set value', dataArray);
        var data = [];
        for (var i = 0; i < dataArray.length; i++) {
            if(Ext.isObject(dataArray[i])) {
                data.push(dataArray[i]);
            } else {
                data.push({
                    id: Ext.id()
                    ,name: dataArray[i]
                });
            }
        }
        this.store.loadData(data);
    }
}
);

Ext.reg('CBBreadcrumb', CB.Breadcrumb);
