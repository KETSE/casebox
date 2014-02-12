Ext.namespace('CB');

CB.PluginPanel = Ext.extend(Ext.Panel, {
    autoHeight: true
    ,padding:0
    ,initComponent: function(){
        CB.PluginPanel.superclass.initComponent.apply(this, arguments);


    }

});

Ext.reg('CBPluginPanel', CB.PluginPanel);
