Ext.namespace('Ext.data');

Ext.override(Ext.data.Store, {
    deleteIds: function(ids){
        var idx
            ,idProperty = (Ext.isEmpty(this.proxy) || Ext.isEmpty(this.proxy.reader))
                ? 'id'
                : Ext.valueFrom(
                    Ext.valueFrom(
                        this.proxy.reader.idProperty
                        ,this.proxy.reader.config.idProperty
                    )
                    ,'id'
                );

        if(Ext.isPrimitive(ids)) {
            ids = String(ids).split(',');
        }

        if((this.getCount() > 0) && this.data) {
            for (var i = 0; i < ids.length; i++) {
                idx = this.findExact(idProperty, String(ids[i]));

                if(idx < 0) {
                    idx = this.findExact(idProperty, parseInt(ids[i], 10));
                }

                if(idx >= 0) {
                    this.removeAt(idx);
                }
            }
        }
    }
});
