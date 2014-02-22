Ext.namespace('CB.form.view.object');

CB.form.view.object.Properties = Ext.extend(CB.PluginsPanel, {
    initComponent: function(){
        CB.form.view.object.Properties.superclass.initComponent.apply(this, arguments);
    }
    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {}
        };
        rez.tbar['edit' +  this.instanceId] = {};
        rez.tbar['openInTabsheet' +  this.instanceId] = {};

        if(CB.DB.templates.getType(this.loadedParams.template_id) == 'file') {
            rez.tbar['download' +  this.instanceId] = {};
        }

        this.items.each(
            function(i) {
                var pi = i.getContainerToolbarItems();
                clog('pi', i, pi)
                if(pi.tbar) {
                    rez.tbar = Ext.apply(rez.tbar, pi.tbar);
                }
                if(pi.menu) {
                    rez.menu = Ext.apply(rez.menu, pi.menu);
                }
            }
            ,this
        );

        return rez;
    }
});

Ext.reg('CBObjectProperties', CB.form.view.object.Properties);
