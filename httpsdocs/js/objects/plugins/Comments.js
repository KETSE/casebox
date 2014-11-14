Ext.namespace('CB.objects.plugins');

Ext.define('CB.objects.plugins.Comments', {
    extend: 'CB.objects.plugins.Base'
    ,alias: 'CBObjectsPluginsComments'

    ,initComponent: function(){

        var tpl = new Ext.XTemplate(
            // <div>
            //     View 12 more comments
            // </div>

            '<table class="block-plugin" style="margin:0">'
            ,'<tpl for="data">'
            ,'<tr>'
            ,'    <td class="obj">'
            ,'        <img class="i32" src="/' + App.config.coreName + '/photo/{cid}.jpg?32={[ CB.DB.usersStore.getPhotoParam(values.cid) ]}" title="{user}">'
            ,'    </td>'
            ,'    <td>'
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
            ,region: 'center'
            ,itemSelector:'tr'
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

        CB.objects.plugins.Comments.superclass.initComponent.apply(this, arguments);
    }

    ,onLoadData: function(r, e) {
        this.loadedData = r;
        if(this.rendered) {
            this.dataView.update(r);

        } else {
            this.dataView.data = r;
            this.doLayout();
        }
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

    ,onAddCommentClick: function(b, e) {
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

});
