Ext.namespace('CB');

Ext.define('CB.widget.block.Map', {
    extend: 'CB.widget.block.Base'

    ,alias: 'CBWidgetBlockMap'

    ,xtype: 'CBWidgetBlockMap'

    ,initComponent: function(){
        this.mapPanel = Ext.create(
            'CB.LeafletPanel'
            ,{
                listeners: {
                    scope: this
                    ,mapready: this.onMapReady
                }
            }
        );

        Ext.apply(this, {
            minWidth: 300
            ,minHeight: 200
            ,layout: 'fit'
            ,items: this.mapPanel
        });

        this.callParent(arguments);
    }

    ,onMapReady: function(p) {
        p.setViewConfig(this.initialConfig.params);
    }
});
