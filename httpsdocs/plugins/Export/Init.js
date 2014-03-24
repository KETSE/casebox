Ext.namespace('CB.plugins.Export');

CB.plugins.Export.init = function(){
    if(window.location.host.substr(0,4) !== 'hcav') {
        return;
    }
    App.on('browserinit', function(c){
        var p = Ext.apply([], Ext.value(c.plugins, []));
        p.push({ptype: 'CBPluginsExportButton'});
        c.plugins = p;
    });
};

Ext.onReady(CB.plugins.Export.init);
