Ext.namespace('CB.notifications');

Ext.define('CB.notifications.View', {
    extend: 'Ext.Panel'

    ,alias: 'widget.CBNotificationsView'

    ,border: false
    ,layout: 'fit'

    ,initComponent: function(){

        //define actions
        this.actions = {
            markAsUnread: new Ext.Action({
                // iconCls: 'im-assignment'
                itemId: 'markAsUnread'
                ,scale: 'medium'
                ,text: L.MarkAsUnread
                ,disabled: true
                ,scope: this
                ,handler: this.onMarkAsUnreadClick
            })
            ,showUnread: new Ext.Action({
                // iconCls: 'im-assignment'
                itemId: 'showUnread'
                ,scale: 'medium'
                ,enableToggle: true
                ,text: L.ShowUnread
                ,scope: this
                ,handler: this.onShowUnreadClick
            })
            ,markAllAsRead: new Ext.Action({
                iconCls: 'im-assignment'
                ,itemId: 'markAllAsRead'
                ,scale: 'medium'
                ,text: L.MarkAllAsRead
                ,scope: this
                ,handler: this.onMarkAllAsReadClick
            })
            ,reload: new Ext.Action({
                iconCls: 'im-refresh'
                ,itemId: 'reload'
                ,scale: 'medium'
                ,tooltip: L.Refresh
                ,scope: this
                ,handler: this.onReloadClick
            })

            ,preview: new Ext.Action({
                itemId: 'preview'
                ,scale: 'medium'
                ,iconCls: 'im-preview'
                ,scope: this
                ,hidden: true
                ,handler: this.onPreviewClick
            })

            ,close: new Ext.Action({
                iconCls: 'im-cancel'
                ,itemId: 'close'
                ,scale: 'medium'
                ,scope: this
                ,handler: this.onCloseClick
            })
        };

        this.defineStore();

        //define toolbar
        this.tbar = new Ext.Toolbar({
            border: false
            ,style: 'background: #ffffff'
            ,defaults: {
                scale: 'medium'
            }
            ,items: [
                this.actions.markAsUnread
                ,this.actions.showUnread
                ,'->'
                ,this.actions.markAllAsRead
                ,this.actions.reload
                ,this.actions.preview
                ,this.actions.close
            ]
        });

        Ext.apply(this, {
            items: [{
                xtype: 'panel'
                ,cls: 'taC'
                ,bodyStyle: 'background-color: #e9eaed'
                ,border: false
                ,scrollable: 'y'
                ,items: [
                    this.getGridConfig()
                ]
            }]
            ,listeners: {
                scope: this
                ,activate: this.onActivateEvent
            }
        });

        this.callParent(arguments);

        this.grid = this.items.getAt(0).items.getAt(0);
        this.checkNotificationsTask = new Ext.util.DelayedTask(
            this.onCheckNotificationsTask
            ,this
        );

        this.selectionDelayTask = new Ext.util.DelayedTask(
            this.onSelectionDelayTask
            ,this
        );

        App.on('cbinit', this.onLogin, this, {delay: 3000, single: true});
    }

    ,defineStore: function() {
        this.store = new Ext.data.DirectStore({
            autoLoad: false
            ,autoDestroy: true
            ,extraParams: {}
            ,pageSize: 200
            ,model: 'Notification'
            ,sorters: [{
                 property: 'action_id'
                 ,direction: 'DESC'
            }]
            ,proxy: new  Ext.data.DirectProxy({
                paramsAsHash: true
                ,directFn: CB_Notifications.getList
                ,reader: {
                    type: 'json'
                    ,successProperty: 'success'
                    ,idProperty: 'ids'
                    ,rootProperty: 'data'
                    ,messageProperty: 'msg'
                }
            })

            ,listeners: {
                scope: this
                ,load: this.onStoreLoad
            }
        });
    }

    ,onStoreLoad: function(store, records, successful, eOpts) {
        var rd = store.proxy.reader.rawData;

        if(rd && (rd.success === true)) {
            this.lastSeenActionId = Ext.valueFrom(rd.lastSeenActionId, 0);
            if((this.lastSeenActionId < 1) && !Ext.isEmpty(records)) {
                this.lastSeenActionId = Ext.valueFrom(records[0].data.action_id, 0);
            }

            this.updateSeenRecords();
        }
    }

    ,updateSeenRecords: function() {
        var visible = this.getEl().isVisible(true);
        this.store.each(
            function(r) {
                var seen = visible || (r.get('action_id') <= this.lastSeenActionId);
                r.set('seen', seen);
            }
            ,this
        );

        this.fireNotificationsUpdated();
    }

    ,fireNotificationsUpdated: function() {
        var readRecs = this.store.queryBy('read', false, false, false, true)
            ,seenRecs = this.store.queryBy('seen', false, false, false, true)
            ,params = {
                total: this.store.getCount()
                ,unread: readRecs.getCount()
                ,unseen: seenRecs.getCount()
            };

        App.fireEvent('notificationsUpdated', params);
    }

    ,getGridConfig: function() {

        var columns = [
            {
                header: 'ID'
                ,width: 80
                ,sortable: false
                ,dataIndex: 'ids'
                ,hidden: true
            },{
                header: L.Action
                ,flex: 1
                ,sortable: false
                ,dataIndex: 'text'
                ,renderer: this.actionRenderer
            }
        ];

        var sm = new Ext.selection.RowModel({
           mode: 'SINGLE'
           ,allowDeselect: true
        });


        var rez = {
            xtype: 'grid'
            ,loadMask: false
            ,border: false
            ,hideHeaders: true
            ,cls: 'notifications-grid'
            ,width: 500
            ,bodyStyle: {
                border: 0
            }
            ,store: this.store
            ,columns: columns
            ,selModel: sm

            ,viewConfig: {
                forceFit: true
                ,loadMask: false
                ,stripeRows: false
                ,emptyText: L.NoData
                ,listeners: {
                    scope: this
                    ,itemcontextmenu: this.onItemContextMenu
                }
            }

            ,features: [{
                ftype: 'rowbody'
                ,setupRowData: this.setupRowBodyData
            }]
            ,listeners:{
                scope: this
                ,rowclick: this.onRowClick
                ,selectionchange: this.onSelectionChange
            }

        };

        return rez;
    }

    ,actionRenderer: function(v, m, r, ri, ci, s){
        var uid = r.get('user_id')
            ,rez = ''; //<span class="i-preview action-btn" title="' + L.Preview + '">&nbsp;</span> ';

        if(r.get('expandable')) {
            if(Ext.isEmpty(r.get('body'))) {
                rez += '<span class="i-bullet-arrow-down action-btn" title="' + L.Expand + '">&nbsp;</span>';
            } else {
                rez += '<span class="i-bullet-arrow-up action-btn" title="' + L.Collapse + '">&nbsp;</span>';
            }
        }

        rez += '<table cellpadding="0" cellspacing="0" border="0">' +
                '<tr><td style="padding: 3px" class="vaT"><img class="i32" src="/' +
            App.config.coreName +
            '/photo/' + uid + '.jpg?32=' +
            CB.DB.usersStore.getPhotoParam(uid) +
            '"></td><td style="padding-top: 3px" class="pl7 vaT notif">' +
            v + '</td></tr></table>'
            ;

        m.tdCls = r.get('read') ? '': 'notification-record-unread';

        return rez;
    }

    ,setupRowBodyData: function(record, rowIndex, rowValues) {
        if(Ext.isEmpty(record.get("body"))) {
            rowValues.rowBodyCls = this.rowBodyHiddenCls;

            return;
        }

        var headerCt = this.view.headerCt,
            colspan = headerCt.getColumnCount();

        // Usually you would style the my-body-class in CSS file
        Ext.apply(rowValues, {
            rowBody: record.get("body"),
            rowBodyCls: "my-body-class",
            rowBodyColspan: colspan
        });
    }

    ,onRowClick: function(grid, record, tr, rowIndex, e, eOpts) {
        var el = e.getTarget('.obj-ref')
            ,selectionData = null;
        if(el) {
            App.openObjectWindow({
                id: el.getAttribute('itemid')
                ,template_id: el.getAttribute('templateid')
                ,name: el.getAttribute('title')
            });

            selectionData = {
                id: el.getAttribute('itemid')
                ,read: d.read
            };
        }

        el = e.getTarget('.action-btn');
        if(el) {
            switch(el.title) {
                case L.Expand:
                    record.set('body', L.LoadingData + ' ...');
                    el.classList.add('i-bullet-arrow-up');
                    el.classList.remove('i-bullet-arrow-down');
                    el.title = L.Collapse;
                    CB_Notifications.getDetails(
                        {
                            ids: record.get('ids')
                        }
                        ,this.onGetDetailsProcess
                        ,this
                    );
                    break;

                case L.Collapse:
                    record.set('body', null);
                    el.classList.add('i-bullet-arrow-down');
                    el.classList.remove('i-bullet-arrow-up');
                    el.title = L.Expand;
                    break;

                // case L.Preview:
                //     selectionData = {
                //         id: record.get('object_id')
                //         ,read: record.get('read')

                //     };
                //     break;

            }
        }

        if(selectionData) {
            //set cuttentSelection so that browser controller gets data that data
            //to show on preview expand
            this.currentSelection = [selectionData];
            this.onPreviewClick();

            this.fireEvent('selectionchange', selectionData);
        }
    }

    ,onGetDetailsProcess: function(r, e) {
        if(r.success !== true) {
            return;
        }

        var rec = this.store.findRecord('ids', r.ids, 0, false, false, true);
        if(rec) {
            rec.set('body', r.data);
        }
    }

    ,onSelectionChange: function (grid, selected, eOpts) {
        this.currentSelection = selected;
        //start 3 seconds delayed task to mark the notification as read
        this.selectionDelayTask.delay(3);

        if(!Ext.isEmpty(selected)) {
            var d = selected[0].data;

            this.actions.markAsUnread.setDisabled(!selected[0].get('read'));

            this.fireEvent(
                'selectionchange',
                {
                    'id': selected[0].data.object_id
                    ,'read': selected[0].data.read
                }
            );
        } else {
            this.actions.markAsUnread.setDisabled(true);
        }
    }

    ,onSelectionDelayTask: function() {
        var recs = this.grid.getSelectionModel().getSelection();

        if(Ext.isEmpty(recs) || (recs[0].get('read'))) {
            return;
        }

        CB_Notifications.markAsRead(
            {
                id: recs[0].get('id')
                ,ids: recs[0].get('ids')
            }
            ,this.onMarkAsRead
            ,this
        );
    }

    ,onMarkAsRead: function(r, e) {
        if(!r || (r.success !== true)) {
            return;
        }

        var rec = this.store.findRecord('id', r.data.id);
        if(rec) {
            rec.set('read', true);
            this.actions.markAsUnread.setDisabled(false);
        }

        this.fireNotificationsUpdated();
    }

    ,onMarkAsUnreadClick: function(b, e) {
        var recs = this.grid.getSelectionModel().getSelection();

        CB_Notifications.markAsUnread(
            {
                id: recs[0].get('id')
                ,ids: recs[0].get('ids')
            }
            ,this.onMarkAsUnreadProcess
            ,this
        );
    }

    ,onMarkAsUnreadProcess: function(r, e) {
        if(!r || (r.success !== true)) {
            return;
        }

        var rec = this.store.findRecord('id', r.data.id);
        if(rec) {
            rec.set('read', false);
            this.actions.markAsUnread.setDisabled(true);
        }

        this.fireNotificationsUpdated();
    }

    ,onShowUnreadClick: function(b, e) {
        if(b.pressed) {
            this.store.filter('read', false);
        } else {
            this.store.clearFilter();
        }
    }

    ,onActivateEvent: function() {
        var fr = this.store.first()
            ,lastId = (fr && fr.get) ? fr.get('action_id') : 0;

        this.grid.getView().refresh();
        this.grid.view.scrollTo(0, 0);

        if (this.lastSeenActionId != lastId) {
            CB_Notifications.updateLastSeenActionId(
                lastId
                ,function(r, e) {
                    if(r && (r.success === true)) {
                        this.lastSeenActionId = lastId;
                        this.updateSeenRecords();
                    }
                }
                ,this
            );
        }
    }

    ,onLogin: function() {
        this.store.load();

        this.checkNotificationsTask.delay(1000 * 60 * 1); //1 minute

        //add listeners for object panel to toggle preview action
        var op = App.explorer.objectPanel;

        this.actions.preview.setHidden(op.getCollapsed() === false);

        op.on(
            'expand'
            ,function() {
                this.actions.preview.setHidden(true);
            }
            ,this
        );

        op.on(
            'collapse'
            ,function() {
                this.actions.preview.setHidden(false);
            }
            ,this
        );
    }

    ,onCheckNotificationsTask: function() {
        var rec = this.store.first()
            ,params= {};
        if(rec) {
            params.fromId = rec.get('action_id');
        }

        CB_Notifications.getNew(
            params
            ,this.processGetNew
            ,this
        );

        this.checkNotificationsTask.delay(1000 * 60 * 1); //1 min
    }

    ,processGetNew: function(r, e) {
        if(!r || (r.success !== true)) {
            return;
        }

        if(!Ext.isEmpty(r.data)) {
            var oldRec
                ,modelName = this.store.getModel().getName();

            for (var i = 0; i < r.data.length; i++) {
                oldRec = this.store.findRecord('ids', r.data[i].ids, 0, false, false, true);
                if(oldRec) {
                    this.store.remove(oldRec);
                }

                this.store.addSorted(
                    Ext.create(modelName, r.data[i])
                );
            }

            this.grid.getView().refresh();
            this.grid.view.scrollTo(0, 0);

            if(this.getEl().isVisible(true)) {
                this.onActivateEvent();
            } else {
                this.updateSeenRecords();
            }
        }

        if(r.lastSeenActionId && (r.lastSeenActionId > this.lastSeenActionId)) {
            this.lastSeenActionId = r.lastSeenActionId;
            this.updateSeenRecords();
        }
    }

    /**
     * handler for mark all as read button
     * @param  object b
     * @param  object e
     * @return void
     */
    ,onMarkAllAsReadClick: function(b, e) {
        CB_Notifications.markAllAsRead(
            this.onReloadClick
            ,this
        );
    }

    ,onReloadClick: function(b, e) {
        this.store.reload();
    }

    /**
     * handler for preview toolbar button
     * @param  button b
     * @param  evente
     * @return void
     */
    ,onPreviewClick: function(b, e) {
        App.explorer.objectPanel.expand();
        this.actions.preview.hide();
    }

    ,onCloseClick: function(b, e) {
        App.mainViewPort.onToggleNotificationsViewClick(b, e);
    }

    ,onItemContextMenu: function(view, record, item, index, e, eOpts) {
        e.stopEvent();
        if(Ext.isEmpty(this.contextMenu)){
            this.contextMenu = new Ext.menu.Menu({
                items: [
                    this.actions.markAsUnread
                ]
            });

        }
        this.contextMenu.node = record;

        this.contextMenu.showAt(e.getXY());
    }
});
