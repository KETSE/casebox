Ext.namespace('CB.form.view.object');

CB.form.view.object.Properties = Ext.extend(CB.PluginsPanel, {
    initComponent: function(){
        CB.form.view.object.Properties.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('CBObjectProperties', CB.form.view.object.Properties);
