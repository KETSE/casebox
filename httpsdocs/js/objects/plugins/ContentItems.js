Ext.namespace('CB.objects.plugins');

CB.objects.plugins.ContentItems = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){

        this.actions = {
           add: new Ext.Action({
                text: L.Add
                ,iconCls: 'i-plus'
                ,scope: this
                ,handler: this.onAddClick
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
            ,'        <span class="gr">{user}, {ago_text}</span>'
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
            ,proxy: new  Ext.data.MemoryProxy()
            ,fields: [
                {name: 'id', type: 'int'}
                ,{name: 'pid', type: 'int'}
                ,'name'
                ,{name: 'template_id', type: 'int'}
                ,{name: 'cid', type: 'int'}
                ,'iconCls'
                ,'user'
                ,'ago_text'
            ]
        });

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,store: this.store
            ,autoHeight: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,click: this.onItemClick
            }
        });

        Ext.apply(this, {
            title: L.Contents
            ,items: this.dataView
        });
        CB.objects.plugins.ContentItems.superclass.initComponent.apply(this, arguments);

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

    ,onItemClick: function ( dv, index, el, e) {
        var te = Ext.get(e.getTarget());
        if(!te) {
            return;
        }

        if(te.hasClass('menu')) {
            this.showActionsMenu(e.getXY());
        } else if(te.hasClass('click')) {
            this.openObjectProperties(this.store.getAt(index).data);
        }
    }

    ,showActionsMenu: function(coord){
        if(Ext.isEmpty(this.puMenu)) {
            this.puMenu = new Ext.menu.Menu({
                items: [
                    {
                        text: 'Close'
                    },{
                        text: 'Delete'
                        ,iconCls: 'i-trash'
                    },'-',{
                        text: 'In new tab'
                        ,iconCls: 'icon-external'
                    }
                ]
            });
        }

        this.puMenu.showAt(coord);
    }

    ,getToolbarItems: function() {
        return [this.actions.add];
    }

    ,onAddClick: function(b, e) {
        clog('!', arguments);
        if(this.pmenu) {
            this.pmenu.destroy();
        }
        this.pmenu = new Ext.menu.Menu({items: []});

        updateMenu(
            {menu: this.pmenu}
            ,getMenuConfig(
                this.params.id
                ,this.params.path
                ,this.params.template_id
            )
            ,this.onCreateObjectClick
            ,this
        );
        this.pmenu.show(b.getEl());
    }

    ,onCreateObjectClick: function(b, e) {
        b.data.pid = this.params.id;
        b.data.path = this.params.path;
        this.fireEvent('createobject', b.data, e);
    }

});

Ext.reg('CBObjectsPluginsContentItems', CB.objects.plugins.ContentItems);
