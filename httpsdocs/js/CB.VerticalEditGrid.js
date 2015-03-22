Ext.namespace('CB');

Ext.define('CB.VerticalEditGrid', {
    extend: 'Ext.grid.GridPanel'
    ,alias: [
        'CBVerticalEditGrid'
        ,'widget.CBVerticalEditGrid'
    ]

    ,border: false
    ,root: 'data'
    ,cls: 'spacy-rows edit-grid'
    ,scrollable: true
    ,autoHeight: true
    ,plugins: []

    ,initComponent: function() {

        // define helperTree if owner does not have already defined one
        var parentWindow = this.getBubbleTarget();
        if(parentWindow.helperTree) {
            this.helperTree = parentWindow.helperTree;
        } else {
            this.helperTree = new CB.VerticalEditGridHelperTree();
        }

        this.initRenderers();
        this.initColumns();

        var viewCfg = {
            autoFill: false
            ,deferInitialRefresh: false
            ,stripeRows: true
            ,getRowClass: function( record, index, rowParams, store ){
                var rez = '';
                if(record.get('type') == 'H'){
                    rez = 'group-titles-colbg';
                    var node = this.grid.helperTree.getNode(record.get('id'));
                    if(node && !Ext.isEmpty(node.data.templateRecord.get('cfg').css)){
                        rez += ' ' + node.data.templateRecord.get('cfg').css;
                    }
                }
                return rez;
            }
            ,plugins: [{
                ptype: 'CBDDGrid'
                ,enableDrop: true
                ,dropZoneConfig:  {
                    onNodeOver: this.onNodeDragOver.bind(this)
                    ,onNodeDrop: this.onNodeDrop.bind(this)
                }
            }]

        };
        if(this.viewConfig) {
            Ext.apply(viewCfg, this.viewConfig);
        }

        var plugins = Ext.apply([], Ext.valueFrom(this.plugins, []));
        plugins.push(
            {
                ptype: 'cellediting'
                ,clicksToEdit: 1
                ,listeners: {
                    scope: this
                    ,beforeedit: this.onBeforeEditProperty
                    ,edit: this.onAfterEditProperty
                }
            }
        );

        Ext.apply(this, {
            store:  new Ext.data.JsonStore({
                model: 'EditGridRecord'
                ,proxy: {
                    type: 'memory'
                    ,reader: {
                        type: 'json'
                        ,idProperty: 'id'
                        ,messageProperty: 'msg'
                    }
                }
                ,listeners: {
                    scope: this
                    ,update: function(store, record, operation) {
                        if(operation != Ext.data.Record.EDIT) {
                            return;
                        }
                        var node = this.helperTree.getNode(record.get('id'));
                        node.data.value['value'] = record.get('value');
                        node.data.value['info'] = record.get('info');
                        node.data.value['cond'] = record.get('cond');
                    }
                }
            })
            ,columns: Ext.apply([], this.gridColumns) //leave default column definitions intact
            ,forceFit: true
            ,selType: 'cellmodel'
            ,header: false
            ,listeners: {
                scope: this
                ,keypress:  function(e){
                    if( (e.getKey() == e.ENTER) && (!e.hasModifier())) {
                        this.onFieldTitleDblClick();
                    }
                }
                ,celldblclick:  this.onFieldTitleDblClick
                ,cellclick:  this.onCellClick
                ,cellcontextmenu: this.onPopupMenu
            }
            ,stateful: true
            ,stateId: Ext.valueFrom(this.stateId, 'veg')//vertical edit grid
            ,viewConfig: viewCfg
            ,editors: {
                iconcombo: function(){
                    return new Ext.form.ComboBox({
                        editable: true
                        ,name: 'iconCls'
                        ,hiddenName: 'iconCls'
                        ,tpl: '<tpl for="."><div class="x-boundlist-item icon-padding16 {name}">{name}</div></tpl>'
                        ,store: CB.DB.templatesIconSet
                        ,valueField: 'name'
                        ,displayField: 'name'
                        ,iconClsField: 'name'
                        ,triggerAction: 'all'
                        ,queryMode: 'local'
                    });
                }
            }

            ,plugins: plugins
        });


        this.enableBubble(['change', 'fileupload', 'filedownload', 'filesdelete', 'loaded', 'saveobject']);
        this.callParent(arguments);
    }

    ,initRenderers: function () {
        this.renderers = {
            iconcombo: App.customRenderers.iconcombo

            ,H: function(){ return '';}

            ,title: function(v, meta, record, row_idx, col_idx, store){
                var id = record.get('id');
                var n = this.helperTree.getNode(id);

                if(Ext.isString(v)) {
                    v = Ext.util.Format.htmlEncode(v);
                }

                // temporary workaround for not found nodes
                if(!n) {
                    return v;
                }

                var tr = n.data.templateRecord;

                if(tr.get('type') == 'H'){
                    meta.css ='vgh';
                } else {
                    meta.css = 'bgcLG vaT';
                    meta.style = 'margin-left: ' + (n.getDepth()-1)+'0px';
                    if(tr.get('cfg').readOnly === true) {
                        meta.css += ' cG';
                    }
                }

                if(!Ext.isEmpty(tr.get('cfg').hint)) {
                    meta.tdAttr = ' title="'+tr.get('cfg').hint+'"';
                }

                /* setting icon for duplicate fields /**/
                if(this.helperTree.isDuplicate(id)){
                    //show duplicate index
                    // if last (and not exsceeded) then show + icon
                    if(this.helperTree.canDuplicate(id) && this.helperTree.isLastDuplicate(id)) {
                        v = '<img name="add_duplicate" title="'+L.addDuplicateField+'" class="fr duplicate-plus" src="'+Ext.BLANK_IMAGE_URL + '" / >' + v;
                    } else {
                        idx = this.helperTree.getDuplicateIndex(id) +1;
                        v = '<img title="' + L.duplicate + ' ' + idx +
                            '" class="fr vc' + idx + '" src="' + Ext.BLANK_IMAGE_URL + '" / >' + v;
                    }
                }

                return v;
            }

            ,value: function(v, meta, record, row_idx, col_idx, store){
                var n = this.helperTree.getNode(record.get('id'));

                if(Ext.isString(v)
                    // && (Ext.util.Format.stripTags(v) !== v)
                ) {
                    v = Ext.util.Format.htmlEncode(v);
                }

                // temporary workaround for not found nodes
                if(!n) {
                    return v;
                }
                var tr = n.data.templateRecord;

                //check validation field
                if (record.get('valid') === false) {
                    meta.css = ' x-form-invalid-field-default';
                    meta.tdAttr = 'data-errorqtip="<ul class=\'x-list-plain\'><li>' + Ext.form.field.Base.prototype.invalidText + '</li></ul>"';
                } else {
                    //Check required field
                    if(tr.get('cfg').required && Ext.isEmpty(v)) {
                        meta.css = ' x-form-invalid-field-default';
                        meta.tdAttr = 'data-errorqtip="<ul class=\'x-list-plain\'><li>' + Ext.form.TextField.prototype.blankText + '</li></ul>"';
                    } else {
                        // Value is valid
                        meta.css = '';
                        meta.tdAttr = 'data-errorqtip=""';
                    }
                }

                if(this.renderers && this.renderers[tr.get('type')]) {
                    return this.renderers[tr.get('type')](v, this);
                }
                if(!Ext.isEmpty(tr.get('cfg').height)) {
                    meta.style += 'min-height:' + tr.get('cfg').height + 'px';
                }

                var renderer = App.getCustomRenderer(tr.get('type'));

                if(Ext.isEmpty(renderer)) {
                    return v;
                }

                //set field config into meta so that renderers could access necesary params
                meta.fieldConfig = tr.get('cfg');

                return renderer(v, meta, record, row_idx, col_idx, store, this);
            }

        };
    }

    ,initColumns: function() {

        this.gridColumns = [
            {
                header: L.Property
                ,sortable: false
                ,dataIndex: 'title'
                ,stateId: 'title'
                ,editable: false
                ,scope: this
                ,renderer: this.renderers.title
            },{
                header: L.Value
                ,itemId: 'value'
                ,sortable: false
                ,dataIndex: 'value'
                ,stateId: 'value'
                ,editor: new Ext.form.TextField()
                ,scope: this
                ,resizable: true
                ,renderer: this.renderers.value
            },{
                header: L.Additionally
                ,sortable: false
                ,dataIndex: 'info'
                ,stateId: 'info'
                ,editor: new Ext.form.TextField()
                ,hideable: false
            }
        ];
    }

    ,onNodeDragOver: function (targetEl, source, e, data){
        var rez = source.dropNotAllowed;
        var record = this.view.getRecord(targetEl);
        var recs = data.records;

        if(Ext.isEmpty(record) ||
            Ext.isEmpty(recs) ||
            isNaN(Ext.Number.from(recs[0].data.nid, recs[0].data.id))
        ) {
            return rez;
        }

        rez = (record.get('type') == '_objects')
            ? source.dropAllowed
            : source.dropNotAllowed;

        return rez;
    }

    ,onNodeDrop: function(targetEl, source, e, sourceData){
        if(this.onNodeDragOver(targetEl, source, e, sourceData) == source.dropAllowed){
            var record = this.view.getRecord(targetEl)
                ,recs = sourceData.records;
            if(record) {
                var bt = this.view.grid.getBubbleTarget();
                var node = this.helperTree.getNode(record.get('id'));
                var tr = node.data.templateRecord;
                var oldValue = node.data.value.value;
                var v = toNumericArray(oldValue);

                var id, idx = null;

                for (var i = 0; i < recs.length; i++) {
                    id = Ext.Number.from(recs[i].data.nid, recs[i].data.id);
                    idx = v.indexOf(id);
                    if(idx >= 0) {
                        v.splice(idx, 1);
                    } else {
                        v.push(id);
                        if(bt.objectsStore) {
                            bt.objectsStore.checkRecordExistance(recs[i].data);
                        }
                    }
                }
                var newValue = v.join(',');

                record.set('value', newValue);
                this.fireEvent('change', tr.get('name'), newValue, oldValue);
            }
            return true;
        }
        return false;
    }

    ,onCellClick: function( g, td, cellIndex, record, tr, rowIndex, e, eOpts){//g, r, c, e
        var el = e.getTarget();
        if(el) {
            switch(el.name){
                case 'add_duplicate':
                    this.onDuplicateFieldClick();
                    break;
            }
        }
    }

    ,onPopupMenu: function(gridView, el, colIndex, record, rowEl, rowIndex, ev, eOpts){
        ev.preventDefault();
        switch(this.columns[colIndex].dataIndex){
            case 'title':
                this.showTitlePopupMenu(this, rowIndex, colIndex, ev);
                break;
        }
    }

    ,showTitlePopupMenu: function(grid, rowIndex, cellIndex, e){
        r = grid.getStore().getAt(rowIndex);
        this.popupForRow = rowIndex;
        if(!this.titlePopupMenu) this.titlePopupMenu = new Ext.menu.Menu({
            items: [
                {
                    text: L.addDuplicateField
                    ,scope: this
                    ,handler: this.onDuplicateFieldClick
                },{
                    text: L.delDuplicateField
                    ,scope: this
                    ,handler: this.onDeleteDuplicateFieldClick
                }
            ]
        });
        this.titlePopupMenu.items.getAt(0).setDisabled(!this.helperTree.canDuplicate(r.get('id')));
        this.titlePopupMenu.items.getAt(1).setDisabled(this.helperTree.isFirstDuplicate(r.get('id')));
        this.titlePopupMenu.showAt(e.getXY());
    }

    ,onFieldTitleDblClick: function(gridView, td, cellIndex, record, tr, rowIndex, e, eOpts){
        var sm = this.getSelectionModel();

        var fieldName = this.columns[cellIndex].dataIndex;

        if(fieldName == 'title'){
            this.editingPlugin.startEdit(record, 1);//begin field edit
        }
    }

    ,getBubbleTarget: function(){
        if(!this.parentWindow){
            this.parentWindow = this.findParentByType('CBGenericForm') || this.refOwner;
        }
        return this.parentWindow;
    }

    ,reload: function(){
        // initialization
        this.data = {};
        this.newItem = true;
        var pw = this.getBubbleTarget(); //parent window

        if(Ext.isDefined(pw.data)) {
            this.newItem = isNaN(pw.data.id);
            if(Ext.isDefined(pw.data[this.root])) {
                this.data = pw.data[this.root];
            }
        }
        //if not specified template_id directly to grid then try to look in owners data
        this.template_id = Ext.valueFrom(pw.data.template_id, this.template_id);
        if(isNaN(this.template_id)) {
            return Ext.Msg.alert('Error', 'No template id specified in data for "' + pw.title + '" window.');
        }
        this.template_id = parseInt(this.template_id, 10);

        this.templateStore = CB.DB['template' + this.template_id];

        var idx = CB.DB.templates.findExact('id', this.template_id);
        if(idx >= 0) {
            // var cm = this.getColumnModel();
            var tc = CB.DB.templates.getAt(idx).get('cfg');//template config

            var infoCol = this.headerCt.child('[dataIndex="info"]'); //cm.findColumnIndex('info');
            var colRequired = (
                (tc.infoColumn === true) ||
                (
                    (Ext.isEmpty(infoCol)) &&
                    (!Ext.isEmpty(App.config.template_info_column))
                )
            );

            var newConfig = Ext.apply([], this.gridColumns);

            if(!Ext.isEmpty(infoCol) &&  !colRequired) {
                if(!colRequired) {
                    newConfig.pop();
                }
                // newConfig.push({
                //     header: ''
                //     ,dataIndex: 'id'
                //     ,hideable: false
                //     ,width: 3
                //     ,resizable: false
                //     ,renderer: Ext.emptyFn
                // });

                // cm.setConfig(newConfig);
                this.reconfigure(this.store, newConfig);
                // var el = this.getEl();
                // if(el && el.isVisible(true)) {
                //     this.getView().refresh(true);
                // }
            }
        }
        // if parent have a helperTree then it is responsible for helper reload
        if(!pw.helperTree) {
            this.helperTree.newItem = this.newItem;
            this.helperTree.loadData(this.data, this.templateStore);
        }

        this.syncRecordsWithHelper();

        this.fireEvent('loaded', this);
    }

    ,syncRecordsWithHelper: function(){
        if(!this.store) {
            return;
        }

        var nodesList = this.helperTree.queryNodeListBy(this.helperNodesFilter.bind(this));

        if(this.store && this.store.suspendEvents) {
            this.store.suspendEvents(true);
        }

        this.store.removeAll(false);

        var records = [];
        for (var i = 0; i < nodesList.length; i++) {
            var attr = nodesList[i].data;
            var r  = attr.templateRecord;

            records.push(
                Ext.create(
                    this.store.getModel().getName()
                    ,{
                        id: attr.id
                        ,title: r.get('title')
                        ,readonly: ((r.get('type') == 'H') || (r.get('cfg').readOnly == 1))
                        ,value: Ext.isNumeric(attr.value.value)
                            ? parseInt(attr.value.value, 10)
                            : attr.value.value
                        ,info: attr.value.info
                        ,type: r.get('type')
                        ,cond: attr.value.cond
                        ,valid: attr.valid
                    }
                )
            );
        }
        this.store.resumeEvents();
        this.store.add(records);
    }

    ,helperNodesFilter: function(node){
        var r = node.data.templateRecord;
        //skip check for root node
        if(Ext.isEmpty(r)) {
            return false;
        }

        return (
            (r.get('type') != 'G')
            &&
            (
                (r.get('cfg').showIn != 'top') ||
                ((r.get('cfg').showIn == 'top') &&
                    this.includeTopFields
                )
            ) &&
            (r.get('cfg').showIn != 'tabsheet') &&
            (node.data.visible !== false)
        );
    }

    ,readValues: function(){
        if(!Ext.isDefined(this.data)) {
            this.data = {};
        }

        this.data = this.helperTree.readValues();

        w = this.getBubbleTarget();
        if(Ext.isDefined(w.data)) {
            w.data[this.root] = this.data;
        }
    }

    ,onBeforeEditProperty: function(editor, context, eOpts){//grid, record, field, value, row, column, cancel
        var node = this.helperTree.getNode(context.record.get('id'));
        // temporary workaround for not found nodes
        if(!node) {
            return false;
        }
        var tr = node.data.templateRecord;
        if((tr.get('type') == 'H') || (tr.get('cfg').readOnly == 1) ){
            return false;
        }
        if(context.field != 'value') {
            return;
        }

        var pw = this.findParentByType(CB.GenericForm, false)
            || this.refOwner
        ; //CB.Objects & CB.TemplateEditWindow
        var t = tr.get('type');
        if(pw && !Ext.isEmpty(pw.data)){
            context.objectId = pw.data.id;
            context.path = pw.data.path;
        }

        /* get and set pidValue if dependent */
        if( (Ext.isDefined(tr.get('cfg').dependency) ) && !Ext.isEmpty(tr.get('pid')) ) {
                context.pidValue = this.helperTree.getParentValue(context.record.get('id'), tr.get('pid'));
        }

        /* prepare time fields */
        if((t == 'time') && !Ext.isEmpty(context.value)) {
            var a = context.value.split(':');
            a.pop();
            context.value = a.join(':');
        }

        var col = context.column;
        var previousEditor = col.getEditor();


        if(this.editors && this.editors[t]) {
            col.setEditor(this.editors[t](this));
        } else {
            context.fieldRecord = this.helperTree.getNode(context.record.get('id')).data.templateRecord;

            //check if custom source and send fields
            if(Ext.isObject(context.fieldRecord.get('cfg')['source'])) {
                var fields = context.fieldRecord.get('cfg')['source'].requiredFields;
                if(!Ext.isEmpty(fields)) {
                    if(!Ext.isArray(fields)) {
                        fields = fields.split(',');
                    }
                    context.objFields = {};
                    var currentData = this.helperTree.readValues();

                    for (var i = 0; i < fields.length; i++) {
                        var f = fields[i].trim();

                        if(!Ext.isEmpty(currentData[f])) {
                            context.objFields[f] = currentData[f];
                        }
                    }
                }
            }

            var te = App.getTypeEditor(t, context);

            this.attachKeyListeners(te);
            if(te) {
                col.setEditor(te);
            }
        }

        // destroy previous editor if changed
        var currentEditor = col.getEditor();
        if(previousEditor && (previousEditor != currentEditor)) {
            Ext.destroy(previousEditor);
        }
    }

    ,gainFocus: function(position){
        var sm = this.getSelectionModel()
            ,navModel = this.getNavigationModel()
            ,lastFocused = navModel.getLastFocused();


        if(lastFocused && !isNaN(lastFocused.rowIdx)){
            sm.select({
                row: lastFocused.rowIdx
                ,column: lastFocused.colIdx
            });

            navModel.setPosition(lastFocused.rowIdx, lastFocused.colIdx);

            navModel.focusPosition(lastFocused);
        }
    }

    ,addKeyMaps: function(c) {
        var map = new Ext.KeyMap(c.getEl(), [
            {
                key: "s"
                ,ctrl: true
                ,shift: false
                ,scope: this
                ,stopEvent: true
                ,fn: this.onSaveObjectEvent
            }
        ]);
    }

    ,attachKeyListeners: function(comp) {
        if(Ext.isEmpty(comp) || !Ext.isObject(comp)) {
            return;
        }
        comp.on(
            'afterrender'
            ,this.addKeyMaps
            ,this
        );
    }

    ,onSaveObjectEvent: function (key, event){
        if(this.editing) {
            this.stopEditing(false);
        }
        this.fireEvent('saveobject', this, event);
    }

    ,onAfterEditProperty: function(editor, context, eOpts){
        var nodeId = context.record.get('id')
            ,node = this.helperTree.getNode(nodeId)
            ,tr = node.data.templateRecord;

        if(context.field == 'value'){
            /* post process value */
            if(!Ext.isEmpty(context.value)) {
                switch(context.fieldRecord.get('type')) {
                    case 'time':
                        if(Ext.isPrimitive(context.value)) {
                            var format = Ext.valueFrom(tr.get('cfg').format, App.timeFormat);
                            context.value = Ext.Date.parse(context.value, format);
                        }

                        context.value = Ext.Date.format(context.value, 'H:i:s');
                        context.record.set('value', context.value);
                        break;

                    case '_objects':
                        if(Ext.isArray(context.value)) {
                            context.value = context.value.join(',');
                            context.record.set('value', context.value);
                        }
                        break;
                }
            }

            //check if field has validator set and notify if validation not passed
            var validator = tr.get('cfg').validator;
            if(!Ext.isEmpty(validator)) {
                if(!Ext.isDefined(CB.Validators[validator])) {
                    plog('Undefined field validator: ' + validator);
                } else {
                    node.data.valid = CB.Validators[validator](context.value);
                }
            }

            if(context.value != context.originalValue){
                this.helperTree.resetChildValues(nodeId);
            }

            //check if editor field has getValueRecords (tag field) method and check records existance
            var fe = context.column.field;
            if(fe.getValueRecords) {
                var records = fe.getValueRecords();
                for (var i = 0; i < records.length; i++) {
                    this.refOwner.objectsStore.checkRecordExistance(records[i].data);
                }
            }
        }

        //fire change event if value changed
        if(context.value != context.originalValue) {
            this.fireEvent(
                'change'
                ,tr.get('name')
                ,context.value
                ,context.originalValue
            );
        }

        this.syncRecordsWithHelper();

        this.gainFocus();
    }

    ,getFieldValue: function(field_id, duplication_id){
        //TODO: review
        result = null;

        this.store.each(
            function(r){
                if((r.get('field_id') == field_id) && (r.get('duplicate_id') == duplication_id)){
                    result = r.get('value');
                    return false;
                }
            }
            ,this
        );
        return result;
    }

    /**
     * set value for a field
     *
     * TODO: review for duplicated fields
     *
     * @param varchar fieldName
     * @param variant value
     */
    ,setFieldValue: function(fieldName, value) {
        var helperTreeNode = this.helperTree.setFieldValue(fieldName, value);

        if(Ext.isEmpty(helperTreeNode)) {
            return;
        }

        var recordIndex = this.store.findExact('id', helperTreeNode.data.id);

        if(recordIndex >= 0) {
            this.store.getAt(recordIndex).set('value', value);
        }
    }

    ,onDuplicateFieldClick: function(b){
        var r = this.getSelectionModel().getSelection()[0];
        if(Ext.isEmpty(r)) {
            return;
        }

        this.fireEvent('savescroll', this);

        this.helperTree.duplicate(r.get('id'));
        this.syncRecordsWithHelper();

        this.fireEvent('restorescroll', this);

        this.fireEvent('change');
    }

    ,onDeleteDuplicateFieldClick: function(b){
        var r = this.getSelectionModel().getSelection()[0];
        if(Ext.isEmpty(r)) {
            return;
        }

        this.fireEvent('savescroll', this);

        this.helperTree.deleteDuplicate(r.get('id'));
        this.syncRecordsWithHelper();

        this.fireEvent('restorescroll', this);

        this.fireEvent('change');
    }

    /**
     * check if every record meets required config option
     * and is valid if validator set
     * @return bool
     */
    ,isValid: function() {
        var rez = true;
        this.store.each(
            function(r) {
                var n = this.helperTree.getNode(r.get('id'));
                if((r.get('valid') === false) ||
                    (n.data.templateRecord.get('cfg').required &&
                    Ext.isEmpty(r.get('value'))
                    )
                ) {
                    rez = false;
                }
                return rez;
            }
            ,this
        );

        return rez;
    }
});
