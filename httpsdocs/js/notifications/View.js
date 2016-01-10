Ext.namespace('CB.notifications');

Ext.define('CB.notifications.View', {
    extend: 'Ext.Panel'

    ,alias: 'widget.CBNotificationsView'

    ,border: false
    ,layout: 'fit'

    ,initComponent: function(){

        //define actions
        this.actions = {
            markAllAsRead: new Ext.Action({
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
                this.actions.markAllAsRead
                ,'->'
                ,this.actions.reload
                ,this.actions.preview
            ]
        });

        Ext.apply(this, {
            items: [
                this.getGridConfig()
            ]
            ,listeners: {
                scope: this
                ,activate: this.onActivateEvent
            }
        });

        this.callParent(arguments);

        this.grid = this.items.getAt(0);
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
            this.lastSeenActionId = rd.lastSeenActionId;
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
            }

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
            ,rez = '<table cellpadding="0" cellspacing="0" border="0">' +
                '<tr><td style="padding: 3px"><img class="i32" src="/' +
            App.config.coreName +
            '/photo/' + uid + '.jpg?32=' +
            CB.DB.usersStore.getPhotoParam(uid) +
            '"></td><td style="padding-top: 3px" class="pl7 vaT notif">' + v + '</td></tr></table>'
            ;

        m.tdCls = r.get('read') ? '': 'notification-record-unread';

        return rez;
    }

    ,onRowClick: function(grid, record, tr, rowIndex, e, eOpts) {
        var el = e.getTarget('.obj-ref');
        if(el) {
            App.openObjectWindow({
                id: el.getAttribute('itemid')
                ,template_id: el.getAttribute('templateid')
                ,name: el.getAttribute('title')
            });
        }

        if(this.lastSelectedRecord == record) {
            this.onSelectionChange(grid, [record], eOpts);
        }
    }

    ,onSelectionChange: function (grid, selected, eOpts) {
        this.lastSelectedRecord = selected[0];
        //start 3 seconds delayed task to mark the notification as read
        this.selectionDelayTask.delay(3);

        if(!Ext.isEmpty(selected)) {
            var d = selected[0].data;

            this.fireEvent(
                'selectionchange'
                ,{
                    id: d.object_id
                    ,read: d.read
                }
            );
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
        }

        this.fireNotificationsUpdated();
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

        if(r.lastSeenId && (r.lastSeenId > this.lastSeenActionId)) {
            this.lastSeenActionId = r.lastSeenId;
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
});
