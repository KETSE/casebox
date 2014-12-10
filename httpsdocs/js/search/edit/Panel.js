Ext.namespace('CB');

Ext.define('CB.search.edit.Panel', {

    extend: 'CB.object.edit.Form'

    ,xtype: 'CBSearchPanel'

    ,hideTitle: true

    ,initComponent: function(){
        this.instanceId = Ext.id();

        this.actions = {
            search: new Ext.Action({
                iconCls: 'ib-search'
                ,id: 'search' + this.instanceId
                ,scale: 'large'
                ,tooltip: L.Search
                ,scope: this
                ,handler: this.onSearchClick
            })
            ,clear: new Ext.Action({
                iconCls: 'ib-refresh'
                ,id: 'clear' + this.instanceId
                ,scale: 'large'
                ,qtip: L.Clear
                ,scope: this
                ,handler: this.onClearClick
            })

            ,save: new Ext.Action({
                iconCls: 'icon-tick'
                ,id: 'save' + this.instanceId
                ,text: L.Save
                ,disabled: true
                ,scope: this
                ,handler: this.onSaveClick
            })
        };

        this.moreMenu = new Ext.menu.Menu({items:[
            new Ext.menu.Item(this.actions.save)
        ]});

        Ext.apply(this, {
            bbar: [
                this.actions.search
                ,this.actions.clear
                ,'->'
                ,{
                    iconCls: 'ib-points'
                    ,id: 'more' + this.instanceId
                    ,scale: 'large'
                    ,scope: this
                    ,handler: function(b, e) {
                        this.moreMenu.showBy(b.getEl());
                    }
                }
            ]
        });

        this.on(
            'change'
            ,function() {
                this.actions.save.enable();
            }
            ,this
        );
        this.on(
            'clear'
            ,function() {
                this.actions.save.disable();
            }
            ,this
        );

        this.enableBubble(['changeparams']);

        this.callParent(arguments);
    }

    ,onSearchClick: function() {
        var p = Ext.copyTo({}, this.data, 'id,template_id');
        p.data = Ext.apply({} , this.readValues().data);

        var browser = App.activateBrowserTab();
        browser.changeSomeParams({search: p});
        // this.fireEvent('changeparams',{search: p});
    }

    ,onClearClick: function (b, e)
    {
        var data = {template_id: this.data.template_id};
        this.clear();
        this.loadData(data);
    }

    ,onSaveClick: Ext.emptyFn
});
