Ext.namespace('CB');

CB.SearchPanel = Ext.extend(CB.form.edit.Object, {
    hideTitle: true
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
            tbar: [
                this.actions.search
                ,this.actions.clear
                ,'->'
                ,{
                    iconCls: 'ib-points'
                    ,id: 'more' + this.instanceId
                    ,scale: 'large'
                    ,scope: this
                    ,handler: function(b, e) {
                        this.moreMenu.show(b.getEl());
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

        this.addEvents('changeparams');
        this.enableBubble(['changeparams']);

        CB.SearchPanel.superclass.initComponent.apply(this, arguments);
    }

    ,onSearchClick: function() {
        var p = Ext.copyTo({}, this.data, 'id,template_id');
        p.data = this.readValues().data;
        this.fireEvent('changeparams', {search: p});
    }

    ,onClearClick: function (b, e)
    {
        var data = {template_id: this.data.template_id};
        this.clear();
        this.loadData(data);
    }

    ,onSaveClick: Ext.emptyFn
});

Ext.reg('CBSearchPanel', CB.SearchPanel);
