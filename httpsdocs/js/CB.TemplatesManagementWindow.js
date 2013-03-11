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
			,'-'
			,{text: L.Links, iconCls: 'icon-arrow-switch-090', handler: function(){this.fireEvent('editlinks')}, scope: this}
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
					if( (n.attributes.type == 2) && (n.attributes.visible != 1)) n.setText( '<i class="cG">'+n.attributes.text+'</i>');
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
		this.addEvents( 'edittemplate', 'editlinks' );
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

CB.TemplatesTagGroupsTree = Ext.extend(Ext.tree.TreePanel, {
	autoScroll: true
	,containerScroll: true
	,animate: false
	,border: false
	,rootVisible: false
	,enableDD: true
	,ddGroup: 'templates'
	,initComponent: function(){
		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({
				directFn: System.getTagGroupsTree
				,paramsAsHash: true
				,listeners:{
					scope: this
					,beforeload: function(treeLoader, node) { treeLoader.baseParams.path = node.getPath('id'); }
					,loadexception: function(loader, node, response) { node.leaf = false; node.loaded = false; }
				}
			})
			,root: {
				nodeType: 'async'
				,expanded: false
				,expandable: true
				,iconCls: 'icon-home'
				,nid: 'root'
				,text: L.TagGroups
			}
			,tbar: [
				{text: L.Remove, iconCls: 'icon-minus', disabled: true, handler: this.onDelNodeClick, scope: this}
				,'-'
				,{iconCls: 'icon-collapse-all', qtip: L.collapseAllText, scope:this, handler:this.collapseAll}
				,{iconCls: 'icon-expand-all', qtip: L.expandAllText, scope:this, handler:this.expandAll} 				
				,'-'
				,{iconCls: 'icon-arrow-up-medium', disabled: true, handler: this.onMoveUpClick, scope: this}
				,{iconCls: 'icon-arrow-down-medium', disabled: true, handler: this.onMoveDownClick, scope: this}
				,'-'
				,{iconCls: 'icon-reload', qtip: L.Reload, scope:this, handler: function(){this.getRootNode().reload();}}
			]
			,listeners:{
				scope: this
				,afterlayout: function(){this.getRootNode().expand()}
				,nodedragover: function(o){
					if( (o.dropNode == o.target) || ((o.point == 'below') && (o.target.nextSibling == o.dropNode)) || ((o.point == 'above') && (o.target.previousSibling == o.dropNode)) ){ //exclude itself
						o.cancel = true;
						return false;
					}
					if((o.dropNode.attributes.type < 1) || (o.dropNode.attributes.type > 5)){ /*only case templates */
						o.cancel = true;
						return false;
					}
					if(  (o.point == 'append') && (o.target.attributes.type != -o.dropNode.attributes.type) ||
						( (o.point != 'append') && (o.target.parentNode.attributes.type != -o.dropNode.attributes.type))
					){ // appending only to corresponding folder types
						o.cancel = true;
						return false;
					}
					
					if(o.target && (o.tree != this)){
						parent = (o.point == 'append') ? o.target : o.target.parentNode;
						c = parent.findChildBy(function(n){/* check if not already associated */
							return (n.attributes.id.split('-').pop() == o.dropNode.attributes.id.split('-').pop());
						}, this, false);
						if(c){
							o.cancel = true;
							return false;
						}
					}
				}
				,beforenodedrop: function(o){
					o.cancel = true;
					o.dropStatus = true;
				}
				,dragdrop: this.processDragDrop
				,beforeappend: function(t, p, n){ 
					//n.setText(n.attributes.text+' '+ n.attributes.type);
					/*isCaseType
					isPhase
					isTag
					isTemplate/**/
					if(n.attributes.id.substr(0, 2) == 't-'){
						n.leaf = true;
						//n.setIconCls('icon-object' + n.attributes.type); //templates
						n.attributes.isTemplate = true;
					}else{
						n.draggable = false;
						if(n.attributes.id.substr(0,3) == 'ct-') n.attributes.isCaseType = true;
						else if(n.attributes.id.substr(0,3) == 'tg-') n.attributes.isPhase = true;
						//else if(n.attributes.id.substr(0,2) == 't-') n.attributes.isTag = true;
					}
					n.attributes.order = parseInt(n.attributes.order);
				}
				,dblClick: this.onNodeDblClick
			}
			,selModel: new Ext.tree.DefaultSelectionModel({
				listeners: {
					scope: this
					,selectionchange: this.onSelectionChange 
				}
			})
			,keys: [{
				key: [10,13]
				,fn: this.onNodeDblClick
				,scope: this
			},{
				key: [46]
				,fn: this.onDelNodeClick
				,scope: this
			}]
		});
		CB.TemplatesTree.superclass.initComponent.apply(this, arguments);
		this.addEvents( 'edittemplate', 'change' );
		this.enableBubble( 'change' );
	}
	,onDelNodeClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n || (!n.isLeaf())) return;
		
		Ext.MessageBox.confirm(L.Confirmation, L.Remove + ' "'+n.attributes.text+'"?', 
		function(btn, text){
			if(btn == 'yes'){
				this.getEl().mask(L.LoadingData)
				n = this.getSelectionModel().getSelectedNode();
				System.templateDeassociateFromTag(n.attributes.id, this.processDelElement, this);
			}
		}
		, this);
	}
	,processDelElement: function(r, e){
		this.getEl().unmask();
		this.focus();
		if(r.success !== true) return;
		sm = this.getSelectionModel();
		n = sm.getSelectedNode();
		if(!sm.selectNext(n)) sm.selectPrevious(n);
		n.remove(true);
	}
	,processDragDrop: function(tree, node, dd, e){
		//when dropping from tree itself then just move the node, otherwise - associating template
		this.dragOverData = dd.dragOverData;
		if(tree == this){ // moving a node from this tree
			System.templateMoveToTag({
				source_id: this.dragOverData.dropNode.attributes.id
				,target_id: this.dragOverData.target.attributes.id
				,point: this.dragOverData.point
			}, this.processMoveElement, this);
		}else{ // asociating a template dropped from outside
			System.templateAssociateToTag({
				template_id: this.dragOverData.dropNode.attributes.id
				,target_id: this.dragOverData.target.attributes.id
				,point: this.dragOverData.point
			}, this.processAddElement, this);
		}
	}
	,processAddElement: function(r, e){
		if(r.success != true) return;
		target = (this.dragOverData.point == 'append') ? this.dragOverData.target : this.dragOverData.target.parentNode;
		if(target.loaded){
			target.expand();
			target.eachChild(function(n){if(n.attributes.order >= r.data.order) n.attributes.order = parseInt(n.attributes.order) +1; }, this);
			target.appendChild(r.data);
			target.sort(this.sortTree);
		}else this.targetNode.expand();
	}
	,processMoveElement: function(r, e){
		if(r.success != true) return;
		target = (this.dragOverData.point == 'append') ? this.dragOverData.target : this.dragOverData.target.parentNode;
		this.dragOverData.dropNode.remove(true);
		if(target.loaded){
			target.expand();
			target.eachChild(function(n){if(n.attributes.order >= r.data.order) n.attributes.order = parseInt(n.attributes.order) +1; }, this);
			target.appendChild(r.data);
			target.sort(this.sortTree);
		}else this.targetNode.expand();
	}
	,onNodeDblClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		if(n.isLeaf() && (n.attributes.type > 0)) return this.fireEvent('edittemplate', n.attributes.id);
		if(n.attributes.isCaseType) return this.fireEvent('editcasetemplate', n.attributes.id);
	}
	,sortTree: function(n1, n2){ 
		// if(Ext.isEmpty(n1.attributes.type) && !Ext.isEmpty(n2.attributes.type)) return -1;
		// if(!Ext.isEmpty(n1.attributes.type) && Ext.isEmpty(n2.attributes.type)) return 1;
		// if(n1.attributes.type < n2.attributes.type) return -1;
		// if(n1.attributes.type > n2.attributes.type) return 1;
		if(n1.attributes.order < n2.attributes.order) return -1;
		if(n1.attributes.order > n2.attributes.order) return 1;
		// if(n1.text < n2.text) return -1;
		// if(n1.text > n2.text) return 1;
		return 0;
	}
	,onMoveUpClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		this.sourceNode = n;
		System.templateMoveToTag({source_id: n.attributes.id, target_id: n.previousSibling.attributes.id, point: 'above'}, this.processMoveUp, this)
	}
	,onMoveDownClick: function(b, e){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		this.sourceNode = n;
		System.templateMoveToTag({source_id: n.attributes.id, target_id: n.nextSibling.attributes.id, point: 'below'}, this.processMoveDown, this)
	}
	,processMoveUp: function(r, e){
		if(r.success !== true) return;
		this.sourceNode.attributes.order = this.sourceNode.previousSibling.attributes.order;
		this.sourceNode.previousSibling.attributes.order++;
		this.sourceNode.parentNode.sort(this.sortTree);
		this.onSelectionChange(this.getSelectionModel(),this.sourceNode)
	}
	,processMoveDown: function(r, e){
		if(r.success !== true) return;
		this.sourceNode.attributes.order = this.sourceNode.nextSibling.attributes.order;
		this.sourceNode.nextSibling.attributes.order--;
		this.sourceNode.parentNode.sort(this.sortTree);
		this.onSelectionChange(this.getSelectionModel(),this.sourceNode)
	}
	,onSelectionChange: function(sm, node){ 
		tb = this.getTopToolbar();
		if(!Ext.isEmpty(node)){
			tb.items.get(0).setDisabled(!node.isLeaf()); 
			tb.items.get(5).setDisabled(!node.isLeaf() || node.isFirst()); 
			tb.items.get(6).setDisabled(!node.isLeaf() || node.isLast()); 
		}else{
			tb.items.get(0).setDisabled(true); 
			tb.items.get(5).setDisabled(true); 
			tb.items.get(6).setDisabled(true); 
		}
	}

});
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
CB.TemplateLinksWindow = Ext.extend(CB.GenericForm, {
	iconCls: 'icon-arrow-switch-090'
	,data: {title: L.Links}
	,initComponent: function(){
	
		this.grid = new Ext.grid.GridPanel({
			stripeRows: true
			,tbar: [
				{iconCls: 'icon-save', text: L.Save, handler: this.onSaveClick, scope: this, disabled: true}
				,{iconCls: 'icon-cancel', text: L.Cancel, handler: this.onCancelClick, scope: this, disabled: true}
			]
			,store: new Ext.data.DirectStore({
				autoLoad: true
				,autoDestroy: true
				,restful: false
				,proxy: new  Ext.data.DirectProxy({ paramsAsHash: true, directFn: Templates.getTemplateLinks })
				,reader: new Ext.data.JsonReader({
					successProperty: 'success'
					,idProperty: 'id'
					,root: 'data'
					,messageProperty: 'msg'
				},[	"id"
					,"name"
					,"size"
					,"pages"
					,{name: "date", mapping: "added", type: 'date', dateFormat: 'Y-m-d H:i:s'}
					,"iconCls"
					,"iconCls32"
					,"associates_count"
				]
				)
			})
			
		});
		
		CB.TemplateLinksWindow.superclass.initComponent.apply(this, arguments);
	}
	,onSaveClick: function(){}
});
Ext.reg('CBTemplateLinksWindow', CB.TemplateLinksWindow); // register xtype

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
				,editlinks: this.onEditLinks
			}
		});
		this.TemplatesTagGroupsTree = new CB.TemplatesTagGroupsTree({
			title: 'Cases structure', closable: false
			,listeners: {
				scope: this
				,edittemplate: this.onEditTemplate
				,editcasetemplate: this.onEditCaseTemplate
			}
		});
	
		this.tabPanel = new Ext.TabPanel({
			region: 'center'
			,activeTab: 0
			,border: false
			,items: [this.TemplatesTagGroupsTree]
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
	,onEditCaseTemplate: function(id){
		Templates.getCaseTypeTempleId(id, function(r, e){
			if(r.success !== true) return;
			this.onEditTemplate(r.id);
		}, this)
	}
	,onUpdateTemplate: function(w, action){
		this.TemplatesTree.getRootNode().cascade(this.updateTreeNode, this);
		this.TemplatesTagGroupsTree.getRootNode().cascade(this.updateTreeNode, this);
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
			if(w.data.visible != 1) text = '<i class="cG">' + text + '</i>'
			n.setText(text);
			n.setIconCls(w.data.iconCls);
			return false;
		}
	}
	,onEditLinks: function(){
		App.openUniqueTabbedWidget('CBTemplateLinksWindow', this.tabPanel)
	}
});
Ext.reg('CBTemplatesManagementWindow', CB.TemplatesManagementWindow); // register xtype
