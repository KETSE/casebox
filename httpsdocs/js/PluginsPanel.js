Ext.namespace('CB');

CB.PluginsPanel = Ext.extend(Ext.Panel, {
    autoScroll: true
    ,padding:0
    ,initComponent: function(){
        CB.PluginsPanel.superclass.initComponent.apply(this, arguments);

        this.delayLoadTask = new Ext.util.DelayedTask(this.doLoad, this);
    }

    ,load: function (params) {
        if(!isNaN(params)) {
            params = {id: params};
        }

        if(Ext.isEmpty(params)) {
            this.clear();
            return;
        }

        this.delayLoadTask.cancel();

        /* check if not the same as current params */
        if(Ext.encode(params) == Ext.encode(this.loadedParams)) {
            return;
        }

        /* delay task */
        this.delayLoadTask.delay(60, this.doLoad, this, arguments);

    }

    ,doLoad: function(params) {
        if(Ext.isEmpty(this.api)) {
            return;
        }

        this.loadedParams = params;
        this.api(params, this.onLoadData, this);
    }

    ,onLoadData: function(r, e) {
        this.clear();
        var items = [];
        Ext.iterate(
            r.data
            ,function(k, v, o) {
                var cl = Ext.util.Format.capitalize(k.substr(0,1)) + k.substr(1);
                cl = 'CBObjectsPlugins' + cl;
                var c = Ext.create({
                        params: this.loadedParams
                    }
                    ,cl
                );
                this.add(c);
                c.onLoadData(v);
            }
            ,this
        );
        this.syncSize();
        // if(items.length > 0) {
        //     this.add(items);
        // }
    }

    ,clear: function() {
        this.removeAll(true);
    }

    ,reload: function() {
        this.doLoad(this.loadedParams);
    }
});

Ext.reg('CBPluginsPanel', CB.PluginsPanel);
