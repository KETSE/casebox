Ext.namespace('CB.browser.view');

CB.browser.view.Grid = Ext.extend(CB.browser.view.Interface,{
    hideBorders: true
    ,initComponent: function(){

        var columns = [
            { header: 'ID', width: 80, dataIndex: 'nid', hidden: true}
            ,{header: L.Name, width: 300, dataIndex: 'name', renderer: function(v, m, r, ri, ci, s){
                    m.css = 'icon-grid-column-top '+ r.get('iconCls');
                    if(r.get('acl_count') > 0) {
                        m.css += ' node-has-acl';
                    }

                    v = Ext.util.Format.htmlEncode(v);
                    m.attr = Ext.isEmpty(v) ? '' : "title='"+v+"'";
                    rez = '<span class="n">' + Ext.value(r.get('hl'), v) + '</span>';
                    if( (this.hideArrows !== true) && r.get('has_childs')) {
                        rez += '<img class="click icon-arrow3" src="'+Ext.BLANK_IMAGE_URL+'" />';
                    }
                    vi = getVersionsIcon(r.get('versions'));
                    if(!Ext.isEmpty(vi)) rez = '<span class="ver_count '+vi+'" title="'+L.FileVersionsCount+'">&nbsp;</span>'+ rez;
                    return rez;
                },scope: this
                ,editable: true
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
                    if(!Ext.isEmpty(r.get('content'))) {
                        rp.body += r.get('content');
                    }

                    if(Ext.isEmpty(rp.body)) {
                        return '';
                    }
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
                    if(!this.allowRename) {
                        return false;
                    }
                    e.grid.getColumnModel().setEditor(
                        e.column
                        ,new Ext.form.TextField({selectOnFocus: true})
                    );
                    delete this.allowRename;
                    return true;
                }
                ,afteredit: function(e){
                    if(e.value == e.originalValue) {
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
                ,rowdblclick: this.onRowDblClick
                // ,contextmenu: this.onContextMenu
                // ,rowcontextmenu: this.onRowContextMenu
                // ,beforedestroy: this.onBeforeDestroy
                ,cellclick: this.onCellClick
                // ,activate: App.onComponentActivated
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
                    ,fn: this.onEnterKeyPress
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
                //     key: Ext.EventObject.DELETE
                //     ,alt: false
                //     ,ctrl: false
                //     ,stopEvent: true
                //     ,fn: this.onDeleteClick
                //     ,scope: this
                // },{
                //     key: Ext.EventObject.F2
                //     ,alt: false
                //     ,ctrl: false
                //     ,stopEvent: true
                //     ,fn: this.onRenameClick
                //     ,scope: this
                // },{
                //     key: Ext.EventObject.F5
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
                ,'-'
                ,'delete'
            ]
        );
    }

    ,onCellClick: function(grid, rowIndex, colIndex, e){
        el = e.getTarget();
        if(el && el.classList.contains('icon-arrow3')) {
            this.fireEvent('changeparams', {path: this.grid.store.getAt(rowIndex).get('nid')});
        }
    }

    ,onScrollerDragDrop: function(targetData, source, e, sourceData){
        App.DD.execute({
            action: e
            ,targetData: this.refOwner.folderProperties
            ,sourceData: sourceData.data
        });
    }

    ,onSelectionChange: function() {
        var s = this.grid.getSelectionModel().getSelections();
        for (var i = 0; i < s.length; i++) {
            s[i] = s[i].data;
        }
        this.fireEvent('selectionchange', s);
    }

    ,onRowDblClick: function(g, ri, e) {
        this.fireEvent('objectopen', g.store.getAt(ri).data);
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

        this.grid.selModel.selectRow(0);
    }
});

Ext.reg('CBBrowserViewGrid', CB.browser.view.Grid);
