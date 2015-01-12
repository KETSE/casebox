Ext.namespace('CB.plugin.Export');

Ext.define('CB.plugin.Export.Button', {
    extend: 'CB.plugin.CustomInterface'
    ,alias: 'plugin.CBPluginExportButton'

    ,init: function(owner) {
        CB.plugin.Export.Button.superclass.init.call(this, arguments);
        this.owner = owner;

        owner.on('exportrecords', this.onExportRecordsEvent, this);
    }

    ,onExportClick: function(b, e) {
        var params = Ext.apply(
            {
                'from': 'grid'
            }
            ,this.owner.params
        );

        window.open('get.php?export=' + Ext.encode(params));
    }

    ,onExportRecordsEvent: function(cmp, e) {
        e.stopPropagation();
        this.onExportClick();
    }
});
