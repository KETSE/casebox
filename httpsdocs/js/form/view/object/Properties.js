Ext.namespace('CB.form.view.object');

CB.form.view.object.Properties = Ext.extend(CB.PluginsPanel, {
    initComponent: function(){
        CB.form.view.object.Properties.superclass.initComponent.apply(this, arguments);
    }
    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {
                reload: {}
            }
        };
        rez.tbar['edit'] = {};
        rez.tbar['openInTabsheet'] = {};

        if(CB.DB.templates.getType(this.loadedParams.template_id) == 'file') {
            rez.tbar['download'] = {};
        }

        this.items.each(
            function(i) {
                var pi = i.getContainerToolbarItems();

                if(pi.tbar) {
                    rez.tbar = Ext.apply(rez.tbar, pi.tbar);
                }
                if(pi.menu) {
                    /* adding dividesrs for first item of each plugin */
                    var isFirstItem = true;
                    Ext.iterate(
                        pi.menu
                        ,function(key, value){
                            if(isFirstItem) {
                                value.addDivider = 'top';
                                isFirstItem = false;
                            } else {
                                return false;
                            }
                        }
                        ,this
                    );

                    rez.menu = Ext.apply(rez.menu, pi.menu);
                }
            }
            ,this
        );

        return rez;
    }
});

Ext.reg('CBObjectProperties', CB.form.view.object.Properties);
