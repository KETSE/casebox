Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Base', {
    extend: 'Ext.Panel'
    ,border: false
    ,header: false
    ,cls: 'obj-plugin'

    ,initComponent: function(){
        this.prepareToolbar();

        this.enableBubble(['openproperties', 'createobject', 'objectopen']);

        this.callParent(arguments);
    }

    /**
     * method to be overriten in descendant classes
     * @param  array r
     * @param  event e
     * @return void
     */
    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }
    }

    /**
     * get parent panel loaded data
     * @return object
     */
    ,getLoadedObjectProperties: function() {
        var pluginsPanel = this.up('panel');

        return pluginsPanel
            ? pluginsPanel.loadedData
            : {};
    }

    /**
     * base method for preparing toolbar items
     * @return void
     */
    ,prepareToolbar: function() {
        if(Ext.isEmpty(this.title) && Ext.isEmpty(this.actions)) {
            return;
        }

        var tbarItems = [];
        if(!Ext.isEmpty(this.title)) {
            tbarItems.push({
                xtype: 'label'
                ,cls: 'title'
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

    /**
     * placeholder to get and array of itemIds for components to be displayed in toolbar
     * @return array
     */
    ,getToolbarItems: function() {
        return [];
    }

    ,setTitle: function(title) {
        if(this.dockedItems) {
            var tbar = this.dockedItems.items[0]
                ,label = tbar.items.get(0);
            label.setText(title);
        } else {
            this.callParent(arguments);
        }
    }

    /**
     * placeholder to get and array of itemIds for components to be displayed in container toolbar
     * @return array
     */
    ,getContainerToolbarItems: function() {
        return {};
    }

    /**
     * common method used to open another object
     * @param  object data
     * @return void
     */
    ,openObjectProperties: function(data) {
        App.openObjectWindow(data);
    }
});
