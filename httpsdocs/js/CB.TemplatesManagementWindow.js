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
	,enableDrag: true
	,ddGroup: 'templates'
	,rootVisible: false
	,initComponent: function(){
		treeMenuItems = [
			{
				text: L.Add, iconCls: 'icon-plus'
				,menu: [{
						iconCls: 'icon-object1'
						,template_type: 1
						,text: L.CaseObject
						,scope: this
						,handler: this.onAddTemplateClick
					},{
						iconCls: 'icon-object2'
						,template_type: 2
						,text: L.IncomingAction
						,scope: this
						,handler: this.onAddTemplateClick
					},{
						iconCls: 'icon-object3'
						,template_type: 3
						,text: L.OutgoingAction
						,scope: this
						,handler: this.onAddTemplateClick
					},'-',{
						iconCls: 'icon-object7'
						,template_type: 7
						,text: L.Contact
						,scope: this
						,handler: this.onAddTemplateClick
					}
					
				]
			}
			,'-'
			,{text: L.Edit, iconCls: 'icon-pencil', disabled: true, handler: this.onNodeDblClick, scope: this}
			,'-'
			,{text: L.Delete, iconCls: 'icon-minus', disabled: true, handler: this.onDelNodeClick, scope: this}
			,'->'
			,{iconCls: 'icon-reload', qtip: L.Reload, scope:this, handler: function(){this.getRootNode().reload();}}
		];
		
		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({
				directFn: Templates.getChildren
				,paramsAsHash: true
				,listeners:{
					scope: this
					,beforeload: function(treeLoader, node) { treeLoader.baseParams.path = node.getPath('id'); }
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
				,text: L.Templates
			}
			,tbar: treeMenuItems
			,listeners:{
				scope: this
				,afterlayout: function(){this.getRootNode().expand()}
				,nodedragover: function(o){ if( (o.point != 'append') || (o.source.tree != o.tree)){ o.cancel = true; return; } }
				,beforenodedrop: function(o){
					o.cancel = true;
					o.dropStatus = true;
					return false;
				}
				,dragdrop: function( tree, node, dd, e ){
					targetTree = dd.dragOverData.target.ownerTree;
					targetTree.processDragDrop(tree, node, dd, e);
				}
				,beforeappend: function(t, p, n){ 
					if(n.attributes.type > 0) n.setText(n.attributes.text + ' <span class="cG">(id: '+n.attributes.id+')</span>');
					if( (n.attributes.type == 2) && (n.attributes.visible != 1)) n.setText( '<i class="cG">'+n.attributes.text+'?</i>');
				}
				,dblClick: this.onNodeDblClick
			}
			,selModel: new Ext.tree.DefaultSelectionModel({
				listeners: {
					selectionchange: {scope: this, fn: function(sm, node){ 
							tb = this.getTopToolbar();
							if(!Ext.isEmpty(node)){
								tb.items.get(2).setDisabled((node.attributes.type < 1) || (node.attributes.type == 6) || (node.attributes.type > 7)); 
								tb.items.get(4).setDisabled((node.attributes.type < 1) || (node.attributes.type == 6) || (node.attributes.type > 7));
							}else{
								tb.items.get(2).setDisabled(true); 
								tb.items.get(4).setDisabled(true); 
							}
						} 
					}
				}
			})
			,keys: [{
				key: [10,13]
				,fn: this.onNodeDblClick
				,scope: this
			}]
		});
		CB.TemplatesTree.superclass.initComponent.apply(this, arguments);
	}
	,afterRender: function() { CB.TemplatesTree.superclass.afterRender.apply(this, arguments); }
	,onAddTemplateClick: function(b){
		Ext.Msg.prompt(L.NewTemplate +': ' + b.text, L.Name, function(btn, text){
			if((btn == 'ok') && (!Ext.isEmpty(text))) Templates.saveElement({text: text, type: b.template_type}, this.processSaveElement, this);
		}, this, false, '');
	}
	,processSaveElement: function(r, e){
		this.getEl().unmask();
		if(r.success != true) return;
		pn = this.getRootNode().findChild('type', -r.data.type, true);
			sm = this.getSelectionModel();
			if(pn.isExpanded()){
				n = pn.appendChild(r.data);
				pn.sort(this.sortTree);
				sm.select(n);
			}else{
				this.lastAddedNodeId = r.data.id;
				pn.reload(function(pn){
					n = pn.findChild('id', this.lastAddedNodeId);
					if(n){
						sm = this.getSelectionModel();
						sm.clearSelections();
						sm.select(n);				
					}
				}, this);
			}
	}
	,onNodeDblClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n || n.isRoot) return;
		
		if(n.attributes.type > 0) return this.fireEvent('edittemplate', n.attributes.id);
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
				Templates.deleteElement(n.attributes.id, this.processDelElement, this);
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

CB.TemplateEditWindow = Ext.extend(CB.GenericForm, {
	title: L.NewTemplate
	,padding: 0
	,initComponent: function(){
		items = [];
		CB.DB.languages.each(function(r){
			items.push({fieldLabel: L.Name+ ' ('+r.get('abreviation')+')', name: 'l'+r.get('id'), xtype: 'textfield', allowBlank: false})
		}, this);

		this.visibilityOptionsStore = new Ext.data.ArrayStore({
			autoLoad: true
			,autoDestroy: true
			,fields: [{name: 'id', type: 'int'}, 'name']
			,data: [[0, L.no],[1, L.inMenu], [2, L.inМenuАndАutoopen], [3, L.inTabsheet]]
		});
		
		idx = CB.DB.templates.findExact('type', -100);
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
					load: Templates.loadTemplate
					,submit: Templates.saveTemplate
					,waitMsg: L.LoadingData + ' ...'
				}
				,paramsAsHash: true
			}
			,tbar: [
				{text: L.Save, iconCls:'icon-save', disabled: true, handler: this.saveForm, scope: this}
				,{text: Ext.MessageBox.buttonText.cancel, iconCls:'icon-cancel', disabled: true, handler: this.loadData, scope: this}
			]
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
					,defaults: {anchor: '97%', msgTarget: 'side', listeners:{
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
				,change: function(){
					this.setDirty(true);
					this.onChange();
				}
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
		tb = this.getTopToolbar();
		b = tb.find('iconCls', 'icon-save')[0];
		if(b) b.setDisabled(!this._isDirty)
		b = tb.find('iconCls', 'icon-cancel')[0];
		if(b) b.setDisabled(!this._isDirty)
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
			// ,activeTab: 0
			,border: false
			// ,items: [this.TemplatesTagGroupsTree]
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
		// this.TemplatesTagGroupsTree.getRootNode().cascade(this.updateTreeNode, this);
	}
	,updateTreeNode: function(n){
		a = String(n.attributes.id).split('-');
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
