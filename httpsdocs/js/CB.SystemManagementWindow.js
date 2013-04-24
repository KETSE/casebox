Ext.namespace('CB'); 
// ------------------ import export Window
CB.ImportTagsForm = Ext.extend(Ext.Window, {  
     modal: true
    ,autoHeight: true
    ,width: 350
    ,frame: true
    ,title: L.UploadFile
    ,bodyPadding: '10 10 0'
    ,initComponent: function(){
        items = [];
        Ext.apply(this, {
            hideBorders: true
            ,items: {
                xtype: 'form'  
                ,fileUpload: true              
                ,monitorValid: true                
                ,items: [{
                    xtype: 'fieldset'
                    ,border: false
                    ,style: 'margin:0'
                    ,items:[{
                        fieldLabel: L.File
                        ,inputType: 'file'
                        ,name: 'file'
                        ,xtype: 'textfield'
                    }]
                }]
                ,buttons: [
                    {text: Ext.MessageBox.buttonText.ok, handler: this.doSubmit, scope: this, iconCls: 'icon-save', formBind: true}
                    ,{text: Ext.MessageBox.buttonText.cancel, handler: this.close, scope: this, iconCls: 'icon-cancel'}
                ]
            }
        });
        CB.ImportTagsForm.superclass.initComponent.apply(this, arguments);
    }
    ,doSubmit: function(){
        f = this.findByType('form')[0];        
        if(f.getForm().isValid()){
            f.getForm().submit({
                url: 'exportCsv.php?dir=import'
                ,waitMsg: ''
                ,scope: this
                ,success: this.onSubmitSuccess
                ,failure: this.onSubmitFailure
            });
        }        
    }
    ,onSubmitSuccess: function(form, action){    
        this.fireEvent('submitsuccess', this, action.result.node);
        this.hide();
    }
    ,onSubmitFailure: function(form, action){
        /**
        * @todo ALEX::error log      
        */
        switch(action.result.type){
            case 'nofile': 
                console.log('no file specified');               
                break;           
            case 'wrongtype': 
                console.log('wrong file type');               
                break;  
            default:              
                console.log('general error');
                break;
        }
    } 
});
// ------------------ add tag/folder form
CB.TagAddForm = Ext.extend(Ext.Window, {
	modal: true
	,autoHeight: true
	,width: 340
	,closeAction: 'hide'
	,initComponent: function(){
		items = [];
		CB.DB.languages.each(function(r){
			items.push({fieldLabel: L.Name+ ' ('+r.get('abreviation')+')', name: 'l'+r.get('id'), xtype: 'textfield', allowBlank: false})
		}, this);
		
		//items.push({fieldLabel: L.Order, name: 'order', xtype: 'numberfield'});
		if(this.data.type == 1) items.push({fieldLabel: L.IconClass, name: 'iconCls', xtype: 'textfield'}, {fieldLabel: L.Hidden, name: 'hidden', xtype: 'checkbox', inputValue: 1, value: 0});
		items.push({ xtype: 'displayfield', hideLabel: true, name: 'errorLabel', cls: 'taC cR', value: '&nbsp;' });
		Ext.apply(this, {
			hideBorders: true
			,items: {
				xtype: 'form'
				,monitorValid: true
				,items: [{
						xtype: 'fieldset'
						,border: false
						,style: 'margin:0'
						,defaults: {width: 200, anchor: '94%', enableKeyEvents: true, 
							listeners: {
								scope: this
								,keypress: function(f, e){ if( (e.getKey() == e.ENTER) && (!e.hasModifier()) ){ e.stopPropagation(); this.onOkClick();} } 
								,invalid: function(field, msg){
									if(field.getEl().hasClass('x-form-invalid')) this.hasInvalidFields = true;
								}
							}
							,msgTarget: 'side'
						}
						,items: items
					}
				]
				,listeners: {
					scope: this
					,clientvalidation: function(f, valid){
						if(!this.hasInvalidFields) return;
						el = this.find('name', 'errorLabel')[0];
						el.setValue( valid ? '&nbsp;': L.EmptyRequiredFields);
					}
				}
				,buttons: [
					{text: Ext.MessageBox.buttonText.ok, handler: this.onOkClick, scope: this, iconCls: 'icon-save', formBind: true}
					,{text: Ext.MessageBox.buttonText.cancel, handler: this.close, scope: this, iconCls: 'icon-cancel'}
				]
			}
		});
		CB.TagAddForm.superclass.initComponent.apply(this, arguments);
	}
	,afterRender: function(){
		CB.TagAddForm.superclass.afterRender.apply(this, arguments);
		this.findByType('form')[0].getForm().setValues(this.data);
		App.focusFirstField(this);
	}
	,onOkClick: function(){
		f = this.findByType('form')[0];
		delete this.data.hidden;
		if(f.getForm().isValid()){
			fields = ['order', 'iconCls', 'hidden'];
			CB.DB.languages.each(function(r){ fields.push('l'+r.get('id'))}, this);
			Ext.copyTo(this.data, f.getForm().getValues(), fields);
			this.status = 'ok';
			this.close();
		}
	}

});

// ----------------------------------------------------------- tags tree
CB.TagsTree = Ext.extend(Ext.tree.TreePanel, {
	autoScroll: true
	,containerScroll: true
	,stateId: 'CB_TagsTree'
	,stateful: true
	,rootVisible: true
	,animate: false
	,border: false
	,enableDD: true
	,ddGroup: 'tags'
	,initComponent: function(){
		treeMenuItems = [
			{text: L.AddTag, iconCls: 'icon-tag-plus', disabled: true, handler: this.onAddTagClick, scope: this}
			,{text: L.AddFolder, iconCls: 'icon-folder-plus', disabled: true, handler: this.onAddFolderClick, scope: this}
			,'-'
			,{text: L.Edit, iconCls: 'icon-pencil', disabled: true, handler: this.onEditNodeClick, scope: this}
			,'-'
			,{text: L.Delete, iconCls: 'icon-minus', disabled: true, handler: this.onDelNodeClick, scope: this}
			,'-'
			,{iconCls: 'icon-arrow-up-medium', disabled: true, handler: this.onMoveUpClick, scope: this}
			,{iconCls: 'icon-arrow-down-medium', disabled: true, handler: this.onMoveDownClick, scope: this}
			,{iconCls: 'icon-sort-alphabet', disabled: true, handler: this.onSortClick, scope: this}
			,{iconCls: 'icon-sort-alphabet-descending', disabled: true, handler: this.onSortDescendingClick, scope: this}
			,'->'
            ,{iconCls: 'icon-folder-export', disabled: true, handler: this.onExportCsvClick, scope: this}
            ,{iconCls: 'icon-folder-export', disabled: true, handler: this.onImportCsvClick, scope: this}

			,{iconCls: 'icon-reload', qtip: L.Reload, scope:this, handler: function(){this.getRootNode().reload();}}
		];
		
		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({
				directFn: System.tagsGetChildren
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
				,leaf: false
				,nid: 'root'
				,text: L.Tags
			}
			,tbar: treeMenuItems
			,listeners:{
				scope: this
				,afterlayout: function(){this.getRootNode().expand()}
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
					n.setText(n.attributes['l'+App.loginData.language_id] + ' <span class="cG">(id: '+n.attributes.id+')</span>');
					n.setIconCls((n.attributes.type != '1') ? 'icon-tree-folder' : Ext.value(n.attributes.iconCls, 'icon-tag-small'));
					if(n.attributes.hidden) n.setCls('cG');
					n.attributes.order = parseInt(n.attributes.order);
				}
				,beforedblclick: function(n, e){ if(n.attributes.type == 1){ this.onEditNodeClick(); return false;} }
			}
			,selModel: new Ext.tree.DefaultSelectionModel({
				listeners: {
					scope: this
					,selectionchange: this.onSelectionChange 
				}
			})
			,keys: [{
				key: [10,13]
				,fn: this.onEditNodeClick
				,scope: this
			}]
		});
		CB.TagsTree.superclass.initComponent.apply(this, arguments);
	}
	,afterRender: function() { CB.TagsTree.superclass.afterRender.apply(this, arguments); }
	,onSelectionChange: function(sm, node){ 
		tb = this.getTopToolbar();
		if(!node) node = this.getSelectionModel().getSelectedNode();
		if(!Ext.isEmpty(node)){
			tb.items.get(0).setDisabled(false); 
			tb.items.get(1).setDisabled(false); 
			tb.items.get(3).setDisabled(node.isRoot); 
			tb.items.get(5).setDisabled(node.isRoot); 
			tb.items.get(7).setDisabled(node.isFirst()); 
			tb.items.get(8).setDisabled(node.isLast()); 
			tb.items.get(9).setDisabled(!node.hasChildNodes() && node.loaded); 
			tb.items.get(10).setDisabled(!node.hasChildNodes() && node.loaded); 
            tb.items.get(12).setDisabled(false); 
			tb.items.get(13).setDisabled(false); 

		}else{
			tb.items.get(0).setDisabled(true); 
			tb.items.get(1).setDisabled(true); 
			tb.items.get(3).setDisabled(true); 
			tb.items.get(5).setDisabled(true); 
			tb.items.get(7).setDisabled(true); 
			tb.items.get(8).setDisabled(true); 
			tb.items.get(9).setDisabled(true); 
			tb.items.get(10).setDisabled(true); 
            tb.items.get(12).setDisabled(true);  
            tb.items.get(13).setDisabled(true);  

		}
	}
	,onAddTagClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		w = new CB.TagAddForm({title: L.NewTag, iconCls: 'icon-tag', data: {type: 1, pid: n.attributes.id}});
		w.on('hide', this.onSaveElementSubmitClick, this);
		w.show();
	}
	,onAddFolderClick: function(params, t){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		w = new CB.TagAddForm({title: L.NewFolder, iconCls: 'icon-folder', data: {type: 0, pid: n.attributes.id}});
		w.on('hide', this.onSaveElementSubmitClick, this);
		w.show();
	}
	,onSaveElementSubmitClick: function(w){
		if(w.status == 'ok'){
			this.getEl().mask(L.LoadingData);
			System.tagsSaveElement(w.data, this.onSaveElementProcess, this);
		}
		w.destroy();
	}
	,onSaveElementProcess: function(r, e){
		this.getEl().unmask();
		if(r.success != true) return;
		pn = this.getSelectionModel().getSelectedNode();
		if(pn.attributes.id == r.data.id){
			Ext.apply(pn.attributes, r.data);
			pn.setText(r.data['l'+App.loginData.language_id]);
			pn.setCls(Ext.isEmpty(pn.attributes.hidden) ? '' : 'cG');
		}else{
			if(pn.isExpanded()){
				n = pn.appendChild(r.data);
				sm = this.getSelectionModel();
				sm.clearSelections();
				sm.select(n);
			}else pn.reload(function(pn){
				n = pn.findChild('id', r.data.id);
				if(n){
					sm = this.getSelectionModel();
					sm.clearSelections();
					sm.select(n);				
				}
			}, this);
		}
	}
	,onEditNodeClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n || n.isRoot) return;
		data = {};
		fields = ['id', 'type', 'order', 'iconCls', 'hidden'];
		CB.DB.languages.each(function(r){ fields.push('l'+r.get('id'))}, this);
		Ext.copyTo(data, n.attributes, fields);
		if(data.iconCls == 'icon-tag-small') delete data.iconCls;
		w = new CB.TagAddForm({title: (data.type == 1) ? L.EditTag : L.EditFolder, iconCls: (data.type == 1) ? 'icon-tag' : 'icon-folder', data: data});
		w.on('hide', this.onSaveElementSubmitClick, this);
		w.show();
	}
	,sortTree: function(n1, n2){ 
		if(n1.attributes.type < n2.attributes.type) return -1;
		if(n1.attributes.type > n2.attributes.type) return 1;
		o1 = parseInt(n1.attributes.order);
		o2 = parseInt(n2.attributes.order);
		if(o1 < o2) return -1;
		if(o1 > o2) return 1;
		if(n1.text < n2.text) return -1;
		if(n1.text > n2.text) return 1;
		return 0;
	}
	,processMoveElement: function(r, e){
		if(r.success !== true) return false;
		attr = this.sourceNode.attributes;
		if(this.sourceNode.hasChildNodes() || !this.sourceNode.loaded) attr.loaded = false;
		this.sourceNode.remove(true);
		nn = null;
		switch(this.point){
			case 'above':
				attr.order = parseInt(this.targetNode.attributes.order);
				parent = this.targetNode.parentNode;
				parent.eachChild(function(n){if(n.attributes.order >= attr.order) n.attributes.order++}, this)
				nn = new Ext.tree.AsyncTreeNode(attr);
				parent.insertBefore(nn, this.targetNode);
				parent.sort(this.sortTree);
				break;
			case 'below':
				attr.order = parseInt(this.targetNode.attributes.order) + 1;
				parent = this.targetNode.parentNode;
				parent.eachChild(function(n){if(n.attributes.order >= attr.order) n.attributes.order++}, this)
				nn = new Ext.tree.AsyncTreeNode(attr);
				if(this.targetNode.isLast) parent.appendChild(nn); else parent.insertBefore(nn, this.targetNode);
				parent.sort(this.sortTree);
				break;
			default:
				if(this.targetNode.loaded){
					attr.order = this.targetNode.lastChild ? parseInt(this.targetNode.lastChild.attributes.order) + 1 : 1;
					this.targetNode.expand(false, false, function(tn){
						nn = new Ext.tree.AsyncTreeNode(attr);
						tn.appendChild(nn);
						tn.sort(this.sortTree);
					}, this);
				}else this.targetNode.expand();
		}
		if(nn){
			this.getSelectionModel().clearSelections();
			this.getSelectionModel().select(nn);
		}
	}
	,onDelNodeClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		
		Ext.MessageBox.confirm(L.Confirmation, ((n.attributes.type == 1) ? L.DeleteTag : L.DeleteFolder) + ' "'+n.attributes.text+'"?', 
		function(btn, text){
			if(btn == 'yes'){
				this.getEl().mask(L.Deleting)
				n = this.getSelectionModel().getSelectedNode();
				System.tagsDeleteElement(n.attributes.id, this.processDelElement, this);
			}
		}
		, this);
	}
	,processDelElement: function(r, e){
		this.getEl().unmask()
		if(r.success !== true){
			Ext.Msg.alert(L.Error, Ext.value(r.msg, 'Error deleting element'));
			return false;
		}
		sm = this.getSelectionModel();
		n = sm.getSelectedNode();
		if(!sm.selectNext(n)) sm.selectPrevious(n);
		n.remove(true);
	}
	,processDragDrop: function(tree, node, dd, e){
		this.sourceNode = dd.dragOverData.dropNode;
		this.point = dd.dragOverData.point;
		this.targetNode = dd.dragOverData.target;
		System.tagsMoveElement({id: this.sourceNode.attributes.id, toId: this.targetNode.attributes.id, point: dd.dragOverData.point}, this.processMoveElement, this);/**/
	}
	,onMoveUpClick: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		this.sourceNode = n;
		System.tagsMoveElement({id: n.attributes.id, toId: n.previousSibling.attributes.id, point: 'above'}, this.processMoveUp, this)
	}
	,onMoveDownClick: function(b, e){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		this.sourceNode = n;
		System.tagsMoveElement({id: n.attributes.id, toId: n.nextSibling.attributes.id, point: 'below'}, this.processMoveDown, this)
	}
	,processMoveUp: function(r, e){
		if(r.success !== true) return;
		this.sourceNode.attributes.order = this.sourceNode.previousSibling.attributes.order;
		this.sourceNode.previousSibling.attributes.order++;
		this.sourceNode.parentNode.sort(this.sortTree);
		this.onSelectionChange()
	}
	,processMoveDown: function(r, e){
		if(r.success !== true) return;
		this.sourceNode.attributes.order = this.sourceNode.nextSibling.attributes.order;
		this.sourceNode.nextSibling.attributes.order--;
		this.sourceNode.parentNode.sort(this.sortTree);
		this.onSelectionChange()
	}
	,onSortClick: function(b, e){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		this.sourceNode = n;
		System.tagsSortChilds({id: n.attributes.id, direction: 'asc'}, this.processSorting, this)
	}
	,onSortDescendingClick: function(b, e){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		this.sourceNode = n;
		System.tagsSortChilds({id: n.attributes.id, direction: 'asc'}, this.processSorting, this)
	}
	,onSortDescendingClick: function(b, e){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		this.sourceNode = n;
		System.tagsSortChilds({id: n.attributes.id, direction: 'desc'}, this.processSorting, this)
	}
	,processSorting: function(r, e){
		if(r.success !== true) return;
		this.sourceNode.reload();
	}
    ,onExportCsvClick: function (b,e){
        n = this.getSelectionModel().getSelectedNode();
        if(!n) return;
        this.sourceNode = n;
        
        try {Ext.destroy(Ext.get('downloadIframe'));}catch(e) {}
        
        Ext.DomHelper.append(document.body, {
            tag: 'iframe'
            ,id:'downloadIframe'
            ,frameBorder: 0
            ,width: 0
            ,height: 0
            ,css: 'display:none;visibility:hidden;height:0px;'
            ,src: 'exportCsv.php?dir=export&node=' + n.id
        });       
    }
    ,onImportCsvClick: function (b,e){           
        w = new CB.ImportTagsForm();        
        w.on('hide', function(w){ w.un('submitsuccess', this.onFileUploaded, this) }, this);
        w.on('submitsuccess', this.onImportSuccess, this);
        w.show();        
    }    
    ,onImportSuccess: function(b, e){
        n = this.getRootNode();
        n.reload();           
    }

})

// ---------------------------------------------- Main
CB.SystemManagementWindow = Ext.extend(Ext.Panel, {
	layout: 'border'
	,border: false
	,closable: true
	,iconCls: 'icon-application-tree'
	,title: L.Thesaurus
	,initComponent: function() {
		this.tagsTree = new CB.TagsTree({region: 'center', width: '300'});
		Ext.apply(this, {
			hideBorders: true
			,items: this.tagsTree
			,listeners: {
				beforedestroy: function(c){
					c.tagsTree.destroy();
				}
			}
		});
		CB.SystemManagementWindow.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('CBSystemManagementWindow', CB.SystemManagementWindow); // register xtype
