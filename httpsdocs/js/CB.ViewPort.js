Ext.namespace('CB');

CB.ViewPort = Ext.extend(Ext.Viewport, {
	layout: 'border'
	,hideBorders: true
	,initComponent: function(){
		App.mainToolBar = new Ext.Toolbar({
				region: 'north'
				,style:'background: #fff; border: 0'
				,height: 34
				,items: [
					{xtype: 'tbtext', html: '<img src="/css/i/casebox-logo-small.png" style="padding: 2px"/>', height: 30}
					,'->'
					,{text: ' ', iconCls: App.loginData.iconCls, menu: [], name: 'userMenu' }
				]
		});

		App.mainTabPanel = new Ext.TabPanel({
			tabWidth: 205
			,minTabWidth: 100
			,enableTabScroll: true
			,resizeTabs: true
			,activeTab: 0
			,region: 'center'
			,plain: true
			,bodyStyle: 'background-color: #FFF'
			,headerCfg: {cls: 'mainTabPanel'}
			,hideBorders: true
			,listeners: {
				tabchange: function(tp, p){
					tp.syncSize();
					p.syncSize();
				}
			}
		});

		App.mainAccordion = new Ext.Panel({
			region: 'west'
			,layout: 'accordion'
			,collapsible: false
			,width: 250
			,split: true
			,collapseMode: 'mini'
			,animCollapse: false
			,fill: true
			,plain: true
			,style: 'border-top: 1px solid #dfe8f6'
			,bodyCssClass: 'main-nav'
			,defaults: {
				border: false
				,hideBorders: false
				,bodyStyle: 'background-color: #F4F4F4'
				,lazyrender: true
				,autoScroll: true
			}
			,layoutConfig: {
				hideCollapseTool: true
				,titleCollapse: true
			}
			,stateful: true
			,stateId: 'mAc'
			,stateEvents: ['resize']
			,getState: function(){ 
				rez = {collapsed: this.collapsed}
				if(this.getWidth() > 0) rez.width = this.getWidth();
				return rez;
			}
		});

		App.mainStatusBar = new Ext.Toolbar({
				region: 'south'
				,cls: 'x-panel-gray'
				,style:'border-top: 1px solid #aeaeae'
				,height: 25
				,items: [
					{xtype: 'tbspacer', width: 610}
					// ,{text: 'Some text', iconCls: App.loginData.iconCls, menu: [], name: 'userMenu' }
					,'->'
					,{xtype: 'uploadwindowbutton'}
					,{xtype: 'tbspacer', width: 20}
				]
		});
		Ext.apply(this, {
			items: [ App.mainToolBar
				,App.mainTabPanel
				,App.mainAccordion
				,App.mainStatusBar
			]
			,listeners: {
				scope: this
				,login: this.onLogin 
				,casecreate: this.onCreateCase
				,fileopen: this.onFileOpen
				,fileupload: this.onFileUpload
				,filedownload: this.onFilesDownload
				,opencase: this.openCase
				,openobject: this.openObject
				//,objectupdated: this.onObjectUpdated
				,deleteobject: this.onDeleteObject
				,opencalendar: this.openCalendar
				,favoritetoggle: this.toggleFavorite
				,taskcreate: this.onTaskCreate
				,taskedit: this.onTaskEdit
				,useradded: this.onUsersChange
				,userdeleted: this.onUsersChange
				//,taskcreated: function(){}
				
			}
		});
		this.addEvents(
			'login'
			,'favoritetoggle'
			,'favoritetoggled'
			,'taskcreated'
			,'taskupdated'
			,'tasksdeleted'
			,'useradded'
			,'userdeleted'
			,'userupdated'
			,'queryadded'
			,'querydeleted'
			,'objectsdeleted'
			,'objectopened'
		);
		CB.ViewPort.superclass.initComponent.apply(this, arguments);
	}
	,onLogin: function(r){
		/* adding menu items */
		if(App.loginData.manage){
			//App.mainToolBar.insert(1, {xtype: 'button', text: L.NewCase, iconCls: 'icon-briefcase-plus', scope: this, handler: this.onCreateCase })
			//App.mainToolBar.insert(1, '-');
		}
		um = App.mainToolBar.find( 'name', 'userMenu')[0];
		um.setText(App.loginData['l'+App.loginData.language_id]);
		um.setIconClass(App.loginData.iconCls);
		managementItems = [];
		if(App.loginData.manage){//admin
			managementItems.push(
				{text: L.Thesaurus, iconCls: 'icon-application-tree', handler: function(){ App.openUniqueTabbedWidget('CBSystemManagementWindow') }}
				,{text: L.Templates, iconCls: 'icon-documents-stack', handler: function(){ App.openUniqueTabbedWidget('CBTemplatesManagementWindow') }}
			);
		}
		if(App.loginData.manage) managementItems.push('-',{text: L.Users, iconCls: 'icon-users', handler: function(){ App.openUniqueTabbedWidget('CBUsersGroups') }});
		if(App.loginData.admin){
			managementItems.push(
				'-'
				,{text: 'Reload thesaury', iconCls: 'icon-reload', handler: reloadThesauri}
				,{text: 'testing', iconCls: 'icon-bug', handler: App.showTestingWindow}
			);
		}
		if(managementItems.length > 0) App.mainToolBar.insert(2, {text: L.Settings, iconCls: 'icon-gear', hideOnClick: false, menu: managementItems});
		App.mainToolBar.doLayout();

		langs = [];
		CB.DB.languages.each(function(r){langs.push({
			text: r.get('name')
			,xtype: 'menucheckitem'
			,checked: (r.get('id') == App.loginData.language_id)
			,data:{id: r.get('id')}
			,scope: this
			,handler: this.setUserLanguage
			,group: 'language'
		})}, this);
		um.menu.add(
			{text: L.Language, iconCls: 'icon-language', hideOnClick: false, menu: langs}
			,'-'
			,{text: L.UserDetails, iconCls: App.loginData.iconCls, handler: function(){
				w = new CB.UserEditWindow({
					title: App.loginData['l'+App.loginData.language_id]
					,iconCls: App.loginData.iconCls
					,data: {id: App.loginData.id}
					,listeners: {
						scope: this
						,savesuccess: function(f, a){
							if(a.result.interface_params_changed)
								Ext.Msg.confirm(L.InterfaceParamsChanged, L.InterfaceParamsChangedMessage, function(btn){ if(btn == 'yes')  document.location.reload(); }, this)
						}
					}
				});
				w.show();
			}}
			,{text: L.UploadPhoto, iconCls: 'file-gif', handler: function(b, e){
				w = new CB.FileUploadWindow({
					title: L.UploadPhoto
					,iconCls: 'file-gif'
					,fieldName: 'photo'
					,fileOnly: true
					,api: User.uploadPhoto
					,data: {id: App.loginData.id}
				});
				w.show();
			}}
			,{text: L.ChangePassword, iconCls: 'icon-key', handler: function(){
				w = new CB.ChangePasswordWindow({data: {id: App.loginData.id}});
				w.show();
			}}
			,'-'
			,{	text: L.Exit 
				,iconCls: 'icon-exit'
				,handler: this.logout, scope: this
			}
		);
		/* end of adding menu items */

		App.Favorites = new CB.Favorites();
		App.Favorites.load();
		this.populateMainMenu();
		App.openUniqueTabbedWidget('CBDashboard');
	}
	,initCB: function(){
		if(CB.DB.templates.getCount() > 0){
			App.mainViewPort.openDefaultExplorer();
			App.mainTabPanel.setActiveTab(0)
			//App.openUniqueTabbedWidget('CBSecurityPanel', null, { data: {id: 237} })
		}else App.mainViewPort.initCB.defer(500);
	}
	,logout: function(){
		return Ext.Msg.show({
			buttons: Ext.Msg.YESNO
			,title: L.ExitConfirmation
			,msg: L.ExitConfirmationMessage
			,fn: function(btn, text){
				if (btn == 'yes')
					Auth.logout(function(response, e){
						if(response.success === true) window.location.reload();
					});
			}
		});
	}
	,populateMainMenu: function(){
		App.mainAccordion.getEl().mask(L.LoadingData, 'icon-loading');
		User.getMainMenuItems(this.processMainMenuItems, this);
	}
	,processMainMenuItems: function(r, e){
		App.mainAccordion.getEl().unmask();
		activeIndex = 0;
		if(r.success !== true) return;
		if(!Ext.isEmpty(r.tbarItems)){
			/* inserting specified components before userMenu item */
			userMenuItem = App.mainToolBar.find( 'name', 'userMenu')[0];
			// index = App.mainToolBar.items.indexOf(userMenuItem);
			index = 2;
			for (var i = 0; i < r.tbarItems.length; i++) {
				if(!Ext.isEmpty(r.tbarItems[i].link)) r.tbarItems[i].listeners = {scope: this, click: this.onAccordionLinkClick }
				App.mainToolBar.insert(index, r.tbarItems[i]);
				index++;
				// App.mainAccordion.add(r.items[i])
			}
			App.mainToolBar.syncSize()
		}

		if(!Ext.isEmpty(r.items))
		for (var i = 0; i < r.items.length; i++) {
			if(!Ext.isEmpty(r.items[i].link)) r.items[i].listeners = {scope: this, beforeexpand: this.onAccordionLinkClick }
			if(r.items[i].active == true) activeIndex = i;
			App.mainAccordion.add(r.items[i])
		}
		App.mainAccordion.getLayout().setActiveItem(activeIndex);
		App.mainAccordion.doLayout()
		trees = App.mainAccordion.findByType(CB.BrowserTree)
		if(!Ext.isEmpty(trees)){
			App.mainTree = trees[0];
			for (var i = 0; i < trees.length; i++) {
				trees[i].getSelectionModel().on('selectionchange', this.onChangeActiveFolder, this)
				trees[i].on('click', this.onTreeNodeClick, this)
				trees[i].on('afterrename', this.onRenameTreeElement, this)
			};
		}
		App.mainViewPort.initCB();
	}
	,onTreeNodeClick: function(node, e){
		if(Ext.isEmpty(node) || Ext.isEmpty(node.getPath)) return;
		if(node.isSelected()) this.onChangeActiveFolder(null, node);
	}
	,onChangeActiveFolder: function(sm, node){
		if(Ext.isEmpty(node) || Ext.isEmpty(node.getPath)) return;
		App.locateObject(null, node.getPath('nid'));
	}
	,onRenameTreeElement: function(tree, r, e){
		node = tree.getSelectionModel().getSelectedNode();
		if(Ext.isEmpty(node) || Ext.isEmpty(node.getPath)) return;
		tab = App.mainTabPanel.getActiveTab();
		if(tab.isXType(CB.FolderView)) tab.onReloadClick();
	}
	,selectGridObject: function(g){
		if(Ext.isEmpty(g) || Ext.isEmpty(App.locateObjectId)) return;
		idx = g.store.findExact('nid', App.locateObjectId);
		if(idx >=0){
			sm = g.getSelectionModel();
			if(sm.hasSelection()) sm.clearSelections()
			sm.selectRow(idx);
			delete App.locateObjectId;
		}
	}
	,onAccordionLinkClick: function(p, animate){
		p = App.openUniqueTabbedWidget(p.link, null, {iconCls: p.iconCls, title: p.title});
		return false; 
	}
	,openCalendar: function(ev){
		if(ev && ev. stopPropagation) ev.stopPropagation();
		App.openUniqueTabbedWidget('CBCalendarPanel');
	}
	,openDefaultExplorer: function(rootId){
		if(Ext.isEmpty(rootId)) rootId = Ext.value( App.mainTree.rootId, '/' );
		if(!App.activateTab(App.mainTabPanel, 'explorer')) App.explorer = App.addTab(App.mainTabPanel, new CB.FolderView({ rootId: rootId, data: {id: 'explorer' }, closable: false }) )
	}
	,openCaseById: function(config){
		c = App.activateTab(App.mainTabPanel, config.params.id, CB.Case);
		if(c){
			if(!Ext.isEmpty(config.selectActionId)) c.grid.selectAction(config.selectActionId);
			return c;
		}
		config.iconCls = 'icon-node-case';
		var pn = new CB.Case(config);
		App.mainTabPanel.add(pn);
		App.mainTabPanel.setActiveTab(pn);
		return pn;
	}
	,openCase: function(config, ev){
		if(ev && ev.stopPropagation) ev.stopPropagation();
		if(Ext.isEmpty(config) || Ext.isPrimitive(config) || Ext.isEmpty(config.params.id)){
			if(Ext.isEmpty(v)) return;
			Cases.getCaseId({nr: v}, function(r, e){
				if(r.success != true) return Ext.Msg.alert(L.Error, L.CaseNotFound);
				config = {params: {id: r.data.id}};
				this.openCaseById(config)
			}, this);
		}else return this.openCaseById(config);
	}
	,openObject: function(data, e){
		if(e){
			e.stopEvent();
			if(e.processed === true) return;
		}

		if(App.activateTab(App.mainTabPanel, data.id, CB.Objects)) return true;

		o = Ext.create({ data: data, iconCls: 'icon-loading', title: L.LoadingData + ' ...' }, 'CBObjects');/*, hideDeleteButton: (data.template_id == 1)/**/ 
		this.fireEvent('objectopened', o);
		return App.addTab(App.mainTabPanel, o);
	}
	,onFileOpen: function(data, e){
		if(e) e.stopEvent();
		
		if(App.activateTab(App.mainTabPanel, data.id)) return true;

		o = Ext.create({ data: data, iconCls: 'icon-loading', title: L.LoadingData + ' ...' }, 'CBFileWindow');/*, hideDeleteButton: (data.template_id == 1)/**/ 
		return App.addTab(App.mainTabPanel, o);
	}
	,onCreateCase: function(b, e){ 
		if(e) e.stopPropagation();
		w = new CB.AddCaseForm({
			modal: true
			,ownerCt: this
			,title: L.NewCase
			,data:{ pid: b.data.pid, callback: this.onCreateCaseCallback }
		});
		w.show();
		return w;
	}
	,onCreateCaseCallback: function(params){
		Cases.create(params, function(r, e){
			if(r.success !== true) return;
			App.mainViewPort.fireEvent('savesuccess', r, e); // maybe case created
		}, this)
	}
	,search: function(query, savedQueryId){
		idx = App.findTab(App.mainTabPanel, 'search');
		if(idx > -1){
			p = App.mainTabPanel.items.itemAt(idx);
			App.mainTabPanel.setActiveTab(idx);
		}else{
			p = new CB.Search({data: {id: 'search'}});
			App.addTab(App.mainTabPanel, p)
		}
		if(!Ext.isEmpty(savedQueryId)) p.openSavedQuery(savedQueryId); 
			else p.searchText(query);
	}
	,setUserLanguage: function(b, e){
		if(b.data.id == App.loginData.language_id) return;
		Ext.Msg.confirm(L.LanguageChange, L.LanguageChangeMessage, function(pb){
			if(pb == 'yes') Auth.setLanguage(b.data.id, this.processSetUserLanguage, this);
			if(b.ownerCt) b.ownerCt.items.each(function(i){ i.setChecked(i.data.id == App.loginData.language_id)}, this);
		}, this)
	}
	,processSetUserLanguage: function(r, e){
		if(r.success == true) document.location.reload();
		else Ext.Msg.Alert(L.Error, L.ErrorOccured);
	}
	,toggleFavorite: function(p){
		Browser.toggleFavorite(p, this.processToggleFavorite, this);
	}
	,processToggleFavorite: function(r, e){
		this.fireEvent('favoritetoggled', r, e);
	}
	,onTaskCreate: function(p, ev){
		if(Ext.isEmpty(p)) p ={ data: {} };
		
		Ext.apply(p, {
			admin: true
			,autoclose: 1
			,privacy: 0
			,reminds: "1|10|1"
			,responsible_user_ids: App.loginData.id
		});		
		if(Ext.isEmpty(p.title)) p.title = L.AddTask;
		if(Ext.isEmpty(p.usersStore)) p.usersStore = CB.DB.usersStore;
		if(Ext.isEmpty(p.tasksStore)) p.tasksStore = new Ext.data.DirectStore( Ext.applyIf({
			directFn: Tasks.getUserTasks
			,autoDestroy: true
		}, CB.DB.tasksStoreConfig));
		dw = new CB.Tasks(p);
		return dw.show();
	}
	,onTaskEdit: function(p, ev){//task_id, object_id, object_title, title
		if(Ext.isEmpty(p.title)) p.title = L.EditTask;
		if(Ext.isEmpty(p.usersStore)) p.usersStore = CB.DB.usersStore;
		if(Ext.isEmpty(p.tasksStore)){
				p.tasksStore = new Ext.data.DirectStore( Ext.applyIf({
				directFn: Tasks.getAssociableTasks
				,baseParams: {task_id: p.data.id}
			}, CB.DB.tasksStoreConfig));
			p.listeners = {beforedestroy: function(w){w.tasksStore.destroy();}}
		}

		dw = new CB.Tasks(p);
		dw.show();
	}
	,onUsersChange: function(){
		CB.DB.usersStore.reload();
	}
	,onDeleteObject: function(data){
		Ext.Msg.confirm(L.DeleteConfirmation, L.DeleteConfirmationMessage + ' "' + data.title+'"?', function(btn){ if(btn == 'yes')  Browser['delete'](data.id, this.onProcessObjectsDeleted, this); }, this)

	}
	,onProcessObjectsDeleted: function(r, e){
		if(r.success !== true) return;
		if(!Ext.isEmpty(r.ids)) this.fireEvent('objectsdeleted', r.ids, e);
	}
	,onFileUpload: function(data, e){
		if(e) e.stopPropagation();
		
		w = App.getFileUploadWindow({data: data });
		w.on('submitsuccess', this.onFileUploaded, this);
		w.on('hide', function(w){ w.un('submitsuccess', this.onFileUploaded, this) }, this)
		w.show();/**/
	}
	,onFileUploaded: function(w, data){
		this.fireEvent('fileuploaded', {data: data});
	}
	,onFilesDownload: function(ids, zipped, e){
		if(e) e.stopPropagation();
		if(zipped !== true){
			if(!Ext.isArray(ids)) ids = String(ids).split(',');
			Ext.each(ids, function(id){if(isNaN(id)) return false; App.downloadFile(id);}, this);
		}else{
			if(Ext.isArray(ids)) ids = ids.join(',');
			App.downloadFile(ids, true);
		}
	}
	,toggleWestRegion: function(visible){
		App.mainAccordion.setVisible(visible === true);
		App.mainViewPort.syncSize();
	}
	,openPath: function(path, id){
		App.locateObject(id, path);
	}

})
