Ext.namespace('CB.objects.plugins');

CB.objects.plugins.ObjectProperties = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){
        Ext.apply(this, {
            html: ''
        });
        CB.objects.plugins.ObjectProperties.superclass.initComponent.apply(this, arguments);
    }
    ,onLoadData: function(r, e) {
        if(this.rendered) {
            this.update(r.data.html);
        } else {
            this.html = r.data.html;
        }
    }
});

Ext.reg('CBObjectsPluginsObjectProperties', CB.objects.plugins.ObjectProperties);
