Ext.namespace('CB.plugin.Search');

CB.plugin.Search.init = function(){
    App.on('browserinit', function(c){
        if(!c.isXType('CBBrowserViewContainer', true)) {
            return;
        }

        /* check if we have search templates */
        if(CB.DB.templates.query('type', 'search').getCount() > 0) {
            var p = Ext.apply([], Ext.valueFrom(c.plugins, []));
            p.push({ptype: 'CBPluginSearchButton'});
            c.plugins = p;
        }
    });
};

Ext.onReady(CB.plugin.Search.init);
