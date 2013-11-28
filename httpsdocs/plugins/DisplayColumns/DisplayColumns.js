Ext.namespace('CB.plugins');

Ext.onReady(function(){
    var plugins = CB.FolderViewGrid.prototype.plugins || [];
    plugins.push({
        ptype: 'CBPluginsDisplayColumns'
    });
    CB.FolderViewGrid.prototype.plugins = plugins;
});


CB.plugins.DisplayColumns = Ext.extend(Ext.util.Observable, {
    lastColumns: []

    ,init: function(owner) {
        this.owner = owner;
        this.grid = owner.grid;
        this.store = owner.grid.store;
        this.cm = owner.grid.getColumnModel();
        this.defaultColumns = owner.grid.defaultColumns;
        this.reader = this.store.reader;
        this.defaultMeta = Ext.apply({}, this.reader.meta);
        this.defaultFieldNames = this.extractFieldNames(this.defaultMeta.fields);
        this.proxy = this.store.proxy;
        this.proxy.on('load', this.onProxyLoad, this);
    }

    ,onProxyLoad: function(proxy, obj, options) {
        //add corresponding metadata to obj.result if DisplayColumns changed
        this.currentColumns = obj.result.DC || [];
        if(Ext.util.JSON.encode(this.lastColumns) !== Ext.util.JSON.encode(this.currentColumns)) {
            obj.result.metaData = this.getNewMetadata();
            this.lastColumns = this.currentColumns;
            this.store.loadData(obj.result);
            this.cm.setConfig(this.getNewColumns());
        }
    }

    ,getNewMetadata: function(){
        var rez = Ext.apply({}, this.defaultMeta);

        Ext.iterate(
            this.currentColumns
            ,function(key, value, obj){
                var field = {
                    name: key
                    ,title: Ext.value(value.title, 'No title')
                };
                rez.fields.push(field);
            }
            ,this
        );
        return rez;
    }

    ,getNewColumns: function(){
        var rez = Ext.apply([], this.defaultColumns);

        Ext.iterate(
            this.currentColumns
            ,function(key, value, obj){
                var column = value;
                column.dataIndex = key;
                column.header = Ext.value(column.header, column.title);
                rez.push(column);
            }
            ,this
        );
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
});

Ext.ComponentMgr.registerPlugin('CBPluginsDisplayColumns', CB.plugins.DisplayColumns);
