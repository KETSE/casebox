Ext.namespace('CB');

Ext.define('CB.widget.block.Grid', {
    extend: 'CB.widget.block.Base'

    ,alias: 'CBWidgetBlockGrid'

    ,xtype: 'CBWidgetBlockGrid'

    ,minHeight: 100

    ,initComponent: function(){
        this.store = new Ext.data.JsonStore({
            autoDestroy: true
            ,remoteSort: false
            ,model: 'FieldObjects'
            ,proxy: {
                type: 'memory'
                ,reader: {
                    type: 'json'
                }
            }
        });

        this.grid = new CB.browser.view.Grid({
            border: false
            ,refOwner: this
            ,store: this.store
            ,getProperty: Ext.emptyFn
            ,saveGridState: Ext.emptyFn
            ,hideBottomBar: true
        });

        Ext.apply(this, {
            layout: 'fit'
            ,items: [
                this.grid
            ]
            ,listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });

        this.callParent(arguments);
    }

    ,onAfterRender: function() {
        var ic = this.initialConfig
            ,s = this.store;
        if(!Ext.isEmpty(ic.data)) {
            for (var i = 0; i < ic.data.length; i++) {
                ic.data[i].iconCls = getItemIcon(ic.data[i]);
            }

            s.proxy.reader.rawData = ic.data;
            s.loadData(ic.data.data);

            s.fireEvent(
                'manualload'
                ,s
                ,s.data.items
                ,true
                ,{}
            );
        }
    }
});
