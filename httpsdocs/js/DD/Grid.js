Ext.namespace('CB.DD');

/**
 * Plugin for drag and drop from/to grid components in casebox
 *
 */

Ext.define('CB.DD.Grid', {
    extend: 'Ext.grid.plugin.DragDrop'
    ,alias: 'plugin.CBDDGrid'

    ,ddGroup: 'CBO'
    ,idProperty: 'id'

    /**
     * pass another ddGroup if needed
     * @param  json config
     * @return void
     */
    ,constructor: function(config){
        var idProperty = Ext.valueFrom(config.idProperty, this.idProperty);
        var defaultConfig = {

            dragZone: {

            }

            ,dropZone: {
                idProperty: idProperty
                ,onNodeEnter: this.onNodeEnter
                ,onNodeOver: this.onNodeOver
                ,onNodeOut: this.onNodeOut
                ,onNodeDrop: this.onNodeDrop
            }
        };

        if(config) {
            Ext.apply(defaultConfig, config);

            if(config.dropZoneConfig) {
                Ext.apply(defaultConfig.dropZone, config.dropZoneConfig);
            }
        }
        Ext.apply(this, defaultConfig);

        this.callParent(defaultConfig);
    }
    /**
     * init method called by the grid when initializing plugins
     *
     * In this method we set all required configurantion and listeners to the grid
     *
     * @param  Ext.grid.GridPanel owner
     * @return void
     */
    ,init: function(owner) {
        this.owner = owner;

        this.idProperty = owner.store.proxy.reader.config.idProperty;

        var cfg = {};
        if(!Ext.isDefined(this.enableDragDrop) &&
            !Ext.isDefined(this.enableDrag) &&
            !Ext.isDefined(this.enableDrop)
        ) {
            this.enableDragDrop = true;
        }

        cfg.enableDrag = this.enableDragDrop || this.enableDrag;
        cfg.enableDrop = this.enableDragDrop || this.enableDrop;

        if(cfg.enableDrag && cfg.enableDrop) {
            cfg.enableDragDrop = true;
        }

        Ext.apply(this, cfg);
        // Ext.apply(this.owner, cfg);

        // owner.on('render', this.onRender, this);
        owner.on('beforedestroy', this.onBeforeDestroy, this);

        this.callParent(arguments);
        // for general case we don't know there the grid stores its params
        // so listeners for actions on objects should be implemented by grid itself
    }

    // ,onRender: function(grid){
    //     if(this.enableDrag) {
    //         var dragZoneConfig = this.dragZoneConfig || {};
    //         Ext.apply(dragZoneConfig, {
    //             idProperty: this.idProperty
    //             ,ddGroup: this.ddGroup
    //             ,nodeToGenericData: this.nodeToGenericData
    //         });
    //         this.owner.getView().dragZone = new CB.DD.GridDragZone(this.owner, dragZoneConfig);
    //     }

    //     if(this.enableDrop) {
    //         var dropZoneConfig = this.dropZoneConfig || {};
    //         Ext.apply(dropZoneConfig, {
    //             idProperty: this.idProperty
    //             ,ddGroup: this.ddGroup
    //             ,nodeToGenericData: this.nodeToGenericData
    //         });
    //         this.owner.dropZone = new CB.DD.GridDropZone(this.owner, dropZoneConfig);
    //     }
    // }

    /**
     * unset all assigned listeners
     * @return void
     */
    ,onBeforeDestroy: function()
    {
        // this.owner.un('render', this.onRender, this);
        this.owner.un('beforedestroy', this.onBeforeDestroy, this);
    }

    /**
     * transfers grid record data to generic structured object for D&D
     * @param  record/data record record or its data
     * @return object
     */
    ,nodeToGenericData: function(record){
        if(Ext.isEmpty(record)){
            return {};
        }
        var na = record.data
                ? record.data
                : record
            ,pid = record.pid
                ? record.pid
                : null;

        var data = {
            id: na[this.idProperty]
            ,pid: pid
            ,name: na.name
            ,path: na.path
            ,template_id: na.template_id
        };

        return data;
    }

    ,onNodeEnter: function(el, source, ev, data){
        Ext.get(el).addCls('drop-target');
    }

    ,onNodeOver: function (el, source, ev, data){
        /* deny drop on:
            - node itself
            - direct parent of dragged node
            - any descendant of dragged node
        */
       var targetRecord = this.view.getRecord(el)
            ,templateId = targetRecord.data.template_id
            ,acceptChildren = CB.DB.templates.acceptChildren(templateId);

        var rez = this.dropAllowed;
        if(Ext.isEmpty(targetRecord) ||
            !data ||
            Ext.isEmpty(data.records) ||
            isNaN(data.records[0].get(this.idProperty))
        ) {
            return this.dropNotAllowed;
        }

        var sourceData = Ext.isArray(data.records)
            ? data.records
            : [data.records];
        var i = 0;

        while ((i < sourceData.length) && (rez == this.dropAllowed))  {
            if( !acceptChildren ||
                (targetRecord.data[this.idProperty] == sourceData[i].get(this.idProperty))
                || (targetRecord.data[this.idProperty] == sourceData[i].get('pid'))
            ) {
                rez = this.dropNotAllowed;
            }
            i++;
        }

        return rez;
    }

    ,onNodeOut: function(el, source, ev, data){
        Ext.get(el).removeCls('drop-target');
    }

    ,onNodeDrop: function(el, source, e, data){
        if(Ext.isElement(el)) {
            if(this.onNodeOver(el, source, e, data) == this.dropAllowed){
                var targetRecord = this.view.getRecord(el);
                if(targetRecord) {
                    var d, sourceData = [];
                    for (var i = 0; i < data.records.length; i++) {
                        d = data.records[i].data;
                        sourceData.push({
                            id: d[this.idProperty]
                            ,name: d['name']
                            ,path: d['path']
                            ,template_id: d['template_id']
                        });
                    }

                    d = targetRecord.data;
                    var targetData = {
                        id: d[this.idProperty]
                        ,name: d['name']
                        ,path: d['path']
                        ,template_id: d['template_id']
                    };
                    App.DD.execute({
                        action: e
                        ,targetData: targetData
                        ,sourceData: sourceData
                    });
                }
            }

        } else { //drop over scroller area of the grid
            var callback = this.scope
                ? this.onScrollerDragDrop.bind(this.scope)
                : this.onScrollerDragDrop;
            callback(el, source, e, data);
        }

        return true;
    }
});
