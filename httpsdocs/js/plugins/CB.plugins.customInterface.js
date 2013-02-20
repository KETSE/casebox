Ext.namespace('CB.plugins');
CB.plugins.customInterface =  Ext.extend(Ext.util.Observable, {
	init: function(owner) {
		//
	}
});

Ext.ComponentMgr.registerPlugin('CBPluginsCustomInterface', CB.plugins.customInterface);