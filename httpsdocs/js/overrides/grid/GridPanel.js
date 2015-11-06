/*
    Overrides
*/

Ext.override(Ext.grid.GridPanel, {

    getState: function(remainVisible) {
        var rez = {columns: {}}
            ,store = this.store
            ,cols = this.headerCt.getGridColumns()
            ,gs
            ,c
            ,di;

        for(var i = 0; i < cols.length; i++){
            c = cols[i];
            di = c.dataIndex;

            //hidden', 'sortable', 'locked', 'flex', 'width
            rez.columns[di] = {
                idx: i
            };

            if(c.width){
                rez.columns[di].width = c.width;
            }

            if(c.hidden){
                rez.columns[di].hidden = true;
            }

            if(c.sortable){
                rez.columns[di].sortable = true;
            }

            if(c.locked){
                rez.columns[di].locked = true;
            }

            if(c.flex){
                rez.columns[di].flex = c.flex;
            }

        }

        if(store){
            var ss = store.getSorters().getAt(0);

            if(ss && ss.getState){
                rez.sort = ss.getState();
            }

            if(store.getGrouper){
                rez.group = store.getGrouper();
                if(rez.group) {
                    rez.group = rez.group.config;
                    rez.group.property = store.proxy.extraParams.sourceGroupField;
                }
            }
        }

        return rez;
    }

    // ,applyState: function (state) {
    //     var me = this
    //         ,sorter = state.sort
    //         ,store = me.store
    //         ,columns = state.columns
    //         ,currentColumns = this.headerCt.getGridColumns()
    //         ,newColumns = [];

    //     delete state.columns;
    //     clog('applying state', this, state, columns);

    //     // Ensure superclass has applied *its* state.
    //     // Component saves dimensions (and anchor/flex) plus collapsed state.
    //     me.callParent(arguments);

    //     //set stateId for received state based on column dataindex
    //     for (var i = currentColumns.length - 1; i >= 0; i--) {
    //         var di = currentColumns[i].dataIndex
    //             ,column = Ext.apply({}, currentColumns[i].initialConfig);

    //         if(Ext.isDefined(columns[di])) {
    //             Ext.apply(column, columns[di]);
    //             column.width = Ext.valueFrom(column.width, column.flex);
    //             clog('apply column state', column, columns[di]);
    //             // currentColumns[i].applyColumnsState(columns[di]);

    //             // this.columns[i].applyColumnsState(columns[di]);
    //             // columns[di].id = currentColumns[i].getStateId();
    //             // newColumns[columns[di].idx] = columns[di];
    //         }
    //         newColumns.push(column);
    //     }

    //     clog('newColumns', newColumns);
    //     if (newColumns) {
    //         me.reconfigure(null, newColumns);
    //     }

    //     // Old stored sort state. Deprecated and will die out.
    //     if (sorter) {
    //         if (store.remoteSort) {
    //             // Pass false to prevent a sort from occurring.
    //             store.sort({
    //                 property: sorter.property,
    //                 direction: sorter.direction,
    //                 root: sorter.root
    //             }, null, false);
    //         } else {
    //             store.sort(sorter.property, sorter.direction);
    //         }
    //     }
    //     // New storeState which encapsulates groupers, sorters and filters.
    //     // else if (storeState) {
    //     //     store.applyState(storeState);
    //     // }
    // }
});
