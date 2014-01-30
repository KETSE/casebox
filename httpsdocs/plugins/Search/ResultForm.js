Ext.namespace('CB.plugins.Search');

CB.plugins.Search.ResultForm = Ext.extend(CB.browser.ViewContainer, {
    title: L.SearchResults
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

        // var tb = this.getTopToolbar();
        // var idx;
        // var hideItems = [
        //     L.Create
        //     ,L.Edit
        //     ,L.Browse
        //     ,L.Upload
        //     ,L.Download
        //     ,L.NewTask
        //     ,L.List
        //     ,L.Tasks
        //     ,L.Calendar
        //     ,L.Charts
        // ];

        // for (var i = 0; i < hideItems.length; i++) {
        //     idx = tb.items.findIndex('text', hideItems[i]);
        //     if(idx >=0) {
        //         tb.remove(tb.items.itemAt(idx));
        //         if(idx > 0) {

        //             var previtem = tb.items.itemAt(idx-1);
        //             var item = tb.items.itemAt(idx);
        //             if(previtem.isXType('tbseparator') &&
        //                 (item.isXType('tbseparator') ||
        //                     item.isXType('tbfill')
        //                 )
        //             ) {
        //                 tb.remove(previtem);
        //             }
        //         }
        //     }
        // }

        // tb.insert(0, '-');
        // tb.insert(0, {
        //     text: L.Search
        //     ,iconCls: 'ib-search'
        //     ,iconAlign:'top'
        //     ,scale: 'large'
        //     ,scope: this
        //     ,handler: function(){
        //         App.openUniqueTabbedWidget( 'CBPluginsSearchForm', null, {data: this.data});
        //     }
        // });
    }
});

Ext.reg('CBPluginsSearchResultForm', CB.plugins.Search.ResultForm);
