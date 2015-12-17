Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Grid', {
    extend: 'CB.browser.view.Interface'
    ,border: false

    ,xtype: 'CBBrowserViewGrid'

    ,initComponent: function(){

        var editor = new Ext.form.TextField({selectOnFocus: true});
        editor._setValue = editor.setValue;
        editor.setValue = function(v) {
            v = Ext.util.Format.htmlDecode(v);
            this._setValue(v);
        };


        var columns = [
            {
                header: 'ID'
                ,width: 80
                ,dataIndex: 'nid'
                ,sort: this.columnSortOverride
                ,groupable: false
            },{
                header: L.Name
                ,width: 300
                ,dataIndex: 'name'
                ,renderer: function(v, m, r, ri, ci, s){
                    m.css = 'icon-grid-column-top '+ r.get('iconCls');

                    if(r.get('acl_count') > 0) {
                        m.css += ' node-has-acl';
                    }

                    m.attr = Ext.isEmpty(v) ? '' : "title=\"" + v + "\"";

                    var rez = '<span class="n">' + Ext.valueFrom(r.get('hl'), v) + '</span>';

                    if( (this.hideArrows !== true) && r.get('has_childs')) {
                        rez += ' <span class="fs9">&hellip;</span>';
                        // rez += '<img class="click icon-arrow3" src="'+Ext.BLANK_IMAGE_URL+'" />';
                    }

                    return rez;
                }
                ,editor: editor
                ,sort: this.columnSortOverride
            },{
                header: L.Path
                ,width: 150
                ,dataIndex: 'path'
                ,renderer: function(v, m, r, ri, ci, s){
                    m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace(/"/g,"&quot;")+'"';
                    return v;
                }
                ,sort: this.columnSortOverride
            },{
                header: L.Project
                ,width: 150
                ,dataIndex: 'case'
                ,renderer: function(v, m, r, ri, ci, s){
                    m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace(/"/g,"&quot;")+'"';
                    return v;
                }
                ,sort: this.columnSortOverride
            },{
                header: L.Date
                ,width: 120
                ,dataIndex: 'date'
                // ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' ' + App.timeFormat
                ,renderer: function(v, m, r, ri, ci, s){
                    return App.customRenderers.datetime(v, false);
                }
                ,sort: this.columnSortOverride
            },{
                header: L.Size
                ,width: 80
                ,dataIndex: 'size'
                ,renderer: App.customRenderers.filesize
                ,sort: this.columnSortOverride
            },{
                header: L.Creator
                ,width: 200
                ,dataIndex: 'cid'
                ,renderer: function(v){
                    return CB.DB.usersStore.getName(v);
                }
                ,sort: this.columnSortOverride
            },{
                header: L.Owner
                ,width: 200
                ,dataIndex: 'oid'
                ,renderer: function(v){
                    return CB.DB.usersStore.getName(v);
                }
                ,sort: this.columnSortOverride
            },{
                header: L.UpdatedBy
                ,width: 200
                ,dataIndex: 'uid'
                ,renderer: function(v){
                    return CB.DB.usersStore.getName(v);
                }
                ,sort: this.columnSortOverride
            },{
                header: L.CommentedBy
                ,width: 200
                ,dataIndex: 'comment_user_id'
                ,renderer: function(v){
                    return CB.DB.usersStore.getName(v);
                }
                ,sort: this.columnSortOverride
            },{
                header: L.CreatedDate
                ,width: 120
                ,dataIndex: 'cdate'
                ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' '  +  App.timeFormat
                ,sort: this.columnSortOverride
            },{
                header: L.UpdatedDate
                ,width: 120
                ,dataIndex: 'udate'
                ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' ' + App.timeFormat
                ,sort: this.columnSortOverride
            },{
                header: L.CommentedDate
                ,width: 120
                ,dataIndex: 'comment_date'
                ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' ' + App.timeFormat
                ,sort: this.columnSortOverride
            }
        ];

        var sm = (this.config.selModel)
            ? this.config.selModel
            : new Ext.selection.RowModel({
               mode: 'MULTI'
               ,allowDeselect: true
            });

        this.grid = new Ext.grid.Panel({
            loadMask: false
            ,cls: 'folder-grid'
            ,border: false
            ,bodyStyle: {
                border: 0
            }

            ,store: this.store
            ,getProperty: this.getProperty // link to view container method
            ,defaultColumns: Ext.apply([], columns)
            ,columns: columns
            ,features: [{
                ftype:'cbGridViewGrouping'
                ,disabled: true
                ,groupHeaderTpl: [
                    '{columnName}: {[Ext.valueFrom(values.children[0].get(\'groupText\'), values.children[0].get(\'group\'))]}'
                ]
                // ,showSummaryRow: true
            }]
            ,viewConfig: {
                forceFit: false
                ,loadMask: false
                ,stripeRows: false
                ,emptyText: L.NoData
                ,deferInitialRefresh: false

                ,listeners: {
                    scope: this
                    ,containermousedown: function(view, e, eOpts) {
                        var g = this.grid
                            ,sm = g.getSelectionModel()
                            ,t = e.getTarget();

                        if((sm.selType != 'checkboxmodel') && t) {
                            t = Ext.get(t);
                            var s = t.getViewSize();

                            //deselect all selected records when clicking on empty area of the grid
                            //except the scroller
                            if ((e.pageX < t.getLeft() + s.width) && (e.pageY < t.getTop() + s.height)){
                                this.grid.getSelectionModel().deselectAll();
                            }
                        }
                    }
                }
                ,plugins: [{
                        ptype: 'CBPluginDDFilesDropZone'
                        ,pidPropety: 'nid'
                        ,dropZoneConfig: {
                            text: L.GridDDMgs
                            ,onScrollerDragDrop: this.onScrollerDragDrop
                            ,scope: this
                        }
                    },{
                        ptype: 'CBDDGrid'
                        ,idProperty: 'nid'
                        ,dropZoneConfig: {
                            text: L.GridDDMgs
                            // ,onScrollerDragDrop: this.onScrollerDragDrop
                            // ,scope: this
                            ,onContainerOver: Ext.Function.bind(this.ddOnContainerOver, this)
                            ,onContainerDrop: Ext.Function.bind(this.ddOnContainerDrop, this)
                        }
                    }
                ]
            }
            ,selModel: sm
            ,listeners:{
                scope: this
                ,columnhide: this.saveGridState
                ,columnshow: this.saveGridState

                ,keydown: this.onKeyDown
                ,rowclick: this.onRowClick
                ,rowdblclick: this.onRowDblClick
                ,selectionchange: this.onSelectionChange

                ,columnmove:    this.saveGridState
                ,columnresize:  this.saveGridState
                ,groupchange:   this.saveGridState
                ,sortchange: function() {
                    this.saveGridState();
                }
                ,itemcontextmenu: this.onItemContextMenu
                ,containercontextmenu: this.onContainerContextMenu
            }
            ,keys: [{
                    key: Ext.event.Event.DOWN //down arrow (select forst row in the grid if no row already selected)  - does not work
                    ,ctrl: false
                    ,shift: false
                    ,stopEvent: true
                    ,fn: this.onDownClick
                    ,scope: this
                },{
                    key: [10,13]
                    ,alt: false
                    ,ctrl: false
                    ,shift: false
                    ,stopEvent: true
                    ,fn: this.onEnterKeyPress
                    ,scope: this
                },{
                    key: Ext.event.Event.F2
                    ,alt: false
                    ,ctrl: false
                    ,stopEvent: true
                    ,fn: this.onRenameClick
                    ,scope: this
                // },{
                //     key: 'x'
                //     ,ctrl: true
                //     ,shift: false
                //     ,stopEvent: true
                //     ,fn: this.onCutClick
                //     ,scope: this
                // },{
                //     key: 'c'
                //     ,ctrl: true
                //     ,shift: false
                //     ,stopEvent: true
                //     ,fn: this.onCopyClick
                //     ,scope: this
                // },{
                //     key: 'v'
                //     ,ctrl: true
                //     ,shift: false
                //     ,stopEvent: true
                //     ,fn: this.onPasteClick
                //     ,scope: this
                // },{
                //     key: 'v'
                //     ,alt: true
                //     ,ctrl: true
                //     ,stopEvent: true
                //     ,fn: this.onPasteShortcutClick
                //     ,scope: this
                // },{
                //     key: Ext.event.Event.DELETE
                //     ,alt: false
                //     ,ctrl: false
                //     ,stopEvent: true
                //     ,fn: this.onDeleteClick
                //     ,scope: this
                // },{
                //     key: Ext.event.Event.F5
                //     ,alt: false
                //     ,ctrl: false
                //     ,stopEvent: true
                //     ,fn: this.onReloadClick
                //     ,scope: this
                // },{
                //     key: 'r'
                //     ,alt: false
                //     ,ctrl: true
                //     ,stopEvent: true
                //     ,fn: this.onReloadClick
                //     ,scope: this
                // },{
                //     key: [10, 13]
                //     ,alt: true
                //     ,ctrl: false
                //     ,shift: false
                //     ,stopEvent: true
                //     ,fn: this.onPropertiesClick
                //     ,scope: this
            }]
            ,bbar: {
                xtype: 'CBBrowserViewGridPagingToolbar'
                ,store: this.store
                ,hidden: !Ext.isEmpty(this.hideBottomBar)
                ,doRefresh: this.onReloadClick.bind(this)
            }
            ,plugins: [{
                ptype: 'cellediting'
                ,clicksToEdit: 1
                ,listeners: {
                    scope: this
                    ,beforeedit: function(e){
                        if(!this.allowRename) {
                            return false;
                        }

                        delete this.allowRename;
                        return true;
                    }

                    ,edit: function(editor, context, eOpts){
                        var encodedValue = Ext.util.Format.htmlEncode(context.value);
                        context.record.set('name', encodedValue);

                        if(encodedValue == context.originalValue) {
                            return;
                        }

                        this.renamedOriginalValue = context.originalValue;
                        this.renamedRecord = context.record;
                        CB_BrowserView.rename(
                            {
                                path: context.record.get('nid')
                                ,name: context.value
                            }
                            ,function(r, e){
                                if(!r || (r.success !== true)) {
                                    this.renamedRecord.set('name', this.renamedOriginalValue);
                                    delete this.renamedOriginalValue;
                                    delete this.renamedRecord;
                                    return;
                                }

                                this.renamedRecord.set('name', r.data.newName);

                                delete this.renamedOriginalValue;
                                delete this.renamedRecord;

                                App.fireEvent(
                                    'objectchanged'
                                    ,{
                                        id: parseInt(r.data.id, 10)
                                        ,pid: this.refOwner.folderProperties.id
                                    }
                                    ,e
                                );
                            }
                            ,this
                        );
                    }
                }
            }]
        });

        //reset scroll on veiew refresh
        this.grid.view.on(
            'refresh'
            ,function(view){
                view.scrollTo(0, 0, false);
            }
            ,this
        );

        Ext.apply(this, {
            title: L.Explorer
            ,header: false
            ,layout: 'fit'
            ,viewName: 'grid'
            ,items: [
                this.grid
            ]
        });
        this.callParent(arguments);

        this.store.on('beforeload', this.onBeforeStoreLoad, this);
        this.store.on('load', this.onStoreLoad, this);

        this.enableBubble([
            'reload'
            ,'itemcontextmenu'
        ]);

    }
    /**
     * override sort method for columns
     * used to set userSort flag
     * @param  string direction
     * @return void
     */
    ,columnSortOverride: function(direction) {
        var me = this,
            grid = me.up('tablepanel'),
            store = grid.store;

        // store.remoteSort = (this.config.localSort !== true);
        if(store.remoteSort) {
            grid.userSort = 1;
        } else {
            // store.sort()
        }

        Ext.grid.column.Column.prototype.sort.apply(this, arguments);
    }

    /**
     * fire the evnet for main browser view to update its buttons
     * @return void
     */
    ,updateToolbarButtons: function() {
        this.refOwner.fireEvent(
            'settoolbaritems'
            ,[
                'create'
                ,'upload'
                ,'download'
                ,'-'
                ,'edit'
                ,'delete'
                ,'->'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }

    ,onBeforeStoreLoad: function(store, operation, eOpts) {
        this.savedSelection = this.getSelectedItems();
        this.grid.getSelectionModel().suspendEvents();
    }

    ,onStoreLoad: function(store, recs, successful, options) {
        if(!this.rendered ||
            !this.getEl().isVisible(true) ||
            (successful !== true)
        ) {
            return;
        }

        delete this.grid.userSort;

        //grid selection logic
        var hadSelection = false
            ,prevSelectedId = 0
            ,prevSelectedPid = 0
            ,locateId = Ext.valueFrom(this.refOwner.params, {}).locatingObject;

        if(!Ext.isEmpty(this.savedSelection)) {
            hadSelection = true;
            prevSelectedId = this.savedSelection[0].nid;
            prevSelectedPid = this.savedSelection[0].pid;
        }

        // otherwise select previous items, if any
        if(!Ext.isEmpty(locateId)) {
            this.savedSelection = [{nid: locateId}];
        }

        if(!Ext.isEmpty(this.savedSelection)) {
            this.selectItems(this.savedSelection);
        }

        this.grid.getSelectionModel().resumeEvents(true);

        if(Ext.isEmpty(locateId)) {
            var haveSelection = this.grid.getSelectionModel().hasSelection()
                ,currSelectedId = haveSelection
                    ? this.grid.getSelection()[0].get('nid')
                    : 0
                ,currSelectedPid = Ext.valueFrom(this.refOwner.folderProperties, {}).id;

            if(
                // (hadSelection !== haveSelection) || (prevSelectedId != currSelectedId)
                (prevSelectedPid != currSelectedPid) || (haveSelection && (prevSelectedId != currSelectedId))
            ){
                this.fireSelectionChangeEvent();
            }
        } else {
            delete this.refOwner.params.locatingObject;
        }
        //end of grid selection logic

        this.updateToolbarButtons();

        // update empty text
        var noRecords = Ext.isEmpty(recs)
            ,params = options.request
                ? options.request.config.params
                : {}
            ,filters = params.filters
            ,emptyFilters = Ext.isEmpty(filters) || Ext.Object.isEmpty(filters)
            ,emptyText = (emptyFilters && Ext.isEmpty(params.query) && Ext.isEmpty(params.search))
                ? L.GridEmptyText
                : L.NoResultsFound;

        this.grid.view.emptyText = '<div class="gv-empty-text"><div class="middle"><div class="inner">' + emptyText + '</div></div></div>';
        this.grid.view.refresh();
    }

    ,ddOnContainerOver: function(source, e, data) {
        var d
            ,currentPid = this.refOwner.folderProperties.id
            ,rez = source.dropAllowed;

        for (var i = 0; i < data.records.length; i++) {
            d = data.records[i].data;
            if(d['pid'] == currentPid) {
                rez = source.dropNotAllowed;
            }
        }

        return rez;
    }

    ,ddOnContainerDrop: function(source, e, data) {
        if(this.ddOnContainerOver(source, e, data) == source.dropAllowed) {
            this.onScrollerDragDrop(
                this.refOwner.folderProperties
                ,source
                ,e
                ,data
            );
        }
    }


    ,onScrollerDragDrop: function(targetData, source, e, data){
        var d, sourceData = [];
        for (var i = 0; i < data.records.length; i++) {
            d = data.records[i].data;
            sourceData.push({
                id: d['nid']
                ,name: d['name']
                ,path: d['path']
                ,template_id: d['template_id']
            });
        }

        App.DD.execute({
            action: e
            ,targetData: this.refOwner.folderProperties
            ,sourceData: sourceData
        });
    }

    ,getSelectedItems: function() {
        var s = this.grid.getSelectionModel().getSelection();
        for (var i = 0; i < s.length; i++) {
            s[i] = s[i].data;
        }

        return s;
    }

    ,selectItems: function(itemsArray) {
        if (itemsArray && itemsArray.length) {
            var rs = [],
                r,
                j = 0,
                l = itemsArray.length;
            for (; j < l; j++) {
                r = this.store.findRecord('nid', itemsArray[j].nid);
                if (r) {
                    rs.push(r);
                }
            }
            if (rs.length) {
                // this.grid.setSelection(rs);
                this.grid.getSelectionModel().select(rs, false, true);
            }
        }

        delete this.savedSelection;
    }

    ,fireSelectionChangeEvent: function() {
        this.fireEvent('selectionchange', this.getSelectedItems());
    }

    ,onKeyDown: function(e) {
        if([9, 38, 40].indexOf(e.getKey()) > -1) {
            this.userAction = true;
        }
    }

    ,onRowClick: function(g, ri, e) {
        this.userAction = true;
        this.fireSelectionChangeEvent();
    }

    ,onRowDblClick: function(g, record, tr, rowIndex, e, eOpts) {
        this.fireEvent('objectopen', g.store.getAt(rowIndex).data);
    }

    ,onSelectionChange: function () {
        if(!App.mouseDown || (App.lastMouseButton !== 0)) {
            this.fireSelectionChangeEvent();
        }
    }

    ,onEnterKeyPress: function(key, e) {
        if(this.grid.selModel.hasSelection()) {
            var s = this.grid.selModel.getSelections();
            this.onRowDblClick(this.grid, this.store.indexOf(s[0]), e);
        }
    }

    ,onDownClick: function(key, e) {
        if(!this.grid.selModel.hasSelection() || (this.grid.store.getCount() < 1)) {
            return false;
        }

        this.grid.selModel.select(0);
    }

    ,onRenameClick: function(b, e){
        if(!this.grid.selModel.hasSelection()) {
            return;
        }
        this.grid.editingPlugin.cancelEdit();
        this.allowRename = true;

        var selection = this.grid.selModel.getSelection()
            ,valueCol = this.grid.headerCt.child('[dataIndex="name"]')
            ,colIdx = valueCol.getIndex();

        if(!Ext.isEmpty(selection)) {
            this.grid.editingPlugin.startEdit(
                selection[0]
                ,colIdx
            );
        }
    }
    ,onReloadClick: function() {
        this.fireEvent('reload', this);
    }

    ,saveGridState: function() {
        if(this.store.clearing ||
            (this.grid.disableStateSave && Ext.isEmpty(this.store.proxy.extraParams.userGroup))
        ) {
            return false;
        }

        var state = this.grid.getState();

        //sometimes grid state doesnt return group property
        if(!Ext.isEmpty(state.group)) {
            if(Ext.isEmpty(state.group.property)) {
                state.group.property = this.store.getGroupField();
            }
        }

        CB_State_DBProvider.saveGridViewState(
            {
                params: this.refOwner.params
                ,state: state
            }
        );

        return state;
    }

    ,getViewParams: function() {
        var rez = this.callParent(arguments);

        if(this.grid.userSort) {
            rez.userSort = 1;
        }

        return rez;
    }

    ,onItemContextMenu: function(grid, record, item, index, e, eOpts) {
        this.fireEvent('itemcontextmenu', e);
    }

    ,onContainerContextMenu: function(grid, e, eOpts) {
        this.fireEvent('itemcontextmenu', e);
    }
});
