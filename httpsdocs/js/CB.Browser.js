Ext.namespace('CB');

CB.Browser = Ext.extend(Ext.Panel,{
	title: L.Explorer
	,iconCls: 'icon-folder-tree'
	,hideBorders: true
	,closable: true
	,initComponent: function(){
		
		this.tree = new CB.BrowserTree({
			region: 'west'
			,split: true
			,collapseMode: 'mini'
			,width: 300
			,stateful: true
			,stateId: 'BrowserTree'
			,stateEvents: ['resize']
			,getState: function(){ return {width: this.getWidth()}}
		});

		this.view = new CB.BrowserView({region: 'center'});
		Ext.apply(this, {
			layout: 'border'
			,items: [this.tree, this.view]
		})
		CB.Browser.superclass.initComponent.apply(this, arguments);
	}
})

Ext.reg('CBBrowser', CB.Browser);