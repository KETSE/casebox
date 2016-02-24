Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Comments', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginComments'

    ,commentFieldConfig: {
        xtype: 'CBFieldComment'
    }

    ,initComponent: function(config) {
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
            '<table class="block-plugin" style="margin:0">'
            ,'<div class="load-more click">' + L.MoreCommentsHint
            ,'  <span class="fr cG">{[this.totalText]}</span>'
            ,'</div>'
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
            ,'      {[values.files ? "<div>" + values.files + "</div>" : "" ]}'
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
            ,deferInitialRefresh: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,itemclick: this.onItemClick
                ,containerclick: this.onContainerClick
                ,resize: this.onDataViewResize
            }
        });

        var cfg = Ext.apply(
            this.commentFieldConfig
            ,{
                params: this.params

                ,listeners: {
                    scope: this
                    ,addcomment: this.onAddCommentClick
                }
            }
        );
        this.addCommentLink = Ext.create({
            xtype: 'component'
            ,autoEl: {
                tag: 'div'
                ,html: L.AddComment
                ,cls: 'fwB click icon-padding i-chat-bubble cG'
            }
            ,margin: '3 10 3 10'
            ,border: false
            ,listeners: {
                scope: this
                ,afterrender: function(c, eOpts) {
                    c.getEl().on('click', this.onAddCommentLinkClick, this);
                }
            }
        });

        this.addCommentField = Ext.create(cfg);

        if(this.initialConfig.header !== false) {
            this.title = L.Comments;
        }

        Ext.apply(this, {
            cls: 'obj-plugin block-plugin-comments'
            ,autoHeight: true
            ,anchor: '100%'
            ,border: false
            ,bodyStyle: 'padding-top: 3px'
            ,items: [
                this.dataView
                // ,this.addCommentField
            ]
        });

        if (this.initialConfig.showAddLabel) {
            this.items.push(this.addCommentLink);
        } else {
            this.items.push(this.addCommentField);
        }

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
        this.dataView.tpl.totalText = r.data.length + ' ' + L.of + ' ' + r.total;
        this.dataView.store.loadData(r.data);

        this.attachElementsEvents();

        Ext.defer(this.onDataViewResize, 1500, this);
    }

    ,attachElementsEvents: function() {
        var el  = this.getEl();

        if(el) {
            var lm = el.down('div.load-more');

            if(lm && !lm.hasListener('click')) {
                lm.on('click', this.onLoadMoreClick, this);
            }
        }
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
        if(!r || (r.success !== true)) {
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

        var panel = this.up('panel')
            ,scrollable = false
            ,scrollPosition;

        while(!scrollable && panel) {
            scrollable = panel.getScrollable();
            panel = panel.up('panel');
        }

        if(scrollable) {
            scrollPosition = scrollable.getPosition();
        }

        this.onLoadData(this.loadedData, e);

        if(scrollable) {
            scrollable.scrollTo(scrollPosition);
        }
    }

    ,onGetDraftIdCallback: function(draftId) {
        if(isNaN(draftId)) {
            return;
        }

        this.params.id = draftId;

        this.onAddCommentClick();
    }

    ,onAddCommentClick: function(comment, e) {
        if(isNaN(this.params.id)) {
            this.fireEvent(
                'getdraftid'
                ,this.onGetDraftIdCallback
                ,this
            );
            return ;
        }

        var msg = this.addCommentField.getValue().trim();

        if(Ext.isEmpty(msg)) {
            return Ext.Msg.alert(L.Error, L.SpecifyComment);
        }

        this.addCommentField.disable();

        var p = {
            id: this.params.id
            ,msg: msg
        };

        if(this.addCommentField.draftCommentId) {
            p.draftId = this.addCommentField.draftCommentId;
        }

        CB_Objects.addComment(
            p
            ,this.onAddCommentProcess
            ,this
        );
    }

    ,onAddCommentProcess: function(r, e) {
        this.addCommentField.enable();

        if(!r || (r.success !== true)) {
            // show error
            Ext.Msg.alert(L.Error, L.AddCommentError);

            return;
        } else {

            if(Ext.isEmpty(this.loadedData.data)) {
                this.loadedData.data = [];
            }

            this.loadedData.data.push(r.data);
            this.onLoadData(this.loadedData);

            this.addCommentField.reset();
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

            var name = el.attributes.title
                ? el.attributes.title.value
                : el.innerText;

            this.openObjectProperties({
                id: el.attributes.itemid.value
                ,template_id: el.attributes.templateid.value
                ,name: name
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
            el = Ext.get(el);
            if(!el.hasListener('click')) {
                e.stopEvent();
                this.onLoadMoreClick(e);
            }
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
        if(!r || (r.success !== true)) {
            return;
        }

        var value = (r.data.data && r.data.data['_title'])
            ? r.data.data['_title']
            : r.data.name;

        this.editingCommentId = r.data.id;

        var ed = new CB.TextEditWindow({
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
        if(!r || (r.success !== true)) {
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
                    item.content = r.data.content;
                }
            }

            //remove record from view store
            rec.set('content', r.data.content);

            this.dataView.refresh();

            Ext.defer(this.onDataViewResize, 1500, this);
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
        if(!r || (r.success !== true)) {
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
        this.updateLayout();
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
            ,el = dv.getEl();

        if(Ext.isEmpty(el)) {
            return;
        }
        var divs = dv.getEl().query('td.comment');

        //iterate comments and see if any exceeds default height
        for (var i = 0; i < divs.length; i++) {
            var txtDiv = divs[i].children[0];
            if(txtDiv.clientHeight < txtDiv.scrollHeight) {
                divs[i].setAttribute('class', 'comment comment-big');
            }
        }

        this.updateLayout();
    }

    ,onAddCommentLinkClick: function() {
        this.remove(this.addCommentLink);
        this.add(this.addCommentField);
        this.addCommentField.reset();
    }
});
