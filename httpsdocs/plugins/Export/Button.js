Ext.namespace('CB.plugins.Export');

CB.plugins.Export.Button =  Ext.extend(CB.plugins.customInterface, {

    init: function(owner) {
        CB.plugins.Export.Button.superclass.init.call(this, arguments);
        this.owner = owner;

        this.button = new Ext.Button({
            text: L.Export
            ,iconCls: 'icon32-export'
            ,scale: 'large'
            ,iconAlign:'top'
            ,scope: this
            ,handler: this.onExportClick
        });


        var tb = this.owner.getTopToolbar();
        var idx = tb.items.findIndex('isFill', 'true');

        tb.insert(idx, this.button);
        if(!this.owner.isXType('CBPluginsSearchResultForm')) {
            tb.insert(idx, '-');
        }
    }
    ,onExportClick: function(b, e) {
        window.open('get.php?export=' + Ext.encode(this.owner.params));
        // Export_Instance.getCSV(this.owner.params);
    }
});

Ext.ComponentMgr.registerPlugin('CBPluginsExportButton', CB.plugins.Export.Button);
