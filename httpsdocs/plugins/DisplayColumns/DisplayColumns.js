Ext.namespace('CB.plugins');

Ext.onReady(function(){
    var plugins = CB.browser.view.Grid.prototype.plugins || [];
    plugins.push({
        ptype: 'CBPluginsDisplayColumns'
    });
    CB.browser.view.Grid.prototype.plugins = plugins;
});


Ext.define('CB.plugins.DisplayColumns', {
    extend: 'Ext.util.Observable'
    ,alias: 'plugin.CBPluginsDisplayColumns'
    ,lastColumns: ''

    ,init: function(owner) {
        this.owner = owner;
        this.grid = owner.grid;
        this.store = owner.grid.store;
        // this.cm = owner.grid.getColumnModel();
        this.defaultColumns = owner.grid.defaultColumns;
        this.reader = this.store.proxy.reader;
        this.defaultMeta = Ext.apply({}, this.reader.meta);
        this.defaultFieldNames = this.extractFieldNames(this.defaultMeta.fields);
        this.proxy = this.store.proxy;
        this.proxy.on('load', this.onProxyLoad, this);
    }

    ,onProxyLoad: function(proxy, obj, options) {
        //add corresponding metadata to obj.result if DisplayColumns changed
        this.currentColumns = obj.result.DC || [];

        if(this.lastColumns !== Ext.util.JSON.encode(this.currentColumns)) {
            obj.result.metaData = this.getNewMetadata();
            this.lastColumns = Ext.util.JSON.encode(this.currentColumns);
            this.store.loadData(obj.result);
            var nc = this.getNewColumns();
            this.grid.reconfigure(null, nc);
            // this.cm.setConfig(nc);
        }

        if(!Ext.isEmpty(obj.result.sort)) {// && Ext.isEmpty(this.store.sortInfo)
            this.store.sortInfo = obj.result.sort;
        }
    }

    ,getNewMetadata: function(){
        var rez = Ext.apply({}, this.defaultMeta);
        var currentColumns = Ext.apply({}, this.currentColumns);

        var key, fieldData;


        for (var i = 0; i < rez.fields.length; i++) {
            fieldData = rez.fields[i];

            if(Ext.isString(rez.fields[i])) {
                key = rez.fields[i];
                fieldData = {
                    name: key
                };
            } else {
                key = rez.fields[i].name;
            }

            if(Ext.isDefined(currentColumns[key])) {
                rez.fields[i] = Ext.copyTo(fieldData, currentColumns[key], ['type', 'sortType']);
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
                rez.fields.push(field);
            }
            ,this
        );

        return rez;
    }

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
                    column.id = rez.length;
                    column.dataIndex = key;
                    column.header = Ext.valueFrom(column.header, column.title);
                    switch(column.type) {
                        case 'date':
                            column.renderer = App.customRenderers.datetime;
                            break;

                        default:
                            column.renderer = this.defaultColumnRenderer;
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
    ,defaultColumnRenderer: function (v, meta, record, row_idx, col_idx, store) {
        return record.json[this.dataIndex];
    }
});
