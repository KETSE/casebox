Ext.namespace('CB'); 

CB.HtmlEditWindow = Ext.extend(Ext.Window, {
	bodyBorder: false
	,hideBorders: true
	,border: false
	,closable: true
	,closeAction: 'hide'
	,hideCollapseTool: true
	,layout: 'fit'
	,maximizable: false
	,minimizable: false
	,modal: true
	,plain: true
	,resizable: true
	,stateful: false
	,data: { callback: Ext.emptyFn }
	,title: L.EditingValue
	,width: 700
	,height: 400
	,initComponent: function() {
		this.editor = new Ext.ux.HtmlEditor({border: false, hideBorders: true});
		Ext.apply(this, {
			items: [this.editor]
			,keys:[{
				key: Ext.EventObject.ESC,
				fn: this.doClose,
				scope: this
				}
			]
			,buttons: [	{text: Ext.MessageBox.buttonText.ok, handler: this.doSubmit, scope: this}
						,{text: Ext.MessageBox.buttonText.cancel, handler: this.doClose, scope: this}]
		})
  		CB.HtmlEditWindow.superclass.initComponent.apply(this, arguments);
		
		this.on('show', this.onShow, this);
	}
	,onShow: function(){
		this.editor.setValue(Ext.value(this.data.value, ''));
		this.editor.focus(true, 350);
	},doSubmit: function(){
		f = this.data.callback.createDelegate(Ext.value(this.data.scope, this), [this, this.editor.getValue()]);
		f();
		this.doClose();
	},doClose: function(){
		this.hide();
	}
})

Ext.reg('CBHtmlEditWindow', CB.HtmlEditWindow); // register xtype													
