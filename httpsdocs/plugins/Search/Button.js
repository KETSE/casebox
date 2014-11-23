Ext.namespace('CB.plugin.Search');

Ext.define('CB.plugin.Search.Button', {
    extend: 'CB.plugin.CustomInterface'

    ,alias: 'plugin.CBPluginSearchButton'

    ,xtype: 'CBPluginSearchButton'

    ,init: function(owner) {
        this.historyData = {};

        this.callParent(arguments);

        this.owner = owner;
        var instanceId = owner.instanceId;

        // get filter button from the collection to detect its toggle group
        var fb = owner.buttonCollection.get('filter' + instanceId);
        if(Ext.isEmpty(fb)) {
            return;
        }

        this.button = new Ext.SplitButton({
            text: L.Search
            ,id: 'pluginsearchbutton' + instanceId
            ,iconCls: 'ib-search'
            ,scale: 'large'
            ,allowDepress: false
            ,itemIndex: 2
            ,menu: []
            ,scope: owner
            ,handler: this.onButtonClick
        });

        this.loadSearchTemplates();

        owner.buttonCollection.add(this.button);

        owner.containerToolbar.insert(0, this.button);
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
        var menu = this.button.menu;
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
    }
});
