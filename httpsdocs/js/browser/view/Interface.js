Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Interface', {
    extend: 'Ext.Container'
    ,border: false

    ,xtype: 'CBBrowserViewInterface'

    ,viewName: 'none'

    ,initComponent: function(){

        this.callParent(arguments);

        this.enableBubble([
            'changeparams'
            ,'selectionchange'
            ,'objectopen'
            ,'settoolbaritems'
        ]);
    }

    /**
     * overwrite this function by any descendant class to specify custom request params for view
     * @return object
     */
    ,getViewParams: function() {
        var rez = {
            from: this.viewName
        };

        return rez;
    }

    /**
     * detect sorter to be used according to given params
     *
     * @param  object params
     * @return function | null
     */
    ,detectSorter: function(params) {
        var rez = null;

        if(params && params.sort) {
            var sortersGroup = CB.facet.Base.prototype.sorters[params.sort];
            if(sortersGroup) {
                var dir = Ext.valueFrom(params.direction, 'asc');

                if(dir) {
                    rez = sortersGroup[dir];
                }
            }
        }

        return rez;
    }
});
