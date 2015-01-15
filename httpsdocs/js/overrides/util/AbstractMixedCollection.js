Ext.namespace('Ext.util');

/*
    Overrides for Mixed collection
*/

Ext.override(Ext.util.AbstractMixedCollection, {
    /**
     * allow to get an item by itemId property while items added with addAll without id property
     * @param {String/Number} key The key or index of the item.
     * @return {Object}
     */
    get: function(key){
        var rez = this.callParent(arguments);

        if(Ext.isEmpty(rez) && Ext.isPrimitive(key)) {
            var idx = this.findIndex('itemId', key);
            if(idx > -1) {
                rez = this.getAt(idx);
            }
        }

        return rez;
    }
});
