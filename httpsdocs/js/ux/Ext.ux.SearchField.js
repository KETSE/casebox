Ext.ns('CB');

Ext.ux.SearchField = Ext.extend(Ext.form.TwinTriggerField, {
	trigger1Class: 'trigger-cross'
	,trigger2Class: 'trigger-locate'
	,hideTrigger1: true
	,hideTrigger2: false
	,emptyText: L.Search
	,enableKeyEvents: true
	,initComponent : function(){
		Ext.apply(this, {
			listeners: {
				scope: this
				,keyup: function(ed, e){ 
					if(Ext.isEmpty(this.getValue())) this.triggers[0].hide();
					else this.triggers[0].show();
				}
				,specialkey: function(ed, e){ 
					switch(e.getKey()){
						case e.ESC: 	this.onTrigger1Click(e); break
						case e.ENTER: 	this.onTrigger2Click(e); break;
					}
				}
			}
		})
		Ext.ux.SearchField.superclass.initComponent.apply(this);
	}
	,afterRender: function() {
		Ext.ux.SearchField.superclass.afterRender.apply(this, arguments);
	}

	,onTrigger1Click : function(e){
		if(Ext.isEmpty(this.getValue())) return;
		this.setValue('');
		this.triggers[0].hide();
		this.fireEvent('search', '', e);
	}

	,onTrigger2Click : function(e){
		this.fireEvent('search', this.getValue(), e);
	}
	,clear: function(){
		this.setValue('');
		this.triggers[0].hide();
	}
});

Ext.reg('ExtuxSearchField', Ext.ux.SearchField);