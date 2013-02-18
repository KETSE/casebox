Ext.ns('CB');
/* associate contact field */
Ext.ux.AssociateObjectsField = Ext.extend(Ext.form.TriggerField, {
	editable: false
	,triggerClass: 'icon-trigger-case'
	,hideTrigger: true
	,initComponent : function(){
		cw = this.ownerCt.findParentByType(CB.Case);
		if(cw) this.objectsStore = cw.objectsStore;

		Ext.ux.AssociateObjectsField.superclass.initComponent.call(this);
		this.on('focus', function(f){ this.setHideTrigger(false) }, this);
		this.on('blur', function(f){ if(f.getEl().isVisible(true)) this.setHideTrigger(true) }, this);
		this.addEvents('change');
		this.enableBubble(['change']);
		this._setValue = this.setValue
		this.setValue = function(v){
			if(!Ext.isEmpty(v)) v = String(v);
			this._setValue(v);
			this.internalValue = v;
			rawvalue = this.objectsStore.getTexts(v);
			this.setRawValue(rawvalue);
		}
		this._getValue = this.getValue;
		this.getValue = function(){ return this.internalValue }
	}
	,afterRender: function() {
		Ext.ux.AssociateObjectsField.superclass.afterRender.apply(this, arguments);
	}

	,onTriggerClick : function(){/* popup association window */
		if( this.popupShown ) return ;
		w = new CB.ObjectsAssociationWindow({
			data: {}
			,params: this.params
			,listeners: {
				scope: this
				,associate: function(data, w){
					this.setData(data);
					this.fireEvent('change', this, this.getValue());
					this.focus()
					this.popupShown = false;
				}
				,hide: function(){ this.popupShown = false }
			}
		});
		w.setData(this.objectsStore.getData(this.getValue()))
		w.show();
		this.popupShown = true;
		this.focus();
	}

	,setData: function(data){
		if(Ext.isEmpty(data)) data = [];
		this.data = data;
		ids = [];
		Ext.each(data, function(i){ 
			ids.push(i.id);
			idx = this.objectsStore.checkRecordExistance(i);
		}, this);
		this.setValue(ids.join(','));
	}
});

Ext.reg('ExtuxAssociateObjectsField', Ext.ux.AssociateObjectsField);