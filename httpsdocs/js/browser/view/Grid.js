Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Grid', {
    extend: 'CB.browser.view.Interface'
    ,border: false

    ,xtype: 'CBBrowserViewGrid'

    ,initComponent: function(){

        var columns = [
            {
                header: 'ID'
                ,width: 80
                ,dataIndex: 'nid'
                ,hidden: true
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
                    rez = '<span class="n">' + Ext.valueFrom(r.get('hl'), v) + '</span>';
                    if( (this.hideArrows !== true) && r.get('has_childs')) {
                        rez += ' <span class="fs9">&hellip;</span>';
                        // rez += '<img class="click icon-arrow3" src="'+Ext.BLANK_IMAGE_URL+'" />';
                    }
                    vi = getVersionsIcon(r.get('versions'));
                    if(!Ext.isEmpty(vi)) rez = '<span class="ver_count ' + vi + '" title="'+L.FileVersionsCount+'">&nbsp;</span>'+ rez;

                    return rez;
                }
                // ,scope: this
                ,editable: true
                ,sort: this.columnSortOverride
                ,groupable: false
            },{
                header: L.Path
                ,hidden:true
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
                ,renderer: App.customRenderers.datetime
                ,sort: this.columnSortOverride
            },{
                header: L.Size
                ,width: 80
                ,dataIndex: 'size'
                ,renderer: App.customRenderers.filesize
                ,sort: this.columnSortOverride
            },{
                header: L.Creator
                ,hidden: true
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
                ,hidden:true
                ,width: 120
                ,dataIndex: 'cdate'
                ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' '  +  App.timeFormat
                ,sort: this.columnSortOverride
            },{
                header: L.UpdatedDate
                ,hidden:true
                ,width: 120
                ,dataIndex: 'udate'
                ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' ' + App.timeFormat
                ,sort: this.columnSortOverride
            },{
                header: L.CommentedDate
                ,hidden:true
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
            });

        this.grid = new Ext.grid.Panel({
            loadMask: false
            ,cls: 'folder-grid'
            ,border: false
            ,bodyStyle: {
                border: 0
            }

            ,autoRender: true
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
                ,deferInitialRefresh: false
                // ,enableRowBody: true
                // ,getRowClass: function(r, rowIndex, rp, ds){
                //     rp.body = '';
                //     if(!Ext.isEmpty(r.get('content'))) {
                //         rp.body += r.get('content');
                //     }

                //     if(Ext.isEmpty(rp.body)) {
                //         return '';
                //     }
                //     return 'hasBody';
                // }
                ,plugins: [{
                        ptype: 'CBPluginDDFilesDropZone'
                        ,pidPropety: 'nid'
                        ,dropZoneConfig: {
                            text: 'Drop files here to upload to current folder<br />or drop over a row to upload into that element'
                            ,onScrollerDragDrop: this.onScrollerDragDrop
                            ,scope: this
                        }
                    },{
                        ptype: 'CBDDGrid'
                        ,idProperty: 'nid'
                        ,dropZoneConfig: {
                            text: 'Drop files here to upload to current folder<br />or drop over a row to upload into that element'
                            ,onScrollerDragDrop: this.onScrollerDragDrop
                            ,scope: this
                        }
                    }
                ]
            }
            // ,sm: new Ext.grid.RowSelectionModel({
            ,selModel: sm
            ,listeners:{
                scope: this
                ,columnhide: this.saveGridState
                ,columnshow: this.saveGridState

                ,beforeedit: function(e){
                    if(!this.allowRename) {
                        return false;
                    }

                    var ed = new Ext.form.TextField({selectOnFocus: true});
                    ed._setValue = ed.setValue;
                    ed.setValue = function(v) {
                        v = Ext.util.Format.htmlDecode(v);
                        this._setValue(v);
                    };

                    e.grid.getColumnModel().setEditor(e.column ,ed);

                    delete this.allowRename;
                    return true;
                }

                ,afteredit: function(e){
                    var encodedValue = Ext.util.Format.htmlEncode(e.value);
                    e.record.set('name', encodedValue);

                    if(encodedValue == e.originalValue) {
                        return;
                    }

                    this.renamedOriginalValue = e.originalValue;
                    this.renamedRecord = e.record;
                    CB_BrowserView.rename(
                        {
                            path: e.record.get('nid')
                            ,name: e.value
                        }
                        ,function(r, e){
                            if(r.success !== true){
                                this.renamedRecord.set('name', this.renamedOriginalValue);
                                delete this.renamedOriginalValue;
                                delete this.renamedRecord;
                                return;
                            }

                            this.renamedRecord.set('name', r.data.newName);

                            delete this.renamedOriginalValue;
                            delete this.renamedRecord;


                            this.fireEvent(
                                'objectupdated'
                                ,{
                                    data: {
                                        id: parseInt(r.data.id, 10)
                                        ,pid: this.refOwner.folderProperties.id
                                    }
                                }
                                ,e
                            );
                        }
                        ,this
                    );
                }
                ,keydown: this.onKeyDown
                ,rowclick: this.onRowClick
                ,rowdblclick: this.onRowDblClick
                ,selectionchange: this.onSelectionChange
                // ,contextmenu: this.onContextMenu
                // ,rowcontextmenu: this.onRowContextMenu
                // ,beforedestroy: this.onBeforeDestroy
                // ,activate: App.onComponentActivated
                ,mousedown: function(e){
                    if(e.button == 2){ //rightclick
                        /* lock selection if rightclicking on a selected row. Unlock should be called after corresponding actions (usually called with defer).*/
                        //TO REENABLE THE CODE AFTER POPUP MENU REVIEW
                        // sm = this.grid.getSelectionModel();
                        // s = sm.getSelections();
                        // target = e.getTarget('.x-grid3-row');
                        // for (var i = 0; i < s.length; i++) {
                        //     el = this.grid.getView().getRow(this.grid.store.indexOf(s[i]));
                        //     if( el == target ){
                        //         sm.lock();
                        //         return;
                        //     }
                        // }
                    }
                }

                ,columnmove:    this.saveGridState
                ,columnresize:  this.saveGridState
                ,groupchange:   this.saveGridState
                ,sortchange: function() {
                    this.saveGridState();
                }
                ,itemcontextmenu: this.onItemContextMenu
            }
            ,keys: [{
                    key: Ext.event.Event.DOWN //down arrow (select forst row in the greed if no row already selected)  - does not work
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
                ,doRefresh: this.onReloadClick.bind(this)
            }
        });

        Ext.apply(this, {
            title: L.Explorer
            ,header: false
            ,layout: 'fit'
            ,items: this.grid
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });
        CB.browser.view.Grid.superclass.initComponent.apply(this, arguments);

        this.store.on('beforeload', this.onBeforeStoreLoad, this);
        this.store.on('load', this.onStoreLoad, this);

        this.enableBubble(['reload', 'itemcontextmenu']);

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

    ,onActivate: function() {
        this.fireEvent(
            'settoolbaritems'
            ,[
                'apps'
                ,'create'
                ,'upload'
                ,'download'
                ,'-'
                ,'edit'
                ,'delete'
            ]
        );
    }

    ,onBeforeStoreLoad: function(store, operation, eOpts) {
        this.savedSelection = this.getSelectedItems();
        this.grid.getSelectionModel().suspendEvents();
        // App.mainViewPort.selectGridObject(this.grid);

    }

    ,onStoreLoad: function(store, recs, successful, options) {
        if(!this.rendered ||
            !this.getEl().isVisible(true) ||
            (successful !== true)
        ) {
            return;
        }

        delete this.grid.userSort;

        var hadSelection = false;
        var prevSelectedId = 0;
        var prevSelectedPid = 0;

        if(!Ext.isEmpty(this.savedSelection)) {
            hadSelection = true;
            prevSelectedId = this.savedSelection[0].nid;
            prevSelectedPid = this.savedSelection[0].pid;
        }

        // try to select the item, if set, from App.locateObjectId
        var locatedAnItem = App.mainViewPort.selectGridObject(this.grid);

        // otherwise select previous items, if any
        if(!locatedAnItem &&
            !Ext.isEmpty(this.savedSelection)
        ) {
            this.selectItems(this.savedSelection);
        }

        this.grid.getSelectionModel().resumeEvents(true);

        if(!locatedAnItem) {
            var haveSelection = this.grid.getSelectionModel().hasSelection()
                ,currSelectedId = haveSelection
                    ? this.grid.getSelection()[0].get('nid')
                    : 0
                ,currSelectedPid = this.refOwner.folderProperties.id;

            if(
                // (hadSelection !== haveSelection) || (prevSelectedId != currSelectedId)
                (prevSelectedPid != currSelectedPid) || (haveSelection && (prevSelectedId != currSelectedId))
            ){
                this.fireSelectionChangeEvent();
            }
        } else {
            delete App.locateObjectId;
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
        this.fireSelectionChangeEvent();
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
        this.grid.stopEditing(true);
        var idx = this.grid.store.indexOf(this.grid.selModel.getSelected());
        this.allowRename = true;
        this.grid.startEditing(idx, this.grid.getColumnModel().findColumnIndex('name'));
    }
    ,onReloadClick: function() {
        this.fireEvent('reload', this);
    }

    ,saveGridState: function() {
        var state = this.grid.getState();

        CB_State_DBProvider.saveGridViewState(
            {
                params: this.refOwner.params
                ,state: state
            }
        );

        return state;
    }

    ,getViewParams: function() {
        var rez = {
            from: 'grid'
        };

        if(this.grid.userSort) {
            rez.userSort = 1;
        }

        return rez;
    }

    ,onItemContextMenu: function(grid, record, item, index, e, eOpts) {
        this.fireEvent('itemcontextmenu', e);
    }

});
