Ext.namespace('CB');

Ext.define('CB.widget.block.Base', {
    extend: 'Ext.Panel'

    ,alias: 'CBWidgetBlockBase'

    ,xtype: 'CBWidgetBlockBase'

    ,initComponent: function(){
        Ext.apply(this, {
            border: false
            ,cls: 'panel-header-nobg'
        });

        this.callParent(arguments);
    }
});
