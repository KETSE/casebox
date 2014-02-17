Ext.namespace('CB.DD');
/**
 * Plugin for drag and drop from/to tree components in casebox
 * 
 */

CB.DD.Tree =  Ext.extend(Ext.util.Observable, {
    idProperty: 'id'
    ,ddGroup: 'CBO'
    /**
     * just pass the idProperty used in tree for nodes.
     * @param  json config 
     * @return void
     */
    ,constructor: function(config){
        if(config) {
            Ext.apply(this, config);
        }
    }
    /**
     * init method called by the tree when initializing plugins
     *
     * In this method we set all required configurantion and listeners to the tree
     *     
     * @param  Ext.tree.TreePanel owner
     * @return void
     */
    ,init: function(owner) {
        this.owner = owner;
        owner.on('render', this.onRender, this);
        owner.on('beforedestroy', this.onBeforeDestroy, this);
        App.on('objectsaction', this.onObjectsAction, this);
    }
    
    /**
     * apply config to tree
     * @param  Ext.tree.TreePanel tree 
     * @return void
     */
    ,onRender: function(tree){
        Ext.apply(this.owner, {
            enableDD: true
            ,dragZone: new CB.DD.TreeDragZone(this.owner, {
                idProperty: this.idProperty
                ,ddGroup: this.ddGroup
                ,nodeToGenericData: this.nodeToGenericData
            })
            ,dropZone: new CB.DD.TreeDropZone(this.owner, {
                idProperty: this.idProperty
                ,ddGroup: this.ddGroup
                ,nodeToGenericData: this.nodeToGenericData
            })
        });

    }
    
    /**
     * unset all assigned listeners
     * @return void
     */
    ,onBeforeDestroy: function()
    {
        this.owner.un('render', this.onRender, this);
        this.owner.un('beforedestroy', this.onBeforeDestroy, this);
        App.un('objectsaction', this.onObjectsAction, this);
    }
    
    /**
     * transfers tree node data to generic structured object for D&D 
     * @param  node/atributtes node node object or its attributes
     * @return object
     */
    ,nodeToGenericData: function(node){
        if(Ext.isEmpty(node)){
            return {};
        }
        na = node.attributes ? node.attributes : node;
        pid = node.parentNode
            ? node.parentNode.attributes[this.idProperty]
            : null;

        data = {
            id: na[this.idProperty]
            ,pid: pid
            ,name: na.name
            ,path: na.path
            ,template_id: na.template_id
        };
        return data;
    }

    /**
     * function used to update tree nodes for actions on abjects like create/copy/move/update/delete
     * 
     * @param  object r responce
     * @param  event e 
     * @return void
     */
    ,onObjectsAction: function(action, r, e){
        switch(action){
            case 'copy':
                    this.reloadNode(r.targetId);
                break;
            case 'move':
                if(!Ext.isEmpty(r.processedIds)){
                    // remove moved nodes
                    for (var i = 0; i < r.processedIds.length; i++) {
                        this.removeNode(r.processedIds[i]);
                    }
                    this.reloadNode(r.targetId);
                }
                break;
            case 'create':
                break;
            case 'update':
                break;
            case 'delete':
                break;
        }
    }
    
    /**
     * remove a node by its id
     * @param  int nodeId 
     * @return boolean
     */
    ,removeNode: function(nodeId){
        var rootNode = this.owner.getRootNode();
        var node = rootNode.findChild(this.idProperty, nodeId, true);
        while(node){
            node.remove(true);
            node = rootNode.findChild(this.idProperty, nodeId, true);
        }
    }
    
    /**
     * reload a node by its id
     * @param  int nodeId 
     * @return boolean
     */
    ,reloadNode: function(nodeId){
        var rootNode = this.owner.getRootNode();
        var node = (rootNode.attributes[this.idProperty] == nodeId)
            ? rootNode
            : rootNode.findChild(this.idProperty, nodeId, true);
        if(node) {
            if(node.isLoaded()) {
                node.reload();
            } else {
                node.expand();
            }
            return true;
        }
        return false;
    }
});

Ext.ComponentMgr.registerPlugin('CBDDTree', CB.DD.Tree);

/* custom tree dragZone for handling casebox D&D of objects */
CB.DD.TreeDragZone =  Ext.extend(Ext.tree.TreeDragZone, {
    idProperty: 'id'
    ,constructor: function(tree, config){
        Ext.apply(this, config || {});
        CB.DD.TreeDragZone.superclass.constructor.call(this, tree, config);
    }
    ,getDragData: function(e){
        rez = Ext.dd.Registry.getHandleFromEvent(e);
        // set generic object data from Tree node
        if(rez && rez.node) {
            rez.data = [this.nodeToGenericData(rez.node)];
        }
        return rez;
    }
});

Ext.reg('CBDDTreeDragZone', CB.DD.TreeDragZone);

/* custom tree dropZone for handling casebox D&D of objects */
CB.DD.TreeDropZone =  Ext.extend(Ext.tree.TreeDropZone, {
    idProperty: 'id'
    ,appendOnly: true
    ,constructor: function(tree, config){
        Ext.apply(this, config || {});
        CB.DD.TreeDropZone.superclass.constructor.call(this, tree, config);
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
            var sourceNode = this.tree.getRootNode().findChild(this.idProperty, sourceData[i].id);
            if( (targetData.node.attributes[this.idProperty] == sourceData[i].id)
                || (targetData.node.attributes[this.idProperty] == sourceData[i].pid)
                || targetData.node.isAncestor(sourceNode)
            ) {
                rez = this.dropNotAllowed;
            }
            i++;
        }
        return rez;
    }
    ,onNodeDrop: function(targetData, source, e, sourceData){
        
        if(this.onNodeOver(targetData, source, e, sourceData) == this.dropAllowed){
            App.DD.execute({
                action: e
                ,targetData: this.nodeToGenericData(targetData.node)
                ,sourceData: sourceData.data
            });
            return true;
        }
    }

});

Ext.reg('CBDDTreeDropZone', CB.DD.TreeDropZone);
