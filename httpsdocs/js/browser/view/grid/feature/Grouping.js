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
        store.on('load', this.onStoreLoad, this, {delay: 200});
    }

    ,onStoreLoad: function(store, operation, eOpts) {
        delete store.proxy.extraParams.userGroup;
        delete this.storeExtraParams.userGroup;
    }

    ,onBeforeStoreLoad: function(store, operation, eOpts) {
        Ext.apply(store.proxy.extraParams, this.storeExtraParams);
    }

    ,onGroupMenuItemClick: function(menuItem, e) {
        var me = this
            ,menu = menuItem.parentMenu
            ,hdr  = menu.activeHeader
            ,sgf
            ,view = me.view
            ,store = view.store;

        if(hdr && store.remoteSort) {
            sgf = hdr.dataIndex;

            this.groupTitle = hdr.text;

            this.storeExtraParams = {
                userGroup: 1
                ,sourceGroupField: sgf
            };

            hdr.dataIndex = 'group';
        }

        this.callParent(arguments);

        if(hdr) {
            hdr.dataIndex = sgf;
        }
    }

    ,setupRowData: function(record, idx, rowValues) {
        this.callParent(arguments);
        if(rowValues.isFirstRow) {
            if(this.refreshData.header) {
                this.lastColumnTitle = this.refreshData.header.text;
            }
            rowValues.groupInfo.columnName = this.lastColumnTitle;
        }
    }

    ,getGroupedHeader: function(groupField) {
        var rez = this.callParent([Ext.valueFrom(this.storeExtraParams.sourceGroupField, groupField)]);

        return rez;
    }

    ,disable: function() {
        var me = this
            ,view = me.view
            ,store = view.store;

        store.remoteSort = false;

        this.callParent(arguments);

        store.remoteSort = true;
    }
});
