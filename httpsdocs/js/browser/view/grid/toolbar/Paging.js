Ext.namespace('CB.browser.view.grid.toolbar');

Ext.define('CB.browser.view.grid.toolbar.Paging', {
    extend: 'Ext.toolbar.Paging'

    ,xtype: 'CBBrowserViewGridPagingToolbar'

    ,border: false
    ,displayInfo: true
    ,displayMsg: '{0} - {1} of {2}'

    ,initComponent: function(){
        var me = this;

        this._getPagingItems = this.getPagingItems;
        this.getPagingItems = this.getCustomizedPaginItems;

        this.setCustomItems();

        me.callParent();

        //move display info and spacer before custom buttons
        // var i = me.items.last();
        // me.items.remove(i);
        // me.items.insert(5, i);

        // i = me.items.last();
        // me.items.remove(i);
        // me.items.insert(6, i);


        // enable bubble for event of export button
        this.enableBubble('exportrecords');

        // update columns combo on grid reconfiguration
        // this.ownerCt.on('reconfigure', this.onGridReconfigure, this);
    }

    ,getCustomizedPaginItems: function() {
        var rez = this._getPagingItems();

        rez.shift(); //remove "first page" button
        rez.pop(); //remove reload button
        rez.pop(); //remove divider
        rez.pop(); //remove "last page" button
        rez.splice(1, 1); // remove splitter
        rez.splice(4, 1); // remove socond splitter

        return rez;
    }

    ,setCustomItems: function() {
        var me = this;

        // me.items = [{
        //     xtype: 'combo'
        //     ,disabled: true
        //     ,fieldLabel: L.Group
        //     ,labelWidth: 'auto'
        //     ,store: {
        //         type: 'json'
        //         ,model: 'Generic2'
        //     }
        //     ,queryMode: 'local'
        //     ,displayField: 'name'
        //     ,valueField: 'id'
        // },{
        //     iconCls: 'i-table-export'
        //     ,qtip: L.Export
        //     ,scope: this
        //     ,handler: this.onExportClick
        // },{
        //     iconCls: 'i-points'
        //     ,disabled: true

        // }];
    }

    ,onGridReconfigure: function(grid, store, columns, oldStore, oldColumns, eOpts) {
        clog('columns', arguments);
    }

    // ,onExportClick: function(b, e) {
    //     this.fireEvent('exportrecords', this, e);
    // }
});
