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
	,showDescendants: false
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
					this.changePath(id);
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
				// ,{xtyle: 'label', html: '<span style="color: #777">'+L.View+':&nbsp;</span>'}
				,this.viewButton
				,'-'
				// ,{xtype: 'tbspacer', width: 10}
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
		    		,changepath: this.changePath
		    		,afterrender: function(){
		    			if(!Ext.isEmpty(this.rootId)) this.changePath(this.rootId)
		    		}
		    		,changeview: this.onChangeViewEvent
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
		tb = this.getTopToolbar();
		idx = tb.items.findIndex('viewIndex', index);
		if(idx >= 0){
			tb.items.itemAt(idx).toggle(true);
			this.getLayout().setActiveItem(index);
		}
	}
	,onViewLoaded: function(proxy, o, options){
		//delete this.requestPath;
		this.path = o.result.folderProperties.path
		this.setTitle(o.result.pathtext);
		this.searchField.emptyText = L.Search + ' ' + o.result.folderProperties.name;
		this.searchField.clear();
		if(Ext.isEmpty(options.params.query)) this.getTopToolbar().removeClass('search-on'); else this.getTopToolbar().addClass('search-on')
		this.actions.up.setDisabled( (o.result.pathtext == '/') || (this.rootId == o.result.folderProperties.id) );
		this.favoritesButton.setActiveItem(o.result.folderProperties.id)
	}
	,onChangeViewClick: function(b, e){
		l = this.getLayout();
		if(!Ext.isDefined(b.viewIndex) || ( this.items.itemAt(b.viewIndex) == l.activeItem ) ) return;
		this.searchField.clear();
		l.setActiveItem(b.viewIndex);
		this.viewButton.setText(b.text)
		this.viewButton.setIconClass(b.iconCls)
		ai = l.activeItem;
		if( (ai.setShowDescendants && (ai.showDescendants !== this.showDescendants) ) || (ai.path !== this.path) ){
			ai.setShowDescendants(this.showDescendants);
			if(ai.changePath) ai.changePath(this.path);
		}
	}
	,onBackClick: function(b, e) {
		if(this.actions.back.isDisabled()) return;
		this.historyIndex = (!Ext.isDefined(this.historyIndex)) ? this.history.length - 2 : this.historyIndex - 1; 
		this.gotoPath(this.history[this.historyIndex]);
		this.actions.back.setDisabled(this.historyIndex <= 0);
		this.actions.forward.setDisabled(false);
	}
	,onForwardClick: function(b, e) {
		if(this.actions.forward.isDisabled()) return;
		this.historyIndex = this.historyIndex + 1; 
		this.gotoPath(this.history[this.historyIndex]);
		this.actions.back.setDisabled(false);
		this.actions.forward.setDisabled(this.historyIndex >= (this.history.length -1));
	}
	,onLockClick: function(b, e){
		this.locked = b.pressed;
	}
	,changePath: function(path, options, e){
		if(e && e.stopPropagation) e.stopPropagation();
		clog('change path requested to: ', path)
		if(this.locked) return;
		this.spliceHistory();
		if(options && Ext.isDefined(options.showDescendants)) this.setShowDescendants(options.showDescendants);
		i = this.getLayout().activeItem;
		if(i.changePath) this.gotoPath(path)
	}
	,gotoPath: function(newPath){
		if(Ext.isEmpty(newPath)) newPath = '/';
		if( !Ext.isEmpty(this.requestPath) && (this.requestPath == newPath) ) return;
		if( Ext.isEmpty(this.requestPath) && (this.path == newPath) ) {
			i = this.getLayout().activeItem;
			if(i.grid) App.mainViewPort.selectGridObject(i.grid);
			return;
		}
		this.searchField.clear();

		this.requestPath = String(newPath);
		if(Ext.isEmpty(this.loadPathTask)) this.loadPathTask = new Ext.util.DelayedTask(this.loadPath, this);
		this.loadPathTask.delay(500);
	}
	,loadPath: function(){
		if(this.requestPath == this.path) return;
		if(!Ext.isDefined(this.historyIndex)){
			if(!Ext.isEmpty(this.requestPath)){
				this.history.push(this.requestPath);
				if(this.history.length > 99) this.history.shift();
				this.actions.back.setDisabled(this.history.length < 2);
				this.actions.forward.setDisabled(true);
			}
		}
		i = this.getLayout().activeItem;
		clog('calling views changePath function with path', this.requestPath);
		if(i.changePath) i.changePath(this.requestPath, {query: ''})
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
		//clog('splice, history', this.history);
		//clog('splice, historyIndex: ', this.historyIndex);
		if(Ext.isDefined(this.historyIndex)){
			this.history.splice(this.historyIndex + 1, this.history.length - this.historyIndex);
			delete this.historyIndex;
		}
	}
	,onUpClick: function(b, e) {
		path = this.path.split('/');
		path.pop();
		path = path.join('/');
		if(Ext.isEmpty(path)) path = '/' + Ext.value(this.rootId, '');
		this.spliceHistory()
		this.changePath(path);
	}
	,onSearchQuery: function(query, e) {
		this.lastQuery = query;
		i = this.getLayout().activeItem;
		if(i.onSearchQuery) i.onSearchQuery(query, e)
	}
	,setShowDescendants: function(value){
		this.showDescendants = (value == true)
		//this.actions.showDescendants.toggle(b.pressed);
		this.getTopToolbar().find('iconCls', 'icon-descendants')[0].toggle(this.showDescendants);
		this.getLayout().activeItem.setShowDescendants(this.showDescendants)
	}
	,onShowDescendantsClick: function(b, e){
		i = this.getLayout().activeItem;
		this.setShowDescendants(b.pressed);
		if(i.setShowDescendants){
			i.setShowDescendants(b.pressed);
			i.onReloadClick();
		}

	}
	,onShowDescendantsEvent: function(show, e){
		this.setShowDescendants(show);
		this.onShowDescendantsClick({pressed: show}, e);
	//	this.getLayout().activeItem.onReloadClick();
	}
});

Ext.reg('CBFolderView', CB.FolderView);