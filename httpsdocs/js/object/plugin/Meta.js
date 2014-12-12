Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Meta', {
    extend: 'CB.object.plugin.ObjectProperties'
    ,alias: 'CBObjectPluginMeta'

    ,title: L.Metadata

    ,initComponent: function(){
        this.actions = {
            edit: new Ext.Action({
                text: L.Edit
                ,iconCls: 'i-edit'
                ,scope: this
                ,handler: this.onEditClick
            })
        };

        this.menu = new Ext.menu.Menu({
            items: [
                this.actions.edit
            ]
        });

        this.prepareToolbar();

        this.callParent(arguments);

        Ext.apply(this, {
            border: false
            ,cls: 'obj-plugin'
        });

        this.enableBubble(['editmeta']);
    }

    ,getToolbarItems: function () {

        return [{
            iconCls: 'i-points'
            ,scope: this
            ,handler: this.showMenu
        }];
    }

    ,onEditClick: function(b, e) {
        this.fireEvent('editmeta', this.params, e);
    }

    ,showMenu: function(b, e) {
        this.menu.showBy(b.getEl());
    }
});
