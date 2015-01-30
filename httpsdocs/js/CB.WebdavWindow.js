Ext.namespace('CB');

Ext.define('CB.WebdavWindow', {
    extend: 'Ext.Window'

    ,initComponent: function() {
        this.data = this.config.data;

        if(Ext.isEmpty(this.data)) {
            this.data = {};
        }

        this.textField = new Ext.form.TextField({
           value: this.data.link
            ,fieldLabel: 'Link'
            ,labelWidth: 25
            ,selectOnFocus: true
            ,style: 'font-size: 10px'
            ,width: '100%'
        });

        this.cbHideDialog = new Ext.form.Checkbox({
            xtype: 'checkbox'
            ,height: 20
            ,boxLabel: 'Enable cbdav & don\'t show this dialog'
            ,checked: (Ext.util.Cookies.get('webdavHideDlg') == 1)
        });

        Ext.apply(this, {
            modal: true
            ,width: 400
            ,autoHeight: true
            ,border: true
            ,closable: true
            ,cls: 'webdav'
            ,title: 'WebDAV'
            ,padding: 10
            ,bodyStyle: 'padding:10px'

            ,items: [
                this.textField
                ,{
                    xtype: 'displayfield'
                    ,value: '<br />Open this link in your editor (Word, LibreOffice).<br /> You\'ll be asked for your CaseBox username/password.'
                }
                ,{
                    xtype: 'displayfield'
                    ,value: '<br />Install <a href="http://www.casebox.org/dl/cbdav.exe" class="click">cbdav.exe</a> to automatically open the document when you<br />double clicka file or use the edit button.<br /><br />'
                }
                ,this.cbHideDialog
                ,{
                    xtype: 'button'
                    ,style: 'margin: auto'
                    ,scope: this
                    ,value: 'Ok'
                    ,text: 'Ok'
                    ,label: 'Ok'
                    ,handler: this.onOkClick
                }
            ]

            ,listeners: {
                scope: this
                ,afterrender: this.onShow
            }
        });

        this.callParent(arguments);
    }

    ,onShow: function() {
        this.textField.focus(true, 100);
        this.updateLayout();
    }

    ,onOkClick: function(b, e) {
        Ext.util.Cookies.set('webdavHideDlg', this.cbHideDialog.getValue() ? 1 : 0);

        this.close();
    }
}
);
