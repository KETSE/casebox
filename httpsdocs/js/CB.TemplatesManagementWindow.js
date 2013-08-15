Ext.namespace('CB'); 
// ----------------------------------------------------------- tags tree
CB.TemplatesTree = Ext.extend(Ext.tree.TreePanel, {
	autoScroll: true
	,containerScroll: true
	,stateId: 'CB_TemplatesTree'
	,stateful: true
	,rootVisible: true
	,animate: false
	,border: false
	,enableDD: true
	,ddGroup: 'templates'
	,initComponent: function(){
		this.actions = {
			createTemplate: new Ext.Action({
				iconCls: 'icon-template'
				,text: L.Template
				,scope: this
				,handler: this.onCreateTemplateClick
			})
			,createFolder: new Ext.Action({
				iconCls: 'icon-folder'
				,text: L.Folder
				,scope: this
				,handler: this.onCreateFolderClick
			})
			,edit: new Ext.Action({
				text: L.Edit
				,iconCls: 'icon32-doc-edit'
				,iconAlign:'top'
				,scale: 'large'
				,disabled: true
				,handler: this.onEditClick
				,scope: this
			})
			,'delete': new Ext.Action({
				text: L.Delete
				,iconAlign:'top'
				,iconCls: 'icon32-del'
				,scale: 'large'
				,disabled: true
				,handler: this.onDelNodeClick
				,scope: this
			})
			,reload: new Ext.Action({
				text: L.Reload
				,iconAlign:'top'
				,iconCls: 'icon32-refresh'
				,scale: 'large'
				,scope:this
				,handler: function(){this.getRootNode().reload();}
			})
		}

		treeMenuItems = [
			{
				text: L.Create
				,iconCls: 'icon32-create'
				,iconAlign:'top'
				,scale: 'large'
				,menu: [ this.actions.createTemplate, this.actions.createFolder ]
			}
			,'-'
			,this.actions.edit
			,'-'
			,this.actions['delete']
			,'->'
			,this.actions.reload
		];
		
		this.editor = new Ext.tree.TreeEditor(this, {
			allowBlank: false
			,blankText: 'A name is required'
			,selectOnFocus: true
			,ignoreNoChange: true 
		})
		
		this.editor.on('beforecomplete', this.onBeforeEditComplete, this);

		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({
				directFn: CB_Templates.getChildren
				,paramsAsHash: true
				,listeners:{
					scope: this
					,beforeload: function(treeLoader, node) { treeLoader.baseParams.path = node.getPath('nid'); }
					,load: function(o, n, r) { n.sort(this.sortTree)}
					,loadexception: function(loader, node, response) { node.leaf = false; node.loaded = false; }
				}
			})
			,root: {
				nodeType: 'async'
				,expanded: false
				,expandable: true
				,iconCls: 'icon-home'
				,leaf: false
				,nid: 'root'
				,is_folder: 1
				,text: L.Templates
			}
			,tbar: treeMenuItems
			,tbarCssClass: 'x-panel-white'
			,listeners:{
				scope: this
				,afterlayout: function(){this.getRootNode().expand()}
				,nodedragover: function(o){ 
					if( ( (o.target.attributes.is_folder != 1) && (o.point == 'append') ) // don't append to templates
						|| o.data.node.contains(o.target) // don't drop into a child
						|| (o.data.node == o.target) // don't drop near itself
					){ 
						o.cancel = true;
						return; 
					}
				}
				,beforenodedrop: function(o){
					o.cancel = true;
					o.dropStatus = true;
					return false;
				}
				,dragdrop: function( tree, node, dd, e ){
					this.dropParams = {
						id: node.attributes.nid
						,target_id: dd.dragOverData.target.attributes.nid
						,point: dd.dragOverData.point
					};
					CB_Templates.moveElement(this.dropParams, this.processDrop, this)
				}
				,beforeappend: function(t, p, n){ 
					if(n.attributes.is_folder != 1) n.setText(n.attributes.text + ' <span class="cG">(id: '+n.attributes.nid+')</span>');
				}
				,dblClick: this.onEditClick
			}
			,selModel: new Ext.tree.DefaultSelectionModel({
				listeners: {
					scope: this
					,selectionchange: function(sm, node){ 
						if(Ext.isEmpty(node) || (node.getDepth() < 1) ){
							this.actions.createTemplate.setDisabled(false);
							this.actions.createFolder.setDisabled( false );
							this.actions['delete'].setDisabled(true);
							this.actions.edit.setDisabled(true);
						}else{
							this.actions.createTemplate.setDisabled(node.attributes.is_folder != 1);
							this.actions.createFolder.setDisabled( node.attributes.is_folder != 1);
							this.actions['delete'].setDisabled(false);
							this.actions.edit.setDisabled(node.getDepth() < 1);
						}
					}
				}
			})
			,keys: [{
				key: [10,13,Ext.EventObject.F2]
				,alt: false
				,ctrl: false
				,stopEvent: true
				,fn: this.onEditClick
				,scope: this
			}]
		});
		CB.TemplatesTree.superclass.initComponent.apply(this, arguments);
	}
	,afterRender: function() { CB.TemplatesTree.superclass.afterRender.apply(this, arguments); }
	,getPid: function(){
		pid = null;
		pn = this.getSelectionModel().getSelectedNode();
		if(pn)  pid = pn.attributes.nid;
		return pid;
	}
	,onCreateTemplateClick: function(b){
		Ext.Msg.prompt(L.NewTemplate, L.Name, function(btn, text){
			if((btn == 'ok') && (!Ext.isEmpty(text)))
				CB_Templates.createTemplate({ text: text, pid: this.getPid }, this.processCreateTemplate, this);
		}, this, false, '');
	}
	,processCreateTemplate: function(r, e){
		this.getEl().unmask();
		if(r.success != true) return;
		this.processNodeAppend(r, e)
	}
	,onCreateFolderClick: function(b, e){
		Ext.Msg.prompt(L.NewFolder, L.Name, function(btn, text){
			if((btn == 'ok') && (!Ext.isEmpty(text)))
				CB_Templates.createFolder({  text: text, pid: this.getPid() }, this.processCreateFolder, this);
		}, this, false, L.NewFolder);
	}
	,processCreateFolder: function(r, e){
		if(r.success !== true) return Ext.Msg.alert(L.Error, r.msg);
		this.processNodeAppend(r, e);
	}
	,processNodeAppend: function(r, e){
		sm = this.getSelectionModel();
		pn = sm.getSelectedNode();
		if(Ext.isEmpty(pn)) pn = this.getRootNode();
		if(pn.isExpanded()){
			n = pn.appendChild(r.data);
			pn.sort(this.sortTree);
			sm.select(n);
			if(n.attributes.is_folder != 1) this.fireEvent('edittemplate', n.attributes.nid);
		}else{
			this.lastAddedNodeId = r.data.id;
			pn.reload(function(pn){
				n = pn.findChild('id', this.lastAddedNodeId);
				if(n){
					sm = this.getSelectionModel();
					sm.clearSelections();
					sm.select(n);
					if(n.attributes.is_folder != 1) this.fireEvent('edittemplate', n.attributes.nid);		
				}
			}, this);
		}
	}
	,processDrop: function(r, e){
		if(r.success !== true) return;
		root = this.getRootNode();
		nodes = [];
		root.cascade(function(n){
			if(n.attributes.nid == this.dropParams.id) nodes.push(n);
		}, this);
		for(var i = 0; i < nodes.length; i++) if(nodes[i]) nodes[i].remove(true);

		nodes = [];
		root.cascade(function(n){
			if(n.attributes.nid == this.dropParams.target_id){
				if(this.dropParams.point == 'append') nodes.push(n);
				else nodes.push(n.parentNode);
			}
		}, this);
		for(var i = 0; i < nodes.length; i++) if(nodes[i]) nodes[i].reload();

	}
	,onEditClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n || n.isRoot) return;
		
		if( n.attributes.is_folder == 1 ){
			this.startEditing(n);
		}else this.fireEvent('edittemplate', n.attributes.nid);
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
		CB_Templates.renameFolder({id: n.attributes.nid, name: newVal}, this.processRename, this);
		return false;
	}
	,processRename: function(r, e){
		this.getEl().unmask();
		if(r.success !== true) return;
		this.root.cascade( function (n){ if(n.attributes.nid == r.data.id){ n.attributes.name = r.data.newName; n.setText(r.data.newName); } }, this);
	}
	,sortTree: function(n1, n2){ 
		if(n1.attributes.order < n2.attributes.order) return -1;
		if(n1.attributes.order > n2.attributes.order) return 1;
		if(n1.attributes.type < n2.attributes.type) return -1;
		if(n1.attributes.type > n2.attributes.type) return 1;
		if(n1.text < n2.text) return -1;
		if(n1.text > n2.text) return 1;
		return 0;
	}
	,onDelNodeClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		
		Ext.MessageBox.confirm(L.Confirmation, ((n.attributes.type > 0) ? L.Delete : L.DeleteFolder) + ' "'+n.attributes.text+'"?', 
		function(btn, text){
			if(btn == 'yes'){
				this.getEl().mask(L.Deleting)
				n = this.getSelectionModel().getSelectedNode();
				CB_Templates.deleteElement(n.attributes.nid, this.processDelElement, this);
			}
		}
		, this);		
	}
	,processDelElement: function(r, e){
		this.getEl().unmask()
		if(r.success !== true) return false;
		sm = this.getSelectionModel();
		n = sm.getSelectedNode();
		if(!sm.selectNext(n)) sm.selectPrevious(n);
		n.remove(true);
	}
})

CB.TemplateEditWindow = Ext.extend( CB.GenericForm, {
	title: L.NewTemplate
	,padding: 0
	,initComponent: function(){
		this.actions = {
			save: new Ext.Action({	text: L.Save
					,disabled: true
					,iconAlign:'top'
					,iconCls: 'icon32-save'
					,scale: 'large'
					,handler: this.saveForm
					, scope: this
			})
			,cancel : new Ext.Action({
				text: Ext.MessageBox.buttonText.cancel
				,iconCls:'icon32-cancel'
				,iconAlign:'top'
				,scale: 'large'
				,disabled: true
				,handler: this.loadData
				, scope: this
			})
		}

		items = [];
		CB.DB.languages.each(function(r){
			items.push({
				fieldLabel: L.Name+ ' ('+r.get('abreviation')+')'
				,name: 'l'+r.get('id')
				,xtype: 'textfield'
				,allowBlank: false
				// ,listeners: {
				// 	scope: this
				// 	,change: this.
				// }
			})
		}, this);

		this.visibilityOptionsStore = new Ext.data.ArrayStore({
			autoLoad: true
			,autoDestroy: true
			,fields: [{name: 'id', type: 'int'}, 'name']
			,data: [[0, L.no],[1, L.inMenu], [2, L.inМenuАndАutoopen], [3, L.inTabsheet]]
		});
		
		idx = CB.DB.templates.findExact('type', 'template');
		template_id = (idx >=0 ) ? CB.DB.templates.getAt(idx).get('id') : -1;

		this.propertiesGrid = Ext.create({ 
			title: L.Properties
			,root: 'properties'
			,refOwner: this
			,template_id: template_id
			,editors: {
				iconcombo: function(){
					return new Ext.form.ComboBox({
						editable: false
						,name: 'iconCls'
						,hiddenName: 'iconCls'
						,tpl: '<tpl for="."><div class="x-combo-list-item icon-padding16 {name}">{name}</div></tpl>'
						,store: CB.DB.templatesIconSet
						,valueField: 'name'
						,displayField: 'name'
						,iconClsField: 'name'
						,triggerAction: 'all'
						,mode: 'local'
						,plugins: [new Ext.ux.plugins.IconCombo()]
					})
				}
				,fieldscombo: function(grid){
					return new Ext.form.ComboBox({
						//editable: true
						forceSelection: true
						,store: CB.DB['template'+this.data.id]
						,valueField: 'id'
						,displayField: 'title'
						,triggerAction: 'all'
						,mode: 'local'
					})
				}.createDelegate(this)
				,jsclasscombo: function(){
					return new Ext.form.ComboBox({
						editable: false
						,store: new Ext.data.ArrayStore({
							autoLoad: true
							,autoDestroy: true
							,fields: ['id', 'name']
							,data: [['', L.PropertiesEditGrid], ['CBSentencesEditGrid', L.SentencesEditGrid], ['CBDecisionsEditGrid', L.DecisionsEditGrid]]
						})
						,valueField: 'id'
						,displayField: 'name'
						,triggerAction: 'all'
						,mode: 'local'
					})
				}
			}
			,renderers: {
				iconcombo: function(v, grid){
					return '<img src="css/i/s.gif" class="icon '+v+'" /> '+v
				}
				,fieldscombo: function(v, grid){
					store = CB.DB['template'+this.data.id]
					if(store){
						idx = store.findExact('id', v);
						if(idx >= 0) return store.getAt(idx).get('title');
					}
					return '';
				}.createDelegate(this)
				,jsclasscombo: function(v){
					switch(v){
						case 'CBSentencesEditGrid': return L.SentencesEditGrid; break;
						case 'CBDecisionsEditGrid': return L.DecisionsEditGrid; break;
						default: return L.PropertiesEditGrid; break;
					}
				}
			}
		}, 'CBVerticalEditGrid');
		
		this.fieldsGrid = Ext.create({ 
			title: L.Fields
			,root: 'fields'
			,refOwner: this
			,template_id: null
		}, 'CBVerticalEditGrid');
		
		Ext.apply(this, {
			layout: 'border'
			,hideBorders: true
			,initialConfig:{
				api: { 
					load: CB_Templates.loadTemplate
					,submit: CB_Templates.saveTemplate
					,waitMsg: L.LoadingData + ' ...'
				}
				,paramsAsHash: true
			}
			,tbar: [ this.actions.save, this.actions.cancel ]
			,tbarCssClass: 'x-panel-white'
			,items: [{
				xtype: 'panel'
				,region: 'north'
				,split: true
				,collapseMode: 'mini'
				,layout: 'column'
				,autoScroll: true
				,autoHeight: true
				,hideBorders: true
				,items: [{
					xtype: 'fieldset'
					,padding: 10
					,labelWidth: 120
					,items: items
					,defaults: {
						anchor: '97%'
						,msgTarget: 'side'
						,listeners:{
							scope: this
							,change: this.onChangeEvent
							,check: this.onChangeEvent
						}
					}
					,columnWidth: 1
				}
				]
			},{
				xtype: 'tabpanel'
				,region: 'center'
				,activeItem: 0
				,items: [
					this.propertiesGrid
					,this.fieldsGrid
					,{
					title: L.Links
					}
				]
			}
			
			]
			,listeners:{
				scope: this
				,afterlayout: function(){
					if(this.loaded) return; 
					this.getEl().mask(L.Downloading + ' ...', 'x-mask-loading'); 
				}
				,change: this.onChangeEvent
				,beforedestroy: function(){}
			}
		});
  		CB.TemplateEditWindow.superclass.initComponent.apply(this, arguments);
	}
	,setFormValues: function(){
		this.getEl().unmask();
		if(!this.loaded){
			this.loaded = true;
		}
		this.setTitle(this.data['l'+App.loginData.language_id]);
		
		this.propertiesGrid.reload();
		
		this.setDirty(false);
		this.onChange();
	}
	,getFormValues: function(){
		CB.DB.languages.each(function(r){
			lf = this.find('name', 'l'+r.get('id'));
			if(!Ext.isEmpty(lf)) this.data['l'+r.get('id')] = lf[0].getValue();
		}, this);
		this.propertiesGrid.readValues();
		this.fieldsGrid.readValues();
	}
	,onChangeEvent: function(){
		this.setDirty(true);
		this.onChange();
	}
	,onChange: function(){
		this.actions.save.setDisabled(!this._isDirty)
		this.actions.cancel.setDisabled(!this._isDirty)
	}
});
// ---------------------------------------------- Main
CB.TemplatesManagementWindow = Ext.extend(Ext.Panel, {
	layout: 'border'
	,border: false
	,closable: true
	,iconCls: 'icon-documents-stack'
	,title: L.Templates
	,initComponent: function() {
		
		this.TemplatesTree = new CB.TemplatesTree({
			region: 'west'
			,width: '300'
			,split: true
			,collapseMode: 'mini'
			,listeners: {
				scope: this
				,edittemplate: this.onEditTemplate
			}
		});
		
		this.tabPanel = new Ext.TabPanel({
			region: 'center'
			,plain: true
			,bodyStyle: 'background-color: #FFF'
			,headerCfg: {cls: 'mainTabPanel'}
			,border: false
		});
		Ext.apply(this, {
			hideBorders: true
			,items: [{
				region: 'center'
				,layout: 'border'
				,items: [this.TemplatesTree, this.tabPanel]
			}
			]
			,listeners: {
				scope: this
				,beforedestroy: function(c){
					c.TemplatesTree.destroy();
					c.tabPanel.destroy();
				}
				,savesuccess: this.onUpdateTemplate
			}
		});
		CB.TemplatesManagementWindow.superclass.initComponent.apply(this, arguments);
	}
	,onEditTemplate: function(id){
		id = Ext.isNumber(id) ? id : id.split('-').pop();
		if(App.activateTab(this.tabPanel, id)) return;
		w = new CB.TemplateEditWindow({data: {id: id}});
		App.addTab(this.tabPanel, w);
	}
	,onUpdateTemplate: function(w, action){
		this.TemplatesTree.getRootNode().cascade(this.updateTreeNode, this);
	}
	,updateTreeNode: function(n){
		a = String(n.attributes.nid).split('-');
		id = a.pop();
		prefix = a.join('-');
		if(!Ext.isEmpty(prefix)) prefix += '-';
		if(id == w.data.id){
			Ext.apply(n.attributes, w.data);
			n.attributes.id = prefix + n.attributes.id;
			text = n.attributes['l'+App.loginData.language_id];
			text += '  <span class="cG">(id: '+id+')</span>'
			n.setText(text);
			n.setIconCls(w.data.iconCls);
			return false;
		}
	}
});
Ext.reg('CBTemplatesManagementWindow', CB.TemplatesManagementWindow); // register xtype
