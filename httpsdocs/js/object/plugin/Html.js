Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Html', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginHtml'

    ,title: 'Html'

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
});
