Ext.namespace('CB');
CB.Dashboard = Ext.extend(Ext.Panel, {
	title: L.Dashboard
	,closable: false
	,'layout': 'fit'
	,initComponent: function(){
		this.view = new CB.FolderViewSummary();
		Ext.apply(this, {
			hideBorders: true
			,tbarCssClass: 'x-panel-gray'
			,tbar: [{iconCls: 'icon-refresh', scope: this, handler: this.reload}]
			,items: this.view
			,listeners: {
				scope: this
				,afterrender: this.onAfterRender
			}
		})
		CB.Dashboard.superclass.initComponent.call(this, arguments);
	}
	,onAfterRender: function(){
		this.reload();
	}
	,reload: function(){
		this.view.reload()
	}
})

Ext.reg('CBDashboard', CB.Dashboard);