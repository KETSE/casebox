Ext.namespace('CB');

CB.PluginsPanel = Ext.extend(Ext.Panel, {
    autoScroll: true
    ,padding:0
    ,initComponent: function(){
        CB.PluginsPanel.superclass.initComponent.apply(this, arguments);

        App.on('objectchanged', this.onObjectChanged, this);

        this.delayLoadTask = new Ext.util.DelayedTask(this.doLoad, this);
    }

    ,load: function (params) {
        if(!isNaN(params)) {
            params = {id: params};
        }

        var el = this.getEl();
        if(Ext.isEmpty(el) || !el.isVisible(true)) {
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
        if(!isNaN(params)) {
            params = {id: params};
        }
        this.clear();
        if(Ext.isEmpty(params)) {
            return;
        }

        this.loadedParams = Ext.apply({}, params);
        this.api(params, this.onLoadData, this);
    }

    ,onLoadData: function(r, e) {
        var items = [];
        this.removeAll(true);
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
                if(!Ext.isDefined(v.data)) {
                    c.setVisible(false);
                } else {
                    c.onLoadData(v);
                }
            }
            ,this
        );
        this.doLayout(true, true);
        this.fireEvent('loaded', this);
    }

    ,clear: function() {
        this.removeAll(true);
        delete this.loadedParams;
    }
    ,onObjectChanged: function(data) {
        if(!isNaN(data)) {
            data = {id: data};
        }
        if(!Ext.isEmpty(this.loadedParams)) {
            if(data.id == this.loadedParams.id) {
                this.reload();
            }
        }
    }
    ,reload: function() {
        this.doLoad(this.loadedParams);
    }
    ,getContainerToolbarItems: function() {

    }
});

Ext.reg('CBPluginsPanel', CB.PluginsPanel);
