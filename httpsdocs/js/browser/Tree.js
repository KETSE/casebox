
Ext.namespace('CB.browser');

Ext.define('CB.browser.Tree', {
    extend: 'Ext.tree.TreePanel'
    ,alias: 'widget.CBBrowserTree'

    ,rootVisible: false
    ,scrollable: true
    ,containerScroll: true
    ,animate: false
    ,lines: false
    ,useArrows: true
    // ,showFoldersContent: false
    ,border: false
    ,bodyBoder: false
    ,style: {
        border: '0'
    }
    ,bodyStyle: {
        border: '0'
    }
    ,hideToolbar: true
    ,stateful: false
    ,stateId: 'btree' //browser tree

    ,initComponent: function(){
        if(Ext.isEmpty(this.data)) {
            this.data = {};
        }

        this.actions = {
            edit: new Ext.Action({
                text: L.Edit
                ,scope: this
                ,handler: this.onEditClick
            })

            ,expand: new Ext.Action({
                text: L.Expand
                ,scope: this
                ,handler: this.onExpandClick
            })

            ,collapse: new Ext.Action({
                text: L.Collapse
                ,scope: this
                ,handler: this.onCollapseClick
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

            ,reload: new Ext.Action({
                iconCls: 'icon-refresh'
                ,text: L.Reload
                ,disabled: true
                ,scope: this
                ,handler: this.onReloadClick
            })

            ,'delete': new Ext.Action({
                text: L.Delete
                ,iconCls: 'i-trash'
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

            ,star: new Ext.Action({
                iconCls: 'i-star'
                ,text: L.Star
                ,scope: this
                ,handler: this.onStarClick
            })

            ,unstar: new Ext.Action({
                iconCls: 'i-unstar'
                ,text: L.Unstar
                ,scope: this
                ,handler: this.onUnstarClick
            })

            ,permissions: new Ext.Action({
                text: L.Permissions
                ,iconCls: 'icon-key'
                ,scope: this
                ,disabled: true
                ,handler: this.onPermissionsClick
            })

        };

        //context menu item for create new items menu
        this.createItem = new Ext.menu.Item({
            text: L.Create
            ,hideOnClick: false
            ,menu:[]
        });

        this.editor = new Ext.Editor({
            field: {
                xtype: 'textfield'
                ,allowBlank: false
                ,selectOnFocus: true
            }
        });

        this.editor.on('beforecomplete', this.onBeforeEditComplete, this);

        var rootConfig = Ext.valueFrom(this.config.data.rootNode, {});
        rootConfig = Ext.apply(
            {
                nid: Ext.valueFrom(this.rootId, '')
                ,expanded: true
                ,editable: false
                ,leaf: false
                ,iconCls:'icon-folder'
            }
            ,rootConfig
        );
        rootConfig.text = Ext.valueFrom(rootConfig.text, rootConfig.name);

        this.store = Ext.create('Ext.data.TreeStore', {
            root: rootConfig
            ,proxy: {
                type: 'direct'
                ,directFn: CB_BrowserTree.getChildren
                ,paramsAsHash: true
                ,extraParams: {
                    from: 'tree'
                }
            }
            ,listeners: {
                scope: this
                ,beforeload: function(store, record, eOpts) {
                    var path = record.config.node
                        ? record.config.node.getPath('nid')
                        : '';

                    var p = {
                        path: path
                        // ,showFoldersContent: this.showFoldersContent
                    };
                    Ext.apply(store.proxy.extraParams, p);
                }
            }
        });


        Ext.apply(this, {
            header: false
            ,hideHeaders: true
            ,viewConfig: {
                cls: 'browser-tree'
                ,border: false
                ,bodyBoder: false
                ,scrollable: true
                ,idProperty: 'nid'
                ,loadMask: false
                ,plugins: {
                    ptype: 'CBDDTree'
                    ,idProperty: 'nid'
                }
            }
            ,columns: {
                items: [
                    {
                        xtype: 'treecolumn'
                        ,dataIndex: 'text'

                        ,renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                            metaData.tdCls = record.get('cls') + ' x-grid-item-gray';
                            return value;
                        }
                    }
                ],
                defaults: {
                    flex: 1
                }
            }
            ,listeners:{
                scope: this
                ,afterrender: this.restoreTreeState
                ,beforeitemappend: this.onBeforeNodeAppend
                // ,load: function(node){ this.sortNode(node); }
                ,dblclick: this.onDblClick
                ,itemcontextmenu: this.onContextMenu
                ,beforedestroy: this.onBeforeDestroy
                ,itemkeydown: this.onItemKeyDown
            }
            ,selModel: new Ext.selection.TreeModel({
                allowDeselect: false
                ,listeners: {
                    scope: this
                    ,focuschange: this.onNodeFocusChange
                    ,selectionchange: this.onSelectionChange
                }
            })
            // ,plugins: [ new CB.DD.Tree({idProperty: 'nid'}) ]
        });

        this.callParent(arguments);

        if(!isNaN(this.rootId)) {
            CB_BrowserTree.getRootProperties(
                this.rootId
                ,function(r, e){
                    Ext.apply(this.getRootNode().data, r.data);
                    this.onBeforeNodeAppend(this, null, this.getRootNode());
            }, this);
        }

        this.enableBubble(['createobject']);

        App.clipboard.on('pasted', this.onClipboardAction, this);
        App.mainViewPort.on('savesuccess', this.onObjectsSaved, this);
        App.mainViewPort.on('fileuploaded', this.onObjectsSaved, this);
        App.mainViewPort.on('taskupdated', this.onObjectsSaved, this);
        App.mainViewPort.on('taskcreated', this.onObjectsSaved, this);
        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
        App.on('objectchanged', this.onObjectsSaved, this);
    }

    ,onItemKeyDown: function(tree, record, item, index, e, eOpts){
        switch(e.getKey()) {
            case e.X:
                if(e.ctrlKey && !e.shiftKey && !e.altKey) {
                    e.stopEvent();
                    this.onCutClick();
                }
                break;

            case e.C:
                if(e.ctrlKey && !e.shiftKey && !e.altKey) {
                    e.stopEvent();
                    this.onCopyClick();
                }
                break;

            case e.V:
                if(e.ctrlKey && !e.shiftKey && !e.altKey) {
                    e.stopEvent();
                    this.onPasteClick();
                } else if(e.ctrlKey && !e.shiftKey && e.altKey) {
                    e.stopEvent();
                    this.onPasteShortcutClick();
                }
                break;

            case e.DELETE:
                if(!e.ctrlKey && !e.shiftKey && !e.altKey) {
                    e.stopEvent();
                    this.onDeleteClick();
                }
                break;

            case e.F2:
                if(!e.ctrlKey && !e.shiftKey && !e.altKey) {
                    e.stopEvent();
                    this.onRenameClick();
                }
                break;

            case e.R:
            case e.F2:
                if((!e.ctrlKey && !e.altKey) ||
                    (e.ctrlKey && (e.getKey() == e.R))
                ) {
                    e.stopEvent();
                    this.onReloadClick();
                }
                break;
        }
    }

    ,onBeforeNodeAppend: function(parent, node){
        // node.setId(Ext.id());

        // node id could be literal, so we cannot eval it to int
        // node.data.nid = Ext.Number.from(node.data.nid, null);

        node.data.system = Ext.Number.from(node.data.system, 0);
        node.set('text', node.data.name);

        if(Ext.isEmpty(node.data.iconCls)) {
            if(node.data.cfg && node.data.cfg.iconCls){
                node.set('iconCls', node.data.cfg.iconCls );
            } else {
                node.set('iconCls', getItemIcon(node.data) );
            }
        }

        node.data.editable = false;
        node.draggable = (node.data.system === 0);
        if(node.data.acl_count > 0) {
            node.set('cls', 'node-has-acl');
        }
    }

    ,onBeforeDestroy: function(p){
        App.clipboard.un('pasted', this.onClipboardAction, this);
        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
    }

    ,onClipboardAction: function(pids){
        this.getRootNode().cascadeBy({
            before: function(n){
                if(pids.indexOf(n.data.nid) >= 0 ) {
                    this.store.reload({node: n});
                    // n.reload();
                }
            }
            ,scope: this
        });
    }

    ,onObjectsSaved: function(form, e){
        if(!this.rendered) {
            return;
        }

        var n = this.getRootNode()
            ,data = form.pid
                ? form
                : form.data;
        data = Ext.valueFrom(data, {});

        if(n) {
            n.cascadeBy({
                before: function(n){
                    if(n.data.nid == data.pid) {
                        this.store.reload({node: n});
                    }
                }
                ,scope: this
            });
        }
    }

    ,onNodeFocusChange: function (sm, oldR, newR, eOpts) {
        this.lastFocusedRecord = Ext.valueFrom(newR, oldR);
    }

    ,onSelectionChange: function (sm, selection, ev) {
        var node = Ext.isEmpty(selection)
            ? null
            : selection[0];

        if(Ext.isEmpty(node)){
            this.actions.edit.setHidden(true);
            this.actions.cut.setDisabled(true);
            this.actions.copy.setDisabled(true);
            this.actions.paste.setDisabled(true);
            this.actions.pasteShortcut.setDisabled(true);
            this.actions['delete'].setDisabled(true);
            this.actions.rename.setDisabled(true);
            this.actions.reload.setDisabled(true);
            this.actions.permissions.setDisabled(true);

        } else {
            var canOpen = true;
            this.actions.edit.setHidden(!canOpen);

            var canExpand = (!node.isExpanded() && ((!node.loaded) || node.hasChildNodes()));
            this.actions.expand.setHidden(!canExpand);

            var canCollapse = node.isExpanded() && node.hasChildNodes();
            this.actions.collapse.setHidden(!canCollapse);
            if(this.contextMenu) {
                this.contextMenu.items.getAt(3).setVisible(canOpen || canExpand || canCollapse);
            }

            var canCopy = (node.data.system === 0);
            this.actions.cut.setDisabled(!canCopy);
            this.actions.copy.setDisabled(!canCopy);

            var canPaste = !App.clipboard.isEmpty()
                && App.clipboard.containShortcutsOnly()
                && ( node.data.system === 0 );
            this.actions.paste.setDisabled(!canPaste);

            var canPasteShortcut = !App.clipboard.isEmpty()
                && !App.clipboard.containShortcutsOnly()
                && ( node.data.system === 0 );
            this.actions.pasteShortcut.setDisabled(!canPasteShortcut);

            var canDelete = (node.data.system === 0);
            this.actions['delete'].setDisabled(!canDelete) ;

            var canRename = (node.data.system === 0);
            this.actions.rename.setDisabled(!canRename) ;

            this.actions.reload.setDisabled(false) ;

            this.actions.permissions.setDisabled(!Ext.isNumeric(node.data.nid) || (node.data.nid < 1));
        }

        this.fireEvent('selectionchanged');
    }

    ,onContextMenu: function (tree, record, item, index, e, eOpts) {
        if(Ext.isEmpty(this.contextMenu)){/* create context menu if not aleready created */
            this.contextMenu = new Ext.menu.Menu({
                items: [
                this.actions.edit
                ,'-'
                ,this.actions.cut
                ,this.actions.copy
                ,this.actions.paste
                ,this.actions.pasteShortcut
                ,'-'
                ,this.actions.reload
                ,this.actions['delete']
                ,this.actions.rename
                ,this.actions.star
                ,this.actions.unstar
                ,'-'
                ,this.createItem
                ,'-'
                ,this.actions.permissions
                ]
            });

        }
        // node.select();
        e.stopEvent();
        this.contextMenu.node = record;

        var canStar = !App.Favorites.isStarred(record.data.nid);
        this.actions.star.setHidden(!canStar) ;
        this.actions.unstar.setHidden(canStar) ;

        this.contextMenu.showAt(e.getXY());
    }

    ,updateCreateMenu: function (menuConfig) {
        updateMenu(
            this.createItem
            ,menuConfig
            ,this.onCreateObjectClick
            ,this
        );
    }

    ,onCreateObjectClick: function(b, e) {
        var data = Ext.clone(b.config.data);
        data.pid = this.contextMenu.node.data.nid;
        data.path = this.contextMenu.node.getPath('nid');
        data.pathtext = this.contextMenu.node.getPath('text');

        var tr = CB.DB.templates.getById(data.template_id);

        if(tr && (Ext.valueFrom(tr.get('cfg').editMethod, tr.get('cfg').createMethod) === 'inline')) {
            CB_Objects.create(data, this.processCreateInlineObject, this);
        } else {
            this.fireEvent('createobject', data, e);
        }
    }

    ,processCreateInlineObject: function(r, e){
        this.getEl().unmask();
        if(!r || (r.success !== true)) {
            return;
        }

        r.data.nid = Ext.valueFrom(r.data.nid, r.data.id);
        delete r.data.id;
        this.root.cascadeBy({
            before: function(node){
                //find parent node
                if(node.data.nid == r.data.pid){
                    if(!node.loaded){
                        if(node.isSelected()) {
                            node.expand(
                                false
                                ,false
                                ,function(pn){
                                    var n = pn.findChild('nid', r.data.nid);
                                    if(n) {
                                        this.startEditing(n);
                                    }
                                }
                                ,this
                            );
                        }
                    } else {
                        r.data.loaded = true;
                        node.expand();
                        n = node.appendChild(r.data);
                        this.startEditing(n);
                    }
                }
            }
            ,scope: this
        });
    }

    ,onDblClick: function(b, e){
        var n = this.getSelectionModel().getSelection()[0];

        if(Ext.isEmpty(n)) {
            return;
        }

        if(App.isFolder(n.data.template_id)) {
            return;
        }

        this.onEditClick(b, e);
    }

    ,onEditClick: function (b, e) {
        var n = this.getSelectionModel().getSelection()[0];
        if(Ext.isEmpty(n)) {
            return;
        }

        var tab = App.activateBrowserTab();

        tab.editObject(
            {
                id: n.data.nid
                ,template_id: n.data.template_id
            }
        );
    }

    ,onExpandClick: function (b, e) {
        var n = this.getSelectionModel().getSelection()[0];
        n.expand(
            false
            ,false
            ,function(n){
                // this.onSelectionChange(this.sm, n);
            }
            ,this
        );
    }

    ,onCollapseClick: function (b, e) {
        var n = this.getSelectionModel().getSelection()[0];
        n.collapse();
        // this.onSelectionChange(this.sm, n);
    }

    // ,onShowFoldersChildsClick: function(b, e){
    //     this.showFoldersContent = !b.checked;

    //     this.fireEvent('savestate');

    //     this.store.reload({
    //         node: this.getRootNode()
    //         ,scope: this
    //         ,callback: this.restoreTreeState
    //     });
    // }

    ,getState: function () {
        var rez = {
            paths: []
        };

        if(this.collapsed) {
            rez.collapsed = true;
        }

        rez.width = this.getWidth();

        var p, ok
            ,s = this.getSelectionModel().getSelection()
            ,n = Ext.isEmpty(s)
                ? this.lastFocusedRecord
                : s[0];

        if(!Ext.isEmpty(n) && this.allParentsExpanded(n)) {
            rez.selected = n.getPath('nid');
        }

        this.getRootNode().cascadeBy({
            before: function(n){
                if(n.isExpanded() && this.allParentsExpanded(n)) {
                    rez.paths.push(n.getPath('nid'));
                }
            }
            ,scope: this
        });

        return rez;
    }

    //check if all parents are expanded
    ,allParentsExpanded: function(node) {
        var rez = true
            ,p = node.parentNode;

        while(!Ext.isEmpty(p) && rez) {
            rez = p.isExpanded();
            p = p.parentNode;
        }

        return rez;
    }

    ,restoreTreeState: function() {
        var state = this.deferredState
            ? this.deferredState
            : Ext.state.Manager.getProvider().get(this.stateId);

        //check if nodeContainer ready
        if(this.getView().getNodeContainer()) {
            this.applyState(state);
            delete this.deferredState;
        } else {
            //delay by one sec
            this.deferredState = state;
            Ext.Function.defer(this.restoreTreeState, 1000, this);
        }
    }

    ,applyState: function (state) {
        if(!this.rendered || Ext.isEmpty(state)){
            return;
        }

        if(!Ext.isEmpty(state.paths)) {
            this.expandPaths(state.paths);
        }

        if(!Ext.isEmpty(state.selected) && (state.selected !== '/0')) {
            this.selectPath(
                state.selected
                ,'nid'
                ,'/'
                ,function(success, lastNode) {
                    //sometimes the path selection desnt succeed, probably because of non numeric ids
                    if(!success) {
                        var nid = state.selected.split('/').pop()
                            ,r = this.store.findRecord('nid', nid, 0, false, false, true);
                        if(r) {
                            this.getSelectionModel().select([r]);
                        }
                    }
                }
                ,this
            );
        }
    }

    ,expandPaths: function(paths) {
        this.expandingPath = true;
        var expandIds = [];
        for (var i = 0; i < paths.length; i++) {
            var ids = paths[i].split('/');
            for (var j = 0; j < ids.length; j++) {
                if(!Ext.isEmpty(ids[j]) && (expandIds.indexOf(ids[j]) < 0)) {
                    expandIds.push(ids[j]);
                }
            }
        }

        if(!Ext.isEmpty(expandIds)) {
            this.expandingIds = expandIds;
            this.recursiveExpandIds();
        } else {
            delete this.expandingPath;
        }
    }

    ,recursiveExpandIds: function() {
        if(Ext.isEmpty(this.expandingIds)) {
            delete this.expandingPath;
            this.enableStateSave();
            return;
        }

        var id = this.expandingIds.shift()
            ,rec = this.store.findRecord('nid', id, 0, false, false, true);
            node = rec ? this.store.getNodeById(rec.get('id')) : null;
        if(node) {
            node.expand(
                false
                ,this.recursiveExpandIds
                ,this
            );
        } else {
            this.recursiveExpandIds();
        }
    }

    ,enableStateSave: function() {
        this.stateful = true;
        this.addStateEvents([
            'afteritemexpand'
            ,'afteritemcollapse'
            ,'beforedestroy'
            ,'selectionchange'
            ,'savestate'
        ]);
    }

    ,onCutClick: function(b, e) {
        if(this.actions.cut.isDisabled()) {
            return;
        }
        this.onCopyClick(b, e);
        App.clipboard.setAction('move');
    }

    ,onCopyClick: function(b, e) {
        var n = this.selModel.getSelection()[0];
        if(Ext.isEmpty(n) || this.actions.copy.isDisabled()) {
            return;
        }

        App.clipboard.set(
            {
                id: n.data.nid
                ,name: n.data.name
                ,system: n.data.system
                ,type: n.data.type
                ,iconCls: n.data.iconCls
            }
            ,'copy'
        );
    }
    ,onPasteClick: function(b, e){
        var n = this.selModel.getSelection()[0];
        if(Ext.isEmpty(n) || this.actions.paste.isDisabled()) {
            return;
        }
        App.clipboard.paste(n.data.nid);
    }

    ,onPasteShortcutClick: function(b, e){
        var n = this.selModel.getSelection()[0];
        if(Ext.isEmpty(n) || this.actions.pasteShortcut.isDisabled()) {
            return;
        }
        App.clipboard.paste(n.data.nid, 'shortcut');
    }

    ,onPermissionsClick: function(b, e){
        var n = this.selModel.getSelection()[0];
        if(Ext.isEmpty(n) || this.actions.permissions.isDisabled()) {
            return;
        }
        App.mainViewPort.openPermissions(n.data.nid);
    }

    ,onRenameClick: function(b, e){
        this.startEditing(this.getSelectionModel().getSelection()[0]);
    }

    ,onReloadClick: function(b, e){
        var node = this.getSelectionModel().getSelection()[0];
        if(node && !node.isExpanded()) {
            node.expand();
        } else {
            this.store.reload({
                node: node
            });
        }
    }

    ,startEditing: function(node) {
        Ext.Function.defer(
            function(){
                this.editor.editNode = node;
                this.editor.startEdit(
                    this.getView().getNode(node)
                    ,Ext.util.Format.htmlDecode(node.data.name)
                );
            }
            ,10
            ,this
        );
    }

    ,onBeforeEditComplete: function(editor, newVal, oldVal) {
        var n = editor.editNode;
        n.set('text', Ext.util.Format.htmlEncode(newVal));
        if(newVal === oldVal) {
            return;
        }
        editor.cancelEdit();
        this.getEl().mask(L.Processing, 'x-mask-loading');

        CB_BrowserTree.rename(
            {
                path: n.getPath('nid')
                ,name: newVal
            }
            ,this.processRename
            ,this
        );

        return false;
    }

    ,processRename: function(r, e){
        this.getEl().unmask();
        if(!r || (r.success !== true)) {
            return;
        }

        this.getRootNode().cascadeBy({
            before: function (n){
                if(n.data.nid == r.data.id){
                    n.data.name = r.data.newName;
                    n.set('text', r.data.newName);
                }
            }
            ,scope: this
        });
        this.fireEvent('afterrename', this, r, e);
    }

    ,onDeleteClick: function(b, e){
        Ext.Msg.confirm(
            L.DeleteConfirmation
            ,L.DeleteConfirmationMessage + ' "' + this.getSelectionModel().getSelection()[0].data.text + '"?'
            ,this.onDelete
            ,this
        );
    }

    ,onDelete: function (btn) {
        if(btn !== 'yes') {
            return;
        }

        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_BrowserTree['delete'](
            this.getSelectionModel().getSelection()[0].getPath('nid')
            ,this.processDelete
            ,this
        );
    }

    ,processDelete: function(r, e){
        this.getEl().unmask();
        App.mainViewPort.onProcessObjectsDeleted(r, e);
    }

    ,onObjectsDeleted: function(ids){
        var sm = this.getSelectionModel()
            ,deleteNodes = [];

        this.getRootNode().cascadeBy({
            before: function(n){
                if(ids.indexOf(n.data.nid) >= 0){
                    if(sm.isSelected(n)){
                        var nn = n.isLast()
                            ? (
                                n.isFirst()
                                ? n.parentNode
                                : n.previousSibling
                            )
                            : n.nextSibling;
                        sm.select([nn]);
                    }
                    deleteNodes.push(n);
                }
            }
            ,scope: this
        });

        for (var i = 0; i < deleteNodes.length; i++) {
            deleteNodes[i].remove(true);
        }
        /* TODO: also delete all visible nodes(links) that are links to the deleted node or any its child */
    }

    ,onStarClick: function(b, e) {
        var n = this.contextMenu.node
            ,d = n.data
            ,data = {
                id: d.nid
                ,name: d.name
                ,iconCls: d.iconCls
                ,pathText: d.path
                ,path: n.getPath('nid', '/')
            };

        App.Favorites.setStarred(data);
    }

    ,onUnstarClick: function(b, e) {
        App.Favorites.setUnstarred(this.contextMenu.node.data.nid);
    }
});
