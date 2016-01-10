Ext.namespace('CB');

Ext.define('CB.TextEditWindow', {
    extend: 'Ext.Window'
    ,border: false
    ,bodyBorder: false
    ,closable: true
    ,closeAction: 'destroy'
    ,hideCollapseTool: true
    ,layout: 'fit'
    ,maximizable: false
    ,minimizable: false
    ,modal: true
    ,resizable: true
    ,stateful: false
    ,data: { callback: Ext.emptyFn }
    ,title: L.EditingValue
    ,width: 600
    ,height: 400

    ,initComponent: function() {
        this.data = this.config.data;

        switch(this.config.editor) {
            case 'ace':
                this.editor = new Ext.ux.AceEditor({
                    border: false

                });
                break;

            default:
                this.editor = new Ext.form.TextArea({border: false});
        }

        Ext.apply(this, {
            layout: 'fit'
            ,items: [this.editor]
            ,keys:[{
                key: Ext.event.Event.ESC,
                fn: this.doClose,
                scope: this
                }
            ]
            ,listeners: {
                scope: this
                ,show: this.onWindowsShow
            }
            ,buttons: [
                {
                    text: Ext.MessageBox.buttonText.ok
                    ,handler: this.doSubmit
                    ,scope: this
                },{
                    text: L.Cancel
                    ,handler: this.doClose
                    ,scope: this
                }
            ]
        });

        this.callParent(arguments);
    }

    ,onWindowsShow: function(){
        //update title if set
        var title = Ext.valueFrom(this.data.title, this.title);
        this.setTitle(title);
        this.getHeader().setTitle(title);

        this.editor.setValue(
            Ext.valueFrom(this.data.value, '')

            /* need to clarify why json mode is not present in current ace distribution

             ,{
                mode: this.config.mode //set mode for qace editor
            }/**/
        );
        this.editor.focus(false, 350);
    }

    ,doSubmit: function(){
        var ed = this.editor.editor
                ? this.editor.editor
                : this.editor
            ,session = ed.getSession
                ? ed.getSession()
                : null
            ,value = session
                ? session.getValue()
                : ed.getValue()
            ,f = Ext.Function.bind(
                this.data.callback
                ,Ext.valueFrom(this.data.scope, this)
                ,[this, value]
            );

        f();

        this.close();
    }
});
