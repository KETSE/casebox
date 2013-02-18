Ext.namespace('CB');
CB.Facet = Ext.extend( Ext.Panel, {
	title: 'facet'
	,height: 100
	,collapsible: true
	,titleCollapse: true
	,hideCollapseTool: true
	,cls: 'facet'
	,border: false
	,mode: 'OR'
	,modeToggle: false
	,bodyStyle: 'background: none'
	,initComponent: function(){
		if(this.modeToggle) 
		Ext.apply(this, { tools: [{id: 'unchain', handler: this.onModeToggle, scope: this, qtip: L.searchSwitchModeMessage}] });
		CB.Facet.superclass.initComponent.apply(this, arguments);
		this.addEvents('facetchange', 'modechange');
		this.enableBubble(['facetchange', 'modechange']);
	}
	,setModeVisible: function(visible){
		if(!this.rendered) return;
		this.getEl().removeClass('multivalued');
		if(visible) this.getEl().addClass('multivalued');
	}
	,onModeToggle: function(ev, toolEl, panel, tc){
		if (toolEl.hasClass('x-tool-chain')) {
			toolEl.replaceClass('x-tool-chain', 'x-tool-unchain');
			this.mode = 'OR';
		} else {
			toolEl.replaceClass('x-tool-unchain', 'x-tool-chain');
			this.mode = 'AND';
		}
		this.fireEvent('modechange', this, ev)
	}
}
)

Ext.reg('CBFacet', CB.Facet);