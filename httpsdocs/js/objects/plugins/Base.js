Ext.namespace('CB.objects.plugins');

Ext.define('CB.objects.plugins.Base', {
    extend: 'Ext.Panel'
    ,border: false
    ,header: false
    ,tbarCssClass: 'obj-plugin-h'
    ,cls: 'obj-plugin'

    ,initComponent: function(){
        this.prepareToolbar();

        this.enableBubble(['openproperties', 'createobject']);

        CB.objects.plugins.Base.superclass.initComponent.apply(this, arguments);

    }

    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }
        //overwrite this method and add your logic
    }

    ,prepareToolbar: function()
    {
        if(Ext.isEmpty(this.title) && Ext.isEmpty(this.actions)) {
            return;
        }

        var tbarItems = [];
        if(!Ext.isEmpty(this.title)) {
            tbarItems.push({
                xtype: 'label'
                ,cls: 'fwB'
                ,text: this.title
            });
        }

        var items = this.getToolbarItems();

        if(!Ext.isEmpty(items)) {
            tbarItems.push('->');
            for (var i = 0; i < items.length; i++) {
                tbarItems.push(items[i]);
            }
        }

        this.tbar = tbarItems;
    }

    ,getToolbarItems: function() {
        return [];
    }

    ,getContainerToolbarItems: function() {
        return {};
    }

    ,openObjectProperties: function(data) {
        this.fireEvent('openproperties', data);
    }

});
