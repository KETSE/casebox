Ext.namespace('CB');

Ext.define('CB.controller.History', {
    extend: 'Ext.util.Observable'

    ,xtype: 'historycontroller'

    ,constructor: function() {
        this.callParent(arguments);

        App.on('cbinit', this.onAppInit, this);
    }

    /**
     * on application initialization handler
     * @return void
     */
    ,onAppInit: function() {
        this.VC = App.explorer;

        Ext.History.init();

        window.addEventListener(
            'hashchange'
            ,Ext.Function.bind(this.onHashChange, this)
        );

        this.VC.store.on(
            'beforeload'
            ,this.onStoreBeforeLoad
            ,this
        );

    }

    /**
     * handler for history hash change event
     * @param  historyEvent event [description]
     * @return void
     */
    ,onHashChange: function(event) {

        //if in process o setting hash then dont react
        if(this.settingHash) {
            delete this.settingHash;

            return;
        }

        //this flag is set when a window is closed and we have to restore
        //the hash to the last one
        if(this.closingWindow) {
            delete this.closingWindow;

            return;
        }

        var activeWindow = this.getActiveWindow();

        //if we have an active window - try to close it
        if(!Ext.isEmpty(activeWindow)) {
            this.closingWindow = true;

            window.location = event.oldURL;

            activeWindow.close();

        } else {//react to the changed hash
            var hash = event.newURL.split('#')[1]
                ,p;

            //if we are already on an empty hash then go back
            if(Ext.isEmpty(hash)) {
                this.settingHash = true;

                window.location = event.oldURL;
                return;
            }

            //update view container params
            p = Ext.Object.fromQueryString(hash);
            this.restoreParams = true;
            this.VC.setParams(p);
        }
    }


    /**
     * method for getting active window component
     * @return null | window component
     */
    ,getActiveWindow: function() {
        var rez = null
            ,el = Ext.Element.getActiveElement();

        if(el) {
            el = Ext.get(el);
            if(el) {
                var parent = el.findParent('.x-window', true);
                if(parent) {
                    rez = Ext.getCmp(parent.id);
                }
            }
        }

        return rez;
    }

    /**
     * listener for view contaner store to add loaded params
     * to history by changing current hash into new loading params
     * @param  object store
     * @param  object operation
     * @param  object eOpts
     * @return void
     */
    ,onStoreBeforeLoad: function(store, operation, eOpts) {
        if(this.restoreParams) {
            delete this.restoreParams;
            return;
        }

        var ep = store.proxy.extraParams
            ,p = {
                'path': Ext.valueFrom(ep.path, ep.id)
                ,'page': Ext.valueFrom(ep.page, 1)
                ,'query': ep.query
                ,'lastQuery': ep.lastQuery
                ,'descendants': ep.descendants
            };

            if(Ext.isEmpty(p.query)) {
                delete p.query;
            }
            if(Ext.isEmpty(p.lastQuery)) {
                delete p.lastQuery;
            }
            if(p.descendants !== true) {
                delete p.descendants;
            }

            this.settingHash = true;
            Ext.History.add(Ext.Object.toQueryString(p));
    }
});
