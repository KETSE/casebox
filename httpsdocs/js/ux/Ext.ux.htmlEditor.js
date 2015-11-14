Ext.namespace('Ext.ux');

Ext.define('Ext.ux.HtmlEditor', {
    extend: 'Ext.form.field.HtmlEditor'
    ,alias: 'widget.CBHtmlEditor'

    ,xtype: 'ExtUxHtmlEditor'

    ,itemId: 'htmleditor'
    ,baseUri: ''
    ,border: false
    ,bodyStyle: 'border: 0'
    ,headerInclude: ''

    ,initComponent: function() {
        this.on('render', this.onRenderEvent, this);
        this.callParent(arguments);
    }
    ,onRenderEvent: function(){
        this.addPasteFromWordButton();
    }

    ,addPasteFromWordButton: function(){
        this.getToolbar().add(
            '-'
            ,{
                cls: 'remove-sprites'
                ,iconCls: 'icon-paste-from-word-text'
                ,text: L.PasteFromWord
                ,scope: this
                ,handler: function(b) {
                    if(!Ext.isDefined(CB.thePasteFromWordWindow)) {
                        CB.thePasteFromWordWindow = new CB.PasteFromWord();
                    }
                    var pw = CB.thePasteFromWordWindow;
                    Ext.apply(pw, {opener: this});
                    pw.show();
                }
            }
        );
    }

    ,getDocMarkup : function(){
        if(this.iframe) {
            var inc = (this.baseUri ? '<base href="' + this.baseUri + '" />' : '') + this.headerInclude;
            var h = Ext.fly(this.iframe).getHeight() - this.iframePad * 2;
            return Ext.String.format(
                '<html><head>' +
                inc +
                '<style type="text/css">body{border: 0; margin: 0; padding: {0}px; height: {1}px; cursor: text}</style></head>' +
                '<body></body></html>'
                ,this.iframePad
                ,h
            );
        }
    }

});
