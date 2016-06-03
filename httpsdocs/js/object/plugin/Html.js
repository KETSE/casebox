Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Html', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginHtml'

    ,title: 'Html'

    ,initComponent: function(){
        Ext.apply(this, {
            listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });

        this.callParent(arguments);
    }

    /**
     * method to be overriten in descendant classes
     * @param  array r
     * @param  event e
     * @return void
     */
    ,onLoadData: function(r, e) {
        if(!Ext.isEmpty(r.data)) {
            this.update(r.data);
        }

        this.setTitle(Ext.valueFrom(r.title), this.title);
    }

    ,onAfterRender: function(){
        var a = this.getEl().query('[data-action]');

        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener('click', this.onDataActionClick, this);
            }
            ,this
        );
    }

    ,onDataActionClick: function(ev, el) {
        el = Ext.get(el);
        if(el) {
            var action = el.getAttribute('data-action');

            switch(action) {
                case 'node-view':
                    App.controller.openObjectWindowById(el.getAttribute('data-node-id'));
                    break;

                case 'select-path':
                    App.openPath(el.getAttribute('data-path'));
                    break;
            }

        }
    }

});
