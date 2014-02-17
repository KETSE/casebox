Ext.namespace('demo1');

demo1.CustomizeObjectClass =  Ext.extend(CB.plugins.customInterface, {
	init: function(owner) {
		demo1.CustomizeObjectClass.superclass.init.call(this, arguments);
		this.owner = owner;
		this.owner.on('objectopened', this.applyCustomization, this);
	}
	,applyCustomization: function(){
		this.button = new Ext.Button({text: 'Plugin button', scope: this, handler: this.onButtonClick})
		this.owner.mainToolBar.add(this.button)
	}
	,onButtonClick: function(b, e){
		Ext.Msg.alert('plugin', 'buttonClicked')
		demo1_CustomizeObjects.getCustomInfo( this.owner.data.id, this.processInfo, this )
	}
	,processInfo: function(r, e){
		if(r.success !== true) return;
		Ext.Msg.alert('Process remote result', r.data);
	}
});

Ext.ComponentMgr.registerPlugin('demo1CustomizeObjectClasss', demo1.CustomizeObjectClass);