
Ext.namespace('CB.browser');

Ext.define('CB.browser.NotificationsView', {
    extend: 'Ext.Panel'

    ,alias: 'widget.CBBrowserNotificationsView'

    ,border: false
    ,layout: 'fit'

    ,initComponent: function(){

        //define actions
        this.actions = {
            markAllAsRead: new Ext.Action({
                iconCls: 'im-assignment'
                ,itemId: 'reload'
                ,scale: 'medium'
                ,text: L.MarkAllAsRead
                ,scope: this
                ,handler: this.onMarkAllAsReadClick
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
            ]
        });

        //add grid component
        this.items = [
            this.getGridConfig()
        ];

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
                ,beforeload: this.onBeforeStoreLoad
                ,load: this.onStoreLoad
            }
        });
    }

    ,onBeforeStoreLoad: function() {

    }

    ,onStoreLoad: function(store, records, successful, eOpts) {
        this.fireNotificationsUpdated();
    }

    ,fireNotificationsUpdated: function() {
        var recs = this.store.queryBy('read', false, false, false, true)
            ,params = {
                total: this.store.getCount()
                ,unread: recs.getCount()
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
                ,itemcontextmenu: this.onItemContextMenu
            }

        };

        return rez;
    }

    ,actionRenderer: function(v, m, r, ri, ci, s){
        var uid = r.get('user_id')
            ,rez = '<table cellpadding="0" cellspacing="0" border="0">' +
                '<tr><td><img class="i32" src="/' +
            App.config.coreName +
            '/photo/{' + uid + '}.jpg' +
            CB.DB.usersStore.getPhotoParam(uid) +
            '"></td><td class="pl10 vaT">' + v + '</td></tr></table>'
            //+ (r.get('read') ? 'read': 'no')
            ;

        return rez;
    }

    ,onRowClick: function(g, ri, e) {

    }

    ,onSelectionChange: function (grid, selected, eOpts) {

        //start 3 seconds delayed task to mark the notification as read
        this.selectionDelayTask.delay(3);

        if(!Ext.isEmpty(selected)) {
            var d = selected[0].data;

            this.fireEvent(
                'selectionchange'
                ,{
                    id: d.object_id
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
        if(r.success !== true) {
            return;
        }

        var rec = this.store.findRecord('id', r.data.id);
        if(rec) {
            rec.set('read', true);
        }

        this.fireNotificationsUpdated();
    }

    ,onItemContextMenu: function(grid, record, item, index, e, eOpts) {

    }

    ,onLogin: function() {
        this.store.load();

        this.checkNotificationsTask.delay(1000 * 60 * 2); //2 minutes
    }

    ,onCheckNotificationsTask: function() {
        var rec = this.store.first()
            ,params= {};
        if(rec) {
            params.lastId = rec.get('ids');
        }

        CB_Notifications.getNewCount(
            params
            ,this.processGetNewCount
            ,this
        );

        this.checkNotificationsTask.delay(1000* 20); //2 minutes
    }

    ,processGetNewCount: function(r, e) {
        if(r.success !== true) {
            return;
        }

        if(r.count > 0) {
            this.store.reload();
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
            {}
            ,function() {
                this.store.reload();
            }
            ,this
        );
    }
});
