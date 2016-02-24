Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.TimeTracking', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginTimeTracking'

    ,initComponent: function(){

        this.actions = {
           start: new Ext.Action({
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
            ,'    <td class="obj">'
            ,'        <img class="i16u {iconCls}" src="'+ Ext.BLANK_IMAGE_URL +'">'
            ,'    </td>'
            ,'    <td>'
            ,'        <span class="click">{name}</span><br />'
            ,'        <span class="gr" title="{[ displayDateTime(values.cdate) ]}">{user}, {ago_text}</span>'
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
            ,items: this.dataView
        });

        this.callParent(arguments);
    }

    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }
        for (var i = 0; i < r.data.length; i++) {
            r.data[i].iconCls = getItemIcon(r.data[i]);
        }
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
            this.openObjectProperties(this.store.getAt(index).data);
        }
    }

    ,showActionsMenu: function(coord){
        if(Ext.isEmpty(this.puMenu)) {
            this.puMenu = new Ext.menu.Menu({
                items: [
                   {
                        text: L.Open
                        ,scope: this
                        ,handler: this.onOpenClick
                    },'-',{
                        text: L.Delete
                        ,iconCls: 'i-trash'
                        ,scope: this
                        ,handler: this.onDeleteItemClick
                    }
                    ,this.actions.permalink
                ]
            });
        }

        this.puMenu.showAt(coord);
    }

    ,onDeleteItemClick: function(b, e) {
        App.mainViewPort.onDeleteObject(this.clickedItemData);
    }

    ,onOpenClick: function(b, e) {
        this.openObjectProperties(this.clickedItemData);
    }

    ,getToolbarItems: function() {
        return [this.actions.add];
    }

    ,onAddClick: function(b, e) {
        if(this.pmenu) {
            this.pmenu.destroy();
        }
        this.pmenu = new Ext.menu.Menu({items: []});

        updateMenu(
            {menu: this.pmenu}
            ,this.createMenu
            ,this.onCreateObjectClick
            ,this
        );
        this.pmenu.showBy(b.getEl());
    }

    ,onCreateObjectClick: function(b, e) {
        var d = b.config.data;
        d.pid = this.params.id;
        d.path = this.params.path;
        this.fireEvent('createobject', d, e);
    }

    ,onPermalinkClick: function(b, e) {
        window.prompt(
            'Copy to clipboard: Ctrl+C, Enter'
            , window.location.origin + '/' + App.config.coreName + '/view/' + this.clickedItemData.id + '/'
        );
    }
});
