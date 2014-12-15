Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Comments', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginComments'

    ,initComponent: function(){

        this.actions = {
            edit: new Ext.Action({
                text: L.Edit
                ,scope: this
                ,handler: this.onEditClick
            })
            ,remove: new Ext.Action({
                text: L.Delete
                ,iconCls: 'i-trash'
                ,scope: this
                ,handler: this.onRemoveClick
            })
        };

        var tpl = new Ext.XTemplate(
            // <div>
            //     View 12 more comments
            // </div>

            '<table class="block-plugin" style="margin:0">'
            ,'<tpl for=".">'
            ,'<tr>'
            ,'    <td class="obj">'
            ,'        <img class="i32" src="/' + App.config.coreName + '/photo/{cid}.jpg?32={[ CB.DB.usersStore.getPhotoParam(values.cid) ]}" title="{user}">'
            ,'    </td>'
            ,'    <td>'
            ,'      <tpl if="cid == App.loginData.id">'
            ,'          <span class="i-bullet-arrow-down comment-actions-button">&nbsp;</span>'
            ,'      </tpl>'
            ,'        <b class="user">{[ values.user.split("\\n")[0]]}</b>'
            ,'        {[ Ext.util.Format.nl2br(values.content)]}'
            ,'        <div class="gr" title="{[ displayDateTime(values.cdate) ]}">{cdate_text}</div>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</table>'
        );

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,store: Ext.create('Ext.data.JsonStore', {
                fields: [
                   {name: 'id', type: 'int'}
                   ,{name: 'pid', type: 'int'}
                   ,{name: 'template_id', type: 'int'}
                   ,{name: 'cid', type: 'int'}
                   ,'user'
                   ,'cdate'
                   ,'cdate_text'
                   ,'content'
                ]
                ,proxy: {
                    type: 'memory'
                    ,reader: {
                        type: 'json'
                    }
                }
            })
            ,region: 'center'
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,itemclick: this.onItemClick
            }
        });

        this.messageField = new Ext.form.TextArea({
            emptyText: 'Write a comment...'
            // ,height: 30
            ,anchor: '100%'
            ,grow: true
            ,growMin: 10
            ,enableKeyEvents: true
            //,baseBodyCls: "comment-input"
            ,style: 'margin-top: 5px; font-family: arial,sans-serif; font-size: 12px'
            // disable until prugin refactored for ExtJS 5
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
                    // field.grow = true;
                    field.focused = true;
                    // this.messageToolbar.show();
                }
                ,blur: function(field) {
                    // field.grow = false;
                    delete field.focused;
                    if(!this.mouseOver) {
                        // this.messageToolbar.hide();
                    }
                }
            }
        });

        this.messageToolbar = new Ext.Toolbar({
            height: 24
            ,hidden: false
            ,style: 'padding: 0; border: 0; background-color:  #f1f1f1;' // background-color: transparent;
            ,items: [
                '->'
                ,{
                    text: 'Reply'
                    ,scope: this
                    ,handler: this.onAddCommentClick
                }
            ]
        });

        this.loadLabel = new Ext.panel.Panel({
            height: 40
            ,anchor: '100%'
            ,padding: 0
            ,bodyPadding: 0
            ,boder: false
            ,bodyBoder: false
            ,bodyStyle: 'border: 0'
            ,header: false
            ,cls: 'msg-load'
            ,html: '<div class="d-loader">' + L.sending + ' ... </div>'
            ,hidden: true
        });

        this.addCommentPanel = new Ext.Panel({
            layout: 'border'
            ,height: 65
            ,border: false
            ,items: [
                {
                    xtype: 'label'
                    ,region: 'west'
                    ,width: 50
                    ,html: '<img class="i32" src="/' + App.config.coreName + '/photo/' + App.loginData.id + '.jpg?32=' + CB.DB.usersStore.getPhotoParam(App.loginData.id) + '" title="' + getUserDisplayName(true) + '">'
                }
                ,{
                    xtype: 'panel'
                    ,region: 'center'
                    ,layout: 'anchor'
                    ,padding: '0px 3px 0px 5px'
                    ,autoHeight: true
                    ,boder: false
                    ,bodyCls: 'x-panel-white'
                    ,bodyStyle: 'border: 0'
                    ,items: [
                        this.messageField
                        ,this.messageToolbar
                        ,this.loadLabel
                    ]
                }
            ]
            ,listeners: {
                scope: this

                ,afterrender: function(cmp) {
                    var el = cmp.getEl();
                    el.on('mouseenter', this.onCommentPanelMouseEnter, this);
                    el.on('mouseleave', this.onCommentPanelMouseLeave, this);
                }

                ,beforedestroy: function(cmp) {
                    var el = cmp.getEl();
                    el.un('mouseenter', this.onCommentPanelMouseEnter, this);
                    el.un('mouseleave', this.onCommentPanelMouseLeave, this);
                }
            }
        });

        Ext.apply(this, {
            title: L.Comments
            ,cls: 'obj-plugin block-plugin-comments'
            ,autoHeight: true
            ,anchor: '100%'
            ,border: false
            ,items: [
                this.dataView
                ,this.addCommentPanel
            ]
        });

        this.callParent(arguments);

        this.enableBubble(['getdraftid']);
    }

    ,onLoadData: function(r, e) {
        this.loadedData = r;
        this.dataView.store.loadData(r.data);
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
        this.addCommentPanel.setHeight(field.getHeight() + 36);
    }

    ,onGetDraftIdCallback: function(draftId) {
        if(isNaN(draftId)) {
            return;
        }

        this.params.id = draftId;

        this.onAddCommentClick();
    }

    ,onAddCommentClick: function(b, e) {
        if(isNaN(this.params.id)) {
            this.fireEvent(
                'getdraftid'
                ,this.onGetDraftIdCallback
                ,this
            );
            return ;
        }

        var msg = this.messageField.getValue().trim();

        if(Ext.isEmpty(msg)) {
            return;
        }

        // this.messageField.grow = false;
        // this.messageField.setHeight(30);
        this.messageField.reset();
        this.messageField.autoSize();
        // this.onMessageBoxAutoSize(this.messageField);
        this.messageField.hide();
        // this.messageToolbar.hide();

        this.loadLabel.show();

        CB_Objects.addComment(
            {
                id: this.params.id
                ,msg: msg
            }
            ,this.onAddCommentProcess
            ,this
        );
    }

    ,onAddCommentProcess: function(r, e) {
        this.loadLabel.hide();

        if(r.success !== true) {
            this.messageField.show();
            return;
        } else {
            this.messageField.reset();
            this.messageField.show();
        }


        if(Ext.isEmpty(this.loadedData.data)) {
            this.loadedData.data = [];
        }
        this.loadedData.data.push(r.data);
        this.onLoadData(this.loadedData);
        this.messageField.focus();
        // this.addCommentPanel.syncSize();
    }

    ,onCommentPanelMouseEnter: function(e, el, o) {
        this.mouseOver = true;
    }

    ,onCommentPanelMouseLeave: function(e, el, o) {
        delete this.mouseOver;
        if(this.messageField.focused !== true) {
            // this.messageToolbar.hide();
        }
    }

    ,onItemClick: function(dataView, record, item, index, e, eOpts) {
        var el = e.getTarget('.comment-actions-button');

        if(el) {
            e.stopEvent();
            this.showActionsMenu(e);
        }
    }

    ,showActionsMenu: function(e) {
        if(!this.actionsMenu) {
            this.actionsMenu = Ext.create(
                'Ext.menu.Menu'
                ,{
                    items: [
                        this.actions.edit
                        ,this.actions.remove
                    ]
                }
            );
        }

        this.actionsMenu.showAt(e.getXY());
        // very strange .. the menu desnt show automaticly (maybe because its beta Ext)
        Ext.defer(this.actionsMenu.show, 10, this.actionsMenu);
    }

    ,onEditClick: function(b, e) {
        var rec = this.dataView.getSelection()[0];

        if(!rec || (rec.get('cid') != App.loginData.id)) {
            return;
        }

        CB_Objects.load({id: rec.get('id')}, this.processEditComment, this);
    }

    ,processEditComment: function(r, e) {
        if(r.success !== true) {
            return;
        }

        this.editingCommentId = r.data.id;

        var ed = App.getTextEditWindow({
            data: {
                value: r.data.name
                ,callback: this.onSubmitEditedComment
                ,scope: this
            }
        });

        ed.show();
    }

    ,onSubmitEditedComment: function(editor, value) {
        CB_Objects.updateComment(
            {
                id: this.editingCommentId
                ,msg: value
            }
            ,this.onEditCommentProcess
            ,this
        );

        delete this.editingCommentId;
    }

    ,onEditCommentProcess: function(r, e) {
        if(r.success !== true) {
            return;
        }

        App.fireEvent('objectchanged', r.data, this);
    }

    ,onRemoveClick: function(b, e) {
        var rec = this.dataView.getSelection()[0];

        if(!rec || (rec.get('cid') != App.loginData.id)) {
            return;
        }

        CB_Objects.removeComment(
            {id: rec.get('id')}
            ,this.processRemoveComment
            ,this
        );
    }

    ,processRemoveComment: function(r, e) {
        if(r.success !== true) {
            return;
        }

        var rec = this.dataView.getSelection()[0];
        if(rec) {
            this.dataView.store.remove(rec);
        }
    }
});
