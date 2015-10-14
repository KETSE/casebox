Ext.namespace('CB.plugin.Search');

Ext.define('CB.plugin.Search.Button', {
    extend: 'CB.plugin.CustomInterface'

    ,alias: 'plugin.CBPluginSearchButton'

    ,ptype: 'CBPluginSearchButton'

    ,init: function(owner) {

        this.historyData = {};

        this.callParent(arguments);

        this.owner = owner;

        this.button = new Ext.Button({
            qtip: L.Search
            ,itemId: 'pluginsearchbutton'
            ,arrowVisible: false
            // ,arrowAlign: 'bottom'
            ,iconCls: 'ib-search-negative'
            ,scale: 'large'
            ,allowDepress: false
            ,hidden: true
            ,width: 20
            ,menuAlign: 'tl-tr'
            ,menu: []
            ,listeners: {
                menushow: CB.ViewPort.prototype.onButtonMenuShow
            }

        });

        owner.insert(3, this.button);

        if(App.initialized) {
            this.loadSearchTemplates();
        } else {
            App.on(
                'cbinit'
                ,this.loadSearchTemplates
                ,this
            );
        }
    }

    ,onButtonClick: function(b, e) {
        //load default search template if not already loaded
        var data = b.menu
            ? b.menu.items.getAt(0).config.data
            : b.config.data

            ,config = {
                xtype: 'CBSearchEditWindow'
                ,id: 'sew' + data.template_id
            };

        config.data = Ext.apply({}, data);

        var w  = App.openWindow(config);
        if(!w.existing) {
            w.alignTo(App.mainViewPort.getEl(), 'bl-bl?');
        }

        delete w.existing;
    }

    ,loadSearchTemplates: function(){
        if(Ext.isEmpty(CB.DB.templates)) {
            return;
        }

        var menu = this.button.menu;
            menu.removeAll(true);
        var templates = CB.DB.templates.query('type', 'search');

        templates.each(
            function(t){
                menu.add({
                    iconCls: t.data.iconCls
                    ,data: {template_id: t.data.id}
                    ,text: t.data.title
                    ,scope: this
                    ,handler: this.onButtonClick
                });
            }
            ,this
        );

        this.button.setVisible(menu.items.getCount() > 0);
    }
});
