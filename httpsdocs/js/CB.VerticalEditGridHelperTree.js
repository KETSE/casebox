
Ext.namespace('CB');

Ext.define('CB.VerticalEditGridHelperTree', {
    extend: 'Ext.tree.TreePanel'

    ,initComponent: function(){

        this.store = Ext.create('Ext.data.TreeStore', {
            root: {
                text: 'root'
                ,nid: 0
                ,expanded: true
                ,leaf: false
                ,value: {}
            }
            ,proxy: {
                type: 'memory'
                ,paramsAsHash: true
            }
        });

        Ext.apply(this, {
            listeners:{
                scope: this
                ,beforeitemappend: this.onBeforeNodeAppend
            }
        });

        CB.VerticalEditGridHelperTree.superclass.initComponent.apply(this, arguments);
    }
    ,onBeforeNodeAppend: function(parent, node){
        node.set('id', Ext.id());
    }

    ,loadData: function (data, templateStore){
        this.data = data;
        this.templateStore = templateStore;

        var rn = this.getRootNode();
        rn.removeAll();

        this.addNodes(rn, this.data);

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
                var fieldName = node.data.templateRecord.get('name');
                var value = node.data.value;

                switch (node.data.templateRecord.get('type')) {
                    case 'datetime':
                        if(Ext.isDate(value.value)) {
                            value.value = date_local_to_ISO_string(value.value);
                        }
                        break;
                    case 'date':
                        if(Ext.isDate(value.value)) {
                            value.value = dateToDateString(value.value);
                        }
                        break;
                }
                value.childs = this.readChilds(node);
                value = this.simplifyValue(value);

                if(Ext.isEmpty(value) || (Ext.isObject(value) && isEmptyObject(value))) {
                } else {
                    if(Ext.isEmpty(rez[fieldName])) {
                        rez[fieldName] = [];
                    }

                    rez[fieldName].push(value);
                }
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
            (Ext.isEmpty(value.cond) || Ext.isEmpty(value.value))&&
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
        if(Ext.isDefined(value.cond) && Ext.isEmpty(value.value)) {
            delete value.cond;
        }

        return value;
    }

    ,getGenericArrayDataForNodes: function(fieldData){
        var rez = [{}];
        if(Ext.isEmpty(fieldData)) {
            return rez;
        }
        if(Ext.isPrimitive(fieldData) || Ext.isDate(fieldData)) {
            rez[0].value = fieldData;
            return rez;
        }

        if(Ext.isDefined(fieldData.value) ||
            Ext.isDefined(fieldData.info) ||
            Ext.isDefined(fieldData.childs) ||
            Ext.isDefined(fieldData.cond)
        ){
            rez[0] = fieldData;
            return rez;
        }

        if(Ext.isArray(fieldData)) {
            for (var i = 0; i < fieldData.length; i++) {
                if(Ext.isPrimitive(fieldData[i])) {
                    rez[i] = {value: fieldData[i]};
                }
                if(Ext.isDefined(fieldData[i].value) ||
                    Ext.isDefined(fieldData[i].info) ||
                    Ext.isDefined(fieldData[i].childs) ||
                    Ext.isDefined(fieldData[i].cond)
                ){
                    rez[i] = fieldData[i];
                }
            }
        } else {
            rez[0].value = Ext.util.JSON.encode(fieldData);
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
                    value = Ext.Date.parse(value.substr(0,10), 'Y-m-d');
                }
                break;
            case 'datetime':
                value = date_ISO_to_local_date(value);
                break;
        }
        return value;
    }

    ,addNodes: function(parentNode, data, beforeNode){
        var pid = parentNode.data.nid;
        data = data || {};
        if(Ext.isEmpty(this.templateStore)) {
            return;
        }
        this.templateStore.each(
            function(record) {
                if(record.get('pid') == pid) {
                    /* no check to see if we have more duplicates and have to duplicate this node */
                    var fieldName = record.get('name');
                    var nodeValues = this.getGenericArrayDataForNodes(data[fieldName]);

                    //set default values for new objects
                    if(Ext.isEmpty(nodeValues[0].value) &&
                        this.newItem &&
                        !Ext.isEmpty(record.get('cfg').value)
                    ) {
                        var v = record.get('cfg').value;
                        if(v == 'now') {
                            v = new Date();
                        }
                        nodeValues[0].value = v;
                    }
                    //set default condition for new objects
                    if(Ext.isEmpty(nodeValues[0].cond) &&
                        this.newItem &&
                        !Ext.isEmpty(record.get('cfg').cond)
                    ) {
                        nodeValues[0].cond = record.get('cfg').cond;
                    }

                    for (var i = 0; i < nodeValues.length; i++) {
                        var node = this.addNode(parentNode, record, beforeNode);
                        nodeValues[i].value = this.adjustValueToType(nodeValues[i].value, record.get('type'));
                        node.data.value = nodeValues[i];
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
                ,value: {}
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
        do{
            this.visibilityUpdated = false;
            this.getRootNode().cascadeBy({
                before: this.updateNodeVisibility
                ,scope: this
            });
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

        if(Ext.isEmpty(node.data.templateRecord.get('pid'))){
            if(node.data.visible === false) {
                this.visibilityUpdated = true;
            }
            node.data.visible = true;
            return true;
        }


        var r = node.data.templateRecord;
        var pr = node.parentNode.data.templateRecord;
        if(node.parentNode.data.visible === false) {
            if(node.data.visible !== false) {
                this.visibilityUpdated = true;
                node.data.visible = false;
            }
        } else { // if parent node is visible
            var v = ''; //dependency value
            var va = []; //dependency array value
            var parentNodeValue = node.parentNode.data.value.value;
            if(Ext.isDefined(r.get('cfg').dependency) && !Ext.isEmpty(r.get('cfg').dependency.pidValues)){
                v = r.get('cfg').dependency.pidValues;
                va = toNumericArray(v);
            }

            if( node.data.visible !== false ){
                if( ( !Ext.isEmpty(v) &&
                    !setsHaveIntersection( va, parentNodeValue) ) //if not empty pidValues specified and parent value out of pidValues then hide the field
                    || ( (r.get('cfg').thesauriId == 'dependent') && Ext.isEmpty(parentNodeValue) ) // OR if the field is dinamic and parent has no selected value
                    || ( (r.get('cfg').scope == 'variable') && Ext.isEmpty(parentNodeValue) ) // OR if the field is dinamic and parent has no selected value
                    || ( Ext.isDefined(r.get('cfg').dependency) && Ext.isEmpty(parentNodeValue) && !Ext.isEmpty(va) ) // OR if the field is dinamic and parent has no selected value
                ) {
                    node.data.visible = false;
                    this.visibilityUpdated = true;
                }
            }else{ //when record is not visible
                if( (pr &&
                        (pr.get('type') == 'G') &&
                        (pr.get('type') == 'G')
                        // (node.parentNode.data.visible !==
                    ) || (
                    !Ext.isEmpty(parentNodeValue) && (Ext.isEmpty(v) || setsHaveIntersection( va, parentNodeValue ))
                    && ( (r.get('cfg').thesauriId !== 'dependent') ||  !Ext.isEmpty(parentNodeValue))
                    && ( (r.get('cfg').scope !== 'variable') ||  !Ext.isEmpty(parentNodeValue))
                    && ( Ext.isDefined(r.get('cfg').dependency) ||  !Ext.isEmpty(parentNodeValue))
                    )
                ) { //if no pidValues specified or pidValues contains the parent selected value then show the field
                    node.data.visible = true;
                    this.visibilityUpdated = true;
                }
            }
        }
    }

    ,queryNodeListBy: function(filterFunction){
        var rez = [];
        this.getRootNode().cascadeBy({
            before: function(node){
                if(filterFunction(node)) {
                    rez.push(node);
                }
            }
            ,scope: this
        });

        return rez;
    }

    ,getNode: function(nodeId){
        return this.getRootNode().findChild('id', nodeId, true);
    }

    ,getNodesByFieldName: function(fieldName){
        return this.queryNodeListBy(
            function(n) {
                return (
                    n.data.templateRecord &&
                    (n.data.templateRecord.get('name') == fieldName)
                );
            }
        );
    }

    /**
     * set value for first found node in tree wich has given name
     * (i.e. duplications are not analyzed)
     *
     * TODO: review for duplicated fields
     *
     * @param varchar fieldName
     * @param variant value
     *
     * @return treeNode | null  modified node
     */

    ,setFieldValue: function(fieldName, value) {
        var v, rez = null;

        if(Ext.isPrimitive(value) || Ext.isEmpty(value)) {
            v = {value: value};
        } else {
            if(Ext.isDefined(value.value)) {
                v = value;
            } else {
                v = {
                    value: value
                };
            }
        }

        this.getRootNode().cascadeBy({
            before: function(node) {
                if(node.data.templateRecord && (node.data.templateRecord.get('name') == fieldName)) {
                    node.data.value = v;
                    rez = node;
                    return false;
                }
            }
            ,scope: this
        });

        return rez;
    }

    /**
     * get parent value (of field_id) for a ghiven nodeId with
     * @param  varchar nodeId
     * @param  int field_id     template field id
     * @return variant
     */
    ,getParentValue: function (nodeId, field_id){
        var node = this.getNode(nodeId);
        if(node && node.data.templateRecord) {
            pn = node.parentNode;
            while(pn &&
                pn.data.templateRecord &&
                (pn.data.templateRecord.get('id') != field_id)
            ) {
                pn = pn.parentNode;
            }

            if (pn &&
                pn.data.templateRecord &&
                (pn.data.templateRecord.get('id') == field_id)
            ) {
                return pn.data.value.value;
            }
        }

        return null;
    }

    ,resetChildValues: function(nodeId) {

        var node = this.getNode(nodeId);
        if(node && node.data.templateRecord) {
            node.cascadeBy({
                before: function(n) {
                    var tr = n.data.templateRecord
                        ,cfg = tr.get('cfg');
                    if( tr &&
                        n.isAncestor(node) &&
                        (
                            cfg.thesauriId == 'dependent' ||
                            Ext.isDefined(cfg.dependency)
                        ) &&
                        (tr.get('pid') == node.data.templateRecord.get('id')) &&
                        (cfg.readOnly !==true) &&
                        (cfg.type == '_objects') //resetting only object fields
                    ){
                        n.data.value.value = null;
                    }
                }
                ,scope: this
            });

            this.updateVisibility();
        }
    }

    ,duplicate: function(nodeId){
        var node = this.getNode(nodeId);
        if(!node || !node.data.templateRecord) {
            return false;
        }


        var dn = this.addNode(node.parentNode, node.data.templateRecord, node.nextSibling);
        this.addNodes(dn);
        node.data.templateRecord.get('cfg').maxInstances--;
    }

    ,deleteDuplicate: function(nodeId){
        var node = this.getNode(nodeId);
        if(!node || !node.data.templateRecord) {
            return false;
        }
        node.data.templateRecord.get('cfg').maxInstances++;

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
            if(ps && ps.data.templateRecord) {
                if(ps.data.templateRecord.get('id') == node.data.templateRecord.get('id')) {
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
        if(node && node.data.templateRecord) {
            return (node.data.templateRecord.get('cfg').maxInstances > 1);
        }
        return false;
    }

    ,getDuplicateIndex: function(nodeId){
        var index = -1;
        if(!this.isDuplicate(nodeId)) {
            return index;
        }

        var node = this.getNode(nodeId);
        if(node && node.data.templateRecord) {
            index = 0;
            var pn = node.previousSibling;
            while(pn &&
                pn.data.templateRecord &&
                pn.data.templateRecord.get('id') == node.data.templateRecord.get('id')
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
        if(node && node.data.templateRecord) {
            var pn = node.previousSibling;
            if(pn &&
                pn.data.templateRecord
                && pn.data.templateRecord.get('id') !== node.data.templateRecord.get('id')
            ) {
                return true;
            }
        }
        return false;
    }

    ,isLastDuplicate: function(nodeId){
        var node = this.getNode(nodeId);
        if(node && node.data.templateRecord) {
            var s = node.nextSibling;
            return (
                !s ||
                !s.data.templateRecord ||
                (s.data.templateRecord.get('id') !== node.data.templateRecord.get('id'))
            );
        }
        return null;
    }

});
