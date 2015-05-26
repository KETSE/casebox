Ext.namespace('CB.browser');

Ext.define('CB.browser.ViewContainer', {
    extend: 'Ext.Panel'

    ,xtype: 'CBBrowserViewContainer'

    ,title: 'Browser'
    ,iconCls: 'icon-folder'
    ,closable: true
    ,border: false
    ,layout:'fit'
    ,params: {
        descendants: false
    }

    ,defaultToolbarItems: [
        '->'
        ,'reload'
        ,'apps'
        ,'-'
        ,'more'
    ]

    ,initComponent: function(){
        var pageSize = Ext.valueFrom(App.loginData.cfg.max_rows, 50);
        this.instanceId = Ext.id();

        this.actions = {
            edit: new Ext.Action({
                text: L.Edit
                ,itemId: 'edit'
                ,scope: this
                ,handler: this.onEditClick
            })

            ,reload: new Ext.Action({
                iconCls: 'im-refresh'
                ,itemId: 'reload'
                ,scale: 'medium'
                ,tooltip: L.Refresh
                ,scope: this
                ,handler: this.onReloadClick
            })

            ,contextReload: new Ext.Action({
                iconCls: 'icon-refresh'
                ,text: L.Refresh
                ,scope: this
                ,handler: this.onReloadClick
            })

            ,upload: new Ext.Action({
                text: L.Upload
                ,itemId: 'upload'
                ,scale: 'medium'
                ,iconCls: 'im-upload'
                ,scope: this
                ,handler: this.onUploadClick
            })

            ,download: new Ext.Action({
                text: L.Download
                ,itemId: 'download'
                ,scale: 'medium'
                ,iconCls: 'im-download'
                ,hidden: true
                ,disabled: true
                ,hideParent: false
                ,scope: this
                ,handler: this.onDownloadClick
            })

            ,contextDownload: new Ext.Action({
                text: L.Download
                ,iconCls: 'i-download'
                ,hidden: true
                ,scope: this
                ,handler: this.onDownloadClick
            })

            ,cut: new Ext.Action({
                text: L.Cut
                ,itemId: 'cut'
                ,scope: this
                ,disabled: true
                ,handler: this.onCutClick
            })

            ,copy: new Ext.Action({
                text: L.Copy
                ,itemId: 'copy'
                ,scope: this
                ,disabled: true
                ,handler: this.onCopyClick
            })

            ,paste: new Ext.Action({
                text: L.Paste
                ,itemId: 'paste'
                ,scope: this
                ,disabled: true
                ,handler: this.onPasteClick
            })

            ,pasteShortcut: new Ext.Action({
                text: L.PasteShortcut
                ,itemId: 'pasteshortcut'
                ,scope: this
                ,disabled: true
                ,handler: this.onPasteShortcutClick
            })

            ,takeOwnership: new Ext.Action({
                text: L.TakeOwnership
                ,itemId: 'takeownership'
                ,iconCls: 'icon-user-gray'
                ,disabled: true
                ,scope: this
                ,handler: this.onTakeOwnershipClick
            })

            ,'delete': new Ext.Action({
                qtip: L.Delete
                ,text: L.Delete
                ,itemId: 'delete'
                ,iconCls: 'im-trash'
                ,scale: 'medium'
                ,hidden: true
                ,disabled: true
                ,hideParent: false
                ,scope: this
                ,handler: this.onDeleteClick
            })

            ,contextDelete: new Ext.Action({
                text: L.Delete
                ,iconCls: 'i-trash'
                ,disabled: true
                ,hideParent: false
                ,scope: this
                ,handler: this.onDeleteClick
            })

            ,contextRename: new Ext.Action({
                text: L.Rename
                ,iconCls: 'i-rename'
                ,scope: this
                ,handler: this.onRenameClick
            })

            ,contextExport: new Ext.Action({
                iconCls: 'i-table-export'
                ,text: L.Export
                ,scope: this
                ,handler: this.onExportClick
            })

            ,restore: new Ext.Action({
                text: L.Restore
                ,itemId: 'restore'
                ,iconCls: 'im-restore'
                ,scale: 'medium'
                ,hidden: true
                ,disabled: true
                ,hideParent: false
                ,scope: this
                ,handler: this.onRestoreClick
            })

            ,permissions: new Ext.Action({
                text: L.Permissions
                ,itemId: 'permissions'
                ,iconCls: 'icon-key'
                ,scope: this
                ,disabled: true
                ,handler: this.onPermissionsClick
            })

            ,preview: new Ext.Action({
                itemId: 'preview'
                ,scale: 'medium'
                ,iconCls: 'im-preview'
                ,scope: this
                ,hidden: true
                ,disabled: true
                ,handler: this.onPreviewClick
            })
        };

        this.tbarMoreMenu = new Ext.menu.Menu({
            items: [
                {
                    text: L.Rows
                    ,menu: [
                        {
                            xtype: 'menucheckitem'
                            ,text: 25
                            ,group: 'gvrc' //grid view row count
                            ,checked: (pageSize == 25)
                            ,scope: this
                            ,handler: this.onRowCountChangeClick
                        },{
                            xtype: 'menucheckitem'
                            ,text: 50
                            ,group: 'gvrc'
                            ,checked: (pageSize == 50)
                            ,scope: this
                            ,handler: this.onRowCountChangeClick
                        },{
                            xtype: 'menucheckitem'
                            ,text: 100
                            ,group: 'gvrc'
                            ,checked: (pageSize == 100)
                            ,scope: this
                            ,handler: this.onRowCountChangeClick
                        },{
                            xtype: 'menucheckitem'
                            ,text: 200
                            ,group: 'gvrc'
                            ,checked: (pageSize == 200)
                            ,scope: this
                            ,handler: this.onRowCountChangeClick
                        }
                    ]
                }
                ,this.actions.contextExport
            ]
        });

        this.buttonCollection = new Ext.util.MixedCollection();

        this.buttonCollection.addAll([
            new Ext.Button({
                qtip: L.Views
                ,itemId: 'apps'
                ,arrowVisible: false
                ,iconCls: 'im-apps'
                ,scale: 'medium'
                ,menu: []
            })
            ,new Ext.Button({
                qtip: L.New
                ,text: L.New
                ,itemId: 'create'
                ,iconCls: 'im-create'
                ,disabled: true
                ,scale: 'medium'
                ,menu: [
                ]
            })
            ,new Ext.Button(this.actions.reload)
            ,new Ext.Button(this.actions.upload)
            ,new Ext.Button(this.actions.download)
            ,new Ext.Button({
                text: L.Clipboard
                ,itemId: 'edit'
                ,iconCls: 'im-assignment'
                ,scale: 'medium'
                ,menu: [
                    this.actions.cut
                    ,this.actions.copy
                    ,this.actions.paste
                    ,this.actions.pasteShortcut
                    ,'-'
                    ,this.actions.takeOwnership
                ]
            })
            ,new Ext.Button(this.actions.preview)
            ,new Ext.Button(this.actions.restore)
            ,new Ext.Button(this.actions['delete'])
            ,new Ext.Button({
                qtip: L.More
                ,itemId: 'more'
                ,arrowVisible: false
                ,iconCls: 'im-points'
                ,scale: 'medium'
                ,menu: this.tbarMoreMenu
            })
        ]);

        this.viewToolbar = new Ext.Toolbar({
            border: false
            ,style: 'background: #ffffff'
            ,defaults: {
                scale: 'medium'
            }
            //each view should define it's custom buttons in buttonCollection
            //and specify buttons for diplay
            ,items: []
            ,listeners: {
                scope: this
                ,afterlayout: function(c) {
                    var ic = c.items.getCount();
                    for (var i = 0; i < ic; i++) {
                        if(c.items.getAt(i).disabled) {
                            c.items.getAt(i).hide();
                        }
                    }
                }
            }
        });

        this.descendantsButton = new Ext.Button({
            text: ' ... '
            ,tooltip: L.Descendants
            ,enableToggle: true
            ,allowDepress: true
            ,width: 20
            ,scope: this
            ,handler: this.onDescendantsClick
        });

        this.objectPanel = new CB.object.ViewContainer({
            region: 'east'
            ,header: false
            ,width: 250

            ,split: {
                size: 2
                ,collapsible: false
                ,style: 'background-color: #dfe8f6'
            }
            ,collapsible: true
            ,collapseMode: 'mini'

            ,stateful: true
            ,stateId: 'mopp' //main object properties panel

            ,listeners: {
                scope: this
                // update right panel view on expand
                // because it doesnt load anything when collapsed
                ,expand: function() {
                    this.actions.preview.setDisabled(true);
                    this.actions.preview.setHidden(true);
                    // this.updatePreview();
                }
                ,collapse: function() {
                    this.actions.preview.setDisabled(false);
                    this.actions.preview.setHidden(false);
                }
            }

            ,onCloseClick: Ext.Function.bind(this.onCloseRightPanelClick, this)
        });


        this.store = new Ext.data.DirectStore({
            autoLoad: false
            ,autoDestroy: true
            ,remoteSort: true
            ,sortOnLoad: false
            ,extraParams: {}
            ,pageSize: pageSize
            ,model: 'Items'
            ,proxy: new  Ext.data.DirectProxy({
                paramsAsHash: true
                ,directFn: CB_BrowserView.getChildren
                ,reader: {
                    type: 'json'
                    ,successProperty: 'success'
                    ,idProperty: 'nid'
                    ,rootProperty: 'data'
                    ,messageProperty: 'msg'
                }
            })

            ,loadPage: function(page, options) {
                var store = this.store,
                size = store.getPageSize();

                store.currentPage = page;

                // Clon options
                options = Ext.apply({
                    page: page,
                    start: (page - 1) * size,
                    rows: size,
                }, options);

                this.changeSomeParams(options);
            }.bind(this)

            ,listeners: {
                scope: this
                ,beforeload: this.onBeforeStoreLoad
                ,load: this.onStoreLoad
            }
        });

        this.store.proxy.reader.readRecords = Ext.Function.createInterceptor(this.store.proxy.reader.readRecords, CB.DB.convertJsonReaderDates);

        var getPropertyHandler = this.getProperty.bind(this);

        this.cardContainer = new Ext.Panel({
            layout: 'card'
            ,activeItem: 0
            ,border: false
            ,region: 'center'
            ,tbar: this.viewToolbar
            ,items: [
                new CB.browser.view.Grid({
                    border: false
                    ,refOwner: this
                    ,store: this.store
                    ,showObjectPropertiesPanel: true
                    ,getProperty: getPropertyHandler
                })
                // ,new CB.browser.view.Calendar({
                //     border: false
                //     ,refOwner: this
                //     ,store: this.store
                //     ,getProperty: getPropertyHandler
                //     ,listeners: {
                //         scope: this
                //         ,openobject: this.onObjectsOpenEvent
                //     }
                // })
                ,new CB.browser.view.Charts({
                    border: false
                    ,refOwner: this
                    ,addDivider: true // forr atdding a divider in menu before this view element
                    ,store: this.store
                    ,getProperty: getPropertyHandler
                })
                ,new CB.browser.view.Pivot({
                    refOwner: this
                    ,border: false
                    ,store: this.store
                    ,getProperty: getPropertyHandler
                })
            ]
            ,listeners: {
                scope: this
                ,add: function(o, c, idx) {
                    if(c.isXType('CBBrowserViewInterface')) {
                        var b = this.buttonCollection.get('apps');
                        if(c.addDivider === true) {
                            b.menu.add('-');
                        }
                        b.menu.add({
                            text: c.title
                            ,iconCls: c.iconCls
                            ,scope: this
                            ,viewIndex: idx
                            ,handler: this.onCardItemChangeClick
                        });
                    }
                }
                ,selectionchange: this.onObjectsSelectionChange
                ,objectopen: this.onObjectsOpenEvent
            }
        });

        this.loadParamsTask = new Ext.util.DelayedTask(this.loadParams, this);

        App.fireEvent('browserinit', this);

        Ext.apply(this, {
            cls: 'x-panel-white'
            ,items: [{
                layout: 'border'
                ,border: false
                ,tbarCssClass: 'x-panel-gray'
                ,items: [
                    this.cardContainer
                    ,this.objectPanel
                ]
            }]

            ,listeners: {
                scope: this

                ,render: function() {
                    this.onSetToolbarItems(null);
                }

                ,changeparams: this.changeSomeParams

                ,settoolbaritems: this.onSetToolbarItems

                ,reload: this.onReloadClick

                // ,activate: function() {
                //     this.updatePreview();
                // }

                ,itemcontextmenu: this.onItemContextMenu
            }
        });

        this.callParent(arguments);

        this.enableBubble([
            'viewloaded'
            ,'fileupload'
            ,'filedownload'
            ,'createobject'
        ]);

        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);

        App.clipboard.on('change', this.onClipboardChange, this);
        App.clipboard.on('pasted', this.onClipboardAction, this);

        App.on('objectsaction', this.onObjectsAction, this);
        App.on('objectchanged', this.onObjectChanged, this);
        App.on('filesuploaded', this.onClipboardAction, this);
    }

    ,onBeforeDestroy: function(p){
        App.clipboard.un('change', this.onClipboardChange, this);
        App.clipboard.un('pasted', this.onClipboardAction, this);

        App.un('objectsaction', this.onObjectsAction, this);
        App.un('filesuploaded', this.onClipboardAction, this);
    }

    ,getProperty: function(propertyName){
        if(propertyName == 'nid') propertyName = 'id';
        if(this.folderProperties && this.folderProperties[propertyName]) {
            return this.folderProperties[propertyName];
        }
        return null;
    }

    /**
     * set tollbar items
     * @param  array|null buttonsArray array of button ids, separator or spacer.
     *                                 Set to null to just hide all buttons in toolbar.
     * @return void
     */
    ,onSetToolbarItems: function(buttonsArray) {
        if(!this.getEl().isVisible(true)) {
            return;
        }
        var i, b;

        //suspend toolbar layout
        this.viewToolbar.suspendLayout = true;

        //hide all buttons and remove separators
        while(this.viewToolbar.items.getCount() > 0) {
            b = this.viewToolbar.items.getAt(0);
            b.hide();
            this.viewToolbar.remove(b, b.isXType('tbseparator'));
        }

        if(buttonsArray === null) {
            buttonsArray = this.defaultToolbarItems;
        }
        //add more button
        if(buttonsArray.indexOf('more') < 0) {
            buttonsArray.push('more');
        }

        //add apps button if not present
        if(buttonsArray.indexOf('apps') < 0) {
            buttonsArray.push('apps');
        }

        //add preview button if not present
        if(buttonsArray.indexOf('preview') < 0) {
            buttonsArray.push('preview');
        }

        buttonsArray.splice(1, 0, 'restore');

        //add plugin buttons if defined right after spacer
        var idx = buttonsArray.indexOf('->');
        if(!Ext.isEmpty(this.pluginButtons)) {
            for (i = 0; i < this.pluginButtons.length; i++) {
                buttonsArray.splice(++idx, 0, this.pluginButtons[i]);
            }
            //add a divider
            buttonsArray.splice(++idx, 0, '-');
        }

        for (i = 0; i < buttonsArray.length; i++) {
            if((buttonsArray[i] == '-') || (buttonsArray[i] == '->')) {
                this.viewToolbar.add(buttonsArray[i]);
            } else {
                b = this.buttonCollection.get(buttonsArray[i]);
                if(b) {
                    this.viewToolbar.add(b);
                    if(!b.disabled) {
                        b.show();
                    }
                }
            }
        }

        this.updateToolbarButtons();
        this.viewToolbar.hideInutilSeparators();

        //resume toolbar layout
        this.viewToolbar.suspendLayout = false;

        this.viewToolbar.doLayout();
    }

    ,onCardItemChangeClick: function(b, e) {
        delete this.params.view;

        this.onSetToolbarItems(null);

        this.setActiveView(b.viewIndex);

        //set a flag that user have set the view and dont change the view on store load
        this.userViewSet = true;

        this.store.removeAll();

        this.onReloadClick();
    }

    ,onReloadClick: function(){
        if(Ext.isEmpty(this.reloadTask)) {
            this.reloadTask = new Ext.util.DelayedTask(this.reloadView, this);
        }
        this.reloadTask.delay(100);
    }

    /**
     * change active view
     * @param variant indexOrName
     *
     * @return activated component
     */
    ,setActiveView: function(indexOrName, viewParams) {
        var layout = this.cardContainer.getLayout()
            ,rez = null;

        if(Ext.isNumeric(indexOrName)) {
            rez = this.cardContainer.items.getAt(indexOrName);
        } else {
            var viewName = 'CBBrowserView' + Ext.util.Format.capitalize(indexOrName);
            this.cardContainer.items.each(
                function(i, idx) {
                    if(i.getXType() == viewName) {
                        rez = i;
                    }
                }
                ,this
            );
        }

        if(!Ext.isEmpty(rez)) {
            if(rez !== layout.activeItem) {
                if(viewParams) {
                    rez.viewParams = viewParams;
                }

                rez = layout.setActiveItem(rez);
            }

            //check if need to show objectPanel for selected view
            var showObjPanel = (
                    rez &&
                    (rez.showObjectPropertiesPanel === true)
                )
                ,showPreviewButton = showObjPanel && (this.objectPanel.getCollapsed() !== false);

            this.actions.preview.setDisabled(!showPreviewButton);
            this.actions.preview.setHidden(!showPreviewButton);
            this.objectPanel.setVisible(showObjPanel);
        }

        return rez;
    }

    ,reloadView: function(){
        this.store.load(this.params);
    }

    ,processLoadedParams: function () {
        var result = this.store.proxy.reader.rawData;
        var ep = this.store.proxy.extraParams;

        this.path = ep.path;
        this.folderProperties = Ext.apply({}, result.folderProperties);

        this.folderProperties.system = parseInt(this.folderProperties.system, 10);
        this.folderProperties.type = parseInt(this.folderProperties.type, 10);
        this.folderProperties.pathtext = result.pathtext;

        this.descendantsButton.toggle(ep.descendants === true);

        /* change view if set in params */
        if(!this.userViewSet) {
            //view came from laoded data
            if(!Ext.isEmpty(result.view)) {
                if(Ext.isPrimitive(result.view)) {
                    result.view = {type: result.view};
                }
                this.setActiveView(result.view.type, result.view);
            } else {
                // check if view not set on client params
                if(this.params && this.params.view) {
                    this.setActiveView(this.params.view);
                } else {
                    this.setActiveView('grid');
                }
            }
        }

        /* end of change view if set in loaded params */

        this.fireEvent('viewloaded', this.store.proxy, result, ep);

        this.updateCreateMenuItems(this.buttonCollection.get('create'));

        this.updateToolbarButtons();

        var showPreviewButton = (
                (this.objectPanel.getCollapsed() !== false) &&
                (this.cardContainer.getLayout().activeItem.showObjectPropertiesPanel === true)
            )
            ,pa = this.actions.preview;

        pa.setDisabled(!showPreviewButton);
        pa.setHidden(!showPreviewButton);

        if(App.mainFilterPanel) {
            App.mainFilterPanel.updateFacets(result.facets, ep);
        }
    }

    ,onBeforeStoreLoad: function(store, operation, eOpts) {
        var options = {facets: 'general'};

        Ext.apply(options, Ext.valueFrom(this.params, {}));

        //dont load calendar view when view bound are not set
        var vp = this.cardContainer.getLayout().activeItem.getViewParams(options);
        if( (vp === false) ||
            (
                !Ext.isEmpty(vp) && (vp.from == 'calendar') &&
                (Ext.isEmpty(vp.dateStart) || Ext.isEmpty(vp.dateEnd))
            )
        ) {
            return false;
        }

        Ext.apply(options, vp);

        //reset userViewSet flag if loaded id changed
        if(store.proxy.extraParams.id != options.id) {
            delete this.userViewSet;

            //delete also calendar view bounds
            delete options.dateStart;
            delete options.dateEnd;

        } else if(this.userViewSet) {
            options.userViewChange = true;
        }

        store.proxy.extraParams = options;
        store.currentPage = Ext.valueFrom(options.page, 1);
    }

    ,onStoreLoad: function(store, recs, options) {
        this.getEl().unmask();

        delete this.params.setMaxRows;

        //update interface according to loaded params
        this.processLoadedParams();

        //set icons for all records
        Ext.each(
            recs
            ,function(r){
                var cfg = Ext.valueFrom(r.get('cfg'), {});
                r.set('iconCls', Ext.isEmpty(cfg.iconCls) ? getItemIcon(r.data) : cfg.iconCls);
            }
            ,this
        );
    }

    ,sameParams: function(params1, params2){
        if(Ext.isEmpty(params1) && Ext.isEmpty(params2)) return true;

        if(Ext.isEmpty(params1)) params1 = {};
        if(Ext.isEmpty(params2)) params2 = {};
        path1 = Ext.valueFrom(params1.path, '');
        path2 = Ext.valueFrom(params2.path, '');
        while( (path1.length > 0) && (path1[0] == '/') ) path1 = path1.substr(1);
        while( (path2.length > 0) && (path2[0] == '/') ) path2 = path2.substr(1);
        if ((params1.path != params2.path) || !Ext.isDefined(params1.path) ) return false;
        if ((Ext.Number.from(params1.start, 0) != Ext.Number.from(params2.start, 0))) return false;
        if ((Ext.Number.from(params1.page, 0) != Ext.Number.from(params2.page, 0))) return false;
        if ((!Ext.isEmpty(params1.descendants) || !Ext.isEmpty(params2.descendants) ) && (params1.descendants != params2.descendants) ) return false;
        if ((!Ext.isEmpty(params1.query) || !Ext.isEmpty(params2.query) ) && (params1.query != params2.query) ) return false;
        if ((!Ext.isEmpty(params1.filters) || !Ext.isEmpty(params2.filters) ) && (params1.filters != params2.filters) ) return false;
        if ((!Ext.isEmpty(params1.dateStart) || !Ext.isEmpty(params2.dateStart) ) && (params1.dateStart != params2.dateStart) ) return false;
        if ((!Ext.isEmpty(params1.dateEnd) || !Ext.isEmpty(params2.dateEnd) ) && (params1.dateEnd != params2.dateEnd) ) return false;
        if ((!Ext.isEmpty(params1.view) || !Ext.isEmpty(params2.view) ) && (params1.view != params2.view) ) return false;
        if ((!Ext.isEmpty(params1.search) || !Ext.isEmpty(params2.search) ) && (Ext.encode(params1.search) != Ext.encode(params2.search)) ) return false;
        return true;
    }

    // fired by internal view
    ,changeParams: function(params, e){
        if(e && e.stopPropagation) e.stopPropagation();
        this.setParams(params);
    }

    ,changeSomeParams: function(paramsSubset){
        var p = Ext.apply({}, this.params);

        if(!Ext.isDefined(paramsSubset.start)) {
            if(Ext.isDefined(paramsSubset.page)) {
                paramsSubset.start = (paramsSubset.page -1) * this.store.pageSize;
            } else {
                paramsSubset.page = 1;
                paramsSubset.start = 0;
            }
        }

        //reset userViewSet flag if a view is given
        if(!Ext.isEmpty(paramsSubset.view)) {
            delete this.userViewSet;
        }

        Ext.apply(p, paramsSubset);
        this.setParams(p);
    }

    ,setParams: function(params){
        while(!Ext.isEmpty(params.path) && (params.path[0] == '/')) {
            params.path = params.path.substr(1);
        }

        while(!Ext.isEmpty(params.path) && (params.path[params.path.length -1] == '/')) {
            params.path = params.path.substr(0, params.path.length -1);
        }

        if(Ext.isEmpty(params.path)) {
            params.path = '/';
        }

        if(!Ext.isEmpty(this.params.query)) {
            params.lastQuery = this.params.query;
        } else if(!Ext.isEmpty(this.params.search)) {
            params.lastQuery = this.params.search;
        }

        var newParams = Ext.decode(Ext.encode(params));
        var sameParams = this.sameParams(
            this.params
            ,newParams
        );

        this.loadParamsTask.cancel();

        this.requestParams = newParams;

        this.loadParamsTask.delay(100);
    }

    ,loadParams: function(){
        if(this.sameParams(this.params, this.requestParams)) {
            return;
        }

        this.params = Ext.apply({}, this.requestParams);
        this.reloadView();
    }

    ,updateCreateMenuItems: function(menuButton) {
        updateMenu(
            menuButton
            ,this.folderProperties.menu
            ,this.onCreateObjectClick
            ,this
        );
        menuButton.setDisabled(menuButton.menu.items.getCount() < 1);
    }

    ,updateToolbarButtons: function() {
        var ai = this.cardContainer.getLayout().activeItem
            ,selection = ai.getSelectedItems
                ? ai.getSelectedItems()
                : []
            ,fp = Ext.valueFrom(this.folderProperties, {})
            ,acceptChildren = CB.DB.templates.acceptChildren(fp.template_id)
            ,inRecycleBin = this.inRecycleBin()
            ,inGridView = ai.isXType('CBBrowserViewGrid')
            ,inSearchMode = !Ext.isEmpty(this.params.query);

        this.actions.restore.setHidden(!inRecycleBin || !inGridView);
        this.actions.restore.setDisabled(!inRecycleBin || !inGridView || Ext.isEmpty(selection));

        this.actions.upload.setHidden(
            !acceptChildren ||
            inRecycleBin ||
            !inGridView ||
            inSearchMode
        );
        this.buttonCollection.get(
            'create'
        ).setVisible(!inRecycleBin && inGridView);

        this.buttonCollection.get(
            'edit'
        ).setVisible(!inRecycleBin && inGridView);

        this.buttonCollection.get(
            'more'
        ).setVisible(inGridView);

        if(Ext.isEmpty(selection)) {
            this.actions.cut.setDisabled(true);
            this.actions.copy.setDisabled(true);
            this.actions.takeOwnership.setDisabled(true);

            this.actions.download.setDisabled(true);
            this.actions.download.hide();
            this.actions.contextDownload.setDisabled(true);
            this.actions.contextDownload.hide();

            this.actions['delete'].setDisabled(true);
            this.actions['delete'].hide();
            this.actions.contextDelete.setDisabled(true);

            this.actions.restore.setDisabled(true);
            this.actions.restore.hide();
            this.actions.permissions.setDisabled(isNaN(fp.id));
        } else {
            var firstObjId = Ext.valueFrom(selection[0].nid, selection[0].id);

            this.actions.cut.setDisabled(false);
            this.actions.copy.setDisabled(false);

            var canDownload = true;
            for (var i = 0; i < selection.length; i++) {
                if(CB.DB.templates.getType(selection[i].template_id) !== 'file') {
                    canDownload = false;
                }
            }

            this.actions.download.setDisabled(!canDownload);
            this.actions.contextDownload.setDisabled(!canDownload);

            if(canDownload) {
                this.actions.download.show();
                this.actions.contextDownload.show();
            } else {
                this.actions.download.hide();
                this.actions.contextDownload.hide();
            }

            this.actions['delete'].setDisabled(inRecycleBin);
            this.actions.contextDelete.setDisabled(inRecycleBin);

            if(!inRecycleBin && inGridView) {
                this.actions['delete'].show();
            }

            this.actions.permissions.setDisabled(isNaN(firstObjId));
        }

        this.viewToolbar.hideInutilSeparators();
    }

    ,onRowCountChangeClick: function(b, e) {
        // b.setChecked(true);

        this.params.setMaxRows = true;
        this.params.rows = b.text;
        this.store.setPageSize(b.text);
        this.store.reload();
    }

    /**
     * return current vew selection
     * @return array | null
     */
    ,getSelection: function() {
        return this.cardContainer.getLayout().activeItem.currentSelection;
    }

    ,onDescendantsClick: function(b, e) {
        this.changeSomeParams({
            descendants: b.pressed
            ,start: 0
        });
    }

    ,onObjectsSelectionChange: function(objectsDataArray){
        this.cardContainer.getLayout().activeItem.currentSelection = objectsDataArray;
        this.updateToolbarButtons();
    }

    /**
     * detect if current loaded path is in recycle bin
     * @return boolean
     */
    ,inRecycleBin: function() {
        return (String(Ext.valueFrom(this.folderProperties, {}).path).indexOf('-recycleBin') > -1);
    }

    ,editObject: function(objectData) {
        this.objectPanel.edit(objectData);
    }

    ,onObjectsOpenEvent: function(objectData, e) {
        if(e && e.stopEvent) {
            e.stopEvent();
        }

        var data = Ext.apply({}, objectData);
        if(!Ext.isEmpty(data.nid)) {
            data.id = data.nid;
        }

        if(CB.DB.templates.getType(data.template_id) == 'file') {
            switch(detectFileEditor(data.name)) {
                case 'text':
                case 'html':
                    // open directly in edit mode
                    data.view = 'edit';

                default:
                    App.openObjectWindow(data);
                    break;
            }

            return;
        } else {
            //check if leaf set in template config and open edit if so
            var cfg = CB.DB.templates.getProperty(data.template_id, 'cfg');

            if(cfg && (cfg.leaf === true)) {
                data.view = 'edit';
                App.openObjectWindow(data);

                return;
            }
        }

        // if not opened object window then browse inside the item
        var path = this.folderProperties.path;
        if(path.substr(-1, 1) !== '/') {
            path += '/';
        }
        path += data.nid;
        this.changeSomeParams({
            path: path
            ,query: null
            ,descendants: false
            ,search: null
        });
    }

    ,onFiltersChange: function(filters){
        this.changeSomeParams({filters: filters});
    }

    ,onSearchQuery: function(query, e){
        this.changeSomeParams({query: query});
    }

    ,onCreateObjectClick: function(b, e) {
        b.config.data.pid = this.folderProperties.id;
        b.config.data.path = this.folderProperties.path;
        this.fireEvent('createobject', Ext.apply({}, b.config.data));
    }

    ,onUploadClick: function(b, e) {
        this.fireEvent(
            'fileupload'
            ,{
                pid: Ext.valueFrom(this.folderProperties.id, this.folderProperties.path)
                ,uploadType: b.config.uploadType
            }
            ,e
        );
    }

    ,onDownloadClick: function(b, e) {
        var ids = [];
        var selection = this.cardContainer.getLayout().activeItem.currentSelection;
        if(Ext.isEmpty(selection)) {
            return;
        }
        for (var i = 0; i < selection.length; i++) {
            ids.push(selection[i].nid);
        }
        this.fireEvent('filedownload', ids, false, e);
    }

    ,onDeleteClick: function(b, e) {
        var selection = this.cardContainer.getLayout().activeItem.currentSelection;

        if(Ext.isEmpty(selection)) {
            return;
        }

        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');

        CB.browser.Actions.deleteSelection(
            selection
            ,this.processDelete
            ,this
        );
    }

    ,processDelete: function(r, e){
        this.getEl().unmask();
    }

    ,onObjectsDeleted: function(ids, e) {
        this.store.deleteIds(ids);
    }

    ,onRenameClick: function(b, e) {
        this.cardContainer.getLayout().activeItem.onRenameClick(b, e);
    }

    ,onRestoreClick: function() {
        var s = this.getSelection();
        var ids = [];

        if(Ext.isEmpty(s)) {
            return;
        }

        for (var i = 0; i < s.length; i++) {
            ids.push(Ext.valueFrom(s[i].id, s[i].nid));
        }

        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');

        CB_Browser.restore(ids, this.processRestore, this);
    }

    ,processRestore: function(r, e) {
        this.getEl().unmask();

        if(r.success !== true) {
            Ext.Msg.alert(L.ErrorOccured);
            return;
        }

        this.onReloadClick();
    }

    ,onCutClick: function(buttonOrKey, e) {
        if(this.actions.cut.isDisabled()) {
            return;
        }
        this.onCopyClick(buttonOrKey, e);
        App.clipboard.setAction('move');
    }

    ,onCopyClick: function(buttonOrKey, e) {
        if(this.actions.copy.isDisabled()) {
            return;
        }
        var selection = this.cardContainer.getLayout().activeItem.currentSelection;
        if(Ext.isEmpty(selection)) {
            return;
        }
        var rez = [];
        for (var i = 0; i < selection.length; i++) {
            rez.push({
                id: selection[i].nid
                ,name: selection[i].name
                ,system: selection[i].system
                ,type: selection[i].type
                ,iconCls: selection[i].iconCls
            });
        }
        App.clipboard.set(rez, 'copy');
    }

    ,onPasteClick: function(buttonOrKey, e) {
        if(this.actions.paste.isDisabled()) {
            return;
        }
        App.clipboard.paste(this.folderProperties.id, null);
    }

    ,onPasteShortcutClick: function(buttonOrKey, e) {
        if(this.actions.pasteShortcut.isDisabled()) {
            return;
        }
        App.clipboard.paste(this.folderProperties.id, 'shortcut');
    }

    ,onPermissionsClick: function(b, e){
        if(this.actions.permissions.isDisabled()) {
            return;
        }
        var selection = this.cardContainer.getLayout().activeItem.currentSelection;
        var id = Ext.isEmpty(selection)
            ? this.folderProperties.id
            : Ext.valueFrom(selection[0].nid, selection[0].id);
        App.mainViewPort.openPermissions(id);
    }

    ,onEditClick: function (b, e) {
        var selection = this.cardContainer.getLayout().activeItem.currentSelection;
        var id = Ext.isEmpty(selection)
            ? this.folderProperties.id
            : Ext.valueFrom(selection[0].nid, selection[0].id);

        this.editObject(
            {
                id: id
                ,template_id: selection[0].template_id
            }
        );
    }

    ,onClipboardChange: function(cb){
        this.actions.paste.setDisabled( App.clipboard.isEmpty() );
        this.actions.pasteShortcut.setDisabled( App.clipboard.isEmpty() );
    }

    ,onClipboardAction: function(pids){
        if(pids.indexOf(this.folderProperties.id) >=0 ) {
            this.onReloadClick();
        }
    }

    ,onObjectChanged: function(objData, component){
        var idx = this.store.findExact('nid', String(objData.id));

        if(
            (idx >= 0) ||
            isNaN(this.folderProperties.id) || // virtual folders
            (objData.pid == this.folderProperties.id)
        ) {
            // App.locateObject(objData);
            this.onReloadClick();
        }
    }

    ,onObjectsAction: function(action, r, e){
        if(Ext.isEmpty(r.processedIds)) {
            return;
        }

        switch(action){
            case 'copy':
            case 'shortcut':
                if(r.targetId == this.folderProperties.id){
                    this.onReloadClick();
                }
                break;
            case 'move':
                if(r.targetId == this.folderProperties.id){
                    this.onReloadClick();
                } else {
                    // remove moved record
                    this.store.deleteIds(r.processedIds);
                }
                break;
            case 'create':
                break;
            case 'update':
                break;
            case 'delete':
                break;
        }
    }

    ,onItemContextMenu: function(e) {
        e.stopEvent();
        if(!this.contextMenu) {
            this.createItem = new Ext.menu.Item({
                text: L.Create
                ,hideOnClick: false
                ,menu:[]
            });

            this.contextMenu = new Ext.menu.Menu({
                items: [
                this.actions.edit
                ,this.actions.contextDownload
                ,'-'
                ,{
                    text: L.View
                    ,hideOnClick: false
                    ,menu: [{
                        xtype: 'menucheckitem'
                        ,text: L.Descendants
                        ,checked: (this.params.descendants === true)
                        ,listeners: {
                            scope: this
                            ,checkchange: this.onShowDescendantsCheckChange
                        }
                    }
                    ]
                }
                ,'-'
                ,this.actions.cut
                ,this.actions.copy
                ,this.actions.paste
                ,this.actions.pasteShortcut
                ,'-'
                ,this.actions.contextReload
                ,this.actions.createShortcut
                ,this.actions.contextDelete
                ,this.actions.contextRename
                ,'-'
                ,this.createItem
                ,'-'
                ,this.actions.permissions
                ]
            });
        }

        updateMenu(
            this.createItem
            ,this.folderProperties.menu
            ,this.onCreateObjectClick
            ,this
        );
        this.createItem.setDisabled(this.createItem.menu.items.getCount() < 1);

        this.contextMenu.showAt(e.getXY());
    }

    ,onExportClick: function(b, e) {
        this.fireEvent('exportrecords', this, e);
    }

    ,onShowDescendantsCheckChange: function(cb, checked, eOpts) {
        this.onDescendantsClick({pressed: checked}, eOpts);
    }

    /**
     * handler for close right panel button
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onCloseRightPanelClick: function(b, e) {
        this.objectPanel.collapse();
        this.actions.preview.show();
    }

    /**
     * handler for preview toolbar button
     * @param  button b
     * @param  evente
     * @return void
     */
    ,onPreviewClick: function(b, e) {
        this.objectPanel.expand();
        this.actions.preview.hide();
    }
});
