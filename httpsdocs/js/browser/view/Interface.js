Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Interface', {
    extend: 'Ext.Container'
    ,border: false

    ,xtype: 'CBBrowserViewInterface'

    ,initComponent: function(){
        CB.browser.view.Interface.superclass.initComponent.apply(this, arguments);

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
        return {};
    }
});
