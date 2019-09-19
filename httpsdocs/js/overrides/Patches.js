Ext.define('Ext.patch,EXTJS16166', {
    override: 'Ext.view.View',
    compatibility: '5.1.0.107',
    handleEvent: function(e) {
        var me = this,
            isKeyEvent = me.keyEventRe.test(e.type),
            nm = me.getNavigationModel();

        e.view = me;

        if (isKeyEvent) {
            e.item = nm.getItem();
            e.record = nm.getRecord();
        }

        // If the key event was fired programatically, it will not have triggered the focus
        // so the NavigationModel will not have this information.
        if (!e.item) {
            e.item = e.getTarget(me.itemSelector);
        }
        if (e.item && !e.record) {
            e.record = me.getRecord(e.item);
        }

        if (me.processUIEvent(e) !== false) {
            me.processSpecialEvent(e);
        }

        // We need to prevent default action on navigation keys
        // that can cause View element scroll unless the event is from an input field.
        // We MUST prevent browser's default action on SPACE which is to focus the event's target element.
        // Focusing causes the browser to attempt to scroll the element into view.

        if (isKeyEvent && !Ext.fly(e.target).isInputField()) {
            if (e.getKey() === e.SPACE || e.isNavKeyPress(true)) {
                e.preventDefault();
            }
        }
    }
});

Ext.define('Ext.overrides.util.Collection', {
    override: 'Ext.util.Collection',
    compatibility: '5.1.0.107',

    updateKey: function (item, oldKey) {
        var me = this,
            map = me.map,
            indices = me.indices,
            source = me.getSource(),
            newKey;

        if (source && !source.updating) {
            // If we are being told of the key change and the source has the same idea
            // on keying the item, push the change down instead.
            source.updateKey(item, oldKey);
        }
        // If there *is* an existing item by the oldKey and the key yielded by the new item is different from the oldKey...
        else if (map[oldKey] && (newKey = me.getKey(item)) !== oldKey) {
            if (oldKey in map || map[newKey] !== item) {
                if (oldKey in map) {
                    //<debug>
                    if (map[oldKey] !== item) {
                        Ext.Error.raise('Incorrect oldKey "' + oldKey +
                                        '" for item with newKey "' + newKey + '"');
                    }
                    //</debug>

                    delete map[oldKey];
                }

                // We need to mark ourselves as updating so that observing collections
                // don't reflect the updateKey back to us (see above check) but this is
                // not really a normal update cycle so we don't call begin/endUpdate.
                me.updating++;

                me.generation++;
                map[newKey] = item;
                if (indices) {
                    indices[newKey] = indices[oldKey];
                    delete indices[oldKey];
                }

                me.notify('updatekey', [{
                    item: item,
                    newKey: newKey,
                    oldKey: oldKey
                }]);

                me.updating--;
            }
        }
    }
});

//VBox layout scroll restore issue
Ext.define('My.override.VBoxLayoutFix', {
    override: 'Ext.layout.container.VBox',
    beginLayout: function(ownerContext) {
        var scrollable = this.owner.getScrollable();
        if (scrollable) {
            this.lastScrollPosition = scrollable.getPosition();
        }
        this.callParent(arguments);
    },
    completeLayout: function(ownerContext) {
        var scrollable = this.owner.getScrollable();
        this.callParent(arguments);
        if (scrollable) {
            scrollable.scrollTo(this.lastScrollPosition);
        }
    }
});

/* visual editors bug in chrome */
if(Ext.isChrome && Ext.chromeVersion===43) {
    Ext.getBody().addCls('chrome-43');
}

