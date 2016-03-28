Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.ContentItems', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginContentItems'

    ,initComponent: function(){

        this.actions = {
           add: new Ext.Action({
                iconCls: 'i-plus'
                // ,text: L.Add
                ,scope: this
                ,handler: this.onAddClick
            })

            ,permalink: new Ext.Action({
                text: L.Permalink
                ,itemId: 'permalink'
                ,scope: this
                ,handler: this.onPermalinkClick
            })
        };

        var tpl = new Ext.XTemplate(
            '<table class="block-plugin">'
            ,'<tpl for=".">'
            ,'<tr class="{[ (xindex > this.displayLimit) ? \'more\' : ""]}">'
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
            ,'<tpl if="this.itemCount &gt; this.displayLimit">'
            ,'<div class="toggle taC"><u class="click">' + L.ShowAll + '</u></div>'
            ,'</tpl>'
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
            ,cls: 'limited-list'
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,itemclick: this.onItemClick
                ,containerclick: this.onContainerClick
            }
        });

        Ext.apply(this, {
            title: L.Contents
            ,items: this.dataView
            ,listeners: {
                scope: this
                ,beforedestroy: this.onBeforeDestroy
            }
        });

        this.callParent(arguments);

        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
    }

    ,onBeforeDestroy: function(c) {
        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
    }

    ,onObjectsDeleted: function(ids) {
        this.store.deleteIds(ids);
    }

    ,onLoadData: function(r, e) {
        if(!Ext.isEmpty(r.data)) {
            for (var i = 0; i < r.data.length; i++) {
                r.data[i].iconCls = getItemIcon(r.data[i]);
            }
            this.store.loadData(r.data);
        }

        //set list display items limit
        this.dataView.tpl.displayLimit = Ext.valueFrom(r.limit, 5);
        this.dataView.tpl.itemCount = this.store.getCount();

        if(!Ext.isEmpty(r.title)) {
            this.params.title = r.title;
            this.updateTitle(r.title);
        }

        if(!Ext.isEmpty(r.menu)) {
            this.createMenu = r.menu;
        }
    }

    ,updateTitle: function(title)  {
        if(!title && this.params) {
            title = this.params.title;
        }

        if(!Ext.isEmpty(title)) {
            var count = this.store.getCount()
                ,total = (count > 0) ? '(' + count + ')' : '';

            title = title.replace('({total})', total);
            this.setTitle(title);
        }

        return title;
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

    ,onContainerClick: function(view, e, eOpts) {
        var el = e.getTarget('.toggle');

        if(el) {
            this.addCls('list-expanded');
            this.updateLayout();
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
