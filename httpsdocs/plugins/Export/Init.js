Ext.namespace('CB.plugins.Export');

CB.plugins.Export.init = function(){
    App.on('folderviewinit', function(c){
        clog('event folderviewinit', c.getXType(), arguments);
        if(!c.isXType(CB.FolderViewGrid, false)) {
            return;
        }
        var p = Ext.apply([], Ext.value(c.plugins, []));
        p.push({ptype: 'CBPluginsExportButton'});
        c.plugins = p;
    });
};

Ext.onReady(CB.plugins.Export.init);
