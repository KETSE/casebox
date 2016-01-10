Ext.namespace('CB');

Ext.define('CB.HtmlEditWindow', {
    extend: 'Ext.Window'

    ,itemId: 'htmleditorwindow'
    ,bodyBorder: false
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
        this.editor = new Ext.ux.HtmlEditor({border: false});
        Ext.apply(this, {
            items: [this.editor]
            ,keys:[{
                key: Ext.event.Event.ESC,
                fn: this.doClose,
                scope: this
                }
            ]
            ,buttons: [ {text: Ext.MessageBox.buttonText.ok, handler: this.doSubmit, scope: this}
                        ,{text: L.Cancel, handler: this.doClose, scope: this}]
        });

        this.callParent(arguments);

        this.on('show', this.onShow, this);
    }
    ,onShow: function(){
        this.editor.setValue(Ext.valueFrom(this.data.value, ''));
        this.editor.focus(true, 350);
    },doSubmit: function(){
        var f = this.data.callback.bind(Ext.valueFrom(this.data.scope, this), [this, this.editor.getValue()]);
        f();
        this.doClose();
    },doClose: function(){
        this.hide();
    }
});
