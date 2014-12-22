Ext.namespace('CB');
Ext.define('CB.widget.Breadcrumb', {
    extend: 'Ext.Button'

    ,alias: 'CB.Breadcrumb'

    ,xtype: 'CBBreadcrumb'

    ,border: false

    ,autoWidth: true

    ,initComponent: function(){
        Ext.apply(this, {
            menu: []
        });

        this.callParent(arguments);
    }

    ,setValue: function(path) {
        var items = []
            ,item = ''
            ,a = String(path).split('/');

        for (var i = 0; i < a.length; i++) {
            if(!Ext.isEmpty(a[i])) {
                item += '/' + a[i];
                items.unshift({
                    text: item
                    ,scope: this
                    ,handler: this.onItemClick
                });
            }
        }

        //shift last item
        items.shift();
        this.setText(item);

        this.menu.removeAll(true);
        this.menu.add(items);
    }

    ,onItemClick: function(b, e) {
    }
}
);
