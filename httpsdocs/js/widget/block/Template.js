Ext.namespace('CB');

Ext.define('CB.widget.block.Template', {
    extend: 'CB.widget.block.Base'

    ,alias: 'CBWidgetBlockTemplate'

    ,xtype: 'CBWidgetBlockTemplate'

    ,minHeight: 100

    ,initComponent: function(){
        Ext.apply(this, {
            tpl: this.config.params.tpl
            ,data: this.config.data
        });

        this.callParent(arguments);
    }
});
