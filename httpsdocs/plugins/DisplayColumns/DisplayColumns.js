Ext.namespace('CB.plugin');

Ext.onReady(function(){
    var plugins = CB.browser.view.Grid.prototype.plugins || [];
    plugins.push({
        ptype: 'CBPluginDisplayColumns'
    });
    CB.browser.view.Grid.prototype.plugins = plugins;
});


Ext.define('CB.plugin.DisplayColumns', {
    extend: 'Ext.util.Observable'
    ,alias: 'plugin.CBPluginDisplayColumns'
    ,lastColumns: ''

    ,init: function(owner) {
        this.owner = owner;
        this.grid = owner.grid;
        this.store = owner.grid.store;

        this.defaultColumns = owner.grid.defaultColumns;
        this.reader = this.store.proxy.reader;
        this.model = this.store.getModel();
        this.defaultFieldNames = this.extractFieldNames(this.model.fields);
        this.proxy = this.store.proxy;

        this.store.on('load', this.onStoreLoad, this);
        this.store.on('load', this.clearDisableStateSaveFlag, this, {defer: 1000});
    }

    ,onStoreLoad: function(store, records, successful, eOpts) {//proxy, obj, options
        //dont do anything if view not visible
        if(this.owner.getEl().isVisible(true) !== true) {
            return;
        }

        var rez = store.proxy.reader.rawData;

        //set flag to avoid saving grid state while restoring remote config
        this.grid.disableStateSave = true;

        if(!Ext.isEmpty(rez.sort)) {// && Ext.isEmpty(this.store.sortInfo)
            var sorters = this.store.getSorters();
            sorters.suspendEvents();
            sorters.clear();

            sorters.addSort(rez.sort.property, rez.sort.direction);
            sorters.resumeEvents(true);
        }

        //add corresponding metadata to obj.result if DisplayColumns changed
        this.currentColumns = rez.DC || [];

        if(this.lastColumns !== Ext.util.JSON.encode(this.currentColumns)) {
            var storeFields = this.getNewMetadata();
            store.setFields(storeFields);

            this.lastColumns = Ext.util.JSON.encode(this.currentColumns);

            var nc = this.getNewColumns();
            this.grid.reconfigure(null, nc);
        }

        //restore or disable grouping state
        var groupFeature = this.grid.view.features[0];
        if(!Ext.isEmpty(rez.group) && !Ext.isEmpty(rez.group.property)) {
            store.remoteSort = false;

            if(groupFeature.disabled) {
                var menuItem = groupFeature.getMenuItem(rez.group.property);
                if(Ext.isEmpty(menuItem)) {
                    menuItem = {
                        parentMenu: this.grid.view.headerCt.getMenu()
                    };
                }

                if(Ext.isEmpty(menuItem.parentMenu.activeHeader)) {
                    menuItem.parentMenu.activeHeader = this.grid.getVisibleColumnManager().getHeaderByDataIndex(rez.group.property);
                }

                groupFeature.onGroupMenuItemClick(menuItem, eOpts);
            }

            var groupDir = Ext.valueFrom(rez.group.direction, 'ASC');
            if(store.getGroupDir != groupDir) {
                store.group(rez.group.property, groupDir);
            }

        } else if(Ext.isEmpty(rez.group) && !groupFeature.disabled) {
            store.remoteSort = false;
            groupFeature.disable();
        }
    }

    /**
     * disableStateSave flag is set during state restore received from server
     * It should be removed at the end of the process
     * @return void
     */
    ,clearDisableStateSaveFlag: function() {
        delete this.grid.disableStateSave;
        this.store.remoteSort = true;
    }

    /**
     * get new fields metadata for the store by analyzing DC config received from server
     * @return array
     */
    ,getNewMetadata: function(){
        var i
            ,key
            ,fieldData
            ,rez = Ext.apply([], CB.DB.defaultItemFields)
            ,currentColumns = Ext.apply({}, this.currentColumns);

        for (i = 0; i < rez.length; i++) {
            fieldData = rez[i];

            if(Ext.isString(fieldData)) {
                key = fieldData;
                fieldData = {
                    name: key
                };
            } else {
                key = rez[i].name;
            }

            if(Ext.isDefined(currentColumns[key])) {
                rez[i] = Ext.copyTo(fieldData, currentColumns[key], ['type', 'sortType']);
                rez[i].convert = null;

                delete currentColumns[key];
            }
        }

        Ext.iterate(
            currentColumns
            ,function(key, value, obj){
                var field = {
                    name: key
                    ,title: Ext.valueFrom(value.title, 'No title')
                };
                rez.push(field);
            }
            ,this
        );

        return rez;
    }

    /**
     * get new collumns config for the grid panel
     * @return array
     */
    ,getNewColumns: function(){
        var rez = Ext.apply([], this.defaultColumns);
        var currentColumns = Ext.apply({}, this.currentColumns);
        var i;
        var emptyCurrentColumns = (Ext.encode(currentColumns) == '{}');

        for (i = 0; i < rez.length; i++) {
            if(Ext.isDefined(currentColumns[rez[i].dataIndex])) {
                var nd = currentColumns[rez[i].dataIndex];

                delete rez[i].hidden;

                rez[i] = Ext.apply(rez[i], nd);

                if(nd.width && rez[i].setWidth) {
                    rez[i].setWidth(nd.width);
                }

                delete currentColumns[rez[i].dataIndex];

            } else if(!emptyCurrentColumns) {
                rez[i].hidden = true;
            }
        }

        Ext.iterate(
            currentColumns
            ,function(key, value, obj){
                var column = value;
                if(key !== 'remove') {
                    // column.id = rez.length;
                    column.dataIndex = key;
                    // column.stateId = key;
                    column.header = Ext.valueFrom(column.header, column.title);
                    switch(column.type) {
                        case 'date':
                            column.renderer = App.customRenderers.datetime;
                            break;

                        default:
                            // column.renderer = this.defaultColumnRenderer;
                    }

                    if(this.owner.columnSortOverride) {
                        column.sort = this.owner.columnSortOverride;
                    }

                    rez.push(column);
                }
            }
            ,this
        );

        /* sort columns */
        var changed = true;
        var t;

        i = 0;
        while(changed || (i < (rez.length - 1))) {
            changed = false;

            if((!Ext.isDefined(rez[i].idx) && Ext.isDefined(rez[i+1].idx)) ||
                (rez[i].idx > rez[i+1].idx)
            ) {
                changed = true;
                t = rez[i];
                rez[i] = rez[i+1];
                rez[i+1] = t;
                i = -1;
            }
            i++;
        }

        return rez;
    }

    /**
     * extract field names array from a fields config array
     * @param  array fieldsArray
     * @return array
     */
    ,extractFieldNames: function(fieldsArray){
        var rez = [];
        Ext.each(
            fieldsArray
            ,function(i){
                if(Ext.isObject(i)){
                    rez.push(i.name);
                } else {
                    rez.push(i);
                }
            }
            ,this
        );
        return rez;
    }
    // ,defaultColumnRenderer: function (v, meta, record, row_idx, col_idx, store) {
    //     return record.json[this.dataIndex];
    // }
});
