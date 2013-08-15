Ext.namespace('CB'); 

CB.Objects = Ext.extend(CB.GenericForm, {
	title: L.NewObject
	,padding: 0
	,initComponent: function(){
		
		this.objectsStore = new Ext.data.DirectStore({
			autoLoad: false
			,restful: false
			,proxy: new  Ext.data.DirectProxy({
				paramsAsHash: true
				,api: { read: CB_Objects.getAssociatedObjects }
				,listeners:{
					scope: this
					,load: function(proxy, obj, opt){
						for (var i = 0; i < obj.result.data.length; i++) obj.result.data[i].date = date_ISO_to_date(obj.result.data[i].date);
					}
				}
			})
			,reader: new Ext.data.JsonReader({
				successProperty: 'success'
				,root: 'data'
				,messageProperty: 'msg'
			},[ 
					{name: 'id', type: 'int'}
					,'name'
					,{name: 'date', type: 'date'}
					,{name: 'type', type: 'int'}
					,{name: 'subtype', type: 'int'}
					,{name: 'template_id', type: 'int'}
					,{name: 'status', type: 'int'}
					, 'iconCls'
			]
			)
			,listeners:{
				scope: this
				,add: this.onObjectsStoreChange
				,load: this.onObjectsStoreChange
			}
			,getTexts: getStoreNames
			,getData: function(v){
				if(Ext.isEmpty(v)) return [];
				ids = String(v).split(',');
				data = [];
				Ext.each(ids, function(id){
					 idx = this.findExact('id', parseInt(id));
					if(idx >= 0) data.push(this.getAt(idx).data);			
				}, this)
				return data;
			}
			,checkRecordExistance: function(data){
				if(Ext.isEmpty(data)) return false;
				idx = this.findExact('id', parseInt(data.id));
				if(idx< 0){
					r = new this.recordType(data);
					r.set('iconCls', getItemIcon(data));
					this.add(r);
				}
			}
		});
		
		this.objectsStore.baseParams = {id: this.data.id, template_id: this.data.template_id}
		this.objectsStore.load();

		this.topFieldSet = new Ext.form.FieldSet({
			columnWidth: 0.9
			,autoHeight: true
			,xtype: 'fieldset'
			,border: false
			,labelWidth: 130
			,bodyStyle: 'padding: 10px'
			,cls: 'spacy-fields'
			,autoHeight: true
			,defaults:{
				minWidth: 90
				,anchor: '95%'
				,boxMaxWidth: 800
				,bubbleEvents: ['change']
			}
			,items: []
			,listeners: {
				scope: this
				,add: function(f, c, i){ c.enableBubble('change'); }
			}
		})
		this.tabPanel = new Ext.TabPanel({
			xtype: 'tabpanel'
			,region: 'center'
			,headerCfg: {cls: 'whiteTabPanel'}
			,activeItem: 0
			,enableTabScroll: true
			,tabMargin: 120
		});
		this.violationsStore = new Ext.data.JsonStore({
			autoDestroy: true
			,proxy: new  Ext.data.MemoryProxy()
			,fields: [	'id', 'type_id', 'author', 'details', {name: 'date', type: 'date', dateFormat: 'Y-m-d'} ]
		})
		this.associatedDecisionsStore = new Ext.data.JsonStore({
			autoDestroy: true
			,proxy: new  Ext.data.MemoryProxy()
			,fields: [	'id', 'decision_id', 'decision_title', {name: 'decision_date', type: 'date', dateFormat: 'Y-m-d'}, 'decision_icon', 'violation_id', 'viuolation_type', 'violation_title', {name: 'violation_date', type: 'date', dateFormat: 'Y-m-d'} ]
			,sortInfo: {
				field: 'decision_date',
				direction: 'ASC'
			}
		})
		this.associatedViolationsStore = new Ext.data.JsonStore({
			autoDestroy: true
			,proxy: new  Ext.data.MemoryProxy()
			,fields: [	'id', 'header_row', 'name', 'violation_id', 'violation_title', {name: 'violation_date', type: 'date', dateFormat: 'Y-m-d'}, 
			'decision_id', 'decision_title', {name: 'decision_date', type: 'date', dateFormat: 'Y-m-d'}, 
			'complaint_id', 'complaint_title', 'complaint_satisfaction', {name: 'complaint_date', type: 'date', dateFormat: 'Y-m-d'}, 'result', 'disciplining_duties', 'position' 
			,'complaint_icon', 'decision_icon'
			]
		})
		this.associatedComplaintsStore = new Ext.data.JsonStore({
			autoDestroy: true
			,proxy: new  Ext.data.MemoryProxy()
			,fields: [	'id', 'name', 'header_row', { name: 'position', type: 'int'},
			'complaint_id', 'complaint_title', 'complaint_icon', {name: 'complaint_date', type: 'date', dateFormat: 'Y-m-d'}, 'complaint_satisfaction',
			'decision_id', 'decision_title', 'decision_icon', {name: 'decision_date', type: 'date', dateFormat: 'Y-m-d'}, 
			'violation_id', 'violation_title', {name: 'violation_date', type: 'date', dateFormat: 'Y-m-d'},
			'result', 'disciplining_duties'
			]
		})
		this.associatedAppealsStore = new Ext.data.JsonStore({
			autoDestroy: true
			,proxy: new  Ext.data.MemoryProxy()
			,fields: [	'header_row', 'violation_id', 'violation_title',
			'decision_id', 'decision_title', {name: 'decision_date', type: 'date', dateFormat: 'Y-m-d'}, 
			'complaint_id', 'complaint_title', 'complaint_satisfaction', {name: 'complaint_date', type: 'date', dateFormat: 'Y-m-d'}, 'result', 'disciplining_duties'
			,'complaint_icon', 'decision_icon', {name: 'positions', type: 'int'}
			]
		})
		this.actions = {
			save: new Ext.Action({
				text: L.Save
				,iconAlign:'top'
				,iconCls: 'icon32-save'
				,scale: 'large'
				,disabled: true
				,scope: this
				,handler: this.onSaveClick
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
			,createTask: new Ext.Action({
				text: L.NewTask
				,iconCls: 'icon32-task-new'
				,iconAlign:'top'
				,scale: 'large'
				//,disabled: true
				,scope: this
				,handler: this.onCreateTaskClick.createInterceptor(this.autoSaveObjectInterceptor, this)
			})
			,upload: new Ext.Action({
				tooltip: L.UploadFile
				,iconCls: 'icon-drive-upload'
				,text: L.Upload
				//,disabled: true
				,scope: this
				,handler: this.onUploadClick.createInterceptor(this.autoSaveObjectInterceptor, this)
			})
			,paste: new Ext.Action({
				tooltip: L.PasteFromClipboard
				,text: L.PasteFromClipboard
				,disabled: true
				,scope: this
				,handler: this.onPasteClick.createInterceptor(this.autoSaveObjectInterceptor, this)
			})
		}
		Ext.apply(this, {
			layout: 'fit'
			,initialConfig:{
				api: { 
					load: CB_Objects.load
					,submit: CB_Objects.save
					,waitMsg: L.LoadingData + ' ...'
				}
				,paramsAsHash: true
			}
			,listeners:{
				afterlayout: {scope: this, fn: function(){
					if(this.loaded) return; 
					this.getEl().mask(L.Downloading + ' ...', 'x-mask-loading'); 
				}}
				,activate: function(){
					ep = this.find('region', 'center');
					if(!Ext.isEmpty(ep)) ep[0].syncSize();
				}
				,change: function(c, v){ 
					this.setDirty(true);
					this.onObjectChanged();
					if(c && c.isXType && (c.isXType('combo')) ){
						this.updateDependentFields(c.name, v);
					}
				}
				,savesuccess: this.onObjectSaved
				,beforedestroy: {
					scope: this
					,fn: function(){ 
						this.getBubbleTarget().un('filesdeleted', this.onFilesDeleted, this); 
						this.getBubbleTarget().un('fileuploaded', this.onFileUploaded, this);
						if(this.grid){
							this.grid.destroy();
							delete this.grid;
						}
						if(this.filesGrid){
							this.filesGrid.destroy();
							delete this.filesGrid;
						}
						App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
						App.clipboard.un('change', this.onClipboardChange, this);
						delete this.filesDropPlugin;
					
					}
				}
			}
		});
		
		this.dropZoneConfig = {text: 'Drop files here'}
		this.filesDropPlugin = new CB.plugins.FilesDropZone({pidPropety: 'id'});
		this.filesDropPlugin.init(this);

  		CB.Objects.superclass.initComponent.apply(this, arguments);
		this.addEvents('deleteobject', 'associateObject', 'deassociateObject', 'fileupload', 'filedownload');//, 'filesdelete'
		this.enableBubble(['deleteobject', 'fileupload', 'filedownload']);//, 'filesdelete'
		App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
		App.clipboard.on('change', this.onClipboardChange, this);
		App.fireEvent('objectinit', this);
	}
	,getProperty: function(propertyName){
		if(this.data && this.data[propertyName]){
			if(propertyName == 'pathtext') return this.data[propertyName]+this.data.name+'/';
			return this.data[propertyName];
		}
		return null;
	}
	,autoSaveObjectInterceptor: function(){
		if(isNaN(this.data.id)){
			this.interceptorArguments = arguments;
			this.onSaveClick();
			return false;
		}
		return true;
	}
	,onFormLoaded: function(r, e){
		this.data.cdate = date_ISO_to_date(this.data.cdate);
		this.data.udate = date_ISO_to_date(this.data.udate);
	}
	,onObjectsStoreChange: function(store, records, options){
		Ext.each(records, function(r){ r.set('iconCls', getItemIcon(r.data)) }, this);
		if(this.grid && !this.grid.editing) this.grid.getView().refresh();
	}	
	,prepareInterface: function(){
		toolbarItems = []

		/* insert create menu if needed */
		menuConfig = getMenuConfig(this.data.id, this.data.path, this.data.template_id);
		if( !Ext.isEmpty(menuConfig) ){
			createButton = new Ext.Button({	
				text: L.Create
				,iconCls: 'icon32-create'
				,iconAlign:'top'
				,scale: 'large'
                       		,menu: [ ]
                     	})
			updateMenu(createButton, menuConfig, this.onCreateObjectClick.createInterceptor(this.autoSaveObjectInterceptor, this), this);
			toolbarItems.push(createButton, '-')
		}
		/**/
		toolbarItems.push(this.actions.save);

		if(!this.hideDeleteButton) toolbarItems.push(this.actions['delete']);
		
		toolbarItems.push('-',{text: 'Attach', iconCls: 'icon32-attach', scale: 'large', iconAlign:'top'
                	,menu: [
				this.actions.upload
				,'-'
				,this.actions.paste
			]
         	})

		toolbarItems.push(this.actions.createTask)
		
		if(!this.data.tags) this.data.tags = {};

		northRegionItems = [ this.topFieldSet ]

		this.grid = Ext.create({ 
			title: L.Details
			,show_files: this.templateData.cfg.files
			,refOwner: this
		}, Ext.value(this.templateData.cfg.gridJsClass, 'CBVerticalEditGrid'));
		tabPanelItems = [  ]
		//placing content elements
		contentItems = [this.tabPanel];
		if( (this.topFieldSet.items.getCount() > 0) )
			contentItems.unshift({
				xtype: 'panel'
				,autoHeight: true
				,region: 'north'
				,border: false
				,layout: 'fit'
				,padding: 0
				,items: northRegionItems
		});
		this.childsPanel = new CB.ActionChildsPanel({style: 'margin-bottom: 25px', hidden: true});
		this.filesPanel = new CB.ActionFilesPanel({style: 'margin-bottom: 25px', hidden: true});
		this.tasksPanel = new CB.ActionTasksPanel({style: 'margin-bottom: 25px', hidden: true});
		this.propertiesPanel = new CB.ObjectsPropertiesPanel({
			listeners:{
				scope: this
				,pathclick: this.onPathClick
			}
		});
		this.add({
			xtype: 'panel'
			,tbar: toolbarItems
			,tbarCssClass: 'x-panel-white'
			,layout: 'border'
			,border: false
			,hideBorders: true
			,items: [{
					layout: 'border'
					,region: 'center'
					,border: false
					,defaults: {border: false}
					,xtype: 'panel'
					,items: contentItems
				},{
					xtype: 'panel'
					,region: 'east'
					,width:300
					,split: true
					,statefull: true
					,stateId: 'coEP' //case object east panel
					,bodyStyle: 'background-color: #F4F4F4'
					,autoScroll: true
					,items: [this.childsPanel, this.filesPanel, this.tasksPanel, this.propertiesPanel]
				}
			]
		});
		this.mainToolBar = this.items.first().getTopToolbar();
		while(i = tabPanelItems.pop()) this.tabPanel.insert(0, i);
		
		this.getEl().unmask();	
		
		this.addEvents('taskcreate', 'taskedit');
		this.enableBubble(['taskcreate', 'taskedit']);
		this.getBubbleTarget().on('taskupdated', this.onTaskUpdate, this);
		this.getBubbleTarget().on('tasksdeleted', this.onTaskDelete, this);
		this.fireEvent('objectopened', this);
	}
	,onSaveClick: function(){
		this.saveForm();
	}
	,onTagsChange: function(ed, newValue){
		this.fireEvent('change'); //this.setDirty(true);
	}
	,hasMainFile: function(){
		return (this.data.mainFile && !isNaN(this.data.mainFile.id));
	}
	,getObjectDate: function(){
		idx = this.templateStore.findExact('name', '_date_start');
		if(idx >=0) {
			r = this.templateStore.getAt(idx);
			return this.getCurrentFieldValue(r.get('id'), 0)
		}
		return null;
	}
	,getCurrentFieldValue: function(field_id, duplication_id){
		ed = this.topFieldSet.find('name', 'f'+ field_id+'_0');
		if(!Ext.isEmpty(ed)) return ed[0].getValue();
		if(Ext.isEmpty(this.grid) || Ext.isEmpty(this.grid.getFieldValue)) return null;
		return this.grid.getFieldValue(field_id, duplication_id);
	}
	,onDeleteClick: function(b){
		this.fireEvent('deleteobject', this.data)
	}
	,onObjectsDeleted: function(ids){
		if(ids.indexOf(parseInt(this.data.id)) >=0 ) this.destroy();
	}
	,onClipboardChange: function(cb){
		this.actions.paste.setDisabled(cb.isEmpty());
	}
	,getBubbleTarget: function(){
		return App.mainViewPort;
	}
	,setFormValues: function(){
		lastActiveTabIndex = this.tabPanel.items.indexOf(this.tabPanel.activeTab);
		if(!Ext.isDefined(this.data.associatedObjects)) this.data.associatedObjects = [];
		if(Ext.isEmpty(this.data.gridData)) this.data.gridData = {};
		if(!Ext.isDefined(this.data.tags)) this.data.tags = {};
		/* adding top fields and fields editable in tabsheet */
		if(Ext.isDefined(this.topFieldSet)){
			this.topFieldSet.removeAll(true);
		}
		/* remove tabpanel items that have edit position on tabsheet */
		this.tabPanel.items.each(function(i){ if(i.isTemplateField) this.tabPanel.remove(i, true); }, this);
		tpInsertIndex = 1;
		/* getting the template store and adding fields, that are set to be edited on top, to the fieldSet.
			Also creating tabs in our tabPanel for the fields that are set to be edited in tabpanel
		*/
		/* we admit that the template property is available */
		this.templateData = {};
		idx = CB.DB.templates.findExact('id', parseInt(this.data.template_id));
		if(idx >= 0) this.templateData = CB.DB.templates.getAt(idx).data;
		if(Ext.isEmpty(this.templateData.cfg)) this.templateData.cfg = {}
	
		this.templateStore = CB.DB['template' + this.data.template_id];
		if(!this.templateStore){
			Ext.Msg.alert(L.Error, 'No template store identified');
			this.doClose();
			return;
		}
		tabPanelFieldItems = [];
		this.templateStore.each(function(r){
			if((r.get('cfg').showIn == 'top') && Ext.isDefined(this.topFieldSet)){
				v = this.data.gridData.values ? this.data.gridData.values['f'+r.get('id')+'_0'] : (Ext.isDefined(r.get('cfg').value) ? {value: r.get('cfg').value} : {});
				if(!v) v = {};

				if((r.get('name') == '_title') && isNaN(this.data.id) && !Ext.isEmpty(this.data.custom_title))
					v.value = this.data.custom_title;
				//if(it's object_date_start field and it is a new object then we are setting it's value to today)
				if((r.get('name') == '_date_start') && isNaN(this.data.id))
					v.value = Ext.isEmpty(this.data.date_start) ? new Date() : this.data.date_start;
				//if there is a date set for the date field, we are parsing it to a date value
				if( (r.get('type') == 'date') && Ext.isString(v.value) && !Ext.isEmpty(v.value) )
					v.value = Date.parseDate(v.value.substr(0,10), 'Y-m-d');
				if( (r.get('type') == 'datetime') && Ext.isString(v.value) && !Ext.isEmpty(v.value) )
					v.value = Date.parseDate(v.value, (v.value.indexOf('T') >= 0) ? 'Y-m-dTH:i:s' : 'Y-m-d H:i:s' );

				/* here we are adding fields to the top fieldSet */
				pidValue = null;
				disabled = false;
				if( Ext.isDefined(r.get('cfg').dependency) && !Ext.isEmpty(r.get('pid'))){
					pidValue = this.data.gridData.values? Ext.value(this.data.gridData.values['f'+r.get('pid')+'_0'], {}).value : null;
					disabled = Ext.isEmpty(pidValue);
				}

				ed = App.getTypeEditor(r.get('type'), {
					ownerCt: this
					,record: r
					,pidValue: pidValue
					,objectId: this.data.id
					,path: this.data.path
				});
				if(ed){
					ed.fieldLabel = r.get('title');
					ed.disabled = disabled;
					if(!Ext.isEmpty(r.get('cfg').hint)) ed.fieldLabel = '<span title="'+r.get('cfg').hint+'">'+ed.fieldLabel+'</span>';
					ed.name = 'f' + r.get('id') + '_0';
					//setting the automatic title of the object 
					if(ed.isXType(Ext.ux.TitleField)) ed.setValues(this.data.title, v.value);
					else ed.setValue(v.value);
					
					if(r.get('type') == '_contact'){
						ed.params = {
							pid : r.get('pid')
							,multiValued: (r.data.cfg.multiValued == true)
							,dependency: r.data.cfg.dependency
							,tags: r.data.cfg.tags
							,templates: r.data.cfg.templates
						}
						ed.on('focus', this.onFocusContactField, this);
					}else if(r.get('type') == '_case'){
						ed.params = {
							pid : r.get('pid')
							,multiValued: (r.data.cfg.multiValued == true)
							,dependency: r.data.cfg.dependency
							,tags: r.data.cfg.tags
						}
					}else if(r.get('type') == '_case_object'){
						ed.params = {
							pid: r.get('pid')
							,multiValued: (r.data.cfg.multiValued == true)
							,dependency: r.data.cfg.dependency
							,tags: r.data.cfg.tags
							,templates: r.data.cfg.templates
							,excludeIds: this.data.id
						}
					}
					this.topFieldSet.add(ed);
					//ed.enableBubble('change');
				}
			}else if(r.get('cfg').showIn == 'tabsheet'){
				v = this.data.gridData.values ? this.data.gridData.values['f'+r.get('id')+'_0'] : (Ext.isDefined(r.get('cfg').value) ? {value: r.get('cfg').value} : {});
				if(!v) v = {};
				var cfg = {
					border: false
					,hideBorders: true
					,title: r.get('title')
					,isTemplateField: true
					,name: 'f'+r.get('id')+'_0'
					,value: v.value
					,listeners: {
						scope: this
						,change: function(){ this.fireEvent('change')}
						,sync: function(){ this.fireEvent('change')}
					}
				}
				switch( r.get('type') ){
					case 'text': tabPanelFieldItems.push(new Ext.form.TextArea(cfg));
						break; 
					case 'html': tabPanelFieldItems.push(new Ext.ux.HtmlEditor(cfg));
						break;
				}
			}
		}, this);
		/* end of adding top fields and fields editable in tabsheet */
		
		if(!this.loaded){
			this.loaded = true;
			this.getBubbleTarget().on('filesdeleted', this.onFilesDeleted, this);
			this.getBubbleTarget().on('fileuploaded', this.onFileUploaded, this);
			this.prepareInterface();
			this.childsPanel.reload();
			this.filesPanel.reload();
			this.tasksPanel.reload();
			this.propertiesPanel.data = this.data;
		}else if(this.propertiesPanel && this.propertiesPanel.rendered) this.propertiesPanel.update(this.data)
		this.grid.reload();
		if((this.grid.store.getCount() > 0) && ( (this.tabPanel.items.getCount() == 0) || (this.tabPanel.items.first().items.first() != this.grid) ) )  
			this.tabPanel.insert(0, {
				title: L.Details
				//,autoScroll:true
				,layout: 'fit'
				,items: this.grid
				,bodyStyle:'margin:0; padding: 0'
				,listeners: {
					scope: this
					,afterlayout: function(p){
						w = p.getWidth();
						this.grid.setWidth(w-9)
						this.grid.getEl().setWidth(w);
						
						o = this.grid.getEl().query('.x-panel-body');
						for (var i = 0; i < o.length; i++) Ext.get(o[i]).setWidth(w)
						o = this.grid.getEl().query('.x-grid3');
						for (var i = 0; i < o.length; i++) Ext.get(o[i]).setWidth(w)
						o = this.grid.getEl().query('.x-grid3-scroller');
						for (var i = 0; i < o.length; i++) Ext.get(o[i]).setWidth(w)
					}
				}
			});//this.tabPanel.items.removeAt(0);

		if(this.topFieldSet){
			if(this.topFieldSet.isRendered){
				this.topFieldSet.syncSize()
			}
		}
		this.doLayout();
		this.items.first().items.first().syncSize();
		//setting all form values, inclusive in the grid
		this.violationsStore.removeAll();
		if(this.data.violations) this.violationsStore.loadData(this.data.violations, false);
		this.associatedDecisionsStore.removeAll();
		if(this.data.associatedDecisions) this.associatedDecisionsStore.loadData(this.data.associatedDecisions, false);
		
		this.associatedViolationsStore.removeAll();
		if(this.data.associatedViolations) this.associatedViolationsStore.loadData(this.data.associatedViolations, false);
		
		this.associatedComplaintsStore.removeAll();
		if(this.data.associatedComplaints){
			Ext.each(this.data.associatedComplaints, function(i, idx, arr){
				arr[idx].id = i.complaint_id + '_' + i.decision_id + '_' + i.violation_id;
				arr[idx].position = 1 + (Ext.isEmpty(arr[idx].complaint_id) ? 1 : 0) + (Ext.isEmpty(arr[idx].decision_id) ? 1 : 0)
			});
			this.associatedComplaintsStore.loadData(this.data.associatedComplaints, false);
		}
		this.associatedAppealsStore.removeAll();
		if(this.data.associatedAppeals) this.associatedAppealsStore.loadData(this.data.associatedAppeals, false);
		switch(this.templateData.cfg.violations_edit){
			case 2: if(this.violationsStore.getCount() == 0) break;
			case 3: this.showViolationsEditPanel(0); break;
		}
		switch(this.templateData.cfg.decisions_association){
			case 2: if(this.associatedDecisionsStore.getCount() == 0) break;
			case 3: this.showDecisionsAssociationPanel(0); break;
		}
		switch(this.templateData.cfg.violations_association){
			case 2: if(this.associatedViolationsStore.getCount() == 0) break;
			case 3: this.showViolationsAssociationPanel(0); break;
		}
		switch(this.templateData.cfg.complaints){
			case 2: if(this.associatedComplaintsStore.getCount() == 0) break;
			case 3: this.showComplaintsEditPanel(0); break;
		}
		switch(this.templateData.cfg.appeals){
			case 2: if(this.associatedAppealsStore.getCount() == 0) break;
			case 3: this.showAssociatedAppealsPanel(0); break;
		}
		
		tagsItem = this.mainToolBar.find('iconCls', 'icon-tag')[0];
		if(tagsItem) tagsItem.menu.items.first().setValue(Ext.value(this.data.tags[3], []));
		userTagsItem = this.mainToolBar.find('iconCls', 'icon-tag-label')[0];
		if(userTagsItem) userTagsItem.menu.items.first().setValue(Ext.value(this.data.tags[4], []));
		
		Ext.each(tabPanelFieldItems, function(i){this.tabPanel.insert(tpInsertIndex++, i);}, this)
		lastActiveTabIndex = (lastActiveTabIndex > 0) ? lastActiveTabIndex : 0;
		p = this.tabPanel.items.itemAt(lastActiveTabIndex);
		if(!p || !p.isVisible()) {
			p = this.tabPanel.items.itemAt(lastActiveTabIndex+1);
			if(!p || !p.isVisible()) {
				p = this.tabPanel.items.itemAt(lastActiveTabIndex-1);
				if(p && p.isVisible()) lastActiveTabIndex--;
			}else lastActiveTabIndex++
		}
		this.tabPanel.setActiveTab(lastActiveTabIndex);
		this.setDirty(false);
		this.onObjectChanged();
		this.focusFirstField();
	}
	,focusFirstField: function(){
		App.focusFirstField(this);
	}
	,onCreateObjectClick: function(b, e) {
		data = Ext.apply({}, {
			pid: this.data.id
			,path: this.data.path+'/'+this.data.id
			,pathtext: this.data.pathtext+'/'+Ext.value(this.data.custom_title, this.data.title)
		}, b.data);
		App.mainViewPort.createObject(data, e);
	}
	,onCreateTaskClick: function(o, e){
		this.fireEvent('taskcreate', { data: {pid: this.data.id, path: this.data.path+'/'+this.data.id, pathtext: this.data.pathtext+ Ext.value(this.data.title, this.data.custom_title)} })
	}
	,onTaskUpdate: function(taskData){ // TO REVIEW
		if(taskData.object_id != this.data.id) return;
		i = 0;
		while( (i < this.data.tasks.length) &&  (this.data.tasks[i].id != taskData.id) ) i++;
		if(i < this.data.tasks.length) this.data.tasks[i] = taskData;
		else this.data.tasks.push(taskData);
	}
	,onTaskDelete: function(taskData){ // TO REVIEW
		if(taskData.object_id != this.data.id) return;
		i = 0;
		while( (i < this.data.tasks.length) &&  (this.data.tasks[i].id != taskData.id) ) i++;
		if(i < this.data.tasks.length) this.data.tasks.splice(i, 1);
		this.updateTasksMenu();
	}
	,getFormValues: function(){
		if(!Ext.isDefined(this.data.gridData)) this.data.gridData = {};
		this.data.gridData.values = {};
		this.grid.readValues(); // grid will reset the this.data.gridData array to only its values, so we read other values after it will do its data read
		/* reading values from top fieldSet */
		if(Ext.isEmpty(this.data.tags)) this.data.tags = {};
		if(Ext.isDefined(this.topFieldSet))
			this.topFieldSet.items.each(function(i){
				if(( i.name == 'tags' ) || (i.name == 'user_tags' )) this.data.tags[i.tag_level] = i.getValue();
				else{
					this.data.gridData.values[i.name] = { info: '', file: '', value: i.getValue()}
					if( (i.isXType(Ext.ux.TitleField)) && (!i.hasCustomValue)) this.data.gridData.values[i.name].value = '';
				}
			}, this);
		/* reading values from tabPanel */
		if(this.tabPanel) this.tabPanel.items.each(function(i){ 
				if(i.isTemplateField) 
					this.data.gridData.values[i.name] = { value: i.getValue(), info: '', file: ''}
			}, this)

		this.data.pfu = Ext.isEmpty(this.data.pfu) ? App.loginData.id : null;
		this.data.violations = [];
		this.violationsStore.each(function(r){this.data.violations.push(r.data)}, this);
		this.data.associatedViolations = [];
		this.associatedViolationsStore.each(function(r){this.data.associatedViolations.push(r.data)}, this);
		this.data.associatedDecisions = [];
		this.associatedDecisionsStore.each(function(r){this.data.associatedDecisions.push(r.data)}, this);
		this.data.associatedComplaints = [];
		cs = null;
		this.associatedComplaintsStore.each(function(r){ 
			if(r.get('header_row') == 1) cs = r.get('complaint_satisfaction');
			else{
				r.set('complaint_satisfaction', cs);
				this.data.associatedComplaints.push(r.data)
			}
		}, this);
	}
	,getFileProperties: function(fileId){
		// return false or file properties if possible
		if((!this.filesGrid) || isNaN(fileId)) return false;
		fielId = parseInt(fileId);
		fs = this.filesGrid.getStore();
		ri = fs.findBy( function(r){ return (r.get('id') == fileId) }, this);
		if(ri < 0) return false;
		return fs.getAt(ri).data;
	}
	,onFileUploaded: function(data){ 
		if(data.object_id != this.data.id) return; 
	}
	,onFilesDeleted: function(fileIds){
		st = this.grid.getStore();
		if(st) st.each(function(r){
			if(fileIds.indexOf(r.get('files')) >=0 ){
				r.set('files', null);
				this.fireEvent('change');
			}
		}, this)
	}
	,getIconClass: function(){
		if(Ext.isEmpty(this.data.template_id)) return;
		idx = CB.DB.templates.findExact('id', this.data.template_id);
		if(idx < 0) return;
		return CB.DB.templates.getAt(idx).get('iconCls');
	}
	,showDecisionsAssociationPanel: function(focusTab){
		if(!this.decisionsAssociationPanel){
			this.decisionsAssociationPanel = new CB.ObjectsDecisionsAssociationPanel({
				data:{ id: this.data.id }
				,store: this.associatedDecisionsStore
				,listeners:{ beforeclose: {scope: this, fn: this.onBeforeCloseObjectsPanel} }
			});
		}
		if(Ext.isEmpty(this.tabPanel.findByType(CB.ObjectsDecisionsAssociationPanel))) this.tabPanel.add(this.decisionsAssociationPanel);
		if(focusTab !== 0) this.tabPanel.setActiveTab(this.decisionsAssociationPanel);
	}
	,showViolationsAssociationPanel: function(focusTab){
		if(!this.violationsAssociationPanel){
			this.violationsAssociationPanel = new CB.ObjectsViolationsAssociationPanel({
				data:{ id: this.data.id }
				,store: this.associatedViolationsStore
				,listeners:{ beforeclose: {scope: this, fn: this.onBeforeCloseObjectsPanel} }
			});
		}
		if(Ext.isEmpty(this.tabPanel.findByType(CB.ObjectsViolationsAssociationPanel))) this.tabPanel.add(this.decisionsAssociationPanel);
		if(focusTab !== 0) this.tabPanel.setActiveTab(this.decisionsAssociationPanel);
	}
	,showViolationsEditPanel: function(focusTab){
		if(!this.violationsEditPanel){
			this.violationsEditPanel = new CB.ObjectsViolationsEditPanel({
				data:{ id: this.data.id }
				,store: this.violationsStore
				,listeners:{ beforeclose: {scope: this, fn: this.onBeforeCloseObjectsPanel} }
			});
		}
		if(Ext.isEmpty(this.tabPanel.findByType(CB.ObjectsViolationsEditPanel))) this.tabPanel.add(this.violationsEditPanel);
		if(focusTab !== 0) this.tabPanel.setActiveTab(this.violationsEditPanel);
	}
	,showComplaintsEditPanel: function(focusTab){
		if(!this.complaintsPanel){
			this.complaintsPanel = new CB.ObjectsComplaintsEditPanel({
				data:{id: this.data.id}
				,store: this.associatedComplaintsStore
				,listeners:{ beforeclose: {scope: this, fn: this.onBeforeCloseObjectsPanel} }
			})
		}
		if(Ext.isEmpty(this.tabPanel.findByType(CB.ObjectsComplaintsEditPanel))) this.tabPanel.add(this.complaintsPanel);
		if(focusTab !== 0) this.tabPanel.setActiveTab(this.complaintsPanel);
	}
	,showAssociatedAppealsPanel: function(focusTab){
		if(!this.appealsPanel){
			this.appealsPanel = new CB.ObjectsAssociatedAppealsPanel({
				data:{id: this.data.id}
				,store: this.associatedAppealsStore
				,listeners:{ beforeclose: {scope: this, fn: this.onBeforeCloseObjectsPanel} }
			})
		}
		if(Ext.isEmpty(this.tabPanel.findByType(CB.ObjectsAssociatedAppealsPanel))) this.tabPanel.add(this.appealsPanel);
		if(focusTab !== 0) this.tabPanel.setActiveTab(this.appealsPanel);
	}
	,onBeforeCloseObjectsPanel: function(p){
		p.hide();
		this.tabPanel.remove(p, false);
		return false;
	}
	,onUploadClick: function(b, e) { 
		this.fireEvent('fileupload', {pid: this.data.id, uploadType: 'single'}, e) 
	}
	,onPasteClick: function(b, e) { 
		App.clipboard.paste(this.data.id, null, this.onPasteProcess, this);
	}
	,onPasteProcess: function(pids){
		this.childsPanel.reload();
		this.filesPanel.reload();
		this.tasksPanel.reload();
	}

	,onObjectSaved: function(f, a){
		if(!Ext.isEmpty(this.interceptorArguments)){
			this.interceptorArguments[0].handler.call(this, this.interceptorArguments[0], this.interceptorArguments[1])
			delete this.interceptorArguments;
		}
		App.fireEvent('objectchanged', this);
		this.onObjectChanged();
	}
	,onObjectChanged: function(){
		this.actions.save.setDisabled(!this._isDirty && !isNaN(this.data.id));
		this.actions['delete'].setDisabled(isNaN(this.data.id))
		this.actions.paste.setDisabled(App.clipboard.isEmpty());
	}
	,onFocusContactField: function(editor){
		if( Ext.isDefined(editor.dependency) || Ext.isEmpty(editor.pid)) return;
		f = editor.name.split('_');
		editor.pidValue = this.getCurrentFieldValue(editor.pid, f[1]);
	}
	,updateDependentFields: function(fn, newValue){
		pid = fn.split('_')[0].substr(1);
		if(Ext.isDefined(this.topFieldSet)){
			this.templateStore.each(function(r){
				if((r.get('cfg').showIn == 'top') && Ext.isDefined(r.get('cfg').dependency) && (r.get('pid') == pid) ){
					c = this.topFieldSet.find('name', 'f'+r.get('id')+'_0');
					if(!Ext.isEmpty(c)){
						c = c[0]
						c.setDisabled(Ext.isEmpty(newValue) || (!Ext.isEmpty(r.get('cfg').dependency.pidValues) && !setsHaveIntersection( r.get('cfg').dependency.pidValues, newValue) ) );
						c.data.record = r;
						c.data.pidValue = newValue;
						if(c.updateStore) c.updateStore(c);
						delete c.lastQuery;
					}
				}
			}, this)
		}
	}
	,onPathClick: function(){
	 	App.locateObject( this.data.id, this.data.path );
	 }
})

Ext.reg('CBObjects', CB.Objects); // register xtype													

CB.ObjectsPropertiesPanel = Ext.extend(Ext.Panel, {
	border: false
	,hideBorders: true
	,autoHeight: true
	,bodyStyle: 'background-color: #F4F4F4'
	,initComponent: function(){
		Ext.apply(this, {
			tpl: new Ext.XTemplate(
				'<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Properties+'</h3>'
				,'<table class="item-props">'
				,'{[ Ext.isEmpty(values.name) ? "" : \'<tr><td class="k">'+L.Name+'</td><td>\'+values.name+\'</td></tr>\']}'
				,'<tbody><tr><td class="k">'+L.Path+'</td><td><a class="path" href="#">{pathtext}</a></td></tr>'
				,'{[ Ext.isEmpty(values.size) ? "" : \'<tr><td class="k">'+L.Size+'</td><td>\'+App.customRenderers.filesize(values.size)+\'</td></tr>\']}'
				,'<tr><td class="k">'+L.Created+'</td><td>{[ CB.DB.usersStore.getName(values.cid) ]}<br><span class="dttm" title="Friday, December 14, 2012 at 11:26">{[ Ext.isEmpty(values.cdate) ? "" : values.cdate.format(App.dateFormat) ]}</span></td></tr>'
				,'<tr><td class="k">'+L.Modified+'</td><td>{[ CB.DB.usersStore.getName(values.uid) ]}<br><span class="dttm" title="Friday, December 14, 2012 at 11:26">{[ Ext.isEmpty(values.udate) ? "" : values.udate.format(App.dateFormat) ]}</span></td></tr>'
				,'</tbody></table>'
				,{compiled: true}
			)
			,data: []
			,listeners: {
				scope: this
				,afterlayout: this.onAfterlayout 
				,afterrender: this.onAfterlayout 
			}
		})
		CB.ObjectsPropertiesPanel.superclass.initComponent.apply(this, arguments);
		this.addEvents('pathclick');
		
		this._update= this.update;
		this.update = function(data){
			this._update(data);
			this.onAfterlayout();
		}
	}
	,onAfterlayout: function(){
		p = this.getEl().query('a.path');
		if(Ext.isEmpty(p)) return;
		p = Ext.get(p[0]);
		p.un('click', this.onPathClick, this);
		p.on('click', this.onPathClick, this);

	}
	,onPathClick: function(){
	 	this.fireEvent('pathclick');
	}
})

CB.ActionChildsPanel = Ext.extend(Ext.Panel, {
	border: false
	,hideBorders: true
	,autoHeight: true
	,bodyStyle: 'background-color: #F4F4F4'
	,initComponent: function(){
		Ext.apply(this, {
			tpl: new Ext.XTemplate(
				'<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Actions+'</h3>'
				,'<ul class="action-list"><tpl for=".">'
				,'<li><a href="#" nid="{nid}" class="dIB lh16 icon-padding {iconCls}">{name}</a></li>'
				,'</tpl></ul>'
				,{compiled: true}
			)
			,data: []
			,listeners: {
				scope: this
				,afterlayout: this.attachListeners 
				,afterrender: this.attachListeners
				,beforedestroy: function(){
					App.mainViewPort.un('objectsdeleted', this.onObjectsChange, this)
					App.un('objectchanged', this.onObjectsChange, this);
				}
			}
		})
		CB.ActionChildsPanel.superclass.initComponent.apply(this, arguments);
		this._update= this.update;
		this.update = function(data){
			this._update(data);
			this.attachListeners();
		}

		App.mainViewPort.on('objectsdeleted', this.onObjectsChange, this);
		App.on('objectchanged', this.onObjectsChange, this);
	}
	,onObjectsChange: function(){
		this.reload();
	}
	,attachListeners: function(){
		p = this.getEl().query('a');
		if(Ext.isEmpty(p)) return;
		for (var i = 0; i < p.length; i++) {
			el = Ext.get(p[i]);
			el.un('click', this.onItemClick, this);
			el.on('click', this.onItemClick, this);
		};
	}
	,getCaseObjectId: function(){
		p = this.findParentByType(CB.Objects);
		if(Ext.isEmpty(p)) return;
		id = p.data.id;
		if(isNaN(id)) return;
		return id;
	}
	,reload: function(){
		if(this.rendered) this.update([]); else this.data = [];
		id = this.getCaseObjectId();
		if(Ext.isEmpty(id)) return;
		params = {pid: id
			,template_types: 'object'
			,folders: false
			,sort: 'udate'
			,dir: 'desc'
		}
		p = this.findParentByType(CB.Objects);
		if(!Ext.isEmpty(p) && !Ext.isEmpty(p.data.cfg) && !Ext.isEmpty(p.data.cfg.templates) )  params.templates = p.data.cfg.templates;
		CB_BrowserView.getChildren(params, this.processLoad, this)
	}
	,processLoad: function(r, e){
		if(r.success !== true) return;
		for (var i = 0; i < r.data.length; i++)
			r.data[i].iconCls = getItemIcon(r.data[i]);
		this.update(r.data);
		this.setVisible(r.data.length > 0);
	}
	,onItemClick: function(ev, el){
		if(Ext.isEmpty(el) || Ext.isEmpty(el.attributes['nid']) || Ext.isEmpty(el.attributes['nid'].value)) return;
		App.mainViewPort.openObject({ id: el.attributes['nid'].value }, ev);	 	
	}
})