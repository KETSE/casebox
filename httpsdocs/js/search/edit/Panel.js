Ext.namespace('CB');

Ext.define('CB.search.edit.Panel', {

    extend: 'CB.object.edit.Form'

    ,xtype: 'CBSearchPanel'

    ,hideTitle: true

    ,initComponent: function(){
        this.actions = {
            search: new Ext.Action({
                text: L.Search
                ,iconCls: 'im-search'
                ,itemId: 'search'
                ,scale: 'medium'
                ,tooltip: L.Search
                ,scope: this
                ,handler: this.onSearchClick
            })
            ,tune: new Ext.Action({
                iconCls: 'im-tune'
                ,itemId: 'tune'
                ,scale: 'medium'
                ,qtip: L.Tune
                ,scope: this
                ,enableToggle: true
                ,handler: this.onTuneClick
            })
            ,clear: new Ext.Action({
                iconCls: 'im-refresh'
                ,itemId: 'clear'
                ,scale: 'medium'
                ,qtip: L.Clear
                ,scope: this
                ,handler: this.onClearClick
            })

            ,save: new Ext.Action({
                iconCls: 'is-tick'
                ,itemId: 'save'
                ,scale: 'medium'
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
            tbar: [
                this.actions.search
                ,'->'
                ,this.actions.tune
                ,this.actions.clear
                ,{
                    iconCls: 'im-points'
                    ,itemId: 'more'
                    ,scale: 'medium'
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
        browser.changeSomeParams({
            query: ''
            ,search: p
        });
    }

    ,processLoadData: function(r, e) {
        if(isNaN(r.data.id)) {
            r.data.name = L.Search + ' ' + CB.DB.templates.getName(r.data.template_id);
        }

        this.callParent(arguments);
    }

    ,onTuneClick: function (b, e) {
        this.grid.headerCt.child('[dataIndex="cond"]').setHidden(!b.pressed);
    }

    ,onClearClick: function (b, e) {
        var data = {template_id: this.data.template_id};
        this.clear();
        this.loadData(data);
    }

    ,onSaveClick: Ext.emptyFn
});
