Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.TimeTracking', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginTimeTracking'

    ,xtype: 'CBObjectPluginTimeTracking'

    ,cls: 'obj-plugin'

    ,initComponent: function(){

        this.actions = {
           add: new Ext.Action({
                iconCls: 'i-plus'
                ,scope: this
                ,handler: this.onAddClick
            })

           ,start: new Ext.Action({
                iconCls: 'i-start'
                ,scope: this
                ,handler: this.onStartClick
            })
           ,stop: new Ext.Action({
                iconCls: 'i-stop'
                ,scope: this
                ,handler: this.onStopClick
            })
        };

        var tpl = new Ext.XTemplate(
            '<table class="block-plugin">'
            ,'<tpl for=".">'
            ,'<tr>'
            ,'    <td style="width:60%">'
            ,'        <span class="click" title="{[ displayDateTime(values.cdate) ]}">{[ displayDateTime(values.date, App.longDateFormat) ]}</span> &nbsp;'
            ,'        {user}'
            ,'    </td>'
            ,'    <td style="width:20%">'
            ,'        <span class="click">{time} / {cost}</span>'
            ,'    </td>'
            ,'    <td class="elips">'
            ,'        <span class="click menu"></span>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</table>'
        );

        this.store = new Ext.data.JsonStore({
            autoDestroy: true
            ,model: 'ContentItem'
            ,proxy: new  Ext.data.MemoryProxy()
        });

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,store: this.store
            ,autoHeight: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,itemclick: this.onItemClick
            }
        });

        Ext.apply(this, {
            title: L.TimeSpent
            ,autoHeight: true
            ,anchor: '100%'
            ,items: [
                this.dataView
            ]

        });

        this.callParent(arguments);

        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
    }

    ,onObjectsDeleted: function(ids) {
        this.store.deleteIds(ids);
    }

    ,onLoadData: function(r, e) {
        this.store.loadData(r.data);
    }

    ,onItemClick: function (cmp, record, item, index, e, eOpts) {//dv, index, el, e
        var te = Ext.get(e.getTarget());
        if(!te) {
            return;
        }

        if(te.hasCls('menu')) {
            this.clickedItemData = this.store.getAt(index).data;
            this.showActionsMenu(e.getXY());

        } else if(te.hasCls('click')) {
            this.clickedItemData = this.store.getAt(index).data;
            this.onOpenClick(cmp, e);
        }
    }

    ,showActionsMenu: function(coord){
        if(Ext.isEmpty(this.puMenu)) {
            this.puMenu = new Ext.menu.Menu({
                items: [
                   {
                        text: L.Edit
                        ,scope: this
                        ,handler: this.onOpenClick
                    },'-',{
                        text: L.Delete
                        ,iconCls: 'i-trash'
                        ,scope: this
                        ,handler: this.onDeleteItemClick
                    }
                ]
            });
        }

        this.puMenu.showAt(coord);
    }

    ,onDeleteItemClick: function(b, e) {
        App.mainViewPort.onDeleteObject(this.clickedItemData);
    }

    ,onOpenClick: function(b, e) {
        var data = Ext.clone(this.clickedItemData);
        data.view = 'edit';
        data.modal = true;

        this.openObjectProperties(data);
    }

    ,getToolbarItems: function() {
        return [this.actions.add];
    }

    ,onAddClick: function(b, e) {
        var tpl = CB.DB.templates.findRecord('type', 'time_tracking', 0, false, false, true);

        if(Ext.isEmpty(tpl)) {
            return Ext.Msg.alert(L.Error, 'Time tracking template not found.');
        }

        var d = {
            pid: this.params.id
            ,template_id: tpl.get('id')
            ,path: this.params.path
            ,alignWindowTo: e.getXY()
            ,modal: true
        };

        App.mainViewPort.fireEvent('createobject', d, e);
    }
});
