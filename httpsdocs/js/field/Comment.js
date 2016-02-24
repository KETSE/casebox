Ext.namespace('CB');

Ext.define('CB.field.Comment', {
    extend: 'Ext.panel.Panel'

    ,alias: 'CBFieldComment'

    ,xtype: 'CBFieldComment'

    ,layout: {
        type: 'hbox'
        ,align: 'stretch'
    }

    ,initComponent: function() {

        this.initChildItems();

        Ext.apply(this, {
            autoHeight: true
            ,border: false
            ,listeners: {
                scope: this
                ,beforedestroy: this.removeUploaderListeners
            }
        });

        this.callParent(arguments);
    }

    ,initChildItems: function() {
        this.getTextInputField();

        this.attachFileButton = new Ext.button.Button({
            qtip: L.AttachFile
            ,text: ''
            ,iconCls: 'i-attach'
            ,hidden: (Ext.isEmpty(this.params) || Ext.isEmpty(this.params.id))
            ,width: 24
            ,scope: this
            ,handler: this.onAttachFileClick
        });

        this.filesLabel = new Ext.form.field.Display({
            cls: 'click'
        });

        this.messageToolbar = new Ext.Toolbar({
            hidden: false
            ,style: 'padding: 0; border: 0; background-color:  transparent;' // background-color: transparent;
            ,items: [
                this.attachFileButton
                ,this.filesLabel
                ,'->'
                ,{
                    text: L.Reply
                    ,scope: this
                    ,handler: this.onAddCommentClick
                }
            ]
        });


        this.items = [
            {
                xtype: 'label'
                ,width: 44
                ,html: '<img class="i32" src="/' + App.config.coreName +
                        '/photo/' + App.loginData.id + '.jpg?32=' +
                        CB.DB.usersStore.getPhotoParam(App.loginData.id) +
                        '" title="' + getUserDisplayName(true) + '">'
            }
            ,{
                xtype: 'panel'
                ,flex: 1
                ,layout: 'anchor'
                ,padding: '0px 3px 5px 5px'
                ,autoHeight: true
                ,boder: false
                ,bodyCls: 'x-panel-white'
                ,bodyStyle: 'border: 0'
                ,items: [
                    this.messageField
                    ,this.messageToolbar
                ]
            }
        ];
    }

    ,getTextInputField: function(config) {

        if(Ext.isEmpty(this.messageField)) {
            var cfg = {
                emptyText: L.WriteComment + '...'
                ,anchor: '100%'
                ,grow: true
                ,growMin: 10
                ,enableKeyEvents: true
                ,cls: "comment-input"
                ,style: 'margin-top: 5px; font-family: arial,sans-serif; font-size: 12px'

                ,plugins: [
                    {
                        ptype: 'CBPluginFieldDropDownList'
                    }
                ]

                ,listeners: {
                    scope: this
                    ,keypress: this.onMessageBoxKeyPress
                    ,autosize: this.onMessageBoxAutoSize
                    ,focus: function(field) {
                        field.focused = true;
                    }
                    ,blur: function(field) {
                        delete field.focused;
                    }
                }
            };

            if(config) {
                Ext.apply(cfg, config);
            }

            this.messageField = new Ext.form.TextArea(cfg);
        }

        return this.messageField;
    }

    ,onMessageBoxKeyPress: function(tf, e) {
        if ( ([10, 13].indexOf(e.getKey()) >= 0) && e.ctrlKey) {
            this.onAddCommentClick();
        }
    }

    ,onMessageBoxAutoSize: function(field, width) {
        if(!field.isVisible()) {
            return;
        }
    }

    /**
     * placeholder handler, should be overwriten
     * @param  component b
     * @param  event e
     * @return void
     */
    ,onAddCommentClick: function(b, e) {
        this.fireEvent('addcomment', this.getValue(), e);
    }

    ,getValue: function() {
        return this.messageField.getValue();
    }

    ,reset: function() {
        this.messageField.focus();

        this.messageField.reset();
        this.messageField.autoSize();

        delete this.draftCommentId;

        this.updateFilesLabel();
    }

    ,onAttachFileClick: function(b, e) {
        if(Ext.isEmpty(this.draftCommentId)) {
            this.draftCommentId = Ext.id();
        }

        this.addUploaderListeners();

        App.mainViewPort.fireEvent(
            'fileupload'
            ,{
                pid: this.params.id
                ,draftPid: this.draftCommentId
                ,response: 'autorename'
            }
            ,e
        );
        // App.addFilesToUploadQueue(
        //     field.fileInputEl.dom.files
        //     ,{
        //     }
        // );


        this.updateFilesLabel();
    }

    ,addUploaderListeners: function() {
        var fu = App.getFileUploader();
        if(fu) {
            fu.on(
                'fileuploadend'
                ,this.updateFilesLabel
                ,this
            );
            fu.on(
                'progresschange'
                ,this.updateFilesLabel
                ,this
            );
        }
    }

    ,removeUploaderListeners: function() {
        var fu = App.getFileUploader();

        if(fu) {
            fu.un(
                'fileuploadend'
                ,this.updateFilesLabel
                ,this
            );
            fu.un(
                'progresschange'
                ,this.updateFilesLabel
                ,this
            );
        }
    }

    ,updateFilesLabel: function() {
        var fu = App.getFileUploader()
            ,label = this.filesLabel;

        if(Ext.isEmpty(this.draftCommentId) || Ext.isEmpty(fu)) {
            label.setValue('');
            return;
        }

        var t = ''
            ,store = fu.store
            ,stats = fu.getStatsForPid(this.draftCommentId);

        if(stats.pending > 0) {
            t = Ext.String.uncapitalize(L.Uploading) + ' <span style="color: #555">' +
                Ext.String.repeat('●', stats.total - stats.pending) +
                Ext.String.repeat('○', stats.pending) +
                '</span>';

            label.setValue(t);
        } else {
            if(stats.total > 0) {
                t = stats.total + ' ' + Ext.String.uncapitalize(L.Files);
                label.setValue(t);
                this.filesCount = stats.total;
            } else {
                label.setValue('');
            }
        }
    }
});
