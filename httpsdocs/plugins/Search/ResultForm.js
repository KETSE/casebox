Ext.namespace('CB.plugins.Search');

Ext.define('CB.plugins.Search.ResultForm', {
    extend: 'CB.browser.ViewContainer'
    ,title: L.SearchResults
    ,iconCls: 'icon-search'
    ,closable: true

    ,initComponent: function(){
        var config = {};
        if(CB.plugins.config && CB.plugins.config.Search) {
            config = CB.plugins.config.Search;
        }
        Ext.apply(this, config);
        CB.plugins.Search.ResultForm.superclass.initComponent.apply(this, arguments);
        if(!Ext.isEmpty(this.handler)) {
            var a = this.handler.split('.');
            this.store.proxy.setApi(
                Ext.data.Api.actions.read
                ,window[a[0]][a[1]]
            );
        }
    }
});
