Ext.namespace('CB');

Ext.define('CB.field.CommentLight', {
    extend: 'CB.field.Comment'

    ,alias: 'CBFieldCommentLight'

    ,xtype: 'CBFieldCommentLight'

    ,initChildItems: function() {
        this.attachFileButton = new Ext.form.field.File({
            qtip: L.AttachFile
            ,buttonOnly: true
            ,buttonText: ''
            ,buttonConfig: {
                iconCls: 'i-attach'
            }
            ,hidden: (Ext.isEmpty(this.params) || Ext.isEmpty(this.params.id))
            ,width: 24
            ,listeners: {
                scope: this
                ,change: this.onAttachFileClick
                ,render: function (ed) {
                    ed.fileInputEl.set({
                        multiple: true
                    });
                }
            }
        });

        this.getTextInputField();


        this.filesLabel = new Ext.form.field.Display({
            cls: 'click'
            ,hidden: true //shows only when attaching files
        });

        this.items = [
            {
                xtype: 'label'
                ,width: 50
                ,padding: '2px 0'
                ,html: '<img class="i32" src="/' + App.config.coreName +
                        '/photo/' + App.loginData.id + '.jpg?32=' +
                        CB.DB.usersStore.getPhotoParam(App.loginData.id) +
                        '" title="' + getUserDisplayName(true) + '">'
            }
            ,{
                xtype: 'panel'
                ,padding: 0
                ,border: 0
                ,flex: 1
                ,layout: {
                    type: 'vbox'
                    ,align: 'stretch'
                }
                ,items: [
                    this.messageField
                    ,this.filesLabel
                ]
            }
        ];
    }

    ,getTextInputField: function(config) {

        if(Ext.isEmpty(this.messageField)) {
            var cfg = {
                flex: 1
                ,grow: true
                ,growMin: 10
                ,minHeight: 10
                ,enableKeyEvents: true
                ,cls: "comment-input"
                // ,style: 'margin-top: 5px; font-family: arial,sans-serif; font-size: 12px'
                ,triggers: {
                    attach: {
                        cls: 'comment-trigger-attach'
                        ,scope: this
                        ,handler: this.onAttachFileClick
                    }
                    ,reply: {
                        cls: 'comment-trigger-reply'
                        ,scope: this
                        ,handler: this.onAddCommentClick
                    }
                }

                ,plugins: [
                    {
                        ptype: 'CBPluginFieldDropDownList'
                    }
                ]

                ,listeners: {
                    scope: this
                    ,keypress: this.onMessageBoxKeyPress
                    ,autosize: this.onMessageBoxAutoSize
                }
            };

            if(config) {
                Ext.apply(cfg, config);
            }

            this.messageField = new Ext.form.TextArea(cfg);
        }

        return this.messageField;
    }

    ,updateFilesLabel: function() {
        var f = this.filesLabel;
        this.callParent(arguments);

        f.setHidden(Ext.isEmpty(f.getValue()));
    }
});
