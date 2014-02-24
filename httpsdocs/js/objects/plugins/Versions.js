Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Versions = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){
        CB.objects.plugins.Versions.superclass.initComponent.apply(this, arguments);
    }
    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }
        // if(this.rendered) {
        //     this.dataView.update(r.data);
        // } else {
        //     this.dataView.data = r.data;
        // }
    }
});

Ext.reg('CBObjectsPluginsVersions', CB.objects.plugins.Versions);
