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
        this.idProperty = owner.store.reader.meta.idProperty
        Ext.apply(this.owner, {
            enableDragDrop: true
        });
        owner.on('render', this.onRender, this);
    }
    ,onRender: function(grid){
        this.owner.getView().dragZone = new CB.DD.GridDragZone(
            this.owner
            ,{
                idProperty: this.idProperty
                ,ddGroup: this.ddGroup
                ,nodeToGenericData: this.nodeToGenericData
            }
        )
        this.owner.dropZone = new CB.DD.GridDropZone(
            this.owner
            ,{
                idProperty: this.idProperty
                ,ddGroup: this.ddGroup
                ,nodeToGenericData: this.nodeToGenericData
            }
        )
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
        }
        return data;
    }
});

Ext.ComponentMgr.registerPlugin('CBDDGrid', CB.DD.Grid);


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

            var data = []
            records = this.grid.getSelectionModel().getSelections();
            for (var i = 0; i < records.length; i++) {
                data[i] = this.nodeToGenericData(records[i]);
            }
            rez = this.view.dragData = {
                sourceEl: sourceEl
                ,repairXY: Ext.fly(sourceEl).getXY()
                ,ddel: d
                ,data: data
            }
        }
        return rez;
    }
});

Ext.reg('CBDDGridDragZone', CB.DD.GridDragZone);

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
        };
        return rez;
    }
    ,onNodeOut: function(nodeData, source, e, data){
        Ext.get(nodeData.node).removeClass('drop-target');
    }
    ,onNodeDrop: function(targetData, source, e, sourceData){
        if(this.onNodeOver(targetData, source, e, sourceData) == this.dropAllowed){
            App.DD.execute({
                action: e
                ,targetData: this.nodeToGenericData(targetData.record)
                ,sourceData: sourceData.data
            });
            return true;
        }
    }

});

Ext.reg('CBDDGridDropZone', CB.DD.GridDropZone);