Ext.namespace('CB.browser.view');

CB.browser.view.Interface = Ext.extend(Ext.Container, {
    hideBorders: true

    ,initComponent: function(){
        CB.browser.view.Interface.superclass.initComponent.apply(this, arguments);
        this.addEvents(
                'changeparams'
                ,'selectionchange'
                ,'objectopen'
                ,'settoolbaritems'
        );
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

Ext.reg('CBBrowserViewInterface', CB.browser.view.Interface);
