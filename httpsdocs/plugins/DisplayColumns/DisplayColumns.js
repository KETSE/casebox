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
    ,lastState: ''

    ,init: function(owner) {
        this.owner = owner;
        this.grid = owner.grid;
        this.store = owner.grid.store;

        this.defaultColumns = owner.grid.defaultColumns;
        this.reader = this.store.proxy.reader;
        this.model = this.store.getModel();
        this.defaultFieldNames = this.extractFieldNames(this.model.fields);
        this.proxy = this.store.proxy;

        this.owner.on('activate', this.onActivateView, this);
        this.store.on('load', this.onStoreLoad, this);
        this.store.on('manualload', this.onStoreLoad, this);
        this.store.on('clear', this.onStoreClear, this);
        this.store.on('load', this.clearDisableStateSaveFlag, this, {defer: 1000});
    }

    ,onActivateView: function(view) {
        this.lastState = '';
    }

    ,onStoreClear: function(store) {
        this.grid.disableStateSave = true;
    }

    ,onStoreLoad: function(store, records, successful, eOpts) {//proxy, obj, options
        //dont do anything if view not visible
        if(this.owner.getEl().isVisible(true) !== true) {
            return;
        }

        var rez = store.proxy.reader.rawData
            ,view = Ext.valueFrom(rez.view, {});

        //set flag to avoid saving grid state while restoring remote config
        this.grid.disableStateSave = true;

        if(!Ext.isEmpty(view.sort)) {// && Ext.isEmpty(this.store.sortInfo)
            var sorters = this.store.getSorters();
            sorters.suspendEvents();
            sorters.clear();

            sorters.addSort(view.sort.property, view.sort.direction);
            sorters.resumeEvents(true);
        }

        //add corresponding metadata to obj.result if DisplayColumns changed
        this.currentColumns = rez.DC || [];

        var currentState = view.type +
            Ext.util.JSON.encode(this.currentColumns) +
            (view.sort ? Ext.util.JSON.encode(view.sort) : '');

        if(this.lastState !== currentState) {
            var storeFields = this.getNewMetadata();
            store.setFields(storeFields);

            this.lastState = currentState;

            var nc = this.getNewColumns();
            this.grid.reconfigure(null, nc);
        }

        //restore or disable grouping state
        var groupFeature = this.grid.view.features[0];
        if(!Ext.isEmpty(view.group) && !Ext.isEmpty(view.group.property)) {
            store.remoteSort = false;

            if(groupFeature.disabled) {
                var menuItem = groupFeature.getMenuItem('group');//rez.group.property
                if(Ext.isEmpty(menuItem)) {
                    menuItem = {
                        parentMenu: this.grid.view.headerCt.getMenu()
                    };
                }

                if(Ext.isEmpty(menuItem.parentMenu.activeHeader)) {
                    menuItem.parentMenu.activeHeader = this.grid.getVisibleColumnManager().getHeaderByDataIndex(view.group.property);
                }

                if(!Ext.isEmpty(menuItem.parentMenu.activeHeader)) {
                    groupFeature.onGroupMenuItemClick(menuItem, eOpts);
                }
            }

            var groupDir = Ext.valueFrom(view.group.direction, 'ASC');
            if(store.getGroupDir != groupDir) {
                store.group('group', groupDir);//rez.group.property
            }

        } else if(Ext.isEmpty(view.group) && !groupFeature.disabled) {
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
        var rez = [] //Ext.apply([], this.defaultColumns)
            ,currentColumns = Ext.apply({}, this.currentColumns)
            ,i
            ,refs = {}
            ,emptyCurrentColumns = (Ext.encode(currentColumns) == '{}');

        //create column refs for convenient use
        for (i = 0; i < this.defaultColumns.length; i++) {
            refs[this.defaultColumns[i].dataIndex] = this.defaultColumns[i];
        }

        /*for (i = 0; i < rez.length; i++) {
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
        }/**/

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

                    if(Ext.isDefined(refs[key])) {
                        Ext.applyIf(column, refs[key]);
                    }

                    rez.push(column);
                }
            }
            ,this
        );

        //display name column if DC config is empty
        if(Ext.isEmpty(rez)) {
            rez = [refs.name];
        }

        /* sort columns */
        var changed = rez.length > 1;
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
