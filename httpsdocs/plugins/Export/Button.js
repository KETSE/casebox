Ext.namespace('CB.plugin.Export');

Ext.define('CB.plugin.Export.Button', {
    extend: 'CB.plugin.CustomInterface'
    ,alias: 'plugin.CBPluginExportButton'

    ,init: function(owner) {
        CB.plugin.Export.Button.superclass.init.call(this, arguments);
        this.owner = owner;

        /*this.button = new Ext.menu.Item({
            text: L.Export
            ,iconCls: 'icon-export'
            // ,scale: 'large'
            // ,iconAlign:'top'
            ,scope: this
            ,handler: this.onExportClick
        });


        owner.tbarMoreMenu.add(this.button);/**/

        owner.on('exportrecords', this.onExportRecordsEvent, this);
    }

    ,onExportClick: function(b, e) {
        window.open('get.php?export=' + Ext.encode(this.owner.params));
    }

    ,onExportRecordsEvent: function(cmp, e) {
        e.stopPropagation();
        this.onExportClick();
    }
});
