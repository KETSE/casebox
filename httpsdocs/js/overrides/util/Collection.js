Ext.namespace('Ext.util');

//there are some situations when mixed collection has null defined "items" property
//and results in error
Ext.util.Collection.prototype._getAt = Ext.util.Collection.prototype.getAt;

Ext.util.Collection.prototype.getAt = function(index){
    if(Ext.isEmpty(this.items)){
        clog('Found MixedCollextion with empty "items" property', this);
        return 'null';
    }
    return this._getAt(index);
};
