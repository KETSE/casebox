Ext.namespace('Ext.ux');

Ext.ux.HtmlEditor = Ext.extend(Ext.form.HtmlEditor, {
	baseUri: ''
	,border: false
	,hideBorders: true
	,headerInclude: ''
	,initComponent: function() {
		this.on('render', this.onRenderEvent, this);
		Ext.ux.HtmlEditor.superclass.initComponent.apply(this, arguments);
	}
	,onRenderEvent: function(){
		this.addPasteFromWordButton();
	}
	,addPasteFromWordButton: function(){
		this.getToolbar().add('-',{
			iconCls: 'icon-paste-from-word-text'
			,text: L.PasteFromWord
			,scope: this
			,handler: function(b){ 
				if(!Ext.isDefined(CB.thePasteFromWordWindow))
					CB.thePasteFromWordWindow = new CB.PasteFromWord();
				pw = CB.thePasteFromWordWindow;
				Ext.apply(pw, {opener: this});
				pw.show();
			}
		}
		)
	}
	,getDocMarkup : function(){
        var inc = (this.baseUri ? '<base href="' + this.baseUri + '" />' : '') + this.headerInclude;
		var h = Ext.fly(this.iframe).getHeight() - this.iframePad * 2;
        return String.format('<html><head>' + inc + '<style type="text/css">body{border: 0; margin: 0; padding: {0}px; height: {1}px; cursor: text}</style></head><body></body></html>', this.iframePad, h);
    }
})
Ext.reg('CBHtmlEditor', Ext.ux.HtmlEditor);