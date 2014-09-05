Ext.namespace('CB.plugins.Export');

Ext.define('CB.plugins.Export.Button', {
    extend: 'CB.plugins.customInterface'
    ,alias: 'plugin.CBPluginsExportButton'

    ,init: function(owner) {
        CB.plugins.Export.Button.superclass.init.call(this, arguments);
        this.owner = owner;

        this.button = new Ext.menu.Item({
            text: L.Export
            ,iconCls: 'icon-export'
            // ,scale: 'large'
            // ,iconAlign:'top'
            ,scope: this
            ,handler: this.onExportClick
        });


        owner.tbarMoreMenu.add(this.button);
        // owner.buttonCollection.add(this.button);
        // if(Ext.isEmpty(owner.pluginButtons)) {
        //     owner.pluginButtons = ['pluginexportresultsbutton'];
        // } else {
        //     owner.pluginButtons.push('pluginexportresultsbutton');
        // }
    }
    ,onExportClick: function(b, e) {
        window.open('get.php?export=' + Ext.encode(this.owner.params));
    }
});
