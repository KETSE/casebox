Ext.namespace('CB.plugins.Export');

CB.plugins.Export.init = function(){
    App.on('browserinit', function(c){
        var p = Ext.apply([], Ext.valueFrom(c.plugins, []));
        p.push({ptype: 'CBPluginsExportButton'});
        c.plugins = p;
    });
};

Ext.onReady(CB.plugins.Export.init);
