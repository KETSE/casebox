Ext.namespace('CB');

CB.FolderViewGrid = Ext.extend(Ext.Panel,{
    layout: 'border'
    ,tbarCssClass: 'x-panel-white'
    ,hideBorders: true
    ,initComponent: function(){

        this.actions = {
            open: new Ext.Action({
                text: L.Open
                ,iconAlign:'top'
                ,iconCls: 'icon32-open'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onOpenClick
            }),browse: new Ext.Action({
                text: L.Browse
                ,iconAlign:'top'
                ,iconCls: 'icon32-browse'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onBrowseClick
            })
            ,openItemLocation: new Ext.Action({
                text: L.OpenItemLocation
                ,iconAlign:'top'
                ,disabled: true
                ,scope: this
                ,handler: this.onOpenItemLocationClick
            })
            ,cut: new Ext.Action({
                text: L.Cut
                ,scope: this
                ,disabled: true
                ,handler: this.onCutClick
            })
            ,copy: new Ext.Action({
                text: L.Copy
                ,scope: this
                ,disabled: true
                ,handler: this.onCopyClick
            })
            ,paste: new Ext.Action({
                text: L.Paste
                ,scope: this
                ,disabled: true
                ,handler: this.onPasteClick
            })
            ,pasteShortcut: new Ext.Action({
                text: L.PasteShortcut
                ,scope: this
                ,disabled: true
                ,handler: this.onPasteShortcutClick
            })

            ,createShortcut: new Ext.Action({
                text: L.CreateShortcut
                ,scope: this
                ,disabled: true
                ,handler: this.onCreateShortcutClick
            })
            ,mergeFiles: new Ext.Action({
                text: L.MergeFiles
                ,iconCls: 'icon-merge'
                ,scope: this
                ,disabled: true
                ,handler: this.onMergeFilesClick
            })
            ,'delete': new Ext.Action({
                text: L.Delete
                ,iconAlign:'top'
                ,iconCls: 'icon32-del'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onDeleteClick
            })
            ,rename: new Ext.Action({
                text: L.Rename
                ,disabled: true
                ,scope: this
                ,handler: this.onRenameClick
            })
            ,reload: new Ext.Action({
                text: L.Reload
                ,scope: this
                ,handler: this.onReloadClick
            })

            ,createCase: new Ext.Action({
                text: L.NewCase
                ,iconCls: 'icon-briefcase'
                ,scope: this
                ,handler: this.onCreateCaseClick
            })
            ,createTask: new Ext.Action({
                text: L.NewTask
                ,iconCls: 'icon32-task-new'
                ,iconAlign:'top'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onCreateTaskClick
            })
            ,createEvent: new Ext.Action({
                text: L.NewEvent
                ,iconCls: 'icon-event'
                ,iconAlign:'top'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onCreateEventClick
            })
            ,createFolder: new Ext.Action({
                text: L.NewFolder
                ,iconCls: 'icon-folder'
                ,scope: this
                ,disabled: true
                ,handler: this.onCreateFolderClick
            })

            ,properties: new Ext.Action({
                text: L.Properties
                ,scope: this
                ,disabled: true
                ,handler: this.onPropertiesClick
            })

            ,upload: new Ext.Action({
                tooltip: L.UploadFile
                ,iconCls: 'icon-drive-upload'
                ,disabled: true
                ,scope: this
                ,handler: this.onUploadClick
            })
            ,uploadArchive: new Ext.Action({
                text: L.UploadArchive
                ,iconCls: 'icon-document-zipper'
                ,disabled: true
                ,scope: this
                ,handler: this.onUploadClick
                ,uploadType: 'archive'
            })
            ,uploadMultipleFiles: new Ext.Action({
                text: L.UploadMultipleFiles
                ,iconCls: 'icon-documents-stack'
                ,disabled: true
                ,scope: this
                ,handler: this.onUploadClick
                ,uploadType: 'multiple'
            })
            ,uploadNewVersion: new Ext.Action({
                text: L.UploadNewVersion
                ,disabled: true
                ,scope: this
                ,handler: this.onUploadNewVersionClick
            })
            ,download: new Ext.Action({
                tooltip: L.Download
                ,iconCls: 'icon-drive-download'
                ,disabled: true
                ,scope: this
                ,handler: this.onDownloadClick
            })
            ,downloadArchived: new Ext.Action({
                text: L.DownloadArchived
                ,iconCls: 'icon-document-zipper'
                ,disabled: true
                ,scope: this
                ,handler: this.onDownloadClick
                ,zipped: true
            }),takeOwnership: new Ext.Action({
                text: L.TakeOwnership
                ,iconCls: 'icon-user-gray'
                ,disabled: true
                ,scope: this
                ,handler: this.onTakeOwnershipClick
            })
            ,permissions: new Ext.Action({
                text: L.Permissions
                ,iconCls: 'icon-key'
                ,scope: this
                ,handler: this.onPermissionsClick
            })

        };


        this.store = new Ext.data.DirectStore({
            autoLoad: false
            ,autoDestroy: true
            ,remoteSort: true
            ,baseParams: { facets: 'general' }
            ,proxy: new  Ext.data.DirectProxy({
                paramsAsHash: true
                ,directFn: CB_BrowserView.getChildren
                ,listeners:{
                    scope: this
                    ,load: this.onProxyLoad
                }
            })
            ,reader: new Ext.data.JsonReader({
                successProperty: 'success'
                ,idProperty: 'nid'
                ,root: 'data'
                ,messageProperty: 'msg'
                ,fields: [
                    {name: 'nid'}
                    ,{name: 'pid', type: 'int'}
                    ,{name: 'system', type: 'int'}
                    ,{name: 'type', type: 'int'}
                    ,{name: 'subtype', type: 'int'}
                    ,{name: 'status', type: 'int'}
                    ,{name: 'template_id', type: 'int'}
                    ,'template_type'
                    ,'path'
                    ,'name'
                    ,'hl'
                    ,'iconCls'
                    ,{name: 'date', type: 'date'}
                    ,{name: 'size', type: 'int'}
                    ,'sys_tags'
                    ,{name: 'oid', type: 'int'}
                    ,{name: 'cid', type: 'int'}
                    ,{name: 'versions', type: 'int'}
                    ,{name: 'cdate', type: 'date'}
                    ,{name: 'udate', type: 'date'}
                    ,'case'
                    ,'content'
                    ,{name: 'has_childs', type: 'bool'}
                    ,{name: 'acl_count', type: 'int'}
                    ,'cfg'
            ]
            }
            )
            ,listeners: {
                scope: this
                ,beforeload: function(store, options) {
                    Ext.apply(store.baseParams, Ext.value(this.params, {}));
                    options = store.baseParams;
                }
                ,load: this.onStoreLoad
            }
        });

        var columns = [
            { header: 'ID', width: 80, dataIndex: 'nid', hidden: true}
            ,{header: L.Name, width: 300, dataIndex: 'name', renderer: function(v, m, r, ri, ci, s){
                    m.css = 'icon-grid-column-top '+ r.get('iconCls');
                    if(r.get('acl_count') > 0) {
                        m.css += ' node-has-acl';
                    }

                    m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace('"',"&quot;")+'"';
                    rez = '<span class="n">' + Ext.value(r.get('hl'), v) + '</span>';
                    if( (this.hideArrows !== true) && r.get('has_childs')) {
                        rez += '<img class="click icon-arrow3" src="'+Ext.BLANK_IMAGE_URL+'" />';
                    }
                    vi = getVersionsIcon(r.get('versions'));
                    if(!Ext.isEmpty(vi)) rez = '<span class="ver_count '+vi+'" title="'+L.FileVersionsCount+'">&nbsp;</span>'+ rez;
                    return rez;
                },scope: this
                ,editable: true
                ,editor: new Ext.form.TextField({selectOnFocus: true})
            }
            ,{header: L.Path, hidden:true, width: 150, dataIndex: 'path', renderer: function(v, m, r, ri, ci, s){
                    m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace('"',"&quot;")+'"';
                    return v;
                }
            }
            ,{header: L.Project, width: 150, dataIndex: 'case', renderer: function(v, m, r, ri, ci, s){
                    m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace('"',"&quot;")+'"';
                    return v;
                }
            }
            ,{header: L.Tags, width:200, dataIndex: 'sys_tags', hidden: true, sortable: false, renderer: App.customRenderers.tagIds}
            ,{ header: L.Date, width: 120, dataIndex: 'date',/* xtype: 'datecolumn',/**/ format: App.dateFormat + ' ' + App.timeFormat, renderer: App.customRenderers.datetime}
            ,{ header: L.Size, width: 80, dataIndex: 'size', renderer: App.customRenderers.filesize}
            ,{ header: L.Creator, hidden:true, width: 200, dataIndex: 'cid', renderer: function(v){ return CB.DB.usersStore.getName(v);}}
            ,{ header: L.Owner, width: 200, dataIndex: 'oid', renderer: function(v){ return CB.DB.usersStore.getName(v);}}
            ,{ header: L.CreatedDate, hidden:true, width: 120, dataIndex: 'cdate', xtype: 'datecolumn', format: App.dateFormat+' '+App.timeFormat}
            ,{ header: L.UpdatedDate, hidden:true, width: 120, dataIndex: 'udate', xtype: 'datecolumn', format: App.dateFormat+' '+App.timeFormat}
        ];

        this.grid = new Ext.grid.EditorGridPanel({
            loadMask: true
            ,region: 'center'
            ,tbarCssClass: 'x-panel-white'
            ,cls: 'folder-grid'
            ,store: this.store
            ,defaultColumns: Ext.apply([], columns)
            ,colModel: new Ext.grid.ColumnModel({
                defaults: {
                    width: 120,
                    sortable: true
                },
                columns: columns
            })
            ,viewConfig: {
                forceFit: false
                ,enableRowBody: true
                ,getRowClass: function(r, rowIndex, rp, ds){
                    rp.body = '';
                    if(!Ext.isEmpty(r.get('content'))) rp.body += r.get('content');

                    if(Ext.isEmpty(rp.body)) return '';
                    return 'hasBody';
                }
            }
            ,sm: new Ext.grid.RowSelectionModel({
                singleSelect: false
                ,listeners: {
                    scope: this
                    ,selectionchange: this.onSelectionChange
                }
            })
            ,listeners:{
                scope: this
                ,beforeedit: function(e){
                    if(!this.allowRename) return false;
                    delete this.allowRename;
                    return true;
                }
                ,afteredit: function(e){
                    if(e.value == e.originalValue) return;
                    this.renamedOriginalValue = e.originalValue;
                    this.renamedRecord = e.record;
                    CB_BrowserView.rename({path: e.record.get('nid'), name: e.value}, function(r, e){
                        if(r.success !== true){
                            this.renamedRecord.set('name', this.renamedOriginalValue);
                            delete this.renamedOriginalValue;
                            delete this.renamedRecord;
                            return;
                        }
                        delete this.renamedOriginalValue;
                        delete this.renamedRecord;
                        this.fireEvent('objectupdated', {data: {id: r.data.id, pid: this.folderProperties.id} }, e );
                    }, this);
                }
                ,rowdblclick : this.onRowDblClick
                ,contextmenu: this.onContextMenu
                ,rowcontextmenu: this.onRowContextMenu
                ,beforedestroy: this.onBeforeDestroy
                ,cellclick: this.onCellClick
                ,activate: App.onComponentActivated
                ,mousedown: function(e){
                    if(e.button == 2){ //rightclick
                        /* lock selection if rightclicking on a selected row. Unlock should be called after corresponding actions (usually called with defer).*/
                        sm = this.grid.getSelectionModel();
                        s = sm.getSelections();
                        target = e.getTarget('.x-grid3-row');
                        for (var i = 0; i < s.length; i++) {
                            el = this.grid.getView().getRow(this.grid.store.indexOf(s[i]));
                            if( el == target ){
                                sm.lock();
                                return;
                            }
                        }
                    }
                }
            }
            ,keys: [{
                key: Ext.EventObject.DOWN //down arrow (select forst row in the greed if no row already selected)  - does not work
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
                    ,fn: this.onRowDblClick
                    ,scope: this
                },{
                    key: 'x'
                    ,ctrl: true
                    ,shift: false
                    ,stopEvent: true
                    ,fn: this.onCutClick
                    ,scope: this
                },{
                    key: 'c'
                    ,ctrl: true
                    ,shift: false
                    ,stopEvent: true
                    ,fn: this.onCopyClick
                    ,scope: this
                },{
                    key: 'v'
                    ,ctrl: true
                    ,shift: false
                    ,stopEvent: true
                    ,fn: this.onPasteClick
                    ,scope: this
                },{
                    key: 'v'
                    ,alt: true
                    ,ctrl: true
                    ,stopEvent: true
                    ,fn: this.onPasteShortcutClick
                    ,scope: this
                },{
                    key: Ext.EventObject.DELETE
                    ,alt: false
                    ,ctrl: false
                    ,stopEvent: true
                    ,fn: this.onDeleteClick
                    ,scope: this
                },{
                    key: Ext.EventObject.F2
                    ,alt: false
                    ,ctrl: false
                    ,stopEvent: true
                    ,fn: this.onRenameClick
                    ,scope: this
                },{
                    key: Ext.EventObject.F5
                    ,alt: false
                    ,ctrl: false
                    ,stopEvent: true
                    ,fn: this.onReloadClick
                    ,scope: this
                },{
                    key: 'r'
                    ,alt: false
                    ,ctrl: true
                    ,stopEvent: true
                    ,fn: this.onReloadClick
                    ,scope: this
                },{
                    key: [10, 13]
                    ,alt: true
                    ,ctrl: false
                    ,shift: false
                    ,stopEvent: true
                    ,fn: this.onPropertiesClick
                    ,scope: this
            }]
            ,bbar: new Ext.PagingToolbar({
                store: this.store
                ,displayInfo: true
                ,pageSize: 50
                ,hidden: true
            })

            ,statefull: true
            ,stateId: Ext.value(this.gridStateId, 'fvg')//folder view grid
            ,dropZoneConfig: {
                text: 'Drop files here to upload to current folder<br />or drop over a row to upload into that element'
                ,onScrollerDragDrop: this.onScrollerDragDrop
                ,scope: this
            }
            ,plugins: [{
                    ptype: 'CBPluginsFilesDropZone'
                    , pidPropety: 'nid'
                },{
                    ptype: 'CBDDGrid'
                }
            ]
            ,getProperty: this.getProperty.createDelegate(this)
        });

        this.previewPanel = new CB.PreviewPanel({bodyStyle:'padding: 10px'});

        this.filterButton = new Ext.Button({
            text: L.Filter
            ,enableToggle: true
            ,iconCls: 'icon32-filter'
            ,activeIconCls: 'icon32-filter-on'
            ,iconAlign:'top'
            ,scale: 'large'
            ,toggleGroup: 'rightBtn'
            ,itemIndex: 1
            ,scope: this
            ,toggleHandler: this.onEastPanelButtonClick
        });

        this.filtersPanel = new CB.FilterPanel({
            bindButton: this.filterButton
            ,listeners:{
                scope: this
                ,change: this.onFiltersChange
            }
        });


        this.eastPanel = new Ext.Panel({
            region: 'east'
            ,width: 300
            ,split: true
            ,hidden: true
            ,animCollapse: false
            ,border: false
            ,layout: 'card'
            ,activeItem: 0
            ,hideBorders: true
            ,statefull: true
            ,stateId: Ext.value(this.gridStateId, 'fvg') + 'EP' //taskview east panel
            ,items: [ this.previewPanel, this.filtersPanel ]
        });

        params = Ext.apply({}, this.params, {
            path: '/'
            ,descendants: false
        });

        Ext.apply(this, {
            params: params
            ,folderProperties: {}
            ,tbar: [
                {
                    text: L.Create
                    ,iconCls: 'icon32-create'
                    ,iconAlign:'top'
                    ,scale: 'large'
                    ,menu: [ ]
                },{
                    text: L.Edit
                    ,iconCls: 'icon32-edit'
                    ,iconAlign:'top'
                    ,scale: 'large'
                    ,menu: [
                        this.actions.cut
                        ,this.actions.copy
                        ,this.actions.paste
                        ,this.actions.pasteShortcut
                        ,'-'
                        ,this.actions.mergeFiles
                        ,'-'
                        ,this.actions.takeOwnership
                    ]
                },'-',{
                    text: L.Upload
                    ,iconCls: 'icon32-upload'
                    ,iconAlign:'top'
                    ,scale: 'large'
                    ,xtype:'splitbutton'
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onUploadClick
                    ,menu: [this.actions.uploadArchive, this.actions.uploadMultipleFiles ]
                },{
                    text: L.Download
                    ,iconCls: 'icon32-download'
                    ,iconAlign:'top'
                    ,scale: 'large'
                    ,xtype:'splitbutton'
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onDownloadClick
                    ,menu: [this.actions.downloadArchived]
                },this.actions.open
                ,this.actions.browse
                ,'-'
                ,this.actions['delete']
                ,'-'
                ,this.actions.createTask
                ,'->'
                ,{
                    text: L.Preview
                    ,enableToggle: true
                    ,iconCls: 'icon32-preview'
                    ,iconAlign:'top'
                    ,scale: 'large'
                    ,toggleGroup: 'rightBtn'
                    ,itemIndex: 0
                    ,scope: this
                    ,toggleHandler: this.onEastPanelButtonClick
                },this.filterButton
            ]
            ,items: [this.grid, this.eastPanel]
        });
        CB.FolderViewGrid.superclass.initComponent.apply(this, arguments);

        this.addEvents(
                'selectionchange'
                ,'casecreate'
                ,'taskcreate'
                ,'taskedit'
                ,'createobject'
                ,'objectupdated'
                ,'openobject'
                ,'fileopen'
                ,'fileupload'
                ,'filedownload'
                ,'changeparams'
                ,'viewloaded'
                ,'showdescendants'
        );
        this.enableBubble([
            'casecreate'
            ,'taskcreate'
            ,'taskedit'
            ,'createobject'
            ,'objectupdated'
            ,'openobject'
            ,'fileopen'
            ,'fileupload'
            ,'filedownload'
            ,'changeparams'
            ,'viewloaded'
            ,'showdescendants'
        ]);

        App.clipboard.on('change', this.onClipboardChange, this);
        App.clipboard.on('pasted', this.onClipboardAction, this);
        App.on('objectsaction', this.onObjectsAction, this);
        App.on('filesuploaded', this.onClipboardAction, this);
        App.mainViewPort.on('savesuccess', this.onObjectsSaved, this);
        App.mainViewPort.on('fileuploaded', this.onObjectsSaved, this);
        App.mainViewPort.on('taskupdated', this.onObjectsSaved, this);
        App.mainViewPort.on('taskcreated', this.onObjectsSaved, this);
        App.mainViewPort.on('favoritetoggled', this.onObjectsSaved, this);
        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
        App.mainViewPort.on('objectupdated', this.onObjectsSaved, this);
        App.fireEvent('folderviewinit', this);
    }
    ,onBeforeDestroy: function(p){
        App.clipboard.un('change', this.onClipboardChange, this);
        App.clipboard.un('pasted', this.onClipboardAction, this);
        App.un('filesuploaded', this.onClipboardAction, this);
        App.mainViewPort.un('savesuccess', this.onObjectsSaved, this);
        App.mainViewPort.un('fileuploaded', this.onObjectsSaved, this);
        App.mainViewPort.un('taskupdated', this.onObjectsSaved, this);
        App.mainViewPort.un('taskcreated', this.onObjectsSaved, this);
        App.mainViewPort.un('favoritetoggled', this.onObjectsSaved, this);
        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
        App.mainViewPort.un('objectupdated', this.onObjectsSaved, this);
    }
    ,onCellClick: function(grid, rowIndex, colIndex, e){
        el = e.getTarget();
        if(el && el.classList.contains('icon-arrow3')) {
            this.fireEvent('changeparams', {path: this.grid.store.getAt(rowIndex).get('nid')});
        }
    }
    ,onSearchQuery: function(query, e){
        this.grid.getStore().baseParams.query = query;
        this.onReloadClick();
    }
    ,onClipboardChange: function(cb){
        this.onSelectionChange(this.grid.getSelectionModel());
    }
    ,onClipboardAction: function(pids){
        if(pids.indexOf(this.folderProperties.id) >=0 ) this.onReloadClick();
    }
    ,onObjectsAction: function(action, r, e){
        if(Ext.isEmpty(r.processedIds)) {
            return;
        }
        // if(pids.indexOf(this.folderProperties.id) >=0 ) this.onReloadClick();

        switch(action){
            case 'copy':
                if(r.targetId == this.folderProperties.id){
                    this.onReloadClick();
                }
                break;
            case 'move':
                if(r.targetId == this.folderProperties.id){
                    this.onReloadClick();
                } else {
                    // remove moved record
                    for (var i = 0; i < r.processedIds.length; i++) {
                        idx = this.store.findExact('nid', parseInt(r.processedIds[i], 10));
                        if(idx > -1) {
                            this.store.removeAt(idx);
                        }
                    }
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

    ,onScrollerDragDrop: function(targetData, source, e, sourceData){
        App.DD.execute({
            action: e
            ,targetData: this.folderProperties
            ,sourceData: sourceData.data
        });
    }
    ,onSelectionChange: function(sm) {
        var id = null;
        var i;
        if(!sm.hasSelection()){
            this.actions.open.setDisabled(true);
            this.actions.browse.setDisabled(true);
            this.actions.openItemLocation.setDisabled(true);
            this.actions.cut.setDisabled(true);
            this.actions.copy.setDisabled(true);
            this.actions.paste.setDisabled( App.clipboard.isEmpty() );
            this.actions.pasteShortcut.setDisabled( App.clipboard.isEmpty() );
            this.actions.takeOwnership.setDisabled( true );
            this.actions.mergeFiles.setDisabled( true );
            this.actions.createShortcut.setDisabled(true);
            this.actions.uploadNewVersion.setDisabled(true);
            this.actions['delete'].setDisabled(true);
            this.actions.rename.setDisabled(true);
            this.actions.properties.setDisabled(true);
        }else{
            row = sm.getSelected();
            id = row.get('nid');
            this.actions.open.setDisabled(false);
            this.actions.browse.setDisabled(false);
            this.actions['delete'].setDisabled(row.get('system') == 1);

            canOpenLocation = (this.params.descendants || !Ext.isEmpty(this.grid.store.baseParams.query) );
            this.actions.openItemLocation.setDisabled(!canOpenLocation);

            canCopy = (row.get('system') == 0) && (row.get('nid') > 0 );
            this.actions.cut.setDisabled(!canCopy);
            this.actions.copy.setDisabled(!canCopy);

            canDelete = (row.get('system') == 0) && (row.get('nid') > 0 );
            this.actions['delete'].setDisabled(!canDelete);
            canRename = (row.get('system') == 0);
            this.actions.rename.setDisabled(!canRename);

            s = sm.getSelections();
            canTakeOwnership = true;
            for (i = 0; i < s.length; i++) {
                if( (s[i].get('cid') == App.loginData.id) || (s[i].get('system') == 1) ) canTakeOwnership = false;
            }
            this.actions.takeOwnership.setDisabled(!canTakeOwnership);

            canMerge = (s.length > 1);
            for (i = 0; i < s.length; i++) {
                if(s[i].get('template_type') != 'file') canMerge = false;
            }
            this.actions.mergeFiles.setDisabled(!canMerge);

            canUploadNewVersion = (row.get('template_type') == 'file');
            this.actions.uploadNewVersion.setDisabled(!canUploadNewVersion);
            // this.actions.permissions.setDisabled(false) ;
        }
        canPaste = !App.clipboard.isEmpty()
            && ( !this.folderProperties.inFavorites || App.clipboard.containShortcutsOnly() );

        this.actions.paste.setDisabled(!canPaste);
        canPasteShortcut = !App.clipboard.isEmpty()
            && !App.clipboard.containShortcutsOnly()
            && ( (this.folderProperties.system == 0) && (this.folderProperties.template_type != 'file') );
        this.actions.pasteShortcut.setDisabled(!canPasteShortcut);

        canDownload = sm.hasSelection();
        tb = this.getTopToolbar();
        if(!Ext.isEmpty(tb)){
            db = tb.find('iconCls', 'icon32-download');
            if(!Ext.isEmpty(db)){
                db = db[0];
                if(canDownload){
                    s = sm.getSelections();
                    for (i = 0; i < s.length; i++) {
                        if(s[i].get('template_type') != 'file') canDownload = false;
                    }
                }
                db.setDisabled( !canDownload );
            }
        }
        this.actions.downloadArchived.setDisabled(!canDownload);
        if(this.previewPanel) this.previewPanel.loadPreview(id);
        r = sm.getSelected();
        data = r ? r.data : null;
        this.fireEvent('selectionchange', sm, data);
    }
    ,onContextMenu: function(e) {
        e.stopPropagation();
        e.preventDefault();
        this.onRowContextMenu(this.grid, -1, e);
    }
    ,onRowContextMenu: function(grid, rowIndex, e) {
        if(e){
            e.stopPropagation();
            e.preventDefault();
        }
        grid.selModel.selectRow(rowIndex, false);
        row = grid.store.getAt(rowIndex);
        if(Ext.isEmpty(this.contextMenu)){/* create context menu if not aleready created */
            this.createMenuButton = new Ext.menu.Item({
                text: L.Create
                ,hideOnClick: false
                ,menu:[]
            });
            this.contextMenu = new Ext.menu.Menu({
                items: [
                this.actions.open
                ,this.actions.openItemLocation
                ,'-'
                ,{
                    text: L.View
                    ,hideOnClick: false
                    ,menu: [{
                        xtype: 'menucheckitem'
                        ,text: L.Descendants
                        ,checked: this.params.descendants
                        ,scope: this
                        ,handler: this.onShowDescendantsClick
                    }
                    ]
                }
                ,'-'
                ,this.actions.cut
                ,this.actions.copy
                ,this.actions.paste
                ,this.actions.pasteShortcut
                ,'-'
                ,this.actions.uploadNewVersion
                ,this.actions.mergeFiles
                ,this.actions['delete']
                ,this.actions.rename
                ,'-'
                ,this.actions.takeOwnership
                ,'-'
                ,this.createMenuButton
                ,'-'
                ,this.actions.permissions
                ]
            });

        }
        this.contextMenu.items.itemAt(3).menu.items.itemAt(0).setChecked(this.params.descendants);
        this.updateCreateMenuItems(this.createMenuButton);
        this.contextMenu.row = row;
        this.contextMenu.showAt(e.getXY());
        this.grid.getSelectionModel().unlock.defer(500, this.grid.getSelectionModel());
    }
    ,setParams: function(params){
        if(Ext.isEmpty(params.path)) params.path = '/';
        Ext.apply(this.params, Ext.value(params, {}));
        this.grid.getBottomToolbar().changePage(1);
    }
    ,getProperty: function(propertyName){
        if(propertyName == 'nid') propertyName = 'id';
        if(this.folderProperties && this.folderProperties[propertyName]) return this.folderProperties[propertyName];
        return null;
    }
    ,onProxyLoad: function (proxy, o, options) {
        this.path = this.store.baseParams.path;
        this.folderProperties = Ext.apply({}, o.result.folderProperties);

        this.folderProperties.id = this.folderProperties.id;
        this.folderProperties.system = parseInt(this.folderProperties.system, 10);
        this.folderProperties.type = parseInt(this.folderProperties.type, 10);
        this.folderProperties.subtype = parseInt(this.folderProperties.subtype, 10);
        this.folderProperties.pathtext = o.result.pathtext;
        this.fireEvent('viewloaded', proxy, o, options);

        canUpload = !this.folderProperties.inFavorites;
        tb = this.getTopToolbar();
        if(!Ext.isEmpty(tb)){
            ub = tb.find('iconCls', 'icon32-upload');
            if(!Ext.isEmpty(ub)){
                ub = ub[0];
                // canUpload = canUpload && ...;
                ub.setDisabled( !canUpload );
            }
        }
        this.actions.uploadArchive.setDisabled(!canUpload);
        this.actions.uploadMultipleFiles.setDisabled(!canUpload);

        canCreateTask = true; //TODO: review where we can create tasks
        this.actions.createTask.setDisabled(false);
        this.actions.createEvent.setDisabled(false);

        canCreateFolder = (this.folderProperties.id > 0 );
        this.actions.createFolder.setDisabled(!canCreateFolder) ;

        this.updateCreateMenuItems();
        this.filtersPanel.updateFacets(o.result.facets, options);
    }
    ,onStoreLoad: function(store, recs, options) {
        Ext.each(recs, function(r){
            cfg = Ext.value(r.get('cfg'), {});
            r.set('iconCls', Ext.isEmpty(cfg.iconCls) ? getItemIcon(r.data) : cfg.iconCls );
        }, this);

        pt = this.grid.getBottomToolbar();
        pagingVisible = (store.reader.jsonData.total > pt.pageSize);
        if(pagingVisible) pt.show();
        else pt.hide();
        this.grid.syncSize();
        this.syncSize();
        App.mainViewPort.selectGridObject(this.grid);

    }
    ,updateCreateMenuItems: function(menuButton) {
        if(Ext.isEmpty(menuButton)){
            tb = this.getTopToolbar();
            if(!tb) return;
            cmi = tb.find('iconCls', 'icon32-create');
            if(Ext.isEmpty(cmi)) return;
            menuButton = cmi[0];
        }
        updateMenu(menuButton, getMenuConfig(this.folderProperties.id, this.folderProperties.path, this.folderProperties.template_id), this.onCreateObjectClick, this);

        menuButton.setDisabled(menuButton.menu.items.getCount() < 1);
    }
    ,onCreateObjectClick: function(b, e) {
        data = Ext.apply({}, b.data);
        data.pid = this.folderProperties.id;
        data.path = this.folderProperties.path;
        data.pathtext = this.folderProperties.pathtext;
        tr = CB.DB.templates.getById(data.template_id);
        if(tr && (tr.get('cfg').createMethod == 'inline') )
            CB_Objects.create(data, this.processCreateInlineObject, this);
        else this.fireEvent('createobject', data, e);
    }
    ,processCreateInlineObject: function (r, e) {
        this.getEl().unmask();
        if(r.success !== true) return;
        this.grid.store.loadData(r, true);
        idx = this.grid.store.findExact('nid', parseInt(r.data.nid, 10));
        this.grid.selModel.clearSelections();
        if(idx >= 0) this.grid.selModel.selectRow(idx);
        this.justAddedFolder = r.data.nid;
        this.fireEvent('objectupdated', { data: {id: r.data.nid, pid: r.data.pid } }, e);
        this.onRenameClick(r, e);
    }

    ,onDownClick: function(key, e) {
        if(this.grid.selModel.hasSelection() || (this.grid.store.getCount() < 1)) return false;
        this.grid.selModel.selectRow(0);
    }
    ,onRowDblClick: function( ) {
        r = this.grid.getSelectionModel().getSelected();
        if(!r) return;
        if( App.isFolder( r.get('template_id') ) ) this.onBrowseClick();
        else this.onOpenClick();
    }
    ,onOpenClick: function(b, e) {
        if(!this.grid.selModel.hasSelection()) return;
        row = this.grid.selModel.getSelected();
        if( !App.openObject(row.get('template_id'), row.get('nid'), e) ) {
            this.onBrowseClick(b, e);
        }
    }
    ,onBrowseClick: function(b, e) {
        if(!this.grid.selModel.hasSelection()) return;
        row = this.grid.selModel.getSelected();
        if(Ext.isEmpty(this.grid.store.baseParams.query) ){
            path = String(this.params.path).split('/');
            path.push(row.get('nid'));
            this.fireEvent('changeparams', {path: path.join('/')});
        }else{
            this.fireEvent(
                'changeparams'
                ,{
                    path: row.get('nid')
                    ,query: ''
                    ,descendants: false
                }
            );
        }
    }
    ,onOpenItemLocationClick: function(b, e){
        if(this.actions.openItemLocation.isDisabled()) return;
        if(!this.grid.selModel.hasSelection()) return;
        row = this.grid.selModel.getSelected();
        this.fireEvent('changeparams', {path: row.get('pid'), descendants: false, query:'' }, e);
        App.locateObject(row.data.nid, row.data.pid);
    }
    ,onCutClick: function(buttonOrKey, e) {
        if(this.actions.cut.isDisabled()) return;
        this.onCopyClick(buttonOrKey, e);
        App.clipboard.setAction('move');
    }
    ,onCopyClick: function(buttonOrKey, e) {
        if(this.actions.copy.isDisabled()) return;
        s = this.grid.selModel.getSelections();
        if(Ext.isEmpty(s)) return;
        rez = [];
        for (var i = 0; i < s.length; i++) {
            rez.push({
                id: s[i].get('nid')
                ,name: s[i].get('name')
                ,system: s[i].get('system')
                ,type: s[i].get('type')
                ,subtype: s[i].get('subtype')
                ,iconCls: s[i].get('iconCls')
            });
        }
        App.clipboard.set(rez, 'copy');
    }
    ,onPasteClick: function(buttonOrKey, e) {
        if(this.actions.paste.isDisabled()) return;
        App.clipboard.paste(this.folderProperties.id, null);
    }
    ,onPasteShortcutClick: function(buttonOrKey, e) {
        if(this.actions.pasteShortcut.isDisabled()) return;
        App.clipboard.paste(this.folderProperties.id, 'shortcut');
    }
    ,onPermissionsClick: function(b, e){
        if(this.actions.permissions.isDisabled()) return;
        s = this.grid.selModel.getSelections();
        id = Ext.isEmpty(s) ? this.folderProperties.id : s[0].get('nid');

        if(App.activateTab(null, id, CB.SecurityPanel)) return;
        App.addTab(null, new CB.SecurityPanel({data: { id: id }}));
    }
    ,onMergeFilesClick: function(buttonOrKey, e) {
        if(this.actions.mergeFiles.isDisabled()) return;
        s = this.grid.selModel.getSelections();
        if(Ext.isEmpty(s) || (s.length <2) ) return;
        rez = [];
        for (var i = 0; i < s.length; i++) {
            rez.push(s[i].get('nid'));
        }
        Ext.Msg.confirm( L.MergingFiles, L.MergeFilesConfirmation, function(b){
            if(b == 'yes') CB_Files.merge(rez, this.processMergingFiles, this);
        }, this );
    }
    ,processMergingFiles: function(r, e){
        this.onReloadClick();
        App.mainViewPort.onProcessObjectsDeleted(r, e);
    }
    ,onPropertiesClick: function(buttonOrKey, e) {
        if(this.actions.properties.isDisabled()) return;
        if(!this.grid.selModel.hasSelection()) return;
    }
    ,onCreateFolderClick: function(b, e){
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_Browser.createFolder(this.folderProperties.id, this.processCreateFolder, this);
    }
    ,processCreateFolder: function (r, e) {
        this.getEl().unmask();
        if(r.success !== true) return;
        this.grid.store.loadData(r, true);
        idx = this.grid.store.findExact('nid', parseInt(r.data.nid, 10));
        this.grid.selModel.clearSelections();
        if(idx >= 0) this.grid.selModel.selectRow(idx);
        this.justAddedFolder = r.data.nid;
        this.fireEvent('objectupdated', { data: {id: r.data.nid, pid: r.data.pid } }, e);
        this.onRenameClick(r, e);
    }
    ,onCreateCaseClick: function(b, e){
        if(Ext.isEmpty(b.data)) {
            b.data = {};
        }
        b.data.pid = this.folderProperties.id;
        this.fireEvent('casecreate', b, e);
    }
    ,onCreateTaskClick: function(b, e) {
        this.fireEvent('taskcreate', {
            data: {
                type: 6
                ,template_id: App.config.default_task_template
                ,pid: this.folderProperties.id
                ,path: this.folderProperties.path
                ,pathtext: this.folderProperties.pathtext
            }
        });
    }
    ,onFiltersChange: function(filters){
        params = Ext.apply({}, this.params);
        params.filters = filters;
        this.fireEvent('changeparams', params);
    }
    ,onCreateEventClick: function(b, e) {
        this.fireEvent('taskcreate', {
            data: {
                type: 7
                ,template_id: App.config.default_event_template
                ,pid: this.folderProperties.id
                ,path: this.folderProperties.path
                ,pathtext: this.folderProperties.pathtext
            }
        });
    }
    ,onReloadClick: function(b, e){
        this.grid.store.load();
    }
    ,onRenameClick: function(b, e){
        if(!this.grid.selModel.hasSelection()) return;
        this.grid.stopEditing(true);
        idx = this.grid.store.indexOf(this.grid.selModel.getSelected());
        this.allowRename = true;
        this.grid.startEditing(idx, this.grid.getColumnModel().findColumnIndex('name'));
    }
    ,onDeleteClick: function(b, e) {
        s = this.grid.selModel.getSelections();
        if(Ext.isEmpty(s)) return;
        Ext.Msg.confirm(
            L.DeleteConfirmation
            ,(s.length == 1)
                ? L.DeleteConfirmationMessage + ' "' + s[0].get('name') + '"?'
                : L.DeleteSelectedConfirmationMessage
            ,this.onDelete
            ,this
        );
    }
    ,onDelete: function (btn) {
        if(btn !== 'yes') return;
        s = this.grid.selModel.getSelections();
        if(Ext.isEmpty(s)) return;
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        ids = [];
        Ext.each(s, function(r){ ids.push(r.get('nid'));}, this);
        CB_BrowserView['delete'](ids, this.processDelete, this);
    }
    ,processDelete: function(r, e){
        this.getEl().unmask();
        record = this.grid.getSelectionModel().getSelected();
        if(record){
            this.grid.getView().focusRow(record.store.indexOf(r));
        }
        App.mainViewPort.onProcessObjectsDeleted(r, e);
    }
    ,onObjectsDeleted: function(ids){
        this.selectIdx = -1;
        for (var i = 0; i < ids.length; i++) {
            idx = this.grid.store.findExact('nid', parseInt(ids[i], 10));
            if(idx >=0){
                if(this.grid.getSelectionModel().isSelected(idx)) this.selectIdx = idx;
                this.grid.store.removeAt(idx);
            }
        }
        if(this.selectIdx > -1){
            if(this.grid.store.getCount() > idx) this.grid.getSelectionModel().selectRow(idx);
            else this.grid.getSelectionModel().selectLastRow();
        }
        /* TODO: also delete all visible nodes(links) that are links to the deleted node or any its child */
    }
    ,onObjectsSaved: function(form, e){
        if(!Ext.isEmpty(form.data.id) && (this.justAddedFolder == form.data.id) ){
            delete this.justAddedFolder;
            return;
        }
        if(this.folderProperties.id == form.data.pid) this.onReloadClick();
    }
    ,onUploadClick: function(b, e) { this.fireEvent('fileupload', {pid: this.folderProperties.id, uploadType: b.uploadType}, e); }
    ,onUploadNewVersionClick: function(b, e){
        if(!this.grid.selModel.hasSelection()) return;
        row = this.grid.selModel.getSelected();
        this.fireEvent('fileupload', {id: row.get('nid')}, e);
    }
    ,onDownloadClick: function(b, e) {
        ids = [];
        Ext.each(this.grid.getSelectionModel().getSelections(), function(r){ids.push(r.get('nid'));}, this);
        if(!Ext.isEmpty(ids)) this.fireEvent('filedownload', ids, b.zipped, e);
    }
    ,onEastPanelButtonClick: function(b, e){
        if(b.pressed){
            this.eastPanel.getLayout().setActiveItem(b.itemIndex);
            this.eastPanel.show();
            if(b.itemIndex == 0){
                r = this.grid.getSelectionModel().getSelected();
                if(r) this.previewPanel.loadPreview(r.get('nid'));
            }
        }else{
            this.eastPanel.hide();
        }
        this.syncSize();
    }
    ,onShowDescendantsClick: function(cb, e){
        this.fireEvent('showdescendants', !cb.checked, e);
    }
    ,onTakeOwnershipClick: function(b, e){
        Ext.Msg.confirm(L.TakeOwnership, L.TakeOwnershipConfirmation, function(b, e){
            if(b == 'yes'){
                ids = [];
                s = this.grid.getSelectionModel().getSelections();
                if(Ext.isEmpty(s)) return;
                for (var i = 0; i < s.length; i++) {
                    ids.push(s[i].get('nid'));
                }
                CB_Browser.takeOwnership(ids, this.processTakeOwnership, this);
            }
        }, this);
    }
    ,processTakeOwnership: function(r, e){
        if(r.success !== true) return;
        this.onReloadClick();
    }

});

Ext.reg('CBFolderViewGrid', CB.FolderViewGrid);
