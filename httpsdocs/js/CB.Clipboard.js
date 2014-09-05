Ext.namespace('CB');
Ext.define('CB.Clipboard', {
    extend: 'Ext.util.Observable'
    ,data: []
    ,action: 'copy' // copy / move / shortcut

    ,constructor: function(config){
        CB.Clipboard.superclass.constructor.call(this, config);
    }

    ,set:function(data, action) {
        this.data = Ext.isArray(data) ? data : [data];
        this.action = Ext.valueFrom(action, 'copy');
        this.fireEvent('change', this);
    }

    ,setAction:function(action) {
        this.action = action;
    }

    ,size: function(){
        return this.data.length;
    }

    ,isEmpty: function(){
        return Ext.isEmpty(this.data);
    }

    ,clear: function(){
        this.data = [];
        this.fireEvent('change', this);
    }

    ,containShortcutsOnly: function() {
        var rez = true;
        Ext.each(
            this.data
            ,function(i){
                rez = (i.type == 2);
                return rez;
            }
            ,this
        );
        return rez;
    }

    ,paste: function(pid, action, callback, scope){
        App.DD.execute({
            action: Ext.valueFrom(action, this.action)
            ,confirm: false
            ,targetData: {id: pid}
            ,sourceData: this.data
        }
        ,callback
        ,scope
        );
    }
});
