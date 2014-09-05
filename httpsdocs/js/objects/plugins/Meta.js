Ext.namespace('CB.objects.plugins');

Ext.define('CB.objects.plugins.Meta', {
    extend: 'CB.objects.plugins.ObjectProperties'
    ,alias: 'CBObjectsPluginsMeta'

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
        CB.objects.plugins.Meta.superclass.initComponent.apply(this, arguments);

        Ext.apply(this, {
            border: false
            ,cls: 'obj-plugin'
        });

        this.enableBubble(['editmeta']);
    }

    ,getToolbarItems: function () {

        return [{
            iconCls: 'i-points'
            // ,html:'<b class="icon-padding16 i-points"></b>'
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
