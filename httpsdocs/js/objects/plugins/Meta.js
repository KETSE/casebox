Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Meta = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){
        CB.objects.plugins.Meta.superclass.initComponent.apply(this, arguments);
    }
    ,onLoadData: function(r, e) {
        // if(this.rendered) {
        //     this.dataView.update(r.data);
        // } else {
        //     this.dataView.data = r.data;
        // }
    }
});

Ext.reg('CBObjectsPluginsMeta', CB.objects.plugins.Meta);
