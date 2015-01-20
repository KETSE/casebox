Ext.define('CB.widget.TaskBar', {
    extend: 'Ext.toolbar.Toolbar'

    ,requires: [
        'Ext.button.Button'
        ,'Ext.resizer.Splitter'
        ,'Ext.menu.Menu'
    ]

    ,alias: 'widget.taskbar'

    ,cls: 'cb-taskbar'

    ,alwaysOnTop: true

    ,initComponent: function () {
        var me = this;

        me.windowBar = new Ext.toolbar.Toolbar(me.getWindowBarConfig());

        me.tray = new Ext.toolbar.Toolbar(me.getTrayConfig());

        me.items = [
            me.windowBar
            ,'-'
            ,me.tray
        ];

        me.windowMenu = new Ext.menu.Menu({
            defaultAlign: 'br-tr'
            ,items: [
                {text: L.Restore, handler: me.onWindowMenuRestore, scope: me}
                ,{text: L.Minimize, handler: me.onWindowMenuMinimize, scope: me}
                ,{text: L.Maximize, handler: me.onWindowMenuMaximize, scope: me}
                ,'-'
                ,{text: L.Close, handler: me.onWindowMenuClose, scope: me}
            ]
            ,listeners: {
                scope: me
                ,beforeshow: me.onWindowMenuBeforeShow
                ,hide: me.onWindowMenuHide
            }
        });

        me.callParent();
    }

    ,afterLayout: function () {
        var me = this;
        me.callParent();
        me.windowBar.el.on(
            'contextmenu'
            ,me.onButtonContextMenu
            ,me
        );
    },

    /**
     * This method returns the configuration object for the Tray toolbar. A derived
     * class can override this method, call the base version to build the config and
     * then modify the returned object before returning it.
     */
    getTrayConfig: function () {
        var ret = {
            items: this.trayItems
        };
        delete this.trayItems;

        return ret;
    }

    ,getWindowBarConfig: function () {
        return {
            flex: 1
            ,cls: 'cb-windowbar'
            ,items: ['&#160;']
            ,layout: {
                overflowHandler: 'Scroller'
            }
        };
    }

    ,getWindowBtnFromEl: function (el) {
        var c = this.windowBar.getChildByElement(el);
        return c || null;
    }

    ,onButtonContextMenu: function (e) {
        var me = this
            ,t = e.getTarget()
            ,btn = me.getWindowBtnFromEl(t);

        if (btn) {
            e.stopEvent();
            me.windowMenu.theWin = btn.win;
            me.windowMenu.showBy(t);
        }
    }

    //------------------------------------------------------
    // Window context menu handlers

    ,onWindowMenuBeforeShow: function (menu) {
        var items = menu.items.items
            ,win = menu.theWin;
        items[0].setDisabled(win.maximized !== true && win.hidden !== true); // Restore
        items[1].setDisabled(win.minimized === true); // Minimize
        items[2].setDisabled(win.maximized === true || win.hidden === true); // Maximize
    }

    ,onWindowMenuHide: function (menu) {
        Ext.defer(
            function() {
                menu.theWin = null;
            }
            ,1
        );
    }

    ,onWindowMenuRestore: function () {
        var me = this
            ,win = me.windowMenu.theWin;

        me.restoreWindow(win);
    }

    ,onWindowMenuMinimize: function () {
        var me = this
            ,win = me.windowMenu.theWin;

        win.minimize();
    }

    ,onWindowMenuMaximize: function () {
        var me = this
            ,win = me.windowMenu.theWin;

        win.maximize();
        win.toFront();
    }

    ,onWindowMenuClose: function () {
        var me = this
            ,win = me.windowMenu.theWin;

        win.close();
    }

    ,onWindowBtnClick: function (btn) {
        var win = btn.win;

        if (win.minimized || win.hidden) {
            btn.disable();
            win.show(
                null
                ,function() {
                    btn.enable();
                }
            );
        } else if (win.active) {
            btn.disable();
            win.on(
                'hide'
                ,function() {
                    btn.enable();
                }
                ,null
                ,{single: true}
            );
            win.minimize();
        } else {
            win.toFront();
        }
    }

    ,addTaskButton: function(win) {
        var config = {
            iconCls: win.iconCls
            ,textAlign: 'left'
            ,enableToggle: true
            ,toggleGroup: 'all'
            ,width: 140
            ,margin: '0 2 0 3'
            ,text: Ext.util.Format.ellipsis(win.title, 20)
            ,listeners: {
                // click: this.onWindowBtnClick
                toggle: this.onWindowBtnClick
                ,scope: this
            }
            ,win: win
        };

        var cmp = this.windowBar.add(config);

        this.setActiveButton(cmp);

        win.on('activate', this.markActive, this);
        win.on('beforeshow', this.markActive, this);
        win.on('deactivate', this.markInactive, this);
        win.on('minimize', this.minimizeWin, this);
        win.on('beforedestroy', this.removeWin, this);
        win.on('titlechange', this.onWindowTitleChange, this);
        win.on('iconclschange', this.onWindowIconChange, this);

        return cmp;
    }

    ,removeTaskButton: function (btn) {
        var found
            ,me = this;

        me.windowBar.items.each(
            function (item) {
                if (item === btn) {
                    found = item;
                }
                return !found;
            }
        );
        if (found) {
            me.windowBar.remove(found);
        }

        return found;
    }

    ,setActiveButton: function(btn) {
        this.windowBar.items.each(
            function (item) {
                if (item.isButton) {
                    item.toggle(false, true);
                }
            }
        );
        if (btn) {
            btn.toggle(true, true);
            btn.win.active = true;
        }
    }

    ,markActive: function(win) {
        if (this.activeWindow && this.activeWindow != win) {
            this.markInactive(this.activeWindow);
        }

        this.setActiveButton(win.taskButton);
        this.activeWindow = win;
        win.minimized = false;
        win.active = true;
    }

    ,markInactive: function(win) {
        if (win == this.activeWindow) {
            this.activeWindow = null;
            if(win.taskButton.el) {
                win.taskButton.toggle(false, true);
            }

            delete win.active;
        }
    }

    ,minimizeWin: function(win) {
        win.minimized = true;
        win.hide();
        this.markInactive(win);
    }

    ,removeWin: function(win) {
        this.removeTaskButton(win.taskButton);
    }

    ,restoreWindow: function (win) {
        if (win.isVisible()) {
            win.restore();
            win.toFront();
        } else {
            win.show();
        }
        return win;
    }

    ,onWindowTitleChange: function(win, newTitle, oldTitle, eOpts) {
        win.taskButton.setText(newTitle);
    }

    ,onWindowIconChange: function(win, newIconCls, oldIconCls, eOpts) {
        win.taskButton.setIconCls(newIconCls);
    }
});
