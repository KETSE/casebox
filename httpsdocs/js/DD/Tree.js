Ext.namespace('CB.DD');
/**
 * Plugin for drag and drop from/to tree components in casebox
 *
 */

Ext.define('CB.DD.Tree', {
    extend: 'Ext.tree.plugin.TreeViewDragDrop'
    ,alias: 'plugin.CBDDTree'

    ,idProperty: 'id'
    ,enableDrag: true
    ,enableDrop: true
    ,appendOnly: true
    ,containerScroll: true
    ,ddGroup: 'CBO'
    // ,displayField: 'name'

    /**
     * just pass the idProperty used in tree for nodes.
     * @param  json config
     * @return void
     */
    ,constructor: function(config){
        var idProperty = Ext.valueFrom(config.idProperty, this.idProperty);
        var defaultConfig = {
            dragZone: {
                idProperty: idProperty
                ,onBeforeDrag: this.onBeforeDrag
            }
            ,dropZone: {
                idProperty: idProperty

                ,dropCopy: 'drag-drop-copy'
                ,dropMove: 'drag-drop-move'
                ,dropShortcut: 'drag-drop-shortcut'

                ,onNodeOver: this.onNodeOver
                ,onNodeDrop: this.onNodeDrop
                ,getActionFromEvent: this.getActionFromEvent
            }
        };

        if(config) {
            Ext.apply(defaultConfig, config);
        }

        Ext.apply(this, defaultConfig);

        this.callParent(defaultConfig);
    }
    /**
     * init method called by the tree when initializing plugins
     *
     * In this method we set all required configurantion and listeners to the tree
     *
     * @param  Ext.tree.TreePanel owner
     * @return void
     */
    ,init: function(treeView) {
        this.treeView = treeView;

        treeView.on('beforedestroy', this.onBeforeDestroy, this);
        App.on('objectsaction', this.onObjectsAction, this);

        this.callParent(arguments);
    }

    /**
     * unset all assigned listeners
     * @return void
     */
    ,onBeforeDestroy: function()
    {
        this.treeView.un('beforedestroy', this.onBeforeDestroy, this);
        App.un('objectsaction', this.onObjectsAction, this);
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

    ,onBeforeDrag: function(data, e){
        if(Ext.isEmpty(data.records)) {
            return;
        }

        return (data.records[0].data.system != 1);
    }

    ,getActionFromEvent: function(ev) {
        var rez = (ev.ctrlKey || ev.altKey || ev.shiftKey)
            ? App.DD.detectActionFromEvent(ev)
            : Ext.valueFrom(this.defaultAction, 'move');

        return rez;
    }

    ,onNodeOver: function (node, dragZone, e, data){
        /* deny drop on:
            - node itself
            - direct parent of dragged node
            - any descendant of dragged node
        */

        var action = this.getActionFromEvent(e)
            ,rez = this['drop' + action.charAt(0).toUpperCase() + action.slice(1)];

        var i = 0;

        var targetRecord = this.view.getRecord(node)
            ,templateId = targetRecord.data.template_id
            ,acceptChildren = CB.DB.templates.acceptChildren(templateId);

        while ((i < data.records.length) && (rez != this.dropNotAllowed))  {
            var r = data.records[i];

            if(!acceptChildren ||
                isNaN(r.data[this.idProperty]) ||
                isNaN(targetRecord.data[this.idProperty]) ||
                (targetRecord.data[this.idProperty] == r.data[this.idProperty]) ||
                (targetRecord.data[this.idProperty] == r.data.pid) ||
                targetRecord.isAncestor(r)
            ) {
                rez = this.dropNotAllowed;
            }
            i++;
        }

        return rez;
    }

    ,onNodeDrop: function(node, dragZone, e, data){//targetData, source, e, sourceData
        if(this.onNodeOver(node, dragZone, e, data) != this.dropNotAllowed){
            var d, sourceData = [];

            d = this.view.getRecord(node).data;
            var targetData = {
                id: d[this.idProperty]
                ,name: d['name']
                ,path: d['path']
                ,template_id: d['template_id']
            };

            for (var i = 0; i < data.records.length; i++) {
                if(data.records[i].collapse) {
                    data.records[i].collapse();
                }

                d = data.records[i].data;
                sourceData.push({
                    id: d[this.idProperty]
                    ,name: d['name']
                    ,path: d['path']
                    ,template_id: d['template_id']
                });
            }

            App.DD.execute({
                action: e
                ,targetData: targetData
                ,sourceData: sourceData
            });

            return true;
        }
    }

    /**
     * remove a node by its id
     * @param  int nodeId
     * @return boolean
     */
    ,removeNode: function(nodeId){
        var st = this.treeView.ownerGrid.store;
        var recs = st.query(this.idProperty, nodeId, false, false, true);

        if(recs.getCount() > 0) {
            for (var i = 0; i < recs.getCount(); i++) {
                st.remove(recs.getAt(i));
            }
        }
    }

    /**
     * reload a node by its id
     * @param  int nodeId
     * @return boolean
     */
    ,reloadNode: function(nodeId){
        var st = this.treeView.ownerGrid.store;
        var recs = st.query(this.idProperty, nodeId, false, false, true);

        if(Ext.isEmpty(recs) || (recs.getCount() < 1)) {
            return false;
        }

        var node = recs.getAt(0);

        if(node && !node.isExpanded()) {
            node.expand();
        } else {
            st.reload({
                node: node
            });
        }
    }
});
