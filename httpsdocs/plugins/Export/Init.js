Ext.namespace('CB.plugin.Export');

CB.plugin.Export.init = function(){
    App.on('browserinit', function(c){
        var p = Ext.apply([], Ext.valueFrom(c.plugins, []));
        p.push({ptype: 'CBPluginExportButton'});
        c.plugins = p;
    });
};

Ext.onReady(CB.plugin.Export.init);
