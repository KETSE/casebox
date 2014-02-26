Ext.namespace('CB.DD');

/**
 * Plugin for drag and drop from/to grid components in casebox
 *
 */

CB.DD.Grid =  Ext.extend(Ext.util.Observable, {
    ddGroup: 'CBO'

    /**
     * pass another ddGroup if needed
     * @param  json config
     * @return void
     */
    ,constructor: function(config){
        if(config) {
            Ext.apply(this, config);
        }
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
        this.idProperty = owner.store.reader.meta.idProperty;
        Ext.apply(this.owner, {
            enableDragDrop: true
        });
        owner.on('render', this.onRender, this);
        owner.on('beforedestroy', this.onBeforeDestroy, this);

        // for general case we don't know there the grid stores its params
        // so listeners for actions on objects should be implemented by grid itself
    }

    ,onRender: function(grid){
        var dragZoneConfig = this.dragZoneConfig || {};
        Ext.apply(dragZoneConfig, {
            idProperty: this.idProperty
            ,ddGroup: this.ddGroup
            ,nodeToGenericData: this.nodeToGenericData
        });
        this.owner.getView().dragZone = new CB.DD.GridDragZone(this.owner, dragZoneConfig);

        var dropZoneConfig = this.dropZoneConfig || {};
        Ext.apply(dropZoneConfig, {
            idProperty: this.idProperty
            ,ddGroup: this.ddGroup
            ,nodeToGenericData: this.nodeToGenericData
        });
        this.owner.dropZone = new CB.DD.GridDropZone(this.owner, dropZoneConfig);
    }

    /**
     * unset all assigned listeners
     * @return void
     */
    ,onBeforeDestroy: function()
    {
        this.owner.un('render', this.onRender, this);
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
        na = record.data ? record.data : record;
        pid = record.pid
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
});

Ext.ComponentMgr.registerPlugin('CBDDGrid', CB.DD.Grid);

/* custom grid dragZone for handling casebox D&D of objects */
CB.DD.GridDragZone =  Ext.extend(Ext.grid.GridDragZone, {
    idProperty: 'id'

    ,constructor: function(grid, config){
        this.grid = grid;
        this.view = grid.getView();
        Ext.apply(this, config || {});
        CB.DD.GridDragZone.superclass.constructor.call(this, grid, config);
    }
    ,getDragData: function(e){
        var rez = false;
        var sourceEl = e.getTarget(this.view.itemSelector, 10);
        if (sourceEl) {
            var d = sourceEl.cloneNode(true);
            d.id = Ext.id();

            var data = [];
            records = this.getSelections();
            for (var i = 0; i < records.length; i++) {
                data[i] = this.nodeToGenericData(records[i]);
            }
            rez = this.view.dragData = {
                sourceEl: sourceEl
                ,repairXY: Ext.fly(sourceEl).getXY()
                ,ddel: d
                ,data: data
            };
        }
        return rez;
    }
    ,getSelections: function() {
        var sm = this.grid.getSelectionModel();

        if(sm.getSelections) {
            return sm.getSelections();
        } else if(sm.getSelectedCell){
            var s = sm.getSelectedCell();
            if(s) {
                return [this.grid.store.getAt(s[0])];
            }
        }
        return [];
    }
});

Ext.reg('CBDDGridDragZone', CB.DD.GridDragZone);

/* custom grid dropZone for handling casebox D&D of objects */
CB.DD.GridDropZone =  Ext.extend(Ext.dd.DropZone, {
    idProperty: 'id'
    ,appendOnly: true

    ,constructor: function(grid, config){
        this.grid = grid;
        this.view = grid.getView();

        Ext.apply(this, config || {});
        CB.DD.GridDropZone.superclass.constructor.call(this, grid.getView().scroller, config);
    }
    ,getTargetFromEvent: function(e) {
        // check if over a row
        var t = e.getTarget(this.view.rowSelector);
        if (t) {
            var rowIndex = this.view.findRowIndex(t);
            if ((rowIndex !== false) ) {
                return {
                    node: t,
                    record: this.grid.store.getAt(rowIndex)
                };
            }
        }
        // if owner has handler for dropping in grid's free space
        // then analize for x-grid3-scroller target
        if(this.onScrollerDragDrop){
            t = e.getTarget('.x-grid3-scroller');
            if (t) {
                return { node: t};
            }
        }
    }

    ,onNodeEnter: function(nodeData, source, e, data){
        Ext.get(nodeData.node).addClass('drop-target');
    }

    ,onNodeOver: function (targetData, source, e, data){
        /* deny drop on:
            - node itself
            - direct parent of dragged node
            - any descendant of dragged node
        */
        var rez = this.dropAllowed;
        if(!Ext.isDefined(data.data) || !targetData.record) {
            return this.dropNotAllowed;
        }

        var sourceData = Ext.isArray(data.data)
            ? data.data
            : [data.data];
        var i = 0;
        while ((i < sourceData.length) && (rez == this.dropAllowed))  {
            if( (targetData.record.data[this.idProperty] == sourceData[i].id)
                || (targetData.record.data[this.idProperty] == sourceData[i].pid)
            ) {
                rez = this.dropNotAllowed;
            }
            i++;
        }
        return rez;
    }

    ,onNodeOut: function(nodeData, source, e, data){
        Ext.get(nodeData.node).removeClass('drop-target');
    }

    ,onNodeDrop: function(targetData, source, e, sourceData){
        if(this.onNodeOver(targetData, source, e, sourceData) == this.dropAllowed){
            if(targetData.record) {
                App.DD.execute({
                    action: e
                    ,targetData: this.nodeToGenericData(targetData.record)
                    ,sourceData: sourceData.data
                });
            } else { //drop over scroller area of the grid
                callback = this.scope
                    ? this.onScrollerDragDrop.createDelegate(this.scope)
                    : this.onScrollerDragDrop;
                callback(targetData, source, e, sourceData);
            }
            return true;
        }
    }

});

Ext.ComponentMgr.registerPlugin('CBDDGridDropZone', CB.DD.GridDropZone);
