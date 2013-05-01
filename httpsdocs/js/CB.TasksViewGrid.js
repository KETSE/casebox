Ext.namespace('CB');

CB.TasksViewGrid = Ext.extend(Ext.Panel,{
	layout: 'border'
   	,tbarCssClass: 'x-panel-white'
	,hideBorders: true
	,folderProperties: {}
	,params: {descendants: false}
	
	,initComponent: function(){
		
		this.actions = {
			open: new Ext.Action({
				text: L.Open
				,iconCls: 'icon-folder-open'
				,iconAlign:'top'
				,iconCls: 'icon32-open'
				,scale: 'large'
				,disabled: true
				,scope: this
				,handler: this.onOpenClick
			})
			,openItemLocation: new Ext.Action({
				text: L.OpenItemLocation
				,iconAlign:'top'
				,disabled: true
				,scope: this
				,handler: this.onOpenItemLocationClick
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

			,'delete': new Ext.Action({
				text: L.Delete
				,iconAlign:'top'
				,iconCls: 'icon32-del'
				,scale: 'large'
				,disabled: true
				,scope: this
				,handler: this.onDeleteClick
			})
			,reload: new Ext.Action({
				text: L.Reload
				,scope: this
				,handler: this.onReloadClick
			})

			,createTask: new Ext.Action({
				text: L.NewTask
				,iconCls: 'icon32-task-new'
				,iconAlign:'top'
				,scale: 'large'
				,disabled: true
				,scope: this
				,handler: this.onCreateTaskClick
			})
			,completeTask: new Ext.Action({
				text: L.Complete
				,iconCls: 'icon32-task-complete'
				,iconAlign:'top'
				,scale: 'large'
				,disabled: true
				,scope: this
				,handler: this.onCompleteTaskClick
			})
			,createEvent: new Ext.Action({
				text: L.NewEvent
				,iconCls: 'icon32-event-new'
				,iconAlign:'top'
				,scale: 'large'
				,disabled: true
				,scope: this
				,handler: this.onCreateEventClick
			})
		}

		this.store = new Ext.data.DirectStore({
			autoLoad: false
			,autoDestroy: true
			,remoteSort: true
			,baseParams: {types: [6], facets: 'tasks'}//7
			,proxy: new  Ext.data.DirectProxy({
				paramsAsHash: true
				,directFn: BrowserView.getChildren
				,listeners:{
					scope: this
					,load: this.onProxyLoad
				}
			})
			,reader: new Ext.data.JsonReader({
				successProperty: 'success'
				,idProperty: 'nid'
				,root: 'data'
				,messageProperty: 'msg'
			},[ 	{name: 'nid', type: 'int'}
				, {name: 'pid', type: 'int'}
				, {name: 'system', type: 'int'}
				, {name: 'type', type: 'int'}
				, {name: 'subtype', type: 'int'}
				, {name: 'template_id', type: 'int'}
				, 'name'
				, 'hl'
				, 'path'
				, 'user_ids'
				, 'iconCls'
				, {name: 'date', type: 'date'}
				, {name: 'date_end', type: 'date'}
				, {name: 'category_id', type: 'int'}
				, {name: 'importance', type: 'int'}
				, {name: 'status', type: 'int'}
				, {name: 'cid', type: 'int'}
				, {name: 'completed', type: 'date'}
				, {name: 'cdate', type: 'date'}
				, {name: 'udate', type: 'date'}
				, 'case'
			]
			)
			,listeners: {
				scope: this
				,beforeload: function(store, options) { 
					this.params = this.requestedParams;
					Ext.apply(store.baseParams, this.requestedParams);
					options = store.baseParams
				}
				,load: this.onStoreLoad
			}
		})
		this.grid = new Ext.grid.EditorGridPanel({
			loadMask: true
			,region: 'center'
                   	,tbarCssClass: 'x-panel-white'
			,cls: 'task-grid'
			,store: this.store
			,loadMask: true
			,colModel: new Ext.grid.ColumnModel({
				defaults: {
				    width: 120,
				    sortable: true
				},
				columns: [
				    {header: L.Name, width: 500, dataIndex: 'name', renderer: function(v, m, r, ri, ci, s){
				    		m.css = 'icon-grid-column-top '+ r.get('iconCls');
				    		m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace('"',"&quot;")+'"';
				    		v = '<span class="n"><b>' + Ext.value(r.get('hl'), v ) + '</b></span>';
						info = [];
						if(!Ext.isEmpty(r.get('path'))) info.push(r.get('path'));
				    		if(!Ext.isEmpty(r.get('user_ids'))) info.push(CB.DB.usersStore.getName(r.get('user_ids') ) )
				    		
				    		if(!Ext.isEmpty(info)) v += '<div class="task-info">'+info.join('<br />') + '</div>';
				    		return '<div class="letter">'+v+'</div>';
				    	}
				    }
				    ,{header: L.Path, width: 150, dataIndex: 'path', hidden: true, renderer: function(v, m, r, ri, ci, s){
				    		m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace('"',"&quot;")+'"';
				    		return v;
						}
					}
				    ,{header: L.Project, width: 150, dataIndex: 'case', hidden: true, renderer: function(v, m, r, ri, ci, s){
				    		m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace('"',"&quot;")+'"';
				    		return v;
						}
					}
				    ,{header: L.Owner, width: 150, dataIndex: 'cid', renderer: function(v, m, r, ri, ci, s){ return CB.DB.usersStore.getName( v ); } }
				    ,{header: L.TaskAssigned, width: 200, dataIndex: 'user_ids', sortable: false, renderer: function(v, m, r, ri, ci, s){ return CB.DB.usersStore.getName( v ); } }
				    ,{ header: L.Category, width: 100, dataIndex: 'category_id', renderer: function(v, m, r, ri, ci, s){ 
				    		if(Ext.isEmpty(v)) return '';
				    		m.css = 'icon-grid-column-top '+CB.DB.thesauri.getIcon( v );
				    		return CB.DB.thesauri.getName( v ); 
				    	}
				     }
				    ,{ header: L.Start, width: 120, dataIndex: 'date', format: App.dateFormat+' '+App.timeFormat, renderer: App.customRenderers.datetime}
				    ,{ header: L.Due, width: 120, dataIndex: 'date_end', format: App.dateFormat+' '+App.timeFormat, renderer: App.customRenderers.datetime}
				    ,{ header: L.Importance, width: 100, dataIndex: 'importance', renderer: App.customRenderers.taskImportance}
				    ,{ header: L.Status, width: 100, dataIndex: 'status', renderer: App.customRenderers.taskStatus }
				    ,{ header: L.CompletedDate, hidden:true, width: 120, dataIndex: 'completed', xtype: 'datecolumn', format: App.dateFormat+' '+App.timeFormat}
				    ,{ header: L.CreatedDate, hidden:true, width: 120, dataIndex: 'cdate', xtype: 'datecolumn', format: App.dateFormat+' '+App.timeFormat}
				    ,{ header: L.UpdatedDate, hidden:true, width: 120, dataIndex: 'udate', xtype: 'datecolumn', format: App.dateFormat+' '+App.timeFormat}
				]
			})
			,viewConfig: {
				forceFit: false
				,enableRowBody: true
				,getRowClass: function(r, rowIndex, rp, ds){
					rp.body = '';
					//if(r && (String(r.get('name')).indexOf('class="hl"') < 0) ){
						if(!Ext.isEmpty(r.get('content'))) rp.body += r.get('content');
					//}
					
					if(Ext.isEmpty(rp.body)) return '';
					return 'hasBody';
				}
			}
			,sm: new Ext.grid.RowSelectionModel({
				singleSelect: false
				,listeners: {
					scope: this
					,selectionchange: this.onSelectionChange
				}
			})
			,listeners:{
				scope: this
				,rowdblclick : function( grid, rowIndex, e ) {
					grid.getSelectionModel().clearSelections(true);
					grid.getSelectionModel().selectRow(rowIndex);
					this.onOpenClick(grid, e);
				}
				,contextmenu: this.onContextMenu
				,rowcontextmenu: this.onRowContextMenu
				,beforedestroy: this.onBeforeDestroy
				,mousedown: function(e){
					if(e.button == 2){ //rightclick
						/* lock selection if rightclicking on a selected row. Unlock should be called after corresponding actions (usually called with defer).*/
						sm = this.grid.getSelectionModel();
						s = sm.getSelections();
						target = e.getTarget('.x-grid3-row');
						for (var i = 0; i < s.length; i++) {
							el = this.grid.getView().getRow(this.grid.store.indexOf(s[i]));
							if( el == target ){
								sm.lock();
								return;
							}
						}
					}
				}
			}
			,keys: [{
				key: Ext.EventObject.DOWN //down arrow (select forst row in the greed if no row already selected)  - does not work
				,ctrl: false
				,shift: false
				,stopEvent: true
				,fn: this.onDownClick
				,scope: this
				},{
					key: [10,13]
					,alt: false
					,ctrl: false
					,shift: false
					,stopEvent: true
					,fn: this.onOpenClick
					,scope: this
				},{
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
					,ctrl: true
					,shift: false
					,stopEvent: true
					,fn: this.onPasteClick
					,scope: this
				},{
					key: Ext.EventObject.DELETE
					,alt: false
					,ctrl: false
					,stopEvent: true
					,fn: this.onDeleteClick
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
				}]
			,bbar: new Ext.PagingToolbar({
				store: this.store
				,displayInfo: true
				,pageSize: 50
				,hidden: true
			})

			,statefull: true
			,stateId: 'tvg'//tasks view grid
		});
		this.previewPanel = new CB.PreviewPanel({bodyStyle:'padding: 10px'});
		this.filterButton = new Ext.Button({
             		text: L.Filter
             		,enableToggle: true
             		,iconCls: 'icon32-filter'
             		,activeIconCls: 'icon32-filter-on'
             		,iconAlign:'top'
             		,scale: 'large'
             		,toggleGroup: 'rightBtn'
             		,itemIndex: 1
             		,scope: this
             		,toggleHandler: this.onEastPanelButtonClick
             	})
                this.filtersPanel = new CB.FilterPanel({
                	bindButton: this.filterButton
                	,listeners:{
                		scope: this
                		,change: this.onFiltersChange
                	}
                });
		
		this.eastPanel = new Ext.Panel({
			region: 'east'
			,width: 300
			,split: true
			,hidden: true
			,animCollapse: false
			,border: false
			,layout: 'card'
			,activeItem: 0
			,hideBorders: true
		        ,statefull: true
		        ,stateId: 'tvgEP' //taskview east panel
		        ,stateEvents:['resize']
                      	,items: [ this.previewPanel, this.filtersPanel ]
                })

		Ext.apply(this, {
			tbar: [
                             	this.actions.open
                             	,this.actions['delete']
                             	,'-'
                             	,this.actions.createTask
                             	,'-'
                             	,this.actions.completeTask
                             	,'->'
                             	,{
                             		text: L.Preview
                             		,enableToggle: true
                             		,iconCls: 'icon32-preview'
                             		,iconAlign:'top'
                             		,scale: 'large'
                             		,toggleGroup: 'rightBtn'
                             		,itemIndex: 0
                             		,scope: this
                             		,toggleHandler: this.onEastPanelButtonClick
                             	}
                             	,this.filterButton
                        ]
			,items: [this.grid, this.eastPanel]
		})
		CB.TasksViewGrid.superclass.initComponent.apply(this, arguments);
		
		this.addEvents(
				'selectionchange'
				,'taskcreate'
				,'taskedit'
				,'eventcreate'
				,'eventedit'
				,'changeparams'
				,'viewloaded'
				,'showdescendants'
				,'changeview'
		);
		this.enableBubble([
			'taskcreate'
			,'taskedit'
			,'eventcreate'
			,'eventedit'
			,'changeparams'
			,'viewloaded'
			,'showdescendants'
			,'changeview'
		]);

		App.clipboard.on('pasted', this.onClipboardAction, this);
		App.mainViewPort.on('savesuccess', this.onObjectsSaved, this);
		App.mainViewPort.on('taskupdated', this.onObjectsSaved, this);
		App.mainViewPort.on('taskcreated', this.onObjectsSaved, this);
		App.mainViewPort.on('eventcreated', this.onObjectsSaved, this);
		App.mainViewPort.on('eventupdated', this.onObjectsSaved, this);
		App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);

		if(!Ext.isEmpty(this.tree))
			this.tree.getSelectionModel().on('selectionchange', this.onTreeSelectionChange, this);
	}
	,onBeforeDestroy: function(p){
		App.clipboard.un('pasted', this.onClipboardAction, this);
		App.mainViewPort.un('savesuccess', this.onObjectsSaved, this);
		App.mainViewPort.un('taskupdated', this.onObjectsSaved, this);
		App.mainViewPort.un('taskcreated', this.onObjectsSaved, this);
		App.mainViewPort.un('eventcreated', this.onObjectsSaved, this);
		App.mainViewPort.un('eventupdated', this.onObjectsSaved, this);
		App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
	}
	,onClipboardAction: function(pids){
		if(pids.indexOf(this.folderProperties.id) >=0 ) this.onReloadClick();
	}
	,onSelectionChange: function(sm) {
		id = null;
		if(!sm.hasSelection()){
			this.actions.open.setDisabled(true);
			this.actions.openItemLocation.setDisabled(true);
			this.actions.cut.setDisabled(true);
			this.actions.copy.setDisabled(true);
			this.actions.paste.setDisabled(true);
			this.actions['delete'].setDisabled(true);
			this.actions.completeTask.setDisabled(true);
		}else{
			row = sm.getSelected();
			id = row.get('nid');
			this.actions.open.setDisabled(false);
			this.actions['delete'].setDisabled(row.get('system') == 1);
			
			canOpenLocation = (this.params.descendants || !Ext.isEmpty(this.grid.store.baseParams.query) );
			this.actions.openItemLocation.setDisabled(!canOpenLocation);

			canCopy = (row.get('system') == 0);
			this.actions.cut.setDisabled(!canCopy);
			this.actions.copy.setDisabled(!canCopy);
			
			canDelete = (row.get('type') == 1) && ([0].indexOf(row.get('subtype')) >= 0) || ([2, 3, 4, 5, 6, 7].indexOf(row.get('type'))>=0);
			this.actions['delete'].setDisabled(!canDelete);
			
			u = String(row.get('user_ids')).split(',');
			canComplete = ((row.get('type') == 6) && (u.indexOf(App.loginData.id) >= 0) && (row.get('status') != 3) );
			this.actions.completeTask.setDisabled(!canComplete);
		}

		canPaste = !App.clipboard.isEmpty() 
			&& ( !this.folderProperties.inFavorites || App.clipboard.containShortcutsOnly() ) 
			&& ( ( (this.folderProperties.system == 0) && (this.folderProperties.type != 5) ) 
				|| ( (this.folderProperties.type == 1) && ([2, 7, 9, 10].indexOf(this.folderProperties.subtype) >= 0) ) 
				|| ([3, 4, 6, 7].indexOf(this.folderProperties.type) >= 0 ) 
			   );
		this.actions.paste.setDisabled(!canPaste);
		if(this.previewPanel) this.previewPanel.loadPreview(id);
		r = sm.getSelected();
		data = r ? r.data : null;
		this.fireEvent('selectionchange', sm, data);
	}
	,onContextMenu: function(e) {
		e.stopPropagation()
		e.preventDefault()
		this.onRowContextMenu(this.grid, -1, e);
	}
	,onRowContextMenu: function(grid, rowIndex, e) {
		if(e){
			e.stopPropagation()
			e.preventDefault()
		}
		grid.selModel.selectRow(rowIndex, false);
		row = grid.store.getAt(rowIndex);
		if(Ext.isEmpty(this.contextMenu)){/* create context menu if not aleready created */
			this.contextMenu = new Ext.menu.Menu({
				items: [
				this.actions.open
				,this.actions.openItemLocation
				,'-'
				,{
					text: L.View
					,hideOnClick: false
					,menu: [{
						xtype: 'menucheckitem'
						,text: L.Descendants
						,checked: this.params.descendants
						,scope: this
						,handler: this.onShowDescendantsClick
					}
					]
				}
				,'-'
				,this.actions.cut
				,this.actions.copy
				,this.actions.paste
				,'-'
				,this.actions['delete']
				,'-'
				,this.actions.createTask
				]
			})

		}
		this.contextMenu.items.itemAt(3).menu.items.itemAt(0).setChecked(this.params.descendants);
		this.contextMenu.row = row;
		this.contextMenu.showAt(e.getXY());
		this.grid.getSelectionModel().unlock.defer(500, this.grid.getSelectionModel());
	}
	,setParams: function(params){
		if(Ext.isEmpty(params.path)) params.path = '/';
		Ext.apply(this.grid.getStore().baseParams, Ext.value(params, {}) );
		this.requestedParams = Ext.apply({}, params, this.params);
		this.grid.getBottomToolbar().changePage(1);
	}
	,onProxyLoad: function (proxy, o, options) {
		if(Ext.isEmpty(this.params)) this.params = {}
		this.params.path = this.store.baseParams.path;
		this.fireEvent('viewloaded', proxy, o, options);

		this.folderProperties = o.result.folderProperties
		this.folderProperties.id = parseInt(this.folderProperties.id);
		this.folderProperties.system = parseInt(this.folderProperties.system);
		this.folderProperties.type = parseInt(this.folderProperties.type);
		this.folderProperties.subtype = parseInt(this.folderProperties.subtype);
		this.folderProperties.pathtext = o.result.pathtext;
		canCreate = true; //TODO: review where we can create tasks
		this.actions.createTask.setDisabled(!canCreate); 
		this.actions.createEvent.setDisabled(!canCreate); 

		this.filtersPanel.updateFacets(o.result.facets, options);
	}
	,onStoreLoad: function(store, recs, options) {
		Ext.each(recs, function(r){ r.set('iconCls', getItemIcon(r.data))}, this);
		pt = this.grid.getBottomToolbar();
		pt.setVisible(store.reader.jsonData.total > pt.pageSize); 
		App.mainViewPort.selectGridObject(this.grid);
		this.doLayout();
	}
	,onDownClick: function(key, e) {
		if(this.grid.selModel.hasSelection() || (this.grid.store.getCount() < 1)) return false;
		this.grid.selModel.selectRow(0);
	}
	,onOpenClick: function(b, e) {
		if(!this.grid.selModel.hasSelection()) return;
		row = this.grid.selModel.getSelected();
		if(!App.openObject(row.get('template_id'), row.get('nid'), e) ){
			if(Ext.isEmpty(this.grid.store.baseParams.query) ){
				path = Ext.value(this.params.path, '/').split('/');
				path.push(row.get('nid'));
				this.fireEvent('changeparams', {path: path.join('/')} )
			}else{
				this.fireEvent('changeparams', {path: row.get('nid')} )
			}
		}
	}
	,onOpenItemLocationClick: function(b, e){
		if(this.actions.openItemLocation.isDisabled()) return;
		if(!this.grid.selModel.hasSelection()) return;
		row = this.grid.selModel.getSelected();
		this.fireEvent('changeview', 0, e);
		this.fireEvent('changeparams', {path: row.get('pid'), descendants: false}, e)	
		//App.locateObject(r.data.nid, r.data.pid);
	}
	,onCutClick: function(buttonOrKey, e) {
		if(this.actions.cut.isDisabled()) return;
		this.onCopyClick(buttonOrKey, e)
		App.clipboard.setAction('move');
	}
	,onCopyClick: function(buttonOrKey, e) {
		if(this.actions.copy.isDisabled()) return;
		s = this.grid.selModel.getSelections();
		if(Ext.isEmpty(s)) return;
		rez = [];
		for (var i = 0; i < s.length; i++) {
			rez.push({
				id: s[i].get('nid')
				,name: s[i].get('name')
				,system: s[i].get('system')
				,type: s[i].get('type')
				,subtype: s[i].get('subtype')
				,iconCls: s[i].get('iconCls')
			})
		}
		App.clipboard.set(rez, 'copy');
	}
	,onPasteClick: function(buttonOrKey, e) {
		if(this.actions.paste.isDisabled()) return;
		App.clipboard.paste(this.folderProperties.id);
	}
	,onCreateTaskClick: function(b, e) {
		this.fireEvent('taskcreate', {
			data: {
				type: 6
				,template_id: App.config.default_task_template
				,pid: this.folderProperties.id
				,path: this.folderProperties.path
				,pathtext: this.folderProperties.pathtext
			}
		})
	}
	,onCompleteTaskClick: function(b, e) {
		r = this.grid.getSelectionModel().getSelected();
		if(Ext.isEmpty(r)) return false;
		Ext.Msg.show({
			title: L.CompletingTask
			,msg: L.Message
			,width: 400
			,height: 200
			,buttons: Ext.MessageBox.OKCANCEL
			,multiline: true
			,fn: function(b, message){ if(b == 'ok') Tasks.complete({id: r.get('nid'), message: message}, this.processTaskCompleting, this)}
			,scope: this
		});
	}
	,processTaskCompleting: function(r, e){
		App.mainViewPort.fireEvent('taskupdated', { data: {pid: this.folderProperties.id } }, e);
	}
	,onCreateEventClick: function(b, e) {
		this.fireEvent('taskcreate', {
			data: {
				type: 7
				,template_id: App.config.default_event_template
				,pid: this.folderProperties.id
				,path: this.folderProperties.path
				,pathtext: this.folderProperties.pathtext
			}
		})
	}
	,onFiltersChange: function(filters){
		this.grid.store.baseParams.filters = filters;
		this.onReloadClick();
	}
	,onReloadClick: function(b, e){
		p = Ext.value(this.grid.store.lastOptions, {});
		p.params = this.grid.store.baseParams
		Ext.apply(p.params, this.params);
		this.grid.store.reload(p)
	}
	,onDeleteClick: function(b, e) {
		s = this.grid.selModel.getSelections();
		if(Ext.isEmpty(s)) return;
		Ext.Msg.confirm( L.DeleteConfirmation, (s.length == 1) ? L.DeleteConfirmationMessage + ' "' + s[0].get('name') + '"?' : L.DeleteSelectedConfirmationMessage, this.onDelete, this ) 
	}
	,onDelete: function (btn) {
		if(btn !== 'yes') return;
		s = this.grid.selModel.getSelections();
		if(Ext.isEmpty(s)) return;
		this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
		ids = [];
		Ext.each(s, function(r){ ids.push(r.get('nid'))}, this)
		BrowserView['delete'](ids, this.processDelete, this);
	}
	,processDelete: function(r, e){
		this.getEl().unmask();
		App.mainViewPort.onProcessObjectsDeleted(r, e);
	}
	,onObjectsDeleted: function(ids){
		this.selectIdx = -1;
		for (var i = 0; i < ids.length; i++) {
			idx = this.grid.store.findExact('nid', parseInt(ids[i]));
			if(idx >=0){
				if(this.grid.getSelectionModel().isSelected(idx)) this.selectIdx = idx;
				this.grid.store.removeAt(idx);
			}
		};
		if(this.selectIdx > -1){
			if(this.grid.store.getCount() > idx) this.grid.getSelectionModel().selectRow(idx);
			else this.grid.getSelectionModel().selectLastRow();
		}
		/* TODO: also delete all visible nodes(links) that are links to the deleted node or any its child */
	}
	,onObjectsSaved: function(form, e){
		if(this.folderProperties.id == form.data.pid) this.onReloadClick();
		else if(!Ext.isEmpty(form.data.id)){
			idx = this.grid.store.findExact('nid', parseInt(form.data.id))
			if(idx >=0) this.onReloadClick();
		}
	}
	,onEastPanelButtonClick: function(b, e){
		if(b.pressed){
			this.eastPanel.getLayout().setActiveItem(b.itemIndex);
			this.eastPanel.show();
			if(b.itemIndex == 0){
				r = this.grid.getSelectionModel().getSelected();
				if(r) this.previewPanel.loadPreview(r.get('nid'));
			}
            	}else{
            		this.eastPanel.hide();
            	}
		this.syncSize()
        }
        ,onShowDescendantsClick: function(cb, e){
        	this.fireEvent('showdescendants', !cb.checked, e);
        }
})

Ext.reg('CBTasksViewGrid', CB.TasksViewGrid);

CB.TasksViewGridPanel = Ext.extend(Ext.Panel, {
	hideBorders: true
	,borders: false
	,closable: true
	,layout: 'fit'
	,iconCls: 'icon-taskView'
	,initComponent: function(){
		
		this.view = new CB.TasksViewGrid({
			params: {descendants: true}
		})
		Ext.apply(this,{
			items: this.view
			,iconCls: Ext.value(this.iconCls, 'icon-taskView')
			,listeners:{
				scope: this
				,afterrender: this.onAfterRender
			}
		})
		CB.TasksViewGridPanel.superclass.initComponent.apply(this, arguments);
	}
	,onAfterRender: function(){
		this.view.onFiltersChange({"status":[{"mode":"OR","values":["1","2"]}],"assigned":[{"mode":"OR","values":[App.loginData.id]}]});
	}
})
Ext.reg('CBTasksViewGridPanel', CB.TasksViewGridPanel);