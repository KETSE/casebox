Ext.namespace('CB.plugins.Search');

CB.plugins.Search.Button =  Ext.extend(CB.plugins.customInterface, {

    init: function(owner) {
        CB.plugins.Search.Button.superclass.init.call(this, arguments);
        this.owner = owner;

        this.button = new Ext.Button({
            text: L.Search
            ,id: 'pluginsearchbutton'
            ,iconCls: 'ib-search'
            ,scale: 'large'
            ,iconAlign:'top'
            ,menu:[]
        });

        this.loadSearchTemplates();

        owner.buttonCollection.add(this.button);
        if(Ext.isEmpty(owner.pluginButtons)) {
            owner.pluginButtons = ['pluginsearchbutton'];
        } else {
            owner.pluginButtons.push('pluginsearchbutton');
        }
    }

    ,loadSearchTemplates: function(){
        var menu = this.button.menu;
        var templates = CB.DB.templates.query('type', 'search');
        templates.each(
            function(t){
                menu.add({
                    iconCls: t.data.iconCls
                    ,data: {id: t.data.id}
                    // ,enableToggle: true
                    // ,allowDepress: false
                    // ,toggleGroup: 'viewMode'
                    // ,pressed: true
                    ,text: t.data.title
                    // ,viewIndex: 0
                    ,handler: function() {
                        App.mainTabPanel.remove('CBPluginsSearchForm', true);
                        App.mainTabPanel.remove('CBPluginsSearchResultForm', true);
                        App.openUniqueTabbedWidget(
                            'CBPluginsSearchForm'
                            ,null
                            ,{
                                data:{
                                    template_id: t.data.id
                                }
                            }
                        );
                    }
                });
            }
            ,this
        );
    }
});

Ext.ComponentMgr.registerPlugin('CBPluginsSearchButton', CB.plugins.Search.Button);
