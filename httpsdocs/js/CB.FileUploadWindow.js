Ext.namespace('CB');

Ext.define('CB.FilesConfirmationWindow', {
    extend: 'Ext.Window'
    ,autoShow: true
    ,border: false
    ,bodyBorder: false
    ,closable: true
    ,closeAction: 'hide'
    ,autoHeight: true
    ,maximizable: false
    ,minimizable: false
    ,modal: true
    ,plain: true
    ,resizable: false
    ,stateful: false
    ,title: L.UploadFile
    ,minWidth: 550
    ,width: 550
    ,bodyStyle: 'padding: 10px; border: 0'
    ,buttonAlign: 'center'
    ,data:{
        single: true
        ,autorenameButton: true
    }

    ,initComponent: function(){
        this.data = this.config.data;

        var buttons = [];

        if(this.data.allow_new_version) {
            buttons.push({
                text: L.NewVersion
                ,name: 'newversion'
                ,scope: this
                ,handler: this.onButtonClick
            });
        }

        buttons.push({
            text: L.Replace
            ,name: 'replace'
            ,scope: this
            ,handler: this.onButtonClick
        });

        this.renameButton = new Ext.Button({
            text: L.Rename
            ,name: 'rename'
            ,scope: this
            ,handler: this.onButtonClick
        });

        if(!Ext.isEmpty(this.data.suggestedFilename)) {
            buttons.push(this.renameButton);
        }

        if(this.data.autorenameButton) {
            buttons.push({
                text: L.AutoRename
                ,name: 'autorename'
                ,scope: this
                ,handler: this.onButtonClick
            });
        }

        buttons.push({
            text: L.Cancel
            ,name: 'cancel'
            ,scope: this
            ,handler: this.onButtonClick
        });

        var items = [
            {xtype: 'label', text: this.data.msg}
        ];

        if(this.data.single === false) {
            items.push({
                xtype: 'checkbox'
                ,boxLabel: L.ApplyForAll
                ,style: 'margin-top: 25px'
                ,listeners:{
                    check: function(cb, checked){
                        this.forAll = checked;
                        this.renameButton.setDisabled(checked);
                    }
                    ,scope: this
                }
            });
        }

        Ext.apply(this, {
            items: items
            ,buttons: buttons
        });

        this.callParent(arguments);

        this.response = 'cancel';
    }

    ,onButtonClick: function(b){
        this.response = b.name;
        this.hide();
    }

});
