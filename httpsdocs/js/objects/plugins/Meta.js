Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Meta = Ext.extend(CB.objects.plugins.ObjectProperties, {
    title: L.Metadata
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

        this.addEvents('editmeta');
        this.enableBubble(['editmeta']);
    }

    ,getToolbarItems: function () {

        return [{
            iconCls: 'i-points'
            ,html:'<b class="icon-padding16 i-points"></b>'
            ,scope: this
            ,handler: this.showMenu
        }];
    }

    ,onEditClick: function(b, e) {
        this.fireEvent('editmeta', this.params);
    }

    ,showMenu: function(b, e) {
        this.menu.show(b.getEl());
    }

    // ,onLoadData: function(r, e) {
    //     if(Ext.isEmpty(r.data)) {
    //         return;
    //     }
    //     // if(this.rendered) {
    //     //     this.dataView.update(r.data);
    //     // } else {
    //     //     this.dataView.data = r.data;
    //     // }
    // }
    //

});

Ext.reg('CBObjectsPluginsMeta', CB.objects.plugins.Meta);
