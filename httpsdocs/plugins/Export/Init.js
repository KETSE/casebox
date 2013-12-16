Ext.namespace('CB.plugins.Export');

CB.plugins.Export.init = function(){
    if(window.location.host.substr(0,5) !== 'hcav.') {
        return;
    }
    App.on('folderviewinit', function(c){
        if(!c.isXType(CB.FolderViewGrid, false)) {
            return;
        }
        var p = Ext.apply([], Ext.value(c.plugins, []));
        p.push({ptype: 'CBPluginsExportButton'});
        c.plugins = p;
    });
};

Ext.onReady(CB.plugins.Export.init);
