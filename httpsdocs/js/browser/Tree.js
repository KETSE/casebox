
Ext.namespace('CB.browser');

CB.browser.Tree = Ext.extend(Ext.tree.TreePanel,{
    rootVisible: false
    ,autoScroll: true
    ,containerScroll: true
    ,animate: false
    ,lines: false
    ,useArrows: true
    ,showFoldersContent: false
    ,bodyStyle: 'background-color: #f4f4f4'
    ,hideBorders: true
    ,border: false
    ,hideToolbar: true
    ,initComponent: function(){
        if(Ext.isEmpty(this.data)) {
            this.data = {};
        }

        this.sorters = {
            n30: function(n1, n2){
                if(n1.attributes.system > n2.attributes.system) return -1;
                if(n1.attributes.system < n2.attributes.system) return 1;
                if(n1.attributes.type > n2.attributes.type) return 1;
                if(n1.attributes.type < n2.attributes.type) return -1;
                if(n1.attributes.subtype > n2.attributes.subtype) return 1;
                if(n1.attributes.subtype < n2.attributes.subtype) return -1;
                if(n1.attributes.name > n2.attributes.name) return 1;
                if(n1.attributes.name < n2.attributes.name) return -1;
                return 0;
            }
        };

        this.actions = {
            open: new Ext.Action({
                text: L.Open
                ,scope: this
                ,handler: this.onOpenClick
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

        this.editor = new Ext.tree.TreeEditor(this, {
            allowBlank: false
            ,blankText: 'A name is required'
            ,selectOnFocus: true
            ,ignoreNoChange: true
        });

        this.editor.on('beforecomplete', this.onBeforeEditComplete, this);
        var rootConfig = Ext.value(this.data.rootNode, {});
        rootConfig = Ext.apply(
            {
                nid: Ext.value(this.rootId, '')
                ,expanded: true
                ,editable: false
                ,leaf: false
                ,iconCls:'icon-folder'
            }
            ,rootConfig
        );
        rootConfig.text = Ext.value(rootConfig.text, rootConfig.name);

        Ext.apply(this, {
            loader: new Ext.tree.TreeLoader({
                directFn: CB_BrowserTree.getChildren
                ,paramsAsHash: true
                ,baseParams: {
                    from: 'tree'
                }
                ,listeners: {
                    scope: this
                    ,beforeload: function(treeloader, node, callback) {
                        var p = {
                            path: node.getPath('nid')
                            ,showFoldersContent: this.showFoldersContent
                        };
                        Ext.apply(treeloader.baseParams, p);
                    }
                }
            })
            ,root: new Ext.tree.AsyncTreeNode(rootConfig)
            ,listeners:{
                scope: this
                ,beforeappend: this.onBeforeNodeAppend
                ,load: function(node){ this.sortNode(node); }
                ,dblclick: this.onDblClick
                ,contextmenu: this.onContextMenu
                ,startdrag: function(tree, node, e){
                    if(node.attributes.system == 1){
                        e.cancel = true;
                    }
                }
                ,beforedestroy: this.onBeforeDestroy
            }
            ,selModel: new Ext.tree.DefaultSelectionModel({
                listeners: {
                    scope: this
                    ,selectionchange: this.onSelectionChange
                }
            })
            ,keys: [
                {
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
                    ,alt: false
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
                }
            ]
            ,plugins: [ new CB.DD.Tree({idProperty: 'nid'}) ]
        });

        CB.browser.Tree.superclass.initComponent.apply(this, arguments);
        if(!isNaN(this.rootId)) {
            CB_BrowserTree.getRootProperties(
                this.rootId
                ,function(r, e){
                    Ext.apply(this.getRootNode().attributes, r.data);
                    this.onBeforeNodeAppend(this, null, this.getRootNode());
            }, this);
        }

        this.addEvents('createobject', 'afterrename');
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
    ,onBeforeNodeAppend: function(tree, parent, node){
        node.setId(Ext.id());

        // node id could be literal, so we cannot eval it to int
        // node.attributes.nid = Ext.num(node.attributes.nid, null);

        node.attributes.system = Ext.num(node.attributes.system, 0);
        var text = Ext.util.Format.htmlEncode(node.attributes.name);
        node.setText(text);

        if(Ext.isEmpty(node.attributes.iconCls)) {
            if(node.attributes.cfg && node.attributes.cfg.iconCls){
                node.setIconCls( node.attributes.cfg.iconCls );
            } else {
                node.setIconCls( getItemIcon(node.attributes) );
            }
        }

        node.attributes.editable = false;
        node.draggable = (node.attributes.system === 0);
        if(node.attributes.acl_count > 0) {
            node.setCls('node-has-acl');
        }
    }
    ,onBeforeDestroy: function(p){
        App.clipboard.un('pasted', this.onClipboardAction, this);
        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
    }
    ,onClipboardAction: function(pids){
        this.getRootNode().cascade(
            function(n){
                if(pids.indexOf(n.attributes.nid) >= 0 ) {
                    n.reload();
                }
            }
            ,this
        );
    }
    ,onObjectsSaved: function(form, e){
        if(!this.rendered) {
            return;
        }
        var n = this.getRootNode();
        if(n) {
            n.cascade(
                function(n){
                    if(n.attributes.nid == form.data.pid) {
                        n.reload();
                    }
                }
                ,this
            );
        }
    }
    ,sortNode: function(node){
        sorterName = 'n'+node.attributes.type + node.attributes.subtype;
        if(Ext.isDefined(this.sorters[sorterName])) node.sort(this.sorters[sorterName]);
    }
    ,onSelectionChange: function (sm, node) {
        if(Ext.isEmpty(node)){
            this.actions.open.setHidden(true);
            this.actions.openInNewWindow.setHidden(true);
            this.actions.cut.setDisabled(true) ;
            this.actions.copy.setDisabled(true) ;
            this.actions.paste.setDisabled(true) ;
            this.actions.pasteShortcut.setDisabled(true) ;
            this.actions.createShortcut.setDisabled(true) ;
            this.actions.createShortcut.setDisabled(true) ;
            this.actions['delete'].setDisabled(true) ;
            this.actions.rename.setDisabled(true) ;
            this.actions.reload.setDisabled(true) ;
            this.actions.permissions.setDisabled(true) ;
        }else{
            canOpen = true;
            this.actions.open.setHidden(!canOpen);
            canOpenInNewWindow = true;
            this.actions.openInNewWindow.setHidden(!canOpenInNewWindow);
            canExpand = (!node.isExpanded() && ( (!node.loaded) || node.hasChildNodes() ));
            this.actions.expand.setHidden(!canExpand);
            canCollapse = node.isExpanded() && node.hasChildNodes();
            this.actions.collapse.setHidden(!canCollapse);
            if(this.contextMenu) this.contextMenu.items.itemAt(3).setVisible(canOpen || canExpand || canCollapse);

            canCopy = (node.attributes.system === 0);
            this.actions.cut.setDisabled(!canCopy);
            this.actions.copy.setDisabled(!canCopy);
            canPaste = !App.clipboard.isEmpty()
                && ( !this.inFavorites(node) || App.clipboard.containShortcutsOnly() )
                && ( node.attributes.system === 0 );
            this.actions.paste.setDisabled(!canPaste);
            canPasteShortcut = !App.clipboard.isEmpty()
                && !App.clipboard.containShortcutsOnly()
                && ( node.attributes.system === 0 );
            this.actions.pasteShortcut.setDisabled(!canPasteShortcut);

            canDelete = (node.attributes.system === 0);
            this.actions['delete'].setDisabled(!canDelete) ;
            canRename = (node.attributes.system === 0);
            this.actions.rename.setDisabled(!canRename) ;

            this.actions.reload.setDisabled(false) ;
            this.actions.permissions.setDisabled(false) ;
        }
    }
    ,isFavoriteNode: function(node){
        if(Ext.isEmpty(node)) return false;
        return ( (node.attributes.system == 1) && (node.attributes.type == 1) && (node.attributes.subtype == 2) );
    }
    ,inFavorites: function(node) {
        isFavoriteNode = false;
        do{
            isFavoriteNode = this.isFavoriteNode(node);
            node = node.parentNode;
        }while(node && (node.getDepth() > 0) && !isFavoriteNode);
        return isFavoriteNode;
    }
    ,onContextMenu: function (node, e) {
        if(Ext.isEmpty(this.contextMenu)){/* create context menu if not aleready created */
            this.createItem = new Ext.menu.Item({
                text: L.Create
                ,hideOnClick: false
                ,menu:[]
            });
            this.contextMenu = new Ext.menu.Menu({
                items: [
                this.actions.open
                ,this.actions.openInNewWindow
                ,this.actions.expand
                ,this.actions.collapse
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
        node.select();
        e.stopPropagation();
        e.preventDefault();
        this.contextMenu.node = node;

        updateMenu(
            this.createItem
            ,getMenuConfig(
                node.attributes.nid
                ,node.getPath('nid')
                ,node.attributes.template_id
            )
            ,this.onCreateObjectClick
            ,this
        );
        this.contextMenu.showAt(e.getXY());
    }
    ,onCreateObjectClick: function(b, e) {
        data = Ext.apply({}, b.data);
        data.pid = this.contextMenu.node.attributes.nid;
        data.path = this.contextMenu.node.getPath('nid');
        data.pathtext = this.contextMenu.node.getPath('text');

        var tr = CB.DB.templates.getById(data.template_id);

        if(tr && (tr.get('cfg').createMethod == 'inline')) {
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

        r.data.nid = Ext.value(r.data.nid, r.data.id);
        delete r.data.id;
        this.root.cascade(
            function(node){
                //find parent node
                if(node.attributes.nid == r.data.pid){
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
            ,this
        );
    }
    ,onDblClick: function(b, e){
        n = this.getSelectionModel().getSelectedNode();
        if(Ext.isEmpty(n)) return;
        if( App.isFolder( n.attributes.template_id ) ) return;
        this.onOpenClick(b, e);
    }
    ,onOpenClick: function (b, e) {
        var n = this.getSelectionModel().getSelectedNode();
        if(Ext.isEmpty(n)) return;
        var tab = App.activateBrowserTab();
        tab.onObjectsOpenEvent(
            {
                nid: n.attributes.nid
                ,template_id: n.attributes.template_id
            }
        );
    }
    ,onOpenInNewWindowClick: function (b, e) {
        n = this.getSelectionModel().getSelectedNode();
        if(Ext.isEmpty(n)) return;
        id = 'view'+n.attributes.nid;
        if(!App.activateTab(App.mainTabPanel, id)) {
            App.addTab(
                App.mainTabPanel
                ,new CB.FolderView({
                    rootId: n.attributes.nid
                    ,data: {id: id }
                })
            );
        }
    }
    ,onExpandClick: function (b, e) {
        n = this.getSelectionModel().getSelectedNode();
        n.expand(
            false
            ,false
            ,function(n){
                this.onSelectionChange(this.sm, n);
            }
            ,this
        );
    }
    ,onCollapseClick: function (b, e) {
        n = this.getSelectionModel().getSelectedNode();
        n.collapse();
        this.onSelectionChange(this.sm, n);
    }
    ,onShowFoldersChildsClick: function(b, e){
        this.showFoldersContent = !b.checked;
        this.saveTreeState();
        this.root.reload(this.restoreTreeState, this);
    }
    ,saveTreeState: function () {
        this.treeState = [];
        this.lastSelectedPath = null;
        n = this.getSelectionModel().getSelectedNode();
        if(n) this.lastSelectedPath = n.getPath('nid');
        this.root.cascade(
            function(n){
                if(n.isExpanded()) {
                    this.treeState.push(n.getPath('nid'));
                }
            }
            ,this
        );
    }
    ,restoreTreeState: function () {
        if(Ext.isEmpty(this.treeState)) {
            return;
        }
        var f = function(bSuccess, oLastNode){
            if(oLastNode) {
                oLastNode.expand();
            }
        };

        for (var i = 0; i < this.treeState.length; i++) {
            this.expandPath(
                this.treeState[i]
                ,'nid'
                ,f
            );
        }
        if(!Ext.isEmpty(this.lastSelectedPath)) this.selectPath(this.lastSelectedPath, 'nid');
    }

    ,onCutClick: function(b, e) {
        if(this.actions.cut.isDisabled()) return;
        this.onCopyClick(b, e);
        App.clipboard.setAction('move');
    }
    ,onCopyClick: function(b, e) {
        if(this.actions.copy.isDisabled()) return;
        n = this.selModel.getSelectedNode();
        if(Ext.isEmpty(n)) return;
        App.clipboard.set({
            id: n.attributes.nid
            ,name: n.attributes.name
            ,system: n.attributes.system
            ,type: n.attributes.type
            ,subtype: n.attributes.subtype
            ,iconCls: n.attributes.iconCls
        }, 'copy');
    }
    ,onPasteClick: function(b, e){
        if(this.actions.paste.isDisabled()) return;
        n = this.selModel.getSelectedNode();
        if(Ext.isEmpty(n)) return;
        App.clipboard.paste(n.attributes.nid);
    }
    ,onPasteShortcutClick: function(b, e){
        if(this.actions.pasteShortcut.isDisabled()) return;
        n = this.selModel.getSelectedNode();
        if(Ext.isEmpty(n)) return;
        App.clipboard.paste(n.attributes.nid, 'shortcut');
    }
    ,onPermissionsClick: function(b, e){
        if(this.actions.permissions.isDisabled()) return;
        n = this.selModel.getSelectedNode();
        if(Ext.isEmpty(n)) return;
        if(App.activateTab(null, n.attributes.nid, CB.SecurityPanel)) return;
        App.addTab(null, new CB.SecurityPanel({data: { id: n.attributes.nid }}));
    }
    ,onPropertiesClick: function(b, e){
        if(this.actions.properties.isDisabled()) return;
        // body...
    }
    ,onRenameClick: function(b, e){
        this.startEditing(this.getSelectionModel().getSelectedNode());
    }
    ,onReloadClick: function(b, e){
        this.getSelectionModel().getSelectedNode().reload();
    }
    ,startEditing: function(node) {
        if(!node.isSelected()) node.select();
        var ge = this.editor;
        setTimeout(function(){
            ge.editNode = node;
            ge.startEdit(node.ui.textNode, node.attributes.name);
        }, 10);
    }
    ,onBeforeEditComplete: function(editor, newVal, oldVal) {
        var n = editor.editNode;
        n.setText(Ext.util.Format.htmlEncode(newVal));
        if(newVal === oldVal) return;
        editor.cancelEdit();
        this.getEl().mask(L.Processing, 'x-mask-loading');
        CB_BrowserTree.rename({path: n.getPath('nid'), name: newVal}, this.processRename, this);
        return false;
    }
    ,processRename: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) return;
        this.root.cascade(
            function (n){
                if(n.attributes.nid == r.data.id){
                    n.attributes.name = r.data.newName;
                    n.setText(Ext.util.Format.htmlEncode(r.data.newName));
                }
            }
            ,this
        );
        this.fireEvent('afterrename', this, r, e);
    }
    ,onDeleteClick: function(b, e){
        Ext.Msg.confirm(
            L.DeleteConfirmation
            ,L.DeleteConfirmationMessage + ' "' + this.getSelectionModel().getSelectedNode().text + '"?'
            ,this.onDelete
            ,this
        );
    }
    ,onDelete: function (btn) {
        if(btn !== 'yes') return;
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_BrowserTree['delete'](this.getSelectionModel().getSelectedNode().getPath('nid'), this.processDelete, this);
    }
    ,processDelete: function(r, e){
        this.getEl().unmask();
        App.mainViewPort.onProcessObjectsDeleted(r, e);
    }
    ,onObjectsDeleted: function(ids){
        deleteNodes = [];
        this.getRootNode().cascade(function(n){
            if(ids.indexOf(n.attributes.nid) >= 0){
                if(n.isSelected()){
                    nn = n.isLast() ? ( n.isFirst() ? n.parentNode : n.previousSibling) : n.nextSibling;
                    nn.select();
                }
                deleteNodes.push(n);
            }
        }, this);
        for (var i = 0; i < deleteNodes.length; i++) {
            deleteNodes[i].remove(true);
        }
        /* TODO: also delete all visible nodes(links) that are links to the deleted node or any its child */
    }

});

Ext.reg('CBBrowserTree', CB.browser.Tree);
