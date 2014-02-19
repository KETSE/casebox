Ext.namespace('CB');

CB.WebdavWindow = Ext.extend(Ext.Window, {
    modal: true
    ,width: 400
    ,autoHeight: true
    ,border: false
    ,hideBorders: true
    ,plain: true
    ,cls: 'webdav'
    ,initComponent: function() {
        if(Ext.isEmpty(this.data)) {
            this.data = {};
        }

        this.textField = new Ext.form.TextField({
           value: this.data.link
            ,selectOnFocus: true
            ,style: 'font-size: 10px'
            ,width: 320
        })
        this.cbHideDialog = new Ext.form.Checkbox({
            xtype: 'checkbox'
            ,boxLabel: 'Enable cbdav & don\'t show this dialog'
        });

        Ext.apply(this, {
            bodyStyle: 'margin: 0 15px 10px 15px'
            ,buttonAlign: 'center'
            ,items: [ {
                xtype: 'compositefield'
                ,items: [
                    {
                        xtype: 'displayfield'
                        ,value: 'Link:'
                    }
                    ,this.textField
                ]
            }
            ,{
                xtype: 'displayfield'
                ,value: '<br />Open this link in your editor (Word, LibreOffice).<br /> You\'ll be asked for your CaseBox username/password.'
            }
            ,{
                xtype: 'displayfield'
                ,value: '<br />Install <a href="http://www.casebox.org/dl/cbdav.exe" class="click">cbdav.exe</a> to automatically open the document when you<br />double clicka file or use the edit button.<br /><br />'
            }
            ,this.cbHideDialog
            ]
            ,buttons: [{
                xtype: 'button'
                ,html: '<a>Ok</a>'
                ,style: 'padding: 2px 10px; border: 1px solid gray '
                ,scope: this
                ,handler: this.onOkClick
            }
            ]
            ,listeners: {
                scope: this
                ,afterrender: this.onShow
            }
        });
        CB.WebdavWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findByType('form')[0];
    }

    ,onShow: function() {
        this.textField.focus(true, 100);
    }
    ,onOkClick: function(b, e) {
        if(this.cbHideDialog.getValue()) {
            Ext.util.Cookies.set('webdavHideDlg', 1);
        }
        this.close();
    }
}
);

Ext.reg('CBWebdavWindow', CB.WebdavWindow);
