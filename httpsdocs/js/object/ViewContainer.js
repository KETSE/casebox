Ext.namespace('CB');

Ext.define('CB.object.ViewContainer', {
    extend: 'Ext.Panel'

    ,border: false
    ,layout: 'card'
    ,activeItem: 0
    ,tbarCssClass: 'x-panel-white'

    ,constructor: function() {

        Ext.apply(this, {
            loadedData: {}
        });

        this.callParent(arguments);
    }

    ,initComponent: function() {

        this.initButtons();

        Ext.apply(this, {
            tbar: new Ext.Toolbar({
                border: false
                ,style: 'background: #ffffff'
                ,defaults: {
                    scale: 'medium'
                }
                ,items: [
                    this.BC.get('edit')
                    ,this.BC.get('fitImage')
                    ,this.BC.get('download')
                    ,this.BC.get('completetask')
                    ,'->'
                    ,this.BC.get('preview')
                    ,this.BC.get('more')
                    ,'-'
                    ,this.BC.get('openExternal')
                    ,this.BC.get('close')
                ]
            })

            ,defaults: {
                border: false
                ,header: false
            }
            ,items: [{
                    xtype: 'CBObjectProperties'

                    ,api: CB_Objects.getPluginsData
                    ,listeners: {
                        scope: this

                        ,openpreview: this.onOpenPreviewEvent
                        ,openproperties: this.onOpenPreviewEvent

                        ,editobject: this.onEditObjectEvent
                        ,editmeta: this.onEditObjectEvent

                        ,loaded: this.onCardItemLoaded
                    }
                },{
                    xtype: 'CBObjectPreview'
                    ,listeners: {
                        scope: this
                        ,loaded: this.onCardItemLoaded
                    }
                }
            ]
            ,listeners: {
                scope: this
                ,lockpanel: this.onLockPanelEvent
                ,beforedestroy: this.onBeforeDestroy
            }
        });


        this.callParent(arguments);

        this.topToolbar = this.dockedItems.getAt(0);

        this.delayedLoadTask = new Ext.util.DelayedTask(this.doLoad, this);

        this.enableBubble(['changeparams', 'filedownload', 'createobject']);

        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
        App.on('objectchanged', this.onObjectChanged, this);
        App.on('objectsaction', this.onObjectsAction, this);
    }

    /**
     * init actions used in this component
     * @return void
     */
    ,initActions: function() {
        this.actions = {

            edit: new Ext.Action({
                iconCls: 'im-edit-obj'
                ,itemId: 'edit'
                ,text: L.Edit
                ,disabled: true
                ,scale: 'medium'
                ,scope: this
                ,handler: this.onEditClick
            })

            ,download: new Ext.Action({
                text: L.Download
                ,itemId: 'download'
                ,iconCls: 'im-download'
                ,hidden: true
                ,scale: 'medium'
                ,scope: this
                ,handler: this.onDownloadClick
            })

            ,fitImage: new Ext.Action({
                iconCls: 'im-fit'
                ,itemId: 'fitImage'
                ,hidden: true
                ,enableToggle: true
                ,pressed: true
                ,scale: 'medium'
                ,scope: this
                ,handler: this.onFitImageClick
            })

            ,completeTask: new Ext.Action({
                iconCls: 'im-task-complete'
                ,itemId: 'completetask'
                ,text: L.Done
                ,hidden: true
                ,scale: 'medium'
                ,scope: this
                ,handler: this.onCompleteTaskClick
            })

            ,preview: new Ext.Action({
                iconCls: 'im-preview'
                ,itemId: 'preview'
                ,enableToggle: true
                ,qtip: L.Preview
                ,hidden: true
                ,scale: 'medium'
                ,scope: this
                ,handler: this.onPreviewClick
            })

            ,openExternal: new Ext.Action({
                iconCls: 'im-external'
                ,itemId: 'openExternal'
                ,scale: 'medium'
                ,hidden: true
                ,scope: this
                ,handler: this.onOpenExternalClick
            })

            ,close: new Ext.Action({
                iconCls: 'im-cancel'
                ,itemId: 'close'
                ,scale: 'medium'
                ,scope: this
                ,handler: this.onCloseClick
            })

        };
    }

    /**
     * define buttons config, init ButtonCollection
     * @return void
     */
    ,initButtons: function() {
        this.initActions();

        //define button configs
        this.menuItemConfigs = {
            reload: {
                iconCls: 'i-refresh'
                ,itemId: 'reload'
                ,text: L.Refresh
                ,scope: this
                ,handler: this.onReloadClick
            }

            ,completetask: {
                iconCls: 'im-task-complete'
                ,itemId: 'completetask'
                ,scale: 'medium'
                ,text: L.Done
                ,scope: this
                ,handler: this.onCompleteTaskClick
            }

            ,closetask: {
                text: L.ClosingTask
                ,itemId: 'closetask'
                ,scope: this
                ,handler: this.onCloseTaskClick
            }

            ,reopentask: {
                text: L.ReopeningTask
                ,itemId: 'reopentask'
                ,scope: this
                ,handler: this.onReopenTaskClick
            }

            ,subscribe: {
                text: L.Subscribe
                ,itemId: 'subscribe'
                ,scope: this
                ,handler: this.onSubscribeClick
            }

            ,unsubscribe: {
                text: L.Unsubscribe
                ,itemId: 'unsubscribe'
                ,scope: this
                ,handler: this.onUnsubscribeClick
            }

            // ,metadata: {
            //     text: L.Metadata
            //     ,itemId: 'metadata'
            //     ,scope: this
            //     ,handler: this.onOpenExternalClick
            // }

            ,rename: {
                itemId: 'rename'
                ,text: L.Rename
                ,scope: this
                ,handler: this.onRenameClick
            }

            ,permissions: {
                itemId: 'permissions'
                ,text: L.Permissions
                ,scope: this
                ,handler: this.onPermissionsClick
            }

            ,webdavlink: {
                text: L.WebDAVLink
                ,itemId: 'webdavlink'
                ,scope: this
                ,handler: this.onWebDAVLinkClick
            }

            ,permalink: {
                text: L.Permalink
                ,itemId: 'permalink'
                ,scope: this
                ,handler: this.onPermalinkClick
            }

            ,'new': {
                text: L.New
                ,itemId: 'newItem'
                ,name: 'newmenu'
                ,menu: []
            }
        };

        /* will use BC abreviation for Button Collection */
        this.BC = new Ext.util.MixedCollection();

        this.BC.addAll([
            new Ext.Button(this.actions.edit)
            ,new Ext.Button(this.actions.download)
            ,new Ext.Button(this.actions.close)
            ,new Ext.Button(this.actions.openExternal)
            ,new Ext.Button(this.actions.fitImage)
            ,new Ext.Button(this.actions.completeTask)
            ,new Ext.Button(this.actions.preview)

            ,new Ext.Button({
                itemId: 'more'
                ,arrowVisible: false
                ,iconCls: 'im-points'
                ,scale: 'medium'
                ,menu: []
            })
        ]);
    }

    /**
     * remove listeners on destroy
     * @param  component
     * @return void
     */
    ,onBeforeDestroy: function(c) {
        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
        App.un('objectchanged', this.onObjectChanged, this);
        App.un('objectsaction', this.onObjectsAction, this);
    }

    /**
     * change active view (properties/preview)
     * @param int index
     * @param bool autoLoad
     */
    ,setActiveView: function(index, autoLoad){
        var currentItemIndex = this.items.indexOf(this.getLayout().activeItem);

        if(currentItemIndex == index) {
            return;
        }
        this.loadedData = {};
        this.getLayout().activeItem.clear();

        this.getLayout().setActiveItem(index);

        this.onViewChange();

        if(autoLoad !== false) {
            this.load(this.requestedLoadData);
        }
    }

    /**
     * adjustments on view change
     * @return void
     */
    ,onViewChange: function() {
        var d = this.loadedData;
        this.actions.edit.setDisabled(isNaN(d.id) || Ext.isEmpty(d.id));
        this.BC.get('preview').toggle(this.loadedData.viewIndex == 1, false);
    }

    /**
     * loading an object into the panel in a specific view
     * @param  {[type]} objectData
     * @return {[type]}
     */
    ,load: function(objectData) {
        var el = this.getLayout().activeItem.getEl();

        if(!el || !el.isVisible(true)) {
            return;
        }

        if(this.locked) {
            delete this.requestedLoadData;
            return;
        }

        if(!isNaN(objectData)) {
            objectData = {
                id: objectData
            };
        }

        if(Ext.isEmpty(objectData.id) || isNaN(objectData.id)) {
            this.items.getAt(0).clear();
            return;
        }

        var ai = this.getLayout().activeItem;

        //current view index
        var cvi = this.items.indexOf(ai);

        // check  if a new request is waiting to be loaded
        if(Ext.isEmpty(this.requestedLoadData)) {
            //check if object data are identical to previous loaded object
            if(this.loadedData && objectData &&
                (objectData.id == this.loadedData.id) &&
                (Ext.valueFrom(objectData.viewIndex, cvi) == Ext.valueFrom(this.loadedData.viewIndex, cvi))
            ) {
                return;
            }

            // save current scroll position for history navigation
            if(!Ext.isEmpty(ai.body)) {
                this.loadedData.scroll = ai.body.getScroll();
            }
        } else {
            //check if object data are identical to previous load request
            if((objectData.id == this.requestedLoadData.id) &&
                (Ext.valueFrom(objectData.viewIndex, cvi) == Ext.valueFrom(this.requestedLoadData.viewIndex, cvi))
            ) {
                return;
            }
        }

        // cancel previous wating request and start a new one
        this.delayedLoadTask.cancel();

        // save requested data
        this.requestedLoadData = Ext.apply({}, objectData);

        //automatic switch to plugins panel if different object types
        if(CB.DB.templates.getType(this.requestedLoadData.template_id) !=
            CB.DB.templates.getType(this.loadedData.template_id)
        ) {
            this.setActiveView(0);
        }

        this.loadedData = {};
        this.items.getAt(0).clear();

        // instantiate a delay to exclude flood requests
        this.delayedLoadTask.delay(3, this.doLoad, this);
    }

    /**
     * direct loading method of the this.requestedLoadData
     * @return void
     */
    ,doLoad: function() {
        if(this.locked) {
            delete this.requestedLoadData;
            return;
        }

        var id = this.requestedLoadData
            ? Ext.valueFrom(this.requestedLoadData.nid, this.requestedLoadData.id)
            : null;

        this.loadedData = Ext.apply({id: id}, this.requestedLoadData);

        if(Ext.isDefined(this.loadedData.viewIndex)) {
            this.setActiveView(this.loadedData.viewIndex, false);
        }

        delete this.requestedLoadData;

        var activeItem = this.getLayout().activeItem;

        this.loadedData.viewIndex = this.items.indexOf(activeItem);
        switch(activeItem.getXType()) {
            case 'CBObjectPreview':
                this.topToolbar.setVisible(!Ext.isEmpty(id));
                this.doLayout();

                //used params by preview component to detect wich buttons to display when asked
                activeItem.params = this.loadedData;

                activeItem.loadPreview(id);
                break;
            case 'CBObjectProperties':
            case 'CBEditObject':
                activeItem.load(this.loadedData);
                break;
        }
        this.onViewChange();
    }

    /**
     * adjustments on view loaded
     * @param  object item
     * @return void
     */
    ,onCardItemLoaded: function(item) {
        this.locked = false;

        this.updateToolbarAndMenuItems();

        this.fireEvent('loaded', this, item);

        if(Ext.isEmpty(this.loadedData) || Ext.isEmpty(this.loadedData.scroll)) {
            return;
        }
        if(item.body) {
            item.body.scrollTo('left', this.loadedData.scroll.left);
            item.body.scrollTo('top', this.loadedData.scroll.top);
        }
    }

    /**
     * update toolbar and menu item corresponding to active view
     * @return void
     */
    ,updateToolbarAndMenuItems: function() {
        var ai = this.getLayout().activeItem;
        var ti = ai.getContainerToolbarItems();

        if(this.menu) {
            this.menu.removeAll(true);
            this.menu.destroy();
        }
        this.menu = new Ext.menu.Menu({items:[]});

        this.BC.get('more').setMenu(this.menu, true);

        if(Ext.isEmpty(ti)) {
            return;
        }

        this.topToolbar.items.each(
            function(i) {
                if((i.itemId != ('close')) && (['tbfill', 'tbseparator'].indexOf(i.getXType()) < 0)) {
                    i.hide();
                }
            }
        );

        /* update menu items */
        var isFirstItem = true;
        Ext.iterate(
            ti.menu
            ,function(k, v, o) {

                if(k == '-') {
                    this.menu.add('-');
                } else {
                    var b = this.menuItemConfigs[k];
                    if(b) {
                        if ((!isFirstItem) &&
                          (v.addDivider == 'top')
                        ) {
                            this.menu.add('-');
                        }

                        var cfg = Ext.apply({}, b);
                        var item = this.menu.add(cfg);
                        isFirstItem = false;
                    }
                }
            }
            ,this
        );

        //add "more" button to toolbar config if menu is not empty
        if(this.menu.items.getCount() > 0) {
            ti.tbar['more'] = {};
        }

        // hide all bottons from toolbar
        Ext.iterate(
            ti.tbar
            ,function(k, v, o) {
                var b = this.BC.get(k);
                //if not defined the we should add this custom button
                //to the collection to be available later
                if(b) {
                    if(b.baseAction) {
                        b.baseAction.show();
                    } else {
                        b.show();
                    }
                }
            }
            ,this
        );
    }

    /**
     * update create menu under the points button
     * @return {[type]} [description]
     */
    ,updateCreateMenu: function() {
        if(!this.menu) {
            return;
        }

        var nmb = this.menu.child('[name="newmenu"]');

        if(nmb) {
            updateMenu(
                nmb
                ,this.getLayout().activeItem.createMenu
                ,this.onCreateObjectClick
                ,this
            );
            nmb.setDisabled(nmb.menu.items.getCount() < 1);
        }
    }

    /**
     * edit an item
     * Shold be reviewed and merge with next method
     * or even moved/merged to mainViewPort component
     * @param  object objectData
     * @param  event e
     * @return void
     */
    ,edit: function (objectData, e) {
        objectData.view = 'edit';

        switch(detectFileEditor(objectData.name)) {
            case 'webdav':
                App.openWebdavDocument(objectData);
                break;

            default:
                this.openObjectWindow(objectData);
                break;
        }
    }

    /**
     * open an object edit window with given data
     * @param  object objectData
     * @return void
     */
    ,openObjectWindow: function(objectData) {
        var data = Ext.apply({}, objectData);
        //edit object in popup window
        delete data.html;
        App.openObjectWindow(data);
    }

    /**
     * handler for edit button click
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onEditClick: function(b, e) {
        var p = Ext.apply({}, this.loadedData);

        this.edit(p);
    }

    /**
     * handler for reload button
     * Reloads active view (properties or preview)
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onReloadClick: function(b, e) {
        this.getLayout().activeItem.reload();
    }

    /**
     * handler for rename button
     * Open permissions window for loaded item by calling viewport method
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onRenameClick: function(b, e) {
        Ext.Msg.prompt(
            L.Rename
            ,L.Name
            ,function(btn, text, opt) {
                if(btn !== 'ok') {
                    return;
                }

                CB_BrowserView.rename(
                    {
                        path: this.loadedData.id
                        ,name: text
                    }
                    ,function(r, e){
                        if(r.success !== true){
                            return;
                        }

                        this.loadedData.name = r.data.newName;

                        App.fireEvent(
                            'objectchanged'
                            ,{
                                id: parseInt(r.data.id, 10)
                                ,pid: r.data.pid
                            }
                            ,e
                        );
                    }
                    ,this
                );
            }
            ,this
            ,false
            ,Ext.util.Format.htmlDecode(this.loadedData.name)
        ).setWidth(400).center();
    }

    /**
     * handler for permissions button
     * Open permissions window for loaded item by calling viewport method
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onPermissionsClick: function(b, e) {
        App.mainViewPort.openPermissions(this.loadedData.id);
    }

    /**
     * event handler for objects deletion
     *
     * There is no need to reload this view because the grid will reload and change the selection,
     * but need to cancel the edit
     *
     * @param  array ids
     * @param  object e
     * @return void
     */
    ,onObjectsDeleted: function(ids, e) {
        if(!Ext.isEmpty(this.loadedData) && setsHaveIntersection(ids, this.loadedData.id)) {
            delete this.locked;
            this.setActiveView(0, false);
            this.loadedData = {};
            this.items.getAt(0).clear();
            this.updateToolbarAndMenuItems();
        }
    }

    /**
     * handler for fit image toolbar button
     * toggle fit image preview
     * This method actually should be managed by preview component
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onFitImageClick: function(b, e) {
        var ai = this.getLayout().activeItem;
        if(ai.onFitImageClick) {
            ai.onFitImageClick(b, e);
        }
    }

    /**
     * handler for preview toolbar button
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onPreviewClick: function(b, e) {
        var p = Ext.apply({}, this.loadedData);

        p.viewIndex = b.pressed
            ? 1
            : 0;

        this.delayedLoadTask.cancel();
        this.requestedLoadData = p;

        this.doLoad();
    }

    /**
     * handler for open external toolbar button
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onOpenExternalClick: function(b, e) {
        var d = Ext.apply({}, this.loadedData);
        this.openObjectWindow(d);
    }

    /**
     * handler for open preview from components below
     *
     * It was opening preview in current component,
     * when editing on the right side was available.
     * Now it opens popup window in preview mode.
     *
     * @param  object data
     * @param  event e
     * @return void
     */
    ,onOpenPreviewEvent: function(data, e) {
        if(Ext.isEmpty(data)) {
            data = this.loadedData;
        }

        if(this.loadedData && (data.id == this.loadedData.id)) {
            Ext.applyIf(data, this.loadedData);
        }

        App.openObjectWindow(Ext.clone(data), e);
    }

    /**
     * handler for open edit object event from components below
     *
     * It was opening edit in current component,
     * when editing on the right side was available.
     * Now it opens popup window in edit mode.
     *
     * @param  object data
     * @param  event e
     * @return void
     */
    ,onEditObjectEvent: function(data, e) {
        if(e) {
            e.stopEvent();
        }

        if(Ext.isEmpty(data)) {
            data = this.loadedData;
        }

        var p = Ext.apply({}, data);

        this.edit(p, e);
    }

    /**
     * handler for download toolbar button
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onDownloadClick: function(b, e) {
        this.fireEvent('filedownload', [this.loadedData.id], false, e);
    }

    /**
     * handler for creating new items from dropdown menu
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onCreateObjectClick: function(b, e) {
        this.goBackOnSave = true;

        var d = b.config.data;
        d.pid = this.loadedData.id;
        d.path = this.loadedData.path;
        this.fireEvent('createobject', d, e);
    }

    /**
     * handler for close task toolbar button
     * It is available when an active task is loaded.
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onCloseTaskClick: function(b, e) {
        this.getEl().mask(L.CompletingTask + ' ...', 'x-mask-loading');
        CB_Tasks.close(this.loadedData.id, this.onTaskChanged, this);
    }

    /**
     * handler for reopen a closed task toolbar button
     * It is available when a closed task is loaded.
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onReopenTaskClick: function(b, e) {
        this.getEl().mask(L.ReopeningTask + ' ...', 'x-mask-loading');
        CB_Tasks.reopen(this.loadedData.id, this.onTaskChanged, this);
    }

    /**
     * handler for completing task toolbar button
     * It is available when a task is loaded.
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onCompleteTaskClick: function(b, e) {
        CB_Tasks.complete(
            {
                id: this.loadedData.id
                ,message: ''
            }
            ,this.onTaskChanged
            ,this
        );
    }

    /**
     * common handler for task actions responce
     *
     * @param  responce r
     * @param  event e
     * @return void
     */
    ,onTaskChanged: function(r, e){
        this.getEl().unmask();
        App.fireEvent('objectchanged', this.loadedData, this);
    }

    /**
     * handler for Subscribe menu button
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onSubscribeClick: function(b, e) {
        Ext.Msg.show({
            title: L.Subscribe
            ,msg: L.SubscribeMsg
            ,width: 300
            ,buttons: Ext.MessageBox.YESNOCANCEL
            ,buttonText: {
                yes: L.Subscribe
                ,no: L.SubscribeRecursive
                ,cancel: Ext.Msg.buttonText.cancel
            }
            ,scope: this
            ,fn: function(b, t) {
                if(b !== 'cancel') {
                    CB_Browser.subscribe(
                        {
                            id: this.loadedData.id
                            ,recursive: (b == 'no')
                        }
                        ,this.onSubscribeProcess
                        ,this
                    );
                }
            }
            ,icon: Ext.MessageBox.QUESTION
        });
    }

    /**
     * handler for Unubscribe menu button
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onUnsubscribeClick: function () {
        CB_Browser.unsubscribe(
            {
                id: this.loadedData.id
            }
            ,this.onSubscribeProcess
            ,this
        );
    }

    /**
     * handler for processing Subscribe/Unsubscribe action responces
     *
     * @param  responce r
     * @param  event e
     * @return void
     */
    ,onSubscribeProcess: function (r, e) {
        if(r.success !== true) {
            return;
        }

        this.onReloadClick();
    }

    /**
     * handler for WebDav Link menu button click
     * Shows a window with link for WebDav editing
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onWebDAVLinkClick: function(b, e) {
        App.openWebdavDocument(
            this.loadedData
            ,false
        );
    }

    /**
     * handler for Permalink menu button click
     * Shows a prompt window with permalink
     *
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onPermalinkClick: function(b, e) {
        window.prompt(
            'Copy to clipboard: Ctrl+C, Enter'
            , window.location.origin + '/' + App.config.coreName + '/v-' + this.loadedData.id + '/');
    }

    /**
     * handler for lockpanel event
     * Current component wouldnt accept any load requests when locked
     *
     * @param  boolean status
     * @return void
     */
    ,onLockPanelEvent: function(status) {
        this.locked = status;
    }

    /**
     * method for setting selected file version on contained versions plugin
     * This method was used by files editing window
     * Now it would be changed/removed
     *
     * @param object params
     */
    ,setSelectedVersion: function(params) {
        var ai = this.getLayout().activeItem;
        if(ai.isXType('CBObjectProperties')) {
            ai.setSelectedVersion(params);
        }
    }

    /**
     * handler for objects action event (move/copy)
     *
     * @param  object r responce
     * @param  event e
     * @return void
     */
    ,onObjectsAction: function(action, r, e){
        if(this.loadedData.id == r.targetId) {
            this.onReloadClick();
        }
    }

    /**
     * handler for global object change event
     * Here we react and reload the view if necessary
     * @param  object data
     * @param  component component
     * @return void
     */
    ,onObjectChanged: function(data, component) {
        if(!isNaN(data)) {
            data = {id: data};
        }

        if(!Ext.isEmpty(data.isNew)) {
            this.requestedLoadData = data;
            this.doLoad();
            return;
        }

        if(!Ext.isEmpty(this.loadedData)) {
            if((data.pid == this.loadedData.id) || (data.id == this.loadedData.id)) {
                this.onReloadClick();
            }
        }
    }

    /**
     * close this view container (hides it)
     * @return void
     */
    ,onCloseClick: function() {
        this.collapse();
    }
}
);
