Ext.namespace('CB'); 

CB.TextEditWindow = Ext.extend(Ext.Window, {
    bodyBorder: false
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
    ,width: 600
    ,height: 400
    ,initComponent: function() {
        this.editor = new Ext.form.TextArea({border: false});
        Ext.apply(this, {
            layout: 'fit'
            ,items: [this.editor]
            ,keys:[{
                key: Ext.EventObject.ESC,
                fn: this.doClose,
                scope: this
                }
            ]
            ,buttons: [ {text: Ext.MessageBox.buttonText.ok, handler: this.doSubmit, scope: this}
                        ,{text: Ext.MessageBox.buttonText.cancel, handler: this.doClose, scope: this}]
        })
        CB.TextEditWindow.superclass.initComponent.apply(this, arguments);
        
        this.on('show', this.onShow, this);
    }
    ,onShow: function(){
        this.editor.setValue(Ext.value(this.data.value, ''));
        this.editor.focus(false, 350);
    },doSubmit: function(){
        f = this.data.callback.createDelegate(Ext.value(this.data.scope, this), [this, this.editor.getValue()]);
        f();
        this.doClose();
    },doClose: function(){
        this.hide();
    }
})

Ext.reg('CBTextEditWindow', CB.TextEditWindow); // register xtype                                                   
