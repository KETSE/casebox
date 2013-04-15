Ext.namespace('CB');
CB.FolderView = Ext.extend(Ext.Panel, {
	title: 'Folder view'
	,iconCls: 'icon-folder'
	,closable: true
	,hideBorders: true
	,tbarCssClass: 'x-panel-gray'
	,layout:'card'
	,activeItem: 0 // make sure the active item is set on the container config!
	,deferredRender: true
	,locked: false
	,history: []
	,params: {
		descendants: false
		//,query: ''
		//,path: ''
	}
	,initComponent: function(){
		this.actions = {
			back: new Ext.Action({
				tooltip: L.Back
				,iconCls: 'icon-back'
				,disabled: true
				,scope: this
				,handler: this.onBackClick
			})
			,forward: new Ext.Action({
				tooltip: L.Forward
				,iconCls: 'icon-forward'
				,disabled: true
				,scope: this
				,handler: this.onForwardClick
			})
			,up: new Ext.Action({
				tooltip: L.ParentFolder
				,iconCls: 'icon-up'
				,scope: this
				,disabled: true
				,handler: this.onUpClick
			})
			,reload: new Ext.Action({
				iconCls: 'icon-refresh'
				,tooltip: L.Refresh
				,scope: this
				,handler: this.onReloadClick
			})
			,lock: new Ext.Action({
				iconCls: 'icon-lock'
				,enableToggle: true
				,tooltip: L.LockWindow
				,scope: this
				,handler: this.onLockClick
			})
			,showDescendants: new Ext.Action({
				iconCls: 'icon-descendants'
				,text: L.Descendants
				,enableToggle: true
				,tooltip: L.ShowDescendants
				,scope: this
				,handler: this.onShowDescendantsClick
			})
		}
		this.favoritesButton = new CB.FavoritesMenuItem({
			listeners: {
				scope: this
				,select: function(id){
					this.setParams({path: id});
				}
			}
		});
		this.searchField = new Ext.ux.SearchField({width: 250, minListWidth: 250, listeners: {scope: this, 'search': this.onSearchQuery} } )
		this.viewButton = new Ext.Button({
			text: L.List
			,iconCls: 'icon-listView'
			,menu: [{ 
			    		iconCls: 'icon-listView'
			    		,enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,pressed: true
			    		,text: L.List
			    		,viewIndex: 0
			    		,scope: this
			    		,handler: this.onChangeViewClick
				},{ 
			    		iconCls: 'icon-treeView'
			    		,enableToggle: true
			    		,allowDepress: false
			    		,disabled: true
			    		,toggleGroup: 'viewMode'
			    		,pressed: true
			    		,text: L.Tree
			    		,viewIndex: 0
			    		,scope: this
			    		,handler: this.onChangeViewClick
				},'-',{
			    		iconCls: 'icon-actionView'
			    		,enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,text: L.Actions
			    		,viewIndex: 1
			    		,scope: this
			    		,handler: this.onChangeViewClick
				},{
			    		iconCls: 'icon-taskView'
			    		,enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,text: L.Tasks
			    		,viewIndex: 2
			    		,scope: this
			    		,handler: this.onChangeViewClick
				},{
			    		iconCls: 'icon-calendarView'
			    		,enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,text: L.Calendar
			    		,viewIndex: 3
			    		,scope: this
			    		,handler: this.onChangeViewClick
				},{
			    		iconCls: 'icon-summary-view'
			    		,enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,text: L.Overview
			    		,viewIndex: 4
			    		,scope: this
			    		,handler: this.onChangeViewClick
				}
			]
		})
		Ext.apply(this, {
		    	tbar: [
				this.actions.back
				,this.actions.forward
				,this.actions.reload
				,'-'
				,this.actions.up
				,'-'
				,this.actions.lock
				,this.favoritesButton
				,{xtype: 'tbspacer', width: 5}
				,'->'
				,this.viewButton
				,'-'
				,this.actions.showDescendants
				,this.searchField
			]
			,defaults: { hideMode:'offsets' }
		    	,items: [
				new CB.FolderViewGrid({ iconCls: 'icon-grid-view' })
				,new CB.ActionsViewGrid({ iconCls: 'icon-actions-view' })
				,new CB.TasksViewGrid({ iconCls: 'icon-task-view' })
				,new CB.CalendarView({ iconCls: 'icon-calendar-view' })
				,new CB.FolderViewSummary({ iconCls: 'icon-summary-view' })
		    	]
		    	,listeners:{
		    		scope: this
		    		,changeparams: this.onChangeParams //fired by an internal view
		    		,afterrender: function(){
		    			if(!Ext.isEmpty(this.rootId)){
		    				//this.params.path = this.rootId;
		    				this.setParams({path: this.rootId});
		    			}
		    		}
		    		,changeview: this.onChangeViewEvent  //fired from internal views when locating an item and change the view automaticly
		    		,viewloaded: this.onViewLoaded
		    		,showdescendants: this.onShowDescendantsEvent
		    		,activate: function(){
		    			if(this.searchField.getWidth() < 250 ) this.searchField.clear();
		    		}
		    	}
		    	,keys: [{
				key: Ext.EventObject.LEFT //left arrow (back)
				,alt: true
				,ctrl: false
				,shift: false
				,stopEvent: true
				,fn: this.onBackClick
				,scope: this
			},{
				key: Ext.EventObject.RIGHT //right arrow (forward)
				,alt: true
				,ctrl: false
				,shift: false
				,stopEvent: true
				,fn: this.onForwardClick
				,scope: this
			},{
				key: Ext.EventObject.BACKSPACE  
				,alt: true
				,ctrl: false
				,shift: false
				,stopEvent: true
				,fn: this.onUpClick
				,scope: this
			}]
		})
		
		CB.FolderView.superclass.initComponent.apply(this, arguments);
	}
	,onChangeViewEvent: function(index, e){
		e.stopPropagation();
		idx = this.viewButton.menu.items.findIndex('viewIndex', index);
		if(idx >= 0){
			b = this.viewButton.menu.items.itemAt(idx);
			l = this.getLayout();
			if( this.items.itemAt(b.viewIndex) == l.activeItem ) return;
			l.setActiveItem(index);
			this.viewButton.setText(b.text)
			this.viewButton.setIconClass(b.iconCls)
		}
	}
	,onViewLoaded: function(proxy, o, options){
		this.params.path = o.result.folderProperties.path
		this.setTitle(o.result.pathtext);

		this.searchField.emptyText = L.Search + ' ' + o.result.folderProperties.name;
		if(Ext.isEmpty(this.params.query)) this.searchField.clear();
		
		this.actions.up.setDisabled( (o.result.pathtext == '/') || (this.rootId == o.result.folderProperties.id) );
		this.favoritesButton.setActiveItem(o.result.folderProperties.id)
	}
	,onChangeViewClick: function(b, e){
		l = this.getLayout();
		if(!Ext.isDefined(b.viewIndex) || ( this.items.itemAt(b.viewIndex) == l.activeItem ) ) return;
		this.onChangeViewEvent(b.viewIndex, e);
		if(l.activeItem.setParams) l.activeItem.setParams(this.params);
	}
	,onBackClick: function(b, e) {
		if(this.actions.back.isDisabled()) return;
		this.historyIndex = (!Ext.isDefined(this.historyIndex)) ? this.history.length - 2 : this.historyIndex - 1; 
		this.setParams(this.history[this.historyIndex]);
		this.actions.back.setDisabled(this.historyIndex <= 0);
		this.actions.forward.setDisabled(false);
	}
	,onForwardClick: function(b, e) {
		if(this.actions.forward.isDisabled()) return;
		this.historyIndex = this.historyIndex + 1; 
		this.setParams(this.history[this.historyIndex]);
		this.actions.back.setDisabled(false);
		this.actions.forward.setDisabled(this.historyIndex >= (this.history.length -1));
	}
	,onLockClick: function(b, e){
		this.locked = b.pressed;
	}
	,sameParams: function(params1, params2){
		if(Ext.isEmpty(params1) && Ext.isEmpty(params2)) return true;
		
		if(Ext.isEmpty(params1)) params1 = {};
		if(Ext.isEmpty(params2)) params2 = {};
		path1 = Ext.value(params1.path, '');
		path2 = Ext.value(params2.path, '');
		while( (path1.length > 0) && (path1[0] == '/') ) path1 = path1.substr(1);
		while( (path2.length > 0) && (path2[0] == '/') ) path2 = path2.substr(1);
		if( (params1.path != params2.path) || !Ext.isDefined(params1.path) ) return false;
		if( (!Ext.isEmpty(params1.descendants) || !Ext.isEmpty(params2.descendants) ) && (params1.descendants != params2.descendants) ) return false;
		if( (!Ext.isEmpty(params1.query) || !Ext.isEmpty(params2.query) ) && (params1.query != params2.query) ) return false;
		return true;
	}
	,onChangeParams: function(params, e){// fired by internal view
		if(e && e.stopPropagation) e.stopPropagation();
		if(this.locked) return;
		this.spliceHistory();
		this.setParams(params)
	}
	,setParams: function(params){
		if(this.locked) return;
		if(Ext.isEmpty(params.path)) params.path = '/';
		sameParams = this.sameParams(this.params, Ext.apply({}, params, this.params) );
		if( Ext.isEmpty(this.requestParams) &&  sameParams) {
			i = this.getLayout().activeItem;
			if(i.grid) App.mainViewPort.selectGridObject(i.grid);
			return;
		}

		if( sameParams ) return;
		this.requestParams = Ext.apply({}, params, this.params);
		if(Ext.isEmpty(this.loadParamsTask)) this.loadParamsTask = new Ext.util.DelayedTask(this.loadParams, this);
		this.loadParamsTask.delay(500);
	}
	,loadParams: function(){
		if( this.sameParams(this.params, this.requestParams) ) return;
		if(!Ext.isDefined(this.historyIndex)){
			if(!Ext.isEmpty(this.requestParams)){
				this.history.push(Ext.apply({}, this.requestParams));
				if(this.history.length > 99) this.history.shift();
				this.actions.back.setDisabled(this.history.length < 2);
				this.actions.forward.setDisabled(true);
			}
		}
		Ext.apply(this.params, this.requestParams);
		this.applyParamsVisually()
		i = this.getLayout().activeItem;
		if(i.setParams) i.setParams(this.params)
	}
	,applyParamsVisually: function(){
		this.getTopToolbar().find('iconCls', 'icon-descendants')[0].toggle(this.params.descendants);
		if(Ext.isEmpty(this.params.query)){
			this.searchField.clear()
			this.getTopToolbar().removeClass('search-on');
		}else{
			this.searchField.setValue(this.params.query);
			this.getTopToolbar().addClass('search-on');
		}
	}
	,onReloadClick: function(){
		i = this.getLayout().activeItem;
		if(Ext.isEmpty(i.onReloadClick)) return;
		if(Ext.isEmpty(this.reloadTask)) this.reloadTask = new Ext.util.DelayedTask(this.reloadView, this);
		this.reloadTask.delay(500);
	}
	,reloadView: function(){
		i = this.getLayout().activeItem;
		i.onReloadClick();
	}
	,spliceHistory: function() {
		if(Ext.isDefined(this.historyIndex)){
			this.history.splice(this.historyIndex + 1, this.history.length - this.historyIndex);
			delete this.historyIndex;
		}
	}
	,onUpClick: function(b, e) {
		params = Ext.apply({}, this.params)
		params.path = params.path.split('/');
		params.path.pop();
		params.path = params.path.join('/');
		if(Ext.isEmpty(params.path)) params.path = '/' + Ext.value(this.rootId, '');
		this.spliceHistory()
		this.setParams(params);
	}
	,onSearchQuery: function(query, e) {
		if(query == this.params.query) return;
		params = Ext.apply({}, this.params);
		params.query = query;
		this.setParams(params);
		// i = this.getLayout().activeItem;
		// if(i.setParams) i.setParams(params, e);
	}
	,setShowDescendants: function(value){
		value = (value == true);
		if(value == this.params.descendants) return;
		params = Ext.apply({}, this.params);
		params.descendants = value; 
		this.setParams(params);
	}
	,onShowDescendantsClick: function(b, e){
		if(this.locked) return b.toggle(!b.pressed);
		this.setShowDescendants(b.pressed);
	}
	,onShowDescendantsEvent: function(show, e){
		this.setShowDescendants(show);
		this.onShowDescendantsClick({pressed: show}, e);
	}
});

Ext.reg('CBFolderView', CB.FolderView);