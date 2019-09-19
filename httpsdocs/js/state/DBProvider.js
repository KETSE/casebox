Ext.namespace('CB.state');

Ext.define('CB.state.DBProvider', {
    extend: 'Ext.state.Provider'

    ,constructor: function(config){
        // this.callParent(arguments);
        // CB.state.DBProvider.superclass.constructor.call(this, arguments);

        Ext.apply(
            this
            ,{
                api: {
                    read: CB_State_DBProvider.read
                    ,write: CB_State_DBProvider.set
                }
            }
        );

        Ext.apply(this, config);

        this.callParent(arguments);
        CB.state.DBProvider.superclass.constructor.call(this);

        this.load();
    }

    /**
     * load remote saved state from server
     * @return void
     */
    ,load: function() {
        this.api.read(this.onLoad, this);
    }

    ,onLoad: function(r, e) {
        if(!r || (r.success !== true)) {
            return;
        }

        this.state = r.data;
    }

    /**
     * Sets the value for a key
     * @param {String} name The key name
     * @param {Mixed} value The value to set
     */
    ,set: function(name, value){
        var method = Ext.isEmpty(value)
            ? 'clear'
            : 'set';

        this.api.write(
            {
                'name': name
                ,'value': value
            }
            ,function(r, e) {
                if(!r || (r.success !== true)) {
                    return;
                }
                CB.state.DBProvider.superclass[method].call(this, name, value);
            }
            ,this
        );
    }

    /**
     * Clears a value from the state
     * @param {String} name The key name
     */
    ,clear: function(name){
        this.set(name, null);
    }
});
