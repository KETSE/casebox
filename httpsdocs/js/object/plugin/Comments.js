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
            ,'<div class="load-more click">' + L.ViewMore + '</div>'
            ,'<tpl for=".">'
            ,'<tr>'
            ,'    <td class="obj">'
            ,'        <img class="i32" src="/' + App.config.coreName + '/photo/{cid}.jpg?32={[ CB.DB.usersStore.getPhotoParam(values.cid) ]}" title="{user}">'
            ,'    </td>'
            ,'    <td class="comment">'
            ,'      <div class="comment-text">'
            ,'        <tpl if="cid == App.loginData.id">'
            ,'          <span class="i-bullet-arrow-down comment-actions-button">&nbsp;</span>'
            ,'        </tpl>'
            ,'        <b class="user">{[ values.user.split("\\n")[0]]}</b>'
            ,'        {[ Ext.util.Format.nl2br(values.content)]}'
            ,'      </div>'
            ,'      <div title="' + L.ShowAll + '" class="show-all click"></div>'
            ,'      <div class="gr" title="{[ displayDateTime(values.cdate) ]}">{cdate_text}</div>'
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
                ,containerclick: this.onContainerClick
                ,resize: this.onDataViewResize
            }
        });

        this.messageField = new Ext.form.TextArea({
            emptyText: L.WriteComment + '...'
            // ,height: 30
            ,anchor: '100%'
            ,grow: true
            ,growMin: 10
            ,enableKeyEvents: true
            ,cls: "comment-input"
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
                    text: L.Reply
                    ,scope: this
                    ,handler: this.onAddCommentClick
                }
            ]
        });

        this.addCommentPanel = new Ext.Panel({
            layout: {
                type: 'hbox'
                ,align: 'stretch'
            }
            // ,height: 65
            ,autoHeight: true
            ,border: false
            ,items: [
                {
                    xtype: 'label'
                    ,width: 50
                    ,html: '<img class="i32" src="/' + App.config.coreName + '/photo/' + App.loginData.id + '.jpg?32=' + CB.DB.usersStore.getPhotoParam(App.loginData.id) + '" title="' + getUserDisplayName(true) + '">'
                }
                ,{
                    xtype: 'panel'
                    ,flex: 1
                    ,layout: 'anchor'
                    ,padding: '0px 3px 0px 5px'
                    ,autoHeight: true
                    ,boder: false
                    ,bodyCls: 'x-panel-white'
                    ,bodyStyle: 'border: 0'
                    ,items: [
                        this.messageField
                        ,this.messageToolbar
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

        if(r.total > r.data.length) {
            this.addCls('have-more-items');
        } else {
            this.removeCls('have-more-items');
        }

        this.dataView.store.loadData(r.data);
    }

    /**
     * handler for load more comments click
     * @param  Ext.eventObject e
     * @return void
     */
    ,onLoadMoreClick: function(e) {
        var params = {
            id: this.params.id
        };

        if(this.loadedData && !Ext.isEmpty(this.loadedData.data)) {
            params.beforeId = this.loadedData.data[0].id;
        }

        CB_Objects_Plugins_Comments.loadMore(
            params
            ,this.processLoadMore
            ,this
        );
    }

    /**
     * processing handler for loading more comments from server
     * @param  result r
     * @param  Ext.eventObject e
     * @return void
     */
    ,processLoadMore: function(r, e) {
        if(r.success !== true) {
            App.showException(r);
            return;
        }

        if(Ext.isEmpty(r.data)) {
            return;
        }

        if(Ext.isEmpty(this.loadedData.data)) {
            this.loadedData.data = [];
        }

        this.loadedData.data = r.data.concat(this.loadedData.data);

        this.onLoadData(this.loadedData, e);
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

        this.addCommentPanel.disable();

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
        this.addCommentPanel.enable();

        if(r.success !== true) {
            // show error
            Ext.Msg.alert(L.Error, L.AddCommentError);

            return;
        } else {
            this.messageField.reset();
            this.messageField.autoSize();
            // this.messageField.show();
        }

        if(Ext.isEmpty(this.loadedData.data)) {
            this.loadedData.data = [];
        }
        this.loadedData.data.push(r.data);
        this.onLoadData(this.loadedData);
        this.messageField.focus();
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
            return;
        }

        el = e.getTarget('.obj-ref');
        if(el) {
            e.stopEvent();
            this.openObjectProperties({
                id: el.attributes.href.value.substr(1)
                ,template_id: el.attributes.templateid.value
            });

            return;
        }

        el = e.getTarget('.show-all');
        if(el) {
            e.stopEvent();
            this.onShowAllClick(record, item, index);

            return;
        }

    }

    ,onContainerClick: function(view, e, eOpts) {
        var el = e.getTarget('.load-more');

        if(el) {
            e.stopEvent();
            this.onLoadMoreClick(e);
            return;
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

        var value = (r.data.data && r.data.data['_title'])
            ? r.data.data['_title']
            : r.data.name;

        this.editingCommentId = r.data.id;

        var ed = App.getTextEditWindow({
            data: {
                value: value
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
                ,text: value
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

        //App.fireEvent('objectchanged', r.data, this);

        //replace processed text into loaded data
        var rec = this.dataView.store.findRecord('id', r.data.id, 0, false, false, true);

        if(rec) {
            //remove item from loadedData
            if(Ext.isArray(this.loadedData.data)) {
                var item = Ext.Array.findBy(
                    this.loadedData.data
                    ,function(i) {
                        return (i.id == rec.data.id);
                    }
                    ,this
                );
                if(item) {
                    item.content = r.data.text;
                }
            }

            //remove record from view store
            rec.set('content', r.data.text);

            this.dataView.refresh();
        }
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
            //remove item from loadedData
            if(Ext.isArray(this.loadedData.data)) {
                var item = Ext.Array.findBy(
                    this.loadedData.data
                    ,function(i) {
                        return (i.id == rec.data.id);
                    }
                    ,this
                );
                if(item) {
                    Ext.Array.remove(this.loadedData.data, item);
                }
            }

            //remove record from view store
            this.dataView.store.remove(rec);
        }
    }

    /**
     * expand comment body to see all content when show all button clicked
     * @param  Ext.data.Model record
     * @param  HTMLElement    item
     * @param  int            index
     * @return void
     */
    ,onShowAllClick: function(record, item, index) {
        item.children[1].setAttribute('class', 'comment comment-expanded');
    }

    /**
     * listener to dataview resize event to add css for long comments
     * @param  Ext.Component view
     * @param  int width
     * @param  int height
     * @param  int oldWidth
     * @param  int oldHeight
     * @param  Object eOpts
     * @return void
     */
    ,onDataViewResize: function(view, width, height, oldWidth, oldHeight, eOpts) {
        var dv = this.dataView
            ,store = dv.store
            ,divs = dv.getEl().query('td.comment');

        //iterate comments and see if any exceeds default height
        for (var i = 0; i < divs.length; i++) {
            var txtDiv = divs[i].children[0];
            if(txtDiv.clientHeight < txtDiv.scrollHeight) {
                divs[i].setAttribute('class', 'comment comment-big');
            }
        }
    }
});
