Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Comments = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){

        var tpl = new Ext.XTemplate(
            // <div>
            //     View 12 more comments
            // </div>

            '<table class="block-plugin" style="margin:0">'
            ,'<tpl for="data">'
            ,'<tr>'
            ,'    <td class="obj">'
            ,'        <img class="i32" src="/photo/{cid}.jpg" title="{user}">'
            ,'    </td>'
            ,'    <td>'
            ,'        {[ Ext.util.Format.nl2br(Ext.util.Format.htmlEncode(values.content))]}'
            ,'        <div class="gr" title="{[ displayDateTime(values.cdate) ]}">{cdate_text}</div>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</table>'
        );

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,anchor: '100%'
            ,autoHeight: true
            ,itemSelector:'tr'
        });

        this.messageField = new Ext.form.TextArea({
            emptyText: 'Write a comment...'
            ,flex: 1
            ,height: 32
            // ,autoHeight: true
            ,enableKeyEvents: true
            ,style: 'margin-top: 5px; font-family: \'lucida grande\',tahoma,verdana,arial,sans-serif; font-size: 11px'
            ,listeners: {
                scope: this
                ,keypress: this.onMessageBoxKeyPress
            }
        });
        this.loadLabel = new Ext.form.Label({
            flex: 1
            ,height: 32
            ,cls: 'msg-load'
            ,html: '<div class="d-loader">' + L.sending + ' ... </div>'
            ,hidden: true
        });
        this.messageField.on('focus', this.messageField.syncSize, this.messageField);

        Ext.apply(this, {
            title: L.Comments
            ,cls: 'obj-plugin block-plugin-comments'
            ,autoHeight: true
            ,layout: 'anchor'
            ,anchor: '100%'
            ,items: [
                this.dataView
                ,{
                    xtype: 'compositefield'
                    ,layout: 'hbox'
                    ,cls: 'msg-box'
                    ,autoHeight: true
                    ,items: [
                        {
                            xtype: 'label'
                            ,html: '<img class="i32" src="/photo/' + App.loginData.id + '.jpg" title="' + getUserDisplayName(true) + '">'
                        }
                        ,this.messageField
                        ,this.loadLabel
                    ]
                }
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
        }
        this.doLayout(false, true);
    }

    ,onMessageBoxKeyPress: function(tf, e) {
        this.messageField.syncSize();
        this.syncSize();

        if ((e.getKey() == 10) && e.hasModifier()) {
            var msg = tf.getValue().trim();

            if(Ext.isEmpty(msg)) {
                return;
            }
            this.messageField.hide();
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
    }

    ,onAddCommentProcess: function(r, e) {
        this.messageField.setValue('');
        this.loadLabel.hide();
        this.messageField.show();
        if(r.success !== true) {
            return;
        }

        if(Ext.isEmpty(this.loadedData.data)) {
            this.loadedData.data = [];
        }
        this.loadedData.data.push(r.data);
        this.onLoadData(this.loadedData);
    }


});

Ext.reg('CBObjectsPluginsComments', CB.objects.plugins.Comments);
