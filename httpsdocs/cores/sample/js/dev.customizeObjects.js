Ext.namespace('Dev');
Dev.customizeObjectClass =  Ext.extend(CB.plugins.customInterface, {
	init: function(owner) {
		Dev.customizeObjectClass.superclass.init.call(this, arguments);
		this.owner = owner;
		this.owner.on('objectopened', this.applyCustomization, this);
	}
	,applyCustomization: function(){
		this.button = new Ext.Button({text: 'Plugin button', scope: this, handler: this.onButtonClick})
		clog(this.owner);
		this.owner.mainToolBar.add(this.button)
	}
	,onButtonClick: function(b, e){
		Ext.Msg.alert('plugin', 'buttonClicked')
		customizeObjects.getCustomInfo( this.owner.data.id, this.processInfo, this )
	}
	,processInfo: function(r, e){
		if(r.success !== true) return;
		Ext.Msg.alert('Process remote result', r.data);
	}
});

Ext.ComponentMgr.registerPlugin('DevcustomizeObjectClasss', Dev.customizeObjectClass);