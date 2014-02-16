Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Base = Ext.extend(Ext.Panel, {
    border: false
    ,header: false
    ,tbarCssClass: 'obj-plugin-h'
    ,initComponent: function(){
        this.prepareToolbar();

        CB.objects.plugins.Base.superclass.initComponent.apply(this, arguments);

    }

    ,onLoadData: function(r, e) {
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

    ,openObjectProperties: function(data) {
        var pp = this.findParentByType('CBPluginsPanel');
        if(pp) {
            pp.load(data);
            return true;
        }
        return false;
    }

});

Ext.reg('CBObjectsPluginsBase', CB.objects.plugins.Base);
