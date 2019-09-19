Ext.namespace('CB.plugin.Search');

Ext.define('CB.plugin.Search.ResultForm', {
    extend: 'CB.browser.ViewContainer'
    ,title: L.SearchResults
    ,iconCls: 'icon-search'
    ,closable: true

    ,initComponent: function(){
        var config = {};
        if(CB.plugin.config && CB.plugin.config.Search) {
            config = CB.plugin.config.Search;
        }
        Ext.apply(this, config);
        CB.plugin.Search.ResultForm.superclass.initComponent.apply(this, arguments);
        if(!Ext.isEmpty(this.handler)) {
            var a = this.handler.split('.');
            this.store.proxy.setApi(
                Ext.data.Api.actions.read
                ,window[a[0]][a[1]]
            );
        }
    }
});
