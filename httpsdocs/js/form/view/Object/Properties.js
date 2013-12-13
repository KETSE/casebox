Ext.namespace('CB.form.view.object');

CB.form.view.object.Properties = Ext.extend(Ext.Panel, {
    xtype: 'panel'
    ,autoScroll: true
    ,html: 'Obj properties'
    ,tbarCssClass: 'x-panel-white'
    ,loadMask: true
    ,padding:0
    ,layout: 'fit'
    ,initComponent: function(){
        CB.form.view.object.Properties.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('CBObjectProperties', CB.form.view.object.Properties);
