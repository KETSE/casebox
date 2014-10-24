Ext.namespace('CB.browser.view.grid.feature');

Ext.define('CB.browser.view.grid.feature.Grouping', {
    extend: 'Ext.grid.feature.Grouping'

    ,alias: 'feature.cbGridViewGrouping'

    ,storeExtraParams: {}

    ,init: function() {
        this.callParent(arguments);

        var me = this
            ,view = me.view
            ,store = view.store;

        store.on('beforeload', this.onBeforeStoreLoad, this);
    }

    ,onBeforeStoreLoad: function(store, operation, eOpts) {
        Ext.apply(store.proxy.extraParams, this.storeExtraParams);
    }

    ,onGroupMenuItemClick: function(menuItem, e) {
        var me = this
            ,menu = menuItem.parentMenu
            ,hdr  = menu.activeHeader
            ,sgf = hdr.dataIndex
            ,view = me.view
            ,store = view.store;

        this.storeExtraParams = {
            userGroup: 1
            ,sourceGroupField: sgf
        };

        hdr.dataIndex = 'group';

        this.callParent(arguments);

        hdr.dataIndex = sgf;

        // if (me.disabled) {
        //     me.lastGrouper = null;
        //     me.block();
        //     me.enable();
        //     me.unblock();
        // }

        // store.group(hdr.dataIndex);
        // me.pruneGroupedHeader();

    }

    ,onGroupToggleMenuItemClick: function(menuItem, checked) {
        this.callParent(arguments);
    }

    ,enable: function() {
        this.callParent(arguments);
    }

    ,getGroupedHeader: function(groupField) {
        return this.callParent([Ext.valueFrom(this.storeExtraParams.sourceGroupField, groupField)]);
    }
});
