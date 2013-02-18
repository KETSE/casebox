
Ext.namespace('CB');

CB.BrowserTree = Ext.extend(Ext.tree.TreePanel,{
	rootVisible: false
	,autoScroll: true
	,containerScroll: true
	,animate: false
	,lines: false
	,useArrows: true
	,showFoldersContent: false
	,enableDD: false//true
	,ddGroup: 'CBDD'
	,bodyStyle: 'background-color: #f4f4f4'
	,initComponent: function(){
	
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
		}
		this.actions = {
			open: new Ext.Action({
				text: L.Open
				//,iconCls: 'icon-briefcase'
				,scope: this
				,handler: this.onOpenClick
			})
			,openInNewWindow: new Ext.Action({
				text: L.OpenInNewWindow
				//,iconCls: 'icon-briefcase'
				,scope: this
				,handler: this.onOpenInNewWindowClick
			})
			,expand: new Ext.Action({
				text: L.Expand
				//,iconCls: 'icon-briefcase'
				,scope: this
				,handler: this.onExpandClick
			})
			,collapse: new Ext.Action({
				text: L.Collapse
				//,iconCls: 'icon-briefcase'
				,scope: this
				,handler: this.onCollapseClick
			})
			/*,showFoldersChilds: new Ext.Action({
				text: L.ShowFoldersContent
				//,iconCls: 'icon-minus'
				,enableToggle: true
				,checked: true
				,scope: this
				,handler: this.onShowFoldersChildsClick
			})/**/

			,cut: new Ext.Action({
				text: L.Cut
				//,iconCls: 'icon-shortcut'
				,scope: this
				,disabled: true
				,handler: this.onCutClick
			})
			,copy: new Ext.Action({
				text: L.Copy
				//,iconCls: 'icon-shortcut'
				,scope: this
				,disabled: true
				,handler: this.onCopyClick
			})
			,paste: new Ext.Action({
				text: L.Paste
				//,iconCls: 'icon-shortcut'
				,scope: this
				,disabled: true
				,handler: this.onPasteClick
			})
			,pasteShortcut: new Ext.Action({
				text: L.PasteShortcut
				//,iconCls: 'icon-shortcut'
				,scope: this
				,disabled: true
				,handler: this.onPasteShortcutClick
			})

			,createShortcut: new Ext.Action({
				text: L.CreateShortcut
				//,iconCls: 'icon-shortcut'
				,scope: this
				,disabled: true
				,handler: this.onCreateShortcutClick
			})
			,'delete': new Ext.Action({
				text: L.Delete
				//,iconCls: 'icon-minus'
				,disabled: true
				,scope: this
				,handler: this.onDeleteClick
			})
			,rename: new Ext.Action({
				text: L.Rename
				//,iconCls: 'icon-minus'
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

			,createCase: new Ext.Action({
				text: L.NewCase
				,iconCls: 'icon-briefcase'
				,scope: this
				,handler: this.onCreateCaseClick
			})
			,createTask: new Ext.Action({
				text: L.NewTask
				,iconCls: 'icon-calendar-task'
				,scope: this
				,handler: this.onCreateTaskClick
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
				//,iconCls: 'icon-folder'
				,scope: this
				,disabled: true
				,handler: this.onPropertiesClick
			})

		}
		this.editor = new Ext.tree.TreeEditor(this, {
	           	allowBlank: false
	          	,blankText: 'A name is required'
	            	,selectOnFocus: true
	            	,ignoreNoChange: true 
	        }); 
	        this.editor.on('beforecomplete', this.onBeforeEditComplete, this);

	        if(this.hideToolbar !== true)
			Ext.apply(this, {
				tbar: [{
						text: L.Create
						,iconCls: 'icon-plus'
						,menu:[ this.actions.createCase, '-', this.actions.createTask, '-', this.actions.createFolder]
					}
					, '->'
					,{
						iconCls: 'icon-reload'
						,scope: this
						,handler: function(){this.getRootNode().reload()}
					}
				]
			});

		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({
				directFn: BrowserTree.getChildren
				,paramsAsHash: true
				,listeners: {
					scope: this
					,beforeload: function(treeloader, node, callback) {
						treeloader.baseParams.path = node.getPath('nid');
						treeloader.baseParams.showFoldersContent = this.showFoldersContent;
					}
					//,load: function(loader, node, responce){ this.sortNode(node); }
				}
			})
			,root: new Ext.tree.AsyncTreeNode({
				text: 'root'
				,nid: Ext.value(this.rootId, '')
				,expanded: true
				,editable: false
				,leaf: false
				,iconCls:'icon-folder'
			})
			,listeners:{
				scope: this
				,beforeappend: this.onBeforeNodeAppend
				,load: function(node){ this.sortNode(node); }
				,dblclick: this.onOpenClick
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
					//,beforeselect: function(){clog('before select')}
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
		})
		CB.BrowserTree.superclass.initComponent.apply(this, arguments);
		if(!Ext.isEmpty(this.rootId)) BrowserTree.getRootProperties(this.rootId, function(r, e){
			Ext.apply(this.getRootNode().attributes, r.data)
			//this.getRootNode().setText(r.data.name);
			this.onBeforeNodeAppend(this, null, this.getRootNode())
		}, this)

		this.addEvents('casecreate', 'openobject', 'fileopen', 'filedownload', 'taskedit', 'afterrename');
		this.enableBubble(['casecreate', 'openobject', 'fileopen', 'filedownload', 'taskedit']);
		
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
		node.attributes.nid = parseInt(node.attributes.nid);
		node.attributes.system = parseInt(node.attributes.system);
		node.attributes.type = parseInt(node.attributes.type);
		node.attributes.subtype = parseInt(node.attributes.subtype);
		if((node.attributes.type == 0) && (parent.getDepth() == 4 )){
			node.setText(Ext.value(Date.monthNames[parseInt(node.attributes.name) -1], node.attributes.name));
		}else 
		node.setText(node.attributes.name);// + ' ' + node.attributes.system + ' ' + node.attributes.type+'.' + node.attributes.subtype);
		node.setIconCls( getItemIcon(node.attributes) );// + ' ' + node.attributes.system + ' ' + node.attributes.type+'.' + node.attributes.subtype);
		node.attributes.editable = false; //(node.attributes.system == 0);
		node.draggable = (node.attributes.system == 0);
	}
	,onBeforeDestroy: function(p){
		App.clipboard.un('pasted', this.onClipboardAction, this);
		App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
	}
	,onClipboardAction: function(pids){
		this.getRootNode().cascade(function(n){ if(pids.indexOf(n.attributes.nid) >= 0 ) n.reload()}, this)
	}
	,onObjectsSaved: function(form, e){
		n = this.getRootNode();
		if(n) n.cascade(function(n){ if(n.attributes.nid == form.data.pid) n.reload()}, this)
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
			this.actions.createFolder.setDisabled(true) ;
		}else{
			canOpen = ([2, 4, 5, 6, 7].indexOf(node.attributes.type) >= 0 )
			this.actions.open.setHidden(!canOpen);
			canOpenInNewWindow = ([1, 3, 4].indexOf(node.attributes.type) >= 0 )
			this.actions.openInNewWindow.setHidden(!canOpenInNewWindow);
			canExpand = (!node.isExpanded() && ( (!node.loaded) || node.hasChildNodes() )) && (  (([1].indexOf(node.attributes.type) >= 0) && (node.attributes.subtype != 1)) || ([0, 3, 4, 6, 7].indexOf(node.attributes.type) >=0 ) );
			this.actions.expand.setHidden(!canExpand);
			canCollapse = node.isExpanded() && node.hasChildNodes() && ( (([1].indexOf(node.attributes.type) >= 0) && (node.attributes.subtype != 1)) || ([0, 3, 4, 6, 7].indexOf(node.attributes.type) >=0 ) );
			this.actions.collapse.setHidden(!canCollapse);
			if(this.contextMenu) this.contextMenu.items.itemAt(3).setVisible(canOpen || canExpand || canCollapse);

			canCopy = (node.attributes.system == 0);
			this.actions.cut.setDisabled(!canCopy);
			this.actions.copy.setDisabled(!canCopy);
			canPaste = !App.clipboard.isEmpty() 
				&& ( !this.inFavorites(node) || App.clipboard.containShortcutsOnly() ) 
				&& ( ( (node.attributes.system == 0) && (node.attributes.type != 5) ) 
					|| ( (node.attributes.type == 1) && ([2, 7, 9, 10].indexOf(node.attributes.subtype) >= 0) ) 
					|| ([3, 4, 6, 7].indexOf(node.attributes.type) >= 0 ) 
				   );
			this.actions.paste.setDisabled(!canPaste);
			canPasteShortcut = !App.clipboard.isEmpty() 
				&& !App.clipboard.containShortcutsOnly() 
				&& ( ( (node.attributes.system == 0) && (node.attributes.type != 5) ) 
					|| ( (node.attributes.type == 1) && ([2, 7, 9, 10].indexOf(node.attributes.subtype) >= 0) ) 
					|| ([3, 4, 6, 7].indexOf(node.attributes.type) >= 0 ) 
				   );
			this.actions.pasteShortcut.setDisabled(!canPasteShortcut);

			canCreateFolder = (node.attributes.type == 1) && ([0, 2, 7, 9, 10].indexOf(node.attributes.subtype) >= 0) || (node.attributes.type == 3) || (node.attributes.type == 4);
			this.actions.createFolder.setDisabled(!canCreateFolder) ;
			canDelete = (node.attributes.type == 1) && ([0].indexOf(node.attributes.subtype) >= 0) || ([2, 3, 4, 5, 6, 7].indexOf(node.attributes.type)>=0);
			this.actions['delete'].setDisabled(!canDelete) ;
			canRename = (node.attributes.system == 0);
			this.actions.rename.setDisabled(!canRename) ;
			
			this.actions.reload.setDisabled(false) ;
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
					,menu: [{	//[this.actions.showFoldersChilds]
						xtype: 'menucheckitem'
						,text: L.ShowFoldersContent
						//,iconCls: 'icon-minus'
						//,enableToggle: true
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
				,{
					text: L.Create
					//,iconCls: 'icon-plus'
					,hideOnClick: false
					,menu:[ this.actions.createCase, '-', this.actions.createTask, '-', this.actions.createFolder]
				}
				,'-'
				,this.actions.properties
				]
			})

		}
		node.select();
		e.stopPropagation()
		e.preventDefault();
		this.contextMenu.node = node;
		this.contextMenu.showAt(e.getXY());
		
	}
	,onOpenClick: function (b, e) {
		n = this.getSelectionModel().getSelectedNode();
		if(Ext.isEmpty(n)) return;
		switch(n.attributes.type){
			case 2:  //link
				this.locateObject(n.attributes.nid);
				break;
			case 3:  //App.openCase(n.attributes.nid);
				break;
			case 4:
				this.fireEvent('openobject', {id: n.attributes.nid}, e);
				break;
			case 5:
				//clog('fire');
				this.fireEvent('fileopen', {id: n.attributes.nid}, e)
				break;
			case 6: 
				this.fireEvent('taskedit', { data:{id: n.attributes.nid} }, e);
				break;
		}
	
	}
	,onOpenInNewWindowClick: function (b, e) {
		n = this.getSelectionModel().getSelectedNode();
		if(Ext.isEmpty(n)) return;
		id = 'view'+n.attributes.nid;
		if(!App.activateTab(App.mainTabPanel, id)) App.addTab(App.mainTabPanel, new CB.FolderView({ rootId: n.attributes.nid, data: {id: id } }) )
	}
	,locateObject: function(id){
		this.locateId = id;
		Browser.getPath(id, this.processLocate, this);
	}
	,processLocate: function(r, e){
		if(r.success !== true) return;
		r.path = '/'+this.getRootNode().attributes.nid+r.path;
		this.selectPath(r.path, 'nid', function(bSuccess, oSelNode){ if(!bSuccess) this.fireEvent('locateobject', this.locateId)})

	}
	,onExpandClick: function (b, e) {
		n = this.getSelectionModel().getSelectedNode()
		n.expand(false, false, function(n){this.onSelectionChange(this.sm, n)}, this);
	}
	,onCollapseClick: function (b, e) {
		n = this.getSelectionModel().getSelectedNode()
		n.collapse();
		this.onSelectionChange(this.sm, n);
	}
	,onShowFoldersChildsClick: function(b, e){
		this.showFoldersContent = !b.checked;
		this.saveTreeState()
		this.root.reload(this.restoreTreeState, this);
	}
	,saveTreeState: function () {
		this.treeState = [];
		this.lastSelectedPath = null;
		n = this.getSelectionModel().getSelectedNode();
		if(n) this.lastSelectedPath = n.getPath('nid');
		this.root.cascade(function(n){
			if(n.isExpanded()) this.treeState.push(n.getPath('nid'));
		}, this)
	}
	,restoreTreeState: function () {
		if(Ext.isEmpty(this.treeState)) return;
		for (var i = 0; i < this.treeState.length; i++) {
			this.expandPath(this.treeState[i], 'nid', function(bSuccess, oLastNode){if(oLastNode) oLastNode.expand()});
		}
		if(!Ext.isEmpty(this.lastSelectedPath)) this.selectPath(this.lastSelectedPath, 'nid');
	}

	,onCutClick: function(b, e) {
		if(this.actions.cut.isDisabled()) return;
		this.onCopyClick(b, e);
		App.clipboard.setAction('move')
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
	            	ge.startEdit(node.ui.textNode);
	        }, 10);
	}
	,onBeforeEditComplete: function(editor, newVal, oldVal) {
	        if(newVal === oldVal) return;
	        var n = editor.editNode;
	        editor.cancelEdit();
	        this.getEl().mask(L.Processing, 'x-mask-loading');
	        BrowserTree.rename({path: n.getPath('nid'), name: newVal}, this.processRename, this);
	        return false;
	}
	,processRename: function(r, e){
		this.getEl().unmask();
		if(r.success !== true) return;
		this.root.cascade( function (n){ if(n.attributes.nid == r.data.id){ n.attributes.name = r.data.newName; n.setText(r.data.newName); } }, this);
		this.fireEvent('afterrename', this, r, e)
	}
	,onCreateFolderClick: function(b, e){
		this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
		BrowserTree.createFolder(this.getSelectionModel().getSelectedNode().getPath('nid'), this.processCreateFolder, this);
	}
	,processCreateFolder: function (r, e) {
		this.getEl().unmask();
		if(r.success !== true) return;
		this.root.cascade(function(node){
			if(node.attributes.nid == r.data.pid){
				if(!node.loaded){
					if(node.isSelected()) node.expand(false, false, function(pn){ n = pn.findChild('nid', r.data.nid); if(n) this.startEditing(n); }, this)
				}else{
					r.data.loaded = true
					node.expand();
					n = node.appendChild(r.data);
					this.startEditing(n);
				}
			}
		}, this);
	}
	,onDeleteClick: function(b, e){
		Ext.Msg.confirm( L.DeleteConfirmation, L.DeleteConfirmationMessage + ' "' + this.getSelectionModel().getSelectedNode().text + '"?', this.onDelete, this ) 
	}
	,onDelete: function (btn) {
		if(btn !== 'yes') return;
		this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
		BrowserTree['delete'](this.getSelectionModel().getSelectedNode().getPath('nid'), this.processDelete, this);
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
		}, this)
		for (var i = 0; i < deleteNodes.length; i++) {
			deleteNodes[i].remove(true);
		};
		/* TODO: also delete all visible nodes(links) that are links to the deleted node or any its child */
	}
	,onCreateCaseClick: function(b, e){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		b.data = {pid: n.attributes.nid};
		this.fireEvent('casecreate', b, e);
	}

})

Ext.reg('CBBrowserTree', CB.BrowserTree);