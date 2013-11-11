
Ext.namespace('CB');

CB.VerticalEditGridHelperTree = Ext.extend(Ext.tree.TreePanel, {
    initComponent: function(){

        Ext.apply(this, {
            loader: new Ext.tree.TreeLoader({
                // listeners: {
                //     scope: this
                //     ,beforeload: function(treeloader, node, callback) {
                //         treeloader.baseParams.path = node.getPath('nid');
                //         treeloader.baseParams.showFoldersContent = this.showFoldersContent;
                //     }
                // }
            })
            ,root: new Ext.tree.TreeNode({
                text: 'root'
                ,nid: 0
                ,expanded: true
                ,leaf: false
                ,value: {}
            })
            ,listeners:{
                scope: this
                ,beforeappend: this.onBeforeNodeAppend
            }
        });

        CB.VerticalEditGridHelperTree.superclass.initComponent.apply(this, arguments);
    }
    ,onBeforeNodeAppend: function(tree, parent, node){
        node.setId(Ext.id());
    }

    ,loadData: function (data, templateStore){
        this.data = data;
        this.templateStore = templateStore;

        this.getRootNode().removeAll(true);

        this.addNodes(this.getRootNode(), this.data);

        this.updateVisibility();

    }

    ,readValues: function ()
    {
        this.data = this.readChilds(this.getRootNode());

        return this.data;
    }

    ,readChilds: function(parentNode){
        var rez = {};
        parentNode.eachChild(
            function(node){
                var fieldName = node.attributes.templateRecord.get('name');
                if(Ext.isEmpty(rez[fieldName])) {
                    rez[fieldName] = [];
                }
                var value = node.attributes.value;
                value.childs = this.readChilds(node);
                rez[fieldName].push(this.simplifyValue(value));
            }
            ,this
        );
        for(var fieldName in rez) {
            if(rez.hasOwnProperty(fieldName)){
                if(rez[fieldName].length == 1) {
                    rez[fieldName] = rez[fieldName][0];
                }

            }
        }
        return rez;
    }

    ,simplifyValue: function(value) {
        if(Ext.isEmpty(value.info) &&
            Ext.isEmpty(value.files) &&
            isEmptyObject(value.childs)
        ) {
            return value.value;
        }
        if(Ext.isEmpty(value.info)) {
            delete value.info;
        }
        if(Ext.isEmpty(value.files)) {
            delete value.files;
        }
        if(isEmptyObject(value.childs)) {
            delete value.childs;
        }
        return value;
    }

    ,getGenericArrayDataForNodes: function(fieldData){
        var rez = [{}];
        if(Ext.isEmpty(fieldData)) {
            return rez;
        }
        if(Ext.isPrimitive(fieldData)) {
            rez[0].value = fieldData;
            return rez;
        }
        if(Ext.isDefined(fieldData.value)){
            rez[0] = fieldData;
            return rez;
        }
        if(Ext.isArray(fieldData)) {
            for (var i = 0; i < fieldData.length; i++) {
                if(Ext.isPrimitive(fieldData[i])) {
                    rez[i] = {value: fieldData[i]};
                }
                if(Ext.isDefined(fieldData[i].value)){
                    rez[0] = fieldData[i];
                    return rez;
                }
            }
        }
        return rez;
    }

    ,adjustValueToType: function(value, type){
        if(Ext.isEmpty(value)){
            return value;
        }
        switch(type){
            case 'date':
                if(Ext.isString(value)) {
                    value = Date.parseDate(value.substr(0,10), 'Y-m-d');
                }
                break;
            case 'datetime':
                if(Ext.isString(value)) {
                    value = Date.parseDate(
                        value
                        ,(value.indexOf('T') >= 0)
                            ? 'Y-m-dTH:i:s'
                            : 'Y-m-d H:i:s'
                    );
                }
                break;
        }

        return value;
    }
    ,addNodes: function(parentNode, data, beforeNode){
        var pid = parentNode.attributes.nid;

        this.templateStore.each(
            function(record) {
                if(record.get('pid') == pid) {
                    /* no check to see if we have more duplicates and have to duplicate this node */
                    fieldName = record.get('name');
                    nodeValues = this.getGenericArrayDataForNodes(data[fieldName]);
                    for (var i = 0; i < nodeValues.length; i++) {
                        node = this.addNode(parentNode, record, beforeNode);
                        nodeValues[i].value = this.adjustValueToType(nodeValues[i].value, record.get('type'));
                        node.attributes.value = nodeValues[i];
                        this.addNodes(node, nodeValues[i].childs);
                    }
                }
            }
            ,this
        );
    }
    ,addNode: function(parentNode, templateRecord, beforeNode){
        return parentNode.insertBefore(
            {
                nid: templateRecord.get('id')
                ,templateRecord: templateRecord
            }
            ,beforeNode
        );
    }

    /**
     * upate all nodes visibility
     * @return boolean  true if visibility changed for any node
     */
    ,updateVisibility: function(){
        //flag for checking if any node visibility have been updated
        var rez = false;
        this.visibilityUpdated = false;
        do{
            this.getRootNode().cascade(
                this.updateNodeVisibility
                ,this
            );
            if(this.visibilityUpdated) {
                rez = true;
            }
        } while (this.visibilityUpdated);

        return rez;
    }

    /**
     * update visibility for a single node
     * @param  TreeNode node
     * @return void
     */
    ,updateNodeVisibility: function (node) {
        // skip root node processing
        if(node == this.getRootNode()) {
            return;
        }

        // if the node isn't a subnode then it's always visible
        if(Ext.isEmpty(node.attributes.templateRecord.get('pid'))){
            if(node.attributes.visible === false) {
                this.visibilityUpdated = true;
            }
            node.attributes.visible = true;
            return true;
        }


        var r = node.attributes.templateRecord;
        var pr = node.parentNode.attributes.templateRecord;

        if(node.parentNode.attributes.visible === false) {
            if(node.attributes.visible !== false) {
                this.visibilityUpdated = true;
            }
            node.attributes.visible = false;
        } else { // if parent node is visible
            var v = ''; //dependency value
            var va = []; //dependency array value
            var parentNodeValue = node.parentNode.attributes.value.value;
            if(Ext.isDefined(r.get('cfg').dependency) && !Ext.isEmpty(r.get('cfg').dependency.pidValues)){
                v = r.get('cfg').dependency.pidValues;
                va = toNumericArray(v);
            }

            if( node.attributes.visible !== false ){
                if( ( !Ext.isEmpty(v) &&
                    !setsHaveIntersection( va, parentNodeValue) ) //if not empty pidValues specified and parent value out of pidValues then hide the field
                    || ( (r.get('cfg').thesauriId == 'dependent') && Ext.isEmpty(parentNodeValue) ) // OR if the field is dinamic and parent has no selected value
                    || ( Ext.isDefined(r.get('cfg').dependency) && Ext.isEmpty(parentNodeValue) ) // OR if the field is dinamic and parent has no selected value
                ) {
                    node.attributes.visible = false;
                    this.visibilityUpdated = true;
                }
            }else{ //when record is not visible
                if( (pr.get('tag') == 'G') || (
                    !Ext.isEmpty(parentNodeValue) && (Ext.isEmpty(v) || setsHaveIntersection( va, parentNodeValue ))
                    && ( (r.get('cfg').thesauriId !== 'dependent') ||  !Ext.isEmpty(parentNodeValue))
                    && ( Ext.isDefined(r.get('cfg').dependency) ||  !Ext.isEmpty(parentNodeValue))
                    )
                ) { //if no pidValues specified or pidValues contains the parent selected value then show the field
                    node.attributes.visible = false;
                    this.visibilityUpdated = true;
                }
            }
        }
    }

    ,queryNodeListBy: function(filterFunction){
        rez = [];
        this.getRootNode().cascade(
            function(node){
                if(filterFunction(node)) {
                    rez.push(node);
                }
            }
            ,this
        );

        return rez;
    }
    ,getNode: function(nodeId){
        return this.getRootNode().findChild('id', nodeId);
    }

    /**
     * get parent value (of field_id) for a ghiven nodeId with
     * @param  varchar nodeId
     * @param  int field_id     template field id
     * @return variant
     */
    ,getParentValue: function (nodeId, field_id){
        var node = this.getNode(nodeId);
        if(node && node.attributes.templateRecord) {
            pn = node.parentNode;
            while(pn &&
                pn.attributes.templateRecord &&
                (pn.attributes.templateRecord.get('id') != field_id)
            ) {
                pn = pn.parentNode;
            }

            if (pn &&
                pn.attributes.templateRecord &&
                (pn.attributes.templateRecord.get('id') == field_id)
            ) {
                return pn.attributes.value.value;
            }
        }

        return null;
    }

    ,resetChildValues: function(nodeId) {

        var node = this.getNode(nodeId);
        if(node && node.attributes.templateRecord) {
            node.cascade(
                function(n) {
                    var tr = n.attributes.templateRecord;
                    if( tr &&
                        n.isAncestor(node) &&
                        (
                            tr.get('cfg').thesauriId == 'dependent' ||
                            Ext.isDefined(tr.get('cfg').dependency)
                        ) &&
                        (tr.get('pid') == node.attributes.templateRecord.get('id')) &&
                        (tr.get('cfg').readOnly !==true)
                    ){
                        n.attributes.value.value = null;
                    }
                }
                ,this
            );
            this.updateVisibility();
        }
    }

    ,duplicate: function(nodeId){
        var node = this.getNode(nodeId);
        if(!node || !node.attributes.templateRecord) {
            return false;
        }


        var dn = this.addNode(node.parentNode, node.attributes.templateRecord, node.nextSibling);
        this.addNodes(dn);
        node.attributes.templateRecord.get('cfg').maxInstances--;
    }

    ,deleteDuplicate: function(nodeId){
        var node = this.getNode(nodeId);
        if(!node || !node.attributes.templateRecord) {
            return false;
        }
        node.attributes.templateRecord.get('cfg').maxInstances++;

        node.remove(true);
    }

    /**
     * check if a given node is a duplicate node (that could be duplicated)
     * @param  varchar  nodeId
     * @return boolean
     */
    ,isDuplicate: function(nodeId){
        if(this.canDuplicate(nodeId)){
            return true;
        }
        var node = this.getNode(nodeId);
        if(node) {
            var ps = node.previousSibling;
            if(ps && ps.attributes.templateRecord) {
                if(ps.attributes.templateRecord.get('id') == node.attributes.templateRecord.get('id')) {
                    return true;
                }
            } else {
                return false;
            }
        }
        return null;
    }

    /**
     * check if a given node could be duplicated (and did not exceed maxLimit)
     * @param  varchar nodeId
     * @return boolean
     */
    ,canDuplicate: function(nodeId){
        var node = this.getNode(nodeId);
        if(node && node.attributes.templateRecord) {
            return (node.attributes.templateRecord.get('cfg').maxInstances > 1);
        }
        return false;
    }

    ,getDuplicateIndex: function(nodeId){
        var index = -1;
        if(!this.isDuplicate(nodeId)) {
            return index;
        }

        var node = this.getNode(nodeId);
        if(node && node.attributes.templateRecord) {
            index = 0;
            var pn = node.previousSibling;
            while(pn &&
                pn.attributes.templateRecord &&
                pn.attributes.templateRecord.get('id') == node.attributes.templateRecord.get('id')
            ) {
                index++;
                pn = pn.previousSibling;
            }
            return index;
        }

        return index;
    }

    ,isFirstDuplicate: function(nodeId){
        if(!this.isDuplicate(nodeId)) {
            return false;
        }
        var node = this.getNode(nodeId);
        if(node && node.attributes.templateRecord) {
            var pn = node.previousSibling;
            if(pn &&
                pn.attributes.templateRecord
                && pn.attributes.templateRecord.get('id') !== node.attributes.templateRecord.get('id')
            ) {
                return true;
            }
        }
        return false;
    }
    ,isLastDuplicate: function(nodeId){
        var node = this.getNode(nodeId);
        if(node && node.attributes.templateRecord) {
            var s = node.nextSibling;
            return (
                !s ||
                !s.attributes.templateRecord ||
                (s.attributes.templateRecord.get('id') !== node.attributes.templateRecord.get('id'))
            );
        }
        return null;
    }

});

Ext.reg('CBVerticalEditGridHelperTree', CB.VerticalEditGridHelperTree);
