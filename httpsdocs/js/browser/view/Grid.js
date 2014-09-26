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
                ,scope: this
                ,editable: true
            },{
                header: L.Path
                ,hidden:true
                ,width: 150
                ,dataIndex: 'path'
                ,renderer: function(v, m, r, ri, ci, s){
                    m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace(/"/g,"&quot;")+'"';
                    return v;
                }
            },{
                header: L.Project
                ,width: 150
                ,dataIndex: 'case'
                ,renderer: function(v, m, r, ri, ci, s){
                    m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace(/"/g,"&quot;")+'"';
                    return v;
                }
            },{
                header: L.Date
                ,width: 120
                ,dataIndex: 'date'
                // ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' ' + App.timeFormat
                ,renderer: App.customRenderers.datetime
            },{
                header: L.Size
                ,width: 80
                ,dataIndex: 'size'
                ,renderer: App.customRenderers.filesize
            },{
                header: L.Creator
                ,hidden: true
                ,width: 200
                ,dataIndex: 'cid'
                ,renderer: function(v){
                    return CB.DB.usersStore.getName(v);
                }
            },{
                header: L.Owner
                ,width: 200
                ,dataIndex: 'oid'
                ,renderer: function(v){
                    return CB.DB.usersStore.getName(v);
                }
            },{
                header: L.CreatedDate
                ,hidden:true
                ,width: 120
                ,dataIndex: 'cdate'
                ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' '  +  App.timeFormat
            },{
                header: L.UpdatedDate
                ,hidden:true
                ,width: 120
                ,dataIndex: 'udate'
                ,xtype: 'datecolumn'
                ,format: App.dateFormat + ' ' + App.timeFormat
            }
        ];

        this.pagingToolbar = new Ext.PagingToolbar({
            store: this.store
            ,displayInfo: true
            ,hidden: true
            ,doRefresh: this.onReloadClick.bind(this)
            ,listeners: {
                scope: this
                // prevent toolbar from changing store params and reloading the store
                // we'll make this through viewContainer
                ,beforechange: function(pt, p) {
                //     clog('firing change params', arguments);
                //     this.fireEvent(
                //         'changeparams'
                //         ,{
                //             page: p
                //         }
                //     );
                    // return false;
                }
            }
        });

        this.grid = new Ext.grid.Panel({
            loadMask: false
            ,cls: 'folder-grid'
            ,autoRender: true
            ,store: this.store
            ,getProperty: this.getProperty // link to view container method
            ,defaultColumns: Ext.apply([], columns)
            ,columns: columns

            // ,colModel: new Ext.grid.ColumnModel({
            //     defaults: {
            //         width: 120,
            //         sortable: true
            //     }
                // ,listeners: {
                //     scope: this
                // }
            // })
            ,viewConfig: {
                forceFit: false
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
                        ptype: 'CBPluginsFilesDropZone'
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
                ,listeners: {
                    scope: this
                    // ,rowselect: this.onRowSelect
                    ,selectionchange: this.onSelectionChange
                }
            }
            // ,sm: new Ext.grid.RowSelectionModel({
            ,selModel: new Ext.selection.RowModel({
                mode: 'MULTI'
                // ,listeners: {
                //     scope: this
                //     // ,rowselect: this.onRowSelect
                //     ,selectionchange: this.onSelectionChange
                // }
            })
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

                ,headerclick: function (g, ci, e) {
                    clog('grid header click encounted');
                    return;
                    this.store.remoteSort = (g.getColumnModel().config[ci].localSort !== true);
                    this.userSort = 1;
                }

                ,columnmove:    this.saveGridState
                ,columnresize:  this.saveGridState
                ,groupchange:   this.saveGridState
                ,sortchange: function() {
                    this.saveGridState();
                }
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
            ,bbar: this.pagingToolbar
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

        this.store.on('load', this.onStoreLoad, this);

        this.enableBubble(['reload']);

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
    ,onStoreLoad: function(store, recs, options) {
        var pt = this.pagingToolbar;

        var pagingVisible = (store.proxy.reader.rawData.total > store.pageSize);

        delete this.userSort;

        if(pagingVisible) {
            pt.show();
        } else {
            pt.hide();
        }

        // this.grid.syncSize();
        // this.syncSize();

        App.mainViewPort.selectGridObject(this.grid);

        this.fireSelectionChangeEvent();
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

    ,fireSelectionChangeEvent: function() {
        var s = this.grid.getSelectionModel().getSelection();
        for (var i = 0; i < s.length; i++) {
            s[i] = s[i].data;
        }

        this.fireEvent('selectionchange', s);
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
        var rez = {columns: {}}
            ,store = this.store
            ,cm = this.grid.getColumnModel()
            ,gs
            ,di;

        for(var i = 0, c; (c = cm.config[i]); i++){
            di = cm.getDataIndex(i);
            rez.columns[di] = {
                idx: i
                ,width: c.width
            };
            if(c.hidden){
                rez.columns[di].hidden = true;
            }
            if(c.sortable){
                rez.columns[di].sortable = true;
            }
        }

        if(this.store){
            ss = this.store.getSortState();
            if(ss){
                rez.sort = ss;
            }
            if(this.store.getGroupState){
                gs = this.store.getGroupState();
                if(gs){
                    rez.group = gs;
                }
            }
        }

        CB_State_DBProvider.saveGridViewState(
            {
                params: this.refOwner.params
                ,state: rez
            }
        );

        return rez;
    }

    ,getViewParams: function() {
        var rez = {};

        if(this.userSort) {
            rez.userSort = 1;
        }

        return rez;
    }

});
