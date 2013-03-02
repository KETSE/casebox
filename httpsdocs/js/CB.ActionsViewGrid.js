Ext.namespace('CB');

CB.ActionsViewGrid = Ext.extend(CB.FolderViewGrid,{
	initComponent: function(){
		
		Ext.apply(this, {
			gridStateId: 'avg'
		})
		CB.ActionsViewGrid.superclass.initComponent.apply(this, arguments);
		this.grid.store.baseParams = {types: [4], facets: 'actions'}
		tb =this.getTopToolbar();
		idx = tb.items.findIndex('iconCls', 'icon32-upload');
		if(idx > -1) tb.remove(idx);
		idx = tb.items.findIndex('iconCls', 'icon32-download');
		if(idx > -1) tb.remove(idx);
		idx = tb.items.findIndex('iconCls', 'icon32-task-new');
		if(idx > -1){
			tb.remove(idx);
			tb.remove(idx-1);
		}
		this.actions.createTask.setHidden(true);
		this.actions.createEvent.setHidden(true);
		this.actions.createFolder.setHidden(true);
		this.actions.createCase.setHidden(true);
	}
})

Ext.reg('CBActionsViewGrid', CB.ActionsViewGrid);

CB.ActionsViewGridPanel = Ext.extend(Ext.Panel, {
	hideBorders: true
	,borders: false
	,closable: true
	,layout: 'fit'
	,initComponent: function(){
		
		this.view = new CB.ActionsViewGrid({
			hideArrows: true
			,params: { descendants: true }
			,listeners: {
				scope: this
				,changeparams: this.onChangeParams
			}
		})
		Ext.apply(this,{
			items: this.view
			,listeners:{
				scope: this
				,afterrender: this.onAfterRender
			}
		})
		CB.ActionsViewGridPanel.superclass.initComponent.apply(this, arguments);
	}
	,onAfterRender: function(){
		this.view.onFiltersChange();
	}
	,onChangeParams: function(params){
		this.view.onChangeParams(params)		
	}
})
Ext.reg('CBActionsViewGridPanel', CB.ActionsViewGridPanel);