Ext.namespace('CB');
CB.Clipboard =  Ext.extend(Ext.util.Observable, {
	data: []
	,action: 'copy' // copy / move / shortcut
	,constructor: function(config){
		this.addEvents({
	            'pasted': true
	        });
		CB.Clipboard.superclass.constructor.call(this, config)
	}
	,set:function(data, action) {
		this.data = Ext.isArray(data) ? data : [data];
		this.action = Ext.value(action, 'copy');
	}
	,setAction:function(action) { this.action = action }
	,size: function(){ return this.data.length }
	,isEmpty: function(){ return Ext.isEmpty(this.data) }
	,clear: function(){ this.data = [] }
	,containShortcutsOnly: function() {
		rez = true;
		Ext.each(this.data, function(i){ rez = (i.type == 2); return rez }, this)
		return rez;
	}
	,paste: function(pid, action){
		action = Ext.value(action, this.action);
		this.lastParams = {pid: pid, data: this.data, action: action};
		Browser.paste(this.lastParams, this.processPaste, this);
	}
	,processPaste: function(r, e){
		if(r.success !== true){
			if(r.confirm == true) Ext.Msg.confirm(L.Confirmation, r.msg, function(b){
				if(b == 'yes'){
					this.lastParams.confirmed = true;
					Browser.paste(this.lastParams, this.processPaste, this);
				}
			}, this);
			else Ext.Msg.alert(L.Error, r.msg);
			return;
		}
		this.fireEvent('pasted', r.pids); //fire the event so that all components that are looking for clipboard will take action when clipboard is pasted and will update parent nodes
	}
});

Ext.reg('CBClipboard', CB.Clipboard);