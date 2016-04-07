Ext.namespace('CB');

Ext.define('CB.object.ViewContainer', {
    extend: 'Ext.Panel'

    ,border: false
    ,layout: 'card'
    ,activeItem: 0

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
                    ,this.BC.get('star')
                    ,this.BC.get('unstar')
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

                        ,editobject: this.onEditObjectEvent
                        ,editmeta: this.onEditObjectEvent

                        ,loaded: this.onPluginsContainerLoaded
                        // ,loaded: this.onCardItemLoaded
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
                ,show: this.onShowEvent
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

        App.Favorites.on('change', this.onFavoritesChange, this);
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

            ,star: new Ext.Action({
                iconCls: 'i-star'
                ,qtip: L.Star
                ,itemId: 'star'
                ,scale: 'medium'
                ,hidden: true
                ,scope: this
                ,handler: this.onStarClick
            })

            ,unstar: new Ext.Action({
                iconCls: 'i-unstar'
                ,qtip: L.Unstar
                ,itemId: 'unstar'
                ,scale: 'medium'
                ,hidden: true
                ,scope: this
                ,handler: this.onUnstarClick
            })

            ,notifyOn: new Ext.Action({
                text: L.NotifyOn
                ,iconCls: 'im-watch'
                ,itemId: 'notifyOn'
                ,scope: this
                ,handler: this.onSubscriptionButtonClick
            })

            ,notifyOff: new Ext.Action({
                text: L.NotifyOff
                ,iconCls: 'im-ignore'
                ,itemId: 'notifyOff'
                ,scope: this
                ,handler: this.onSubscriptionButtonClick
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

            ,'setOwner': {
                text: L.SetOwner
                ,itemId: 'setOwner'
                ,menu: getMenuUserItems(
                    this.onSetOwnershipClick
                    ,this
                )
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
            ,new Ext.Button(this.actions.star)
            ,new Ext.Button(this.actions.unstar)
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
     * on show
     * @param  component
     * @return void
     */
    ,onShowEvent: function(c) {
        if(this.lastLoadData) {
            this.load(this.lastLoadData);
        }
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

        this.clear();

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
     * clear function
     * @return {[type]} [description]
     */
    ,clear: function() {
        this.delayedLoadTask.cancel();
        delete this.locked;
        delete this.requestedLoadData;
        this.previousLoadedData = Ext.clone(this.loadedData);
        this.loadedData = {};
        this.getLayout().activeItem.clear();
        this.updateToolbarAndMenuItems();
    }

    /**
     * loading an object into the panel in a specific view
     * @param  {[type]} objectData
     * @return {[type]}
     */
    ,load: function(objectData) {
        var el = this.getLayout().activeItem.getEl();

        if(!el || !el.isVisible(true)) {
            this.lastLoadData = objectData;
            return;
        }

        delete this.lastLoadData;

        if(this.locked) {
            delete this.requestedLoadData;
            return;
        }

        if(!isNaN(objectData)) {
            objectData = {
                id: objectData
            };
        }

        if(Ext.isEmpty(objectData) || Ext.isEmpty(objectData.id) || isNaN(objectData.id)) {
            this.clear();

            return;
        }

        var ai = this.getLayout().activeItem;

        //current view index
        var cvi = this.items.indexOf(ai);

        // check  if a new request is waiting to be loaded
        if(Ext.isEmpty(this.requestedLoadData)) {
            //check if object data are identical to previous loaded object
            if(!objectData.force) {
                if(this.loadedData && objectData &&
                    (objectData.id == this.loadedData.id) &&
                    (Ext.valueFrom(objectData.viewIndex, cvi) == Ext.valueFrom(this.loadedData.viewIndex, cvi))
                ) {
                    return;
                }
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
        // this.delayedLoadTask.cancel();
        this.clear();

        // save requested data
        this.requestedLoadData = Ext.apply({}, objectData);

        //automatic switch to plugins panel if different object types
        if(this.previousLoadedData &&
            (CB.DB.templates.getType(this.requestedLoadData.template_id) !=
            CB.DB.templates.getType(this.previousLoadedData.template_id))
        ) {
            this.setActiveView(0);
        }

        // this.loadedData = {};
        // this.items.getAt(0).clear();

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
            : null
            ,params = Ext.apply({id: id}, this.requestedLoadData);

        delete this.requestedLoadData;

        if(Ext.isDefined(params.viewIndex)) {
            this.setActiveView(params.viewIndex, false);
        }

        var activeItem = this.getLayout().activeItem;

        params.viewIndex = this.items.indexOf(activeItem);

        this.loadedData = params;

        switch(activeItem.getXType()) {
            case 'CBObjectPreview':
                this.topToolbar.setVisible(!Ext.isEmpty(id));
                this.doLayout();

                //used params by preview component to detect wich buttons to display when asked
                activeItem.params = params;

                activeItem.loadPreview(id);
                break;
            case 'CBObjectProperties':
            case 'CBEditObject':
                activeItem.load(this.loadedData);
                break;
        }
        this.onViewChange();
    }

    ,onPluginsContainerLoaded: function(cmp, params) {
        this.loadedData.subscription = params.subscription;
        this.onCardItemLoaded(cmp);
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

        if(this.menu) {
            this.menu.removeAll(true);
            this.menu.destroy();
        }

        this.menu = new Ext.menu.Menu({items:[]});

        this.BC.get('more').setMenu(this.menu, true);

        //hide all by default
        this.topToolbar.items.each(
            function(i) {
                if((['close'].indexOf(i.itemId) < 0) &&
                   (['tbfill', 'tbseparator'].indexOf(i.getXType()) < 0)
                ) {
                    i.hide();
                }
            }
        );

        if(!Ext.isNumeric(this.loadedData.id)) {
            return;
        }

        var ti = ai.getContainerToolbarItems();
        if(Ext.isEmpty(ti)) {
            return;
        }

        ti.menu['notifyOn'] = {addDivider: 'top'};
        ti.menu['notifyOff'] = {};

        /* update menu items */
        var isFirstItem = true;
        Ext.iterate(
            ti.menu
            ,function(k, v, o) {

                if(k === '-') {
                    this.menu.add('-');
                } else {
                    var b = (this.menuItemConfigs[k])
                        ? Ext.clone(this.menuItemConfigs[k])
                        : this.actions[k];

                    if(b) {
                        if ((!isFirstItem) &&
                          (v.addDivider === 'top')
                        ) {
                            this.menu.add('-');
                        }

                        this.menu.add(b);
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

        ti.tbar.star = {};
        ti.tbar.unstar = {};

        var subscription = Ext.valueFrom(this.loadedData.subscription, 'ignore');

        this.actions.notifyOn.setHidden(subscription === 'watch');
        this.actions.notifyOff.setHidden(subscription === 'ignore');

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

        this.onFavoritesChange();

        this.updateCreateMenu();
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

        this.openObjectWindow(objectData);
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

        switch(detectFileEditor(p.name)) {
            case 'webdav':
                App.openWebdavDocument(p);
                break;

            default:
                p.comment = this.getCommentValue();

                this.setCommentValue('');

                this.edit(p, e);
                break;
        }
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
        var data = {
            path: this.loadedData.id
            ,name: Ext.util.Format.htmlDecode(this.loadedData.name)
            ,scope: this
            ,callback: function(r, e) {
                this.loadedData.name = r.data.newName;
            }
        };

        App.promptRename(data);
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
            // delete this.locked;
            this.setActiveView(0, false);
            this.clear();
            // this.loadedData = {};
            // this.items.getAt(0).clear();
            // this.updateToolbarAndMenuItems();
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
        var p = Ext.clone(this.loadedData);

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

        d.comment = this.getCommentValue();

        this.setCommentValue('');

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

        var p = Ext.clone(data);

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

    ,onSubscriptionButtonClick: function(b, e) {
        var type = (b.itemId === 'notifyOn')
            ? 'watch'
            : 'ignore';

        CB_Objects.setSubscription(
            {
                objectId: this.loadedData.id
                ,type: type
            }
            ,function(r, e) {
                if(!r || (r.success !== true)) {
                    return;
                }

                this.actions.notifyOn.setHidden(type === 'watch');
                this.actions.notifyOff.setHidden(type === 'ignore');
            }
            ,this
        );
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
            , window.location.origin + '/' + App.config.coreName + '/view/' + this.loadedData.id + '/');
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

        if(!Ext.isEmpty(data.isNew) && (data.type !== 'time_tracking')) {
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

    ,onSetOwnershipClick: function(b, e) {
        if(!Ext.isEmpty(this.loadedData.id)) {
            CB_Objects.setOwnership(
                {
                    ids: this.loadedData.id
                    ,userId: b.userId
                }
                ,this.processSetOwnership
                ,this
            );
        }
    }

    ,processSetOwnership: function(r, e) {
        if(r && r.success) {
            App.fireEvent('objectchanged', this.loadedData, this);
        }
    }

    ,onStarClick: function(b, e) {
        var ld = this.loadedData
            ,d = {
                id: ld.id
                ,name: ld.name
                ,iconCls: ld.iconCls
                ,path: '/' + ld.pids + '/' + ld.id
                ,pathText: ld.path
            };

        App.Favorites.setStarred(d);
    }

    ,onUnstarClick: function(b, e) {
        App.Favorites.setUnstarred(this.loadedData.id);
    }

    ,onFavoritesChange: function() {
        if(this.loadedData) {
            var isStarred = App.Favorites.isStarred(this.loadedData.id);

            this.actions.star.setHidden(isStarred);
            this.actions.unstar.setHidden(!isStarred);
        }
    }
}
);

CB.object.ViewContainer.borrow(
    CB.object.view.Properties
    ,[
        'getCommentComponent'
        ,'getCommentValue'
        ,'setCommentValue'
    ]
);
