
Ext.namespace('CB.browser');

Ext.define('CB.browser.Tree', {
    extend: 'Ext.tree.TreePanel'
    ,alias: 'widget.CBBrowserTree'

    ,rootVisible: false
    ,autoScroll: true
    ,containerScroll: true
    ,animate: false
    ,lines: false
    ,useArrows: true
    ,showFoldersContent: false
    ,border: false
    ,bodyBoder: false
    ,style: {
        border: '0'
    }
    ,bodyStyle: {
        border: '0'
    }
    ,hideToolbar: true
    ,stateful: true
    ,stateId: 'btree' //browser tree
    ,stateEvents: ['itemexpand', 'itemcollapse', 'beforedestroy', 'selectionchange', 'savestate']

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

            ,openInNewWindow: new Ext.Action({
                text: L.OpenInNewWindow
                ,scope: this
                ,handler: this.onOpenInNewWindowClick
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

            ,createShortcut: new Ext.Action({
                text: L.CreateShortcut
                ,scope: this
                ,disabled: true
                ,handler: this.onCreateShortcutClick
            })

            ,'delete': new Ext.Action({
                text: L.Delete
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
                ,disabled: true
                ,scope: this
                ,handler: this.onReloadClick
            })

            ,properties: new Ext.Action({
                text: L.Properties
                ,scope: this
                ,disabled: true
                ,handler: this.onPropertiesClick
            })

            ,permissions: new Ext.Action({
                text: L.Permissions
                ,iconCls: 'icon-key'
                ,scope: this
                ,disabled: true
                ,handler: this.onPermissionsClick
            })

        };

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
                        ,showFoldersContent: this.showFoldersContent
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
                ,autoScroll: true
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

        CB.browser.Tree.superclass.initComponent.apply(this, arguments);
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
        App.mainViewPort.on('favoritetoggled', this.onObjectsSaved, this);
        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
        App.mainViewPort.on('objectupdated', this.onObjectsSaved, this);
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

            case e.ENTER:
                if(!e.ctrlKey && !e.shiftKey && e.altKey) {
                    e.stopEvent();
                    this.onPropertiesClick();
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
        var n = this.getRootNode();
        if(n) {
            n.cascadeBy({
                before: function(n){
                    if(n.data.nid == form.data.pid) {
                        this.store.reload({node: n});
                        // n.reload();
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
            this.actions.openInNewWindow.setHidden(true);
            this.actions.cut.setDisabled(true);
            this.actions.copy.setDisabled(true);
            this.actions.paste.setDisabled(true);
            this.actions.pasteShortcut.setDisabled(true);
            this.actions.createShortcut.setDisabled(true);
            this.actions.createShortcut.setDisabled(true);
            this.actions['delete'].setDisabled(true);
            this.actions.rename.setDisabled(true);
            this.actions.reload.setDisabled(true);
            this.actions.permissions.setDisabled(true);

        } else {
            var canOpen = true;
            this.actions.edit.setHidden(!canOpen);

            var canOpenInNewWindow = true;
            this.actions.openInNewWindow.setHidden(!canOpenInNewWindow);

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
                && ( !this.inFavorites(node) || App.clipboard.containShortcutsOnly() )
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
            this.actions.permissions.setDisabled(false) ;
        }

        this.fireEvent('selectionchanged');
    }

    ,isFavoriteNode: function(node){
        if(Ext.isEmpty(node)) {
            return false;
        }
        return ((node.data.system == 1) && (node.data.type == 1));
    }

    ,inFavorites: function(node) {
        isFavoriteNode = false;
        do{
            isFavoriteNode = this.isFavoriteNode(node);
            node = node.parentNode;
        }while(node && (node.getDepth() > 0) && !isFavoriteNode);
        return isFavoriteNode;
    }

    ,onContextMenu: function (tree, record, item, index, e, eOpts) {
        if(Ext.isEmpty(this.contextMenu)){/* create context menu if not aleready created */
            this.createItem = new Ext.menu.Item({
                text: L.Create
                ,hideOnClick: false
                ,menu:[]
            });
            this.contextMenu = new Ext.menu.Menu({
                items: [
                this.actions.edit
                ,this.actions.openInNewWindow
                ,'-'
                ,{
                    text: L.View
                    ,hideOnClick: false
                    ,menu: [{
                        xtype: 'menucheckitem'
                        ,text: L.ShowFoldersContent
                        ,checked: this.showFoldersContent
                        ,scope: this
                        ,handler: this.onShowFoldersChildsClick
                    }
                    ]
                }
                ,'-'
                ,this.actions.cut
                ,this.actions.copy
                ,this.actions.paste
                ,this.actions.pasteShortcut
                ,'-'
                ,this.actions.createShortcut
                ,this.actions['delete']
                ,this.actions.rename
                ,this.actions.reload
                ,'-'
                ,this.createItem
                ,'-'
                ,this.actions.permissions
                ,this.actions.properties
                ]
            });

        }
        // node.select();
        e.stopEvent();
        this.contextMenu.node = record;

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
        data = Ext.apply({}, b.config.data);
        data.pid = this.contextMenu.node.data.nid;
        data.path = this.contextMenu.node.getPath('nid');
        data.pathtext = this.contextMenu.node.getPath('text');

        var tr = CB.DB.templates.getById(data.template_id);

        if(tr && (Ext.valueFrom(tr.get('cfg').editMethod, tr.get('cfg').createMethod) == 'inline')) {
            CB_Objects.create(data, this.processCreateInlineObject, this);
        } else {
            this.fireEvent('createobject', data, e);
        }
    }

    ,processCreateInlineObject: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) {
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
        n = this.getSelectionModel().getSelection()[0];

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

    ,onOpenInNewWindowClick: function (b, e) {
        var n = this.getSelectionModel().getSelection()[0];
        if(Ext.isEmpty(n)) {
            return;
        }

        var id = 'view'+n.data.nid;
        if(!App.activateTab(App.mainTabPanel, id)) {
            App.addTab(
                App.mainTabPanel
                ,new CB.browser.ViewContainer({
                    rootId: n.data.nid
                    ,data: {id: id }
                })
            );
        }
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

    ,onShowFoldersChildsClick: function(b, e){
        this.showFoldersContent = !b.checked;
        this.fireEvent('savestate');

        this.store.reload({
            node: this.getRootNode()
            ,scope: this
            ,callback: this.restoreTreeState
        });
    }

    ,getState: function () {
        var rez = {
            paths: []
        };

        if(this.collapsed) {
            rez.collapsed = true;
        }

        rez.width = this.getWidth();

        var s = this.getSelectionModel().getSelection();
        var n = Ext.isEmpty(s)
            ? this.lastFocusedRecord
            : s[0];

        if(!Ext.isEmpty(n)) {
            rez.selected = n.getPath('nid');
        }

        this.getRootNode().cascadeBy({
            before: function(n){
                if(n.isExpanded()) {
                    //check if all parents are expanded
                    var ok = true;
                    var p = n.parentNode;
                    while(!Ext.isEmpty(p) && ok) {
                        ok = p.isExpanded();
                        p = p.parentNode;
                    }
                    if(ok) {
                        rez.paths.push(n.getPath('nid'));
                    }
                }
            }
            ,scope: this
        });

        return rez;
    }

    ,restoreTreeState: function() {
        var state = Ext.state.Manager.getProvider().get(this.stateId);
        this.applyState(state);
    }

    ,applyState: function (state) {
        if(!this.rendered){
            return;
        }

        if(Ext.isEmpty(state)) {
            return;
        }

        var f = function(bSuccess, oLastNode){
            if(oLastNode) {
                oLastNode.expand();
            }
        };
        if(!Ext.isEmpty(state.paths)) {
            for (var i = 0; i < state.paths.length; i++) {
                this.expandPath(
                    state.paths[i]
                    ,'nid'
                    ,f
                );
            }
        }

        if(!Ext.isEmpty(state.selected)) {
            this.selectPath(state.selected, 'nid', '/');
        }
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

    ,onPropertiesClick: function(b, e){
        if(this.actions.properties.isDisabled()) {
            return;
        }
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
        if(r.success !== true) {
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
        if(btn !== 'yes') return;
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_BrowserTree['delete'](this.getSelectionModel().getSelection()[0].getPath('nid'), this.processDelete, this);
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
                        nn = n.isLast() ? ( n.isFirst() ? n.parentNode : n.previousSibling) : n.nextSibling;
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
});
