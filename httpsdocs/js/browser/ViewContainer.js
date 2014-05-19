Ext.namespace('CB.browser');

CB.browser.ViewContainer = Ext.extend(Ext.Panel, {
    title: 'Browser'
    ,iconCls: 'icon-folder'
    ,closable: true
    ,hideBorders: true
    ,layout:'fit'
    ,params: {
        descendants: false
    }
    ,initComponent: function(){
        var viewGroup = Ext.id();
        this.viewGroup = viewGroup;

        this.history = [];

        this.actions = {
            back: new Ext.Action({
                tooltip: L.Back
                ,id: 'back'
                ,iconCls: 'icon-back'
                ,disabled: true
                ,scope: this
                ,handler: this.onBackClick
            })

            ,forward: new Ext.Action({
                tooltip: L.Forward
                ,id: 'forward'
                ,iconCls: 'icon-forward'
                ,disabled: true
                ,scope: this
                ,handler: this.onForwardClick
            })

            ,reload: new Ext.Action({
                iconCls: 'icon-refresh'
                ,id: 'reload'
                ,tooltip: L.Refresh
                ,scope: this
                ,handler: this.onReloadClick
            })

            ,upload: new Ext.Action({
                text: L.Upload
                ,id: 'upload'
                ,iconAlign:'top'
                ,scale: 'large'
                ,iconCls: 'ib-upload'
                ,scope: this
                ,handler: this.onUploadClick
            })

            ,download: new Ext.Action({
                text: L.Download
                ,id: 'download'
                ,iconAlign:'top'
                ,scale: 'large'
                ,iconCls: 'ib-download'
                ,hidden: true
                ,disabled: true
                ,hideParent: false
                ,scope: this
                ,handler: this.onDownloadClick
            })

            ,cut: new Ext.Action({
                text: L.Cut
                ,id: 'cut'
                ,scope: this
                ,disabled: true
                ,handler: this.onCutClick
            })

            ,copy: new Ext.Action({
                text: L.Copy
                ,id: 'copy'
                ,scope: this
                ,disabled: true
                ,handler: this.onCopyClick
            })

            ,paste: new Ext.Action({
                text: L.Paste
                ,id: 'paste'
                ,scope: this
                ,disabled: true
                ,handler: this.onPasteClick
            })

            ,pasteShortcut: new Ext.Action({
                text: L.PasteShortcut
                ,id: 'pasteshortcut'
                ,scope: this
                ,disabled: true
                ,handler: this.onPasteShortcutClick
            })

            ,mergeFiles: new Ext.Action({
                text: L.MergeFiles
                ,id: 'merge'
                ,iconCls: 'icon-merge'
                ,scope: this
                ,disabled: true
                ,handler: this.onMergeFilesClick
            })

            ,takeOwnership: new Ext.Action({
                text: L.TakeOwnership
                ,id: 'takeownership'
                ,iconCls: 'icon-user-gray'
                ,disabled: true
                ,scope: this
                ,handler: this.onTakeOwnershipClick
            })

            ,'delete': new Ext.Action({
                text: L.Delete
                ,id: 'delete'
                ,iconAlign:'top'
                ,iconCls: 'ib-trash'
                ,scale: 'large'
                ,hidden: true
                ,disabled: true
                ,hideParent: false
                ,scope: this
                ,handler: this.onDeleteClick
            })
        };

        this.buttonCollection = new Ext.util.MixedCollection();

        this.buttonCollection.addAll([
            new Ext.Button({
                text: L.Views
                ,id: 'apps'
                ,iconAlign:'top'
                ,iconCls: 'ib-apps'
                ,scale: 'large'
                ,menu: []
            })
            ,new Ext.Button({
                text: L.New
                ,id: 'create'
                ,iconAlign:'top'
                ,iconCls: 'ib-create'
                ,scale: 'large'
                ,menu: [
                ]
            })
            ,new Ext.Button(this.actions.upload)
            ,new Ext.Button(this.actions.download)
            ,new Ext.Button({
                text: L.Edit
                ,id: 'edit'
                ,iconCls: 'ib-edit'
                ,iconAlign:'top'
                ,scale: 'large'
                ,menu: [
                    this.actions.cut
                    ,this.actions.copy
                    ,this.actions.paste
                    ,this.actions.pasteShortcut
                    ,'-'
                    ,this.actions.mergeFiles
                    ,'-'
                    ,this.actions.takeOwnership
                ]
            })
            ,new Ext.Button(this.actions['delete'])
            ,new Ext.Button({
                text: L.More
                ,id: 'more'
                ,iconCls: 'ib-points'
                ,iconAlign:'top'
                ,scale: 'large'
                ,scope: this
                ,handler: function(b, e) {
                    this.tbarMoreMenu.show(b.getEl());
                }
            })
            ,new Ext.Button({
                text: L.Filter
                ,id: 'filter'
                ,enableToggle: true
                ,iconCls: 'ib-filter'
                ,activeIconCls: 'ib-filter-on'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'fvgRB' + viewGroup
                ,allowDepress: false
                ,itemIndex: 0
                ,scope: this
                ,toggleHandler: this.onRightPanelViewChangeClick
            })
            ,new Ext.Button({
                text: L.Preview
                ,id: 'preview'
                ,enableToggle: true
                ,iconCls: 'ib-preview'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'fvgRB' + viewGroup
                ,allowDepress: false
                ,pressed: true
                ,itemIndex: 1
                ,scope: this
                ,toggleHandler: this.onRightPanelViewChangeClick
            })
        ]);

        this.viewToolbar = new Ext.Toolbar({
            region: 'center'
            ,defaults: {
                iconAlign:'top'
                ,scale: 'large'
            }
            //each view should define it's custom buttons in buttonCollection
            //and specify buttons for diplay
            ,items: []
            ,listeners: {
                scope: this
                ,afterlayout: function(c) {
                    var ic = c.items.getCount();
                    for (var i = 0; i < ic; i++) {
                        if(c.items.itemAt(i).disabled) {
                            c.items.itemAt(i).hide();
                        }
                    }
                }
            }
        });

        this.containerToolbar = new Ext.Toolbar({
            region: 'east'
            ,border: true
            ,defaults: {
                iconAlign:'top'
                ,scale: 'large'
            }
            ,items: [
                this.buttonCollection.get('filter')
                ,this.buttonCollection.get('preview')

            ]
        });

        this.mainToolbar = new Ext.Panel({
            layout: 'border'
            ,height: 60
            ,border: false
            ,items: [
                this.viewToolbar
                ,this.containerToolbar

            ]
            ,listeners: {
                scope: this
                ,resize: function(c, adjWidth, adjHeight, rawWidth, rawHeight){
                    if(this.viewToolbar.rendered) {
                        var cw = 5;

                        this.containerToolbar.items.each(
                            function(b) {
                                cw += b.getWidth();
                            }
                            ,this
                        );
                        this.viewToolbar.setWidth(adjWidth - cw);
                        this.containerToolbar.setWidth(cw);
                    }
                }
            }
        });
        this.breadcrumb = new CB.Breadcrumb({
            listeners: {
                scope: this
                ,click: this.onBreadcrumbItemClick
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

        this.searchField = new Ext.ux.SearchField({
            width: 250
            ,minListWidth: 250
            ,listeners: {
                scope: this
                ,'search': this.onSearchQuery
            }
        });

        this.navToolbar = new Ext.Toolbar({
            items: [
                this.actions.back
                ,this.actions.forward
                ,this.actions.reload
                ,this.breadcrumb
                ,this.descendantsButton
                ,'->'
                ,this.searchField
            ]
        });

        this.tbarMoreMenu = new Ext.menu.Menu({items: []});

        this.objectPanel = new CB.ObjectCardView();

        this.filtersPanel = new CB.FilterPanel({
            title: L.Filter
            ,header: false
            ,collapsible: true
            ,collapseMode: 'mini'
            ,region: 'west'
            ,width: 300
            ,listeners:{
                scope: this
                ,change: this.onFiltersChange
            }
        });

        this.rightPanel = new Ext.Panel({
            header: false
            ,split: true
            ,collapsible: true
            ,collapseMode: 'mini'
            ,animCollapse: false
            ,hideBorders: true
            ,region: 'east'
            ,width: 300
            ,layout: 'card'
            ,activeItem: 1
            ,statefull: true
            ,stateId: 'vcrp'
            ,items: [
                this.filtersPanel
                ,this.objectPanel
            ]
        });

        var reader = new Ext.data.JsonReader({
            successProperty: 'success'
            ,idProperty: 'nid'
            ,root: 'data'
            ,messageProperty: 'msg'
            ,fields: [
                {name: 'nid', type: 'string'}
                ,{name: 'pid', type: 'int'}
                ,{name: 'system', type: 'int'}
                ,{name: 'status', type: 'int'}
                ,{name: 'task_status', type: 'int'}
                ,{name: 'template_id', type: 'int'}
                ,{name: 'category_id', type: 'int'}
                ,'template_type'
                ,'path'
                ,'name'
                ,'hl'
                ,'iconCls'
                ,{name: 'date', type: 'date'}
                ,{name: 'date_end', type: 'date'}
                ,{name: 'size', type: 'int'}
                ,{name: 'oid', type: 'int'}
                ,{name: 'cid', type: 'int'}
                ,{name: 'versions', type: 'int'}
                ,{name: 'cdate', type: 'date'}
                ,{name: 'udate', type: 'date'}
                ,'case'
                ,'content'
                ,{name: 'has_childs', type: 'bool'}
                ,{name: 'acl_count', type: 'int'}
                ,'cfg'
                ,'cls'
                ,'can'
            ]
        }
        );

        reader.readRecords = reader.readRecords.createInterceptor(CB.DB.convertJsonReaderDates);

        this.store = new Ext.data.DirectStore({
            autoLoad: false
            ,autoDestroy: true
            ,remoteSort: true
            ,baseParams: {}
            ,proxy: new  Ext.data.DirectProxy({
                paramsAsHash: true
                ,directFn: CB_BrowserView.getChildren
                ,listeners:{
                    scope: this
                    ,load: this.onProxyLoad
                }
            })
            ,reader: reader
            ,listeners: {
                scope: this
                ,beforeload: function(store, options) {
                    options = {facets: 'general'};
                    Ext.apply(options, Ext.value(this.params, {}));

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
                    store.baseParams = options;
                }
                ,load: this.onStoreLoad
            }
        });

        var getPropertyHandler = this.getProperty.createDelegate(this);

        this.cardContainer = new Ext.Panel({
            layout: 'card'
            ,activeItem: 0
            ,region: 'center'
            ,items: [
                new CB.browser.view.Grid({
                    iconCls: 'icon-grid-view'
                    ,refOwner: this
                    ,store: this.store
                    ,getProperty: getPropertyHandler
                })
                ,new CB.browser.view.Calendar({
                    iconCls: 'icon-calendar-view'
                    ,refOwner: this
                    ,store: this.store
                    ,getProperty: getPropertyHandler
                })
                ,new CB.browser.view.Charts({
                    iconCls: 'icon-chart'
                    ,refOwner: this
                    ,addDivider: true // forr atdding a divider in menu before this view element
                    ,store: this.store
                    ,getProperty: getPropertyHandler
                })
                ,new CB.browser.view.Pivot({
                    refOwner: this
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
            tbarCssClass: 'x-panel-white'
            ,tbar: this.mainToolbar
            ,items: [ {
                layout: 'border'
                ,hideBorders: true
                ,tbarCssClass: 'x-panel-gray'
                ,tbar: this.navToolbar
                ,items: [
                    this.cardContainer
                    ,this.rightPanel
                ]
            }
            ]
            ,listeners: {
                scope: this
                ,changeparams: this.changeSomeParams
                ,settoolbaritems: this.onSetToolbarItems
                ,createobject: this.onCreateObjectEvent
                ,reload: this.onReloadClick
                ,activate: function() {
                    this.updatePreview();
                }
                // ,objectopen: this.onObjectsOpenEvent
            }
        });

        CB.browser.ViewContainer.superclass.initComponent.apply(this, arguments);

        this.addEvents(
            'viewloaded'
            ,'fileupload'
            ,'filedownload'
        );

        this.enableBubble([
            'viewloaded'
            ,'fileupload'
            ,'filedownload'
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

    ,onSetToolbarItems: function(buttonsArray) {
        // this.viewToolbar.removeAll(false); // this method does not work as expected
        if(!this.getEl().isVisible(true)) {
            return;
        }
        var i, b;
        while(this.viewToolbar.items.getCount() > 0) {
            b = this.viewToolbar.items.itemAt(0);
            b.hide();
            this.viewToolbar.remove(b, b.isXType('tbseparator'));
        }

        if(buttonsArray.indexOf('apps') < 0) {
            buttonsArray.unshift('apps');
        }

        if(buttonsArray.indexOf('more') < 0) {
            buttonsArray.push('more');
        }

        //add plugin buttons if defined
        if(!Ext.isEmpty(this.pluginButtons)) {
            buttonsArray.push('->');
            for (i = 0; i < this.pluginButtons.length; i++) {
                buttonsArray.push(this.pluginButtons[i]);
            }
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
        this.viewToolbar.doLayout();
    }

    ,onCardItemChangeClick: function(b, e) {
        delete this.params.view;
        this.cardContainer.getLayout().setActiveItem(b.viewIndex);
        this.onReloadClick();
    }

    ,onBackClick: function(b, e) {
        if(this.actions.back.isDisabled()) {
            return;
        }
        this.historyIndex = (!Ext.isDefined(this.historyIndex))
            ? this.history.length - 2
            : this.historyIndex - 1;
        this.isHistoryAction = true;
        this.setParams(this.history[this.historyIndex]);
        this.actions.back.setDisabled(this.historyIndex <= 0);
        this.actions.forward.setDisabled(false);
    }

    ,onForwardClick: function(b, e) {
        if(this.actions.forward.isDisabled()) {
            return;
        }
        this.historyIndex = this.historyIndex + 1;
        this.isHistoryAction = true;
        this.setParams(this.history[this.historyIndex]);
        this.actions.back.setDisabled(false);
        this.actions.forward.setDisabled(this.historyIndex >= (this.history.length -1));
    }

    ,spliceHistory: function() {
        if(this.isHistoryAction) {
            delete this.isHistoryAction;
            return;
        }
        if(Ext.isDefined(this.historyIndex)){
            this.history.splice(this.historyIndex + 1, this.history.length - this.historyIndex);
            delete this.historyIndex;
        }
    }

    ,onReloadClick: function(){
        if(Ext.isEmpty(this.reloadTask)) {
            this.reloadTask = new Ext.util.DelayedTask(this.reloadView, this);
        }
        this.reloadTask.delay(500);
    }

    ,reloadView: function(){
        if(this.params && this.params.view) {
            var viewName = 'CBBrowserView' + Ext.util.Format.capitalize(this.params.view);
            this.cardContainer.items.each(
                function(i, idx) {
                    if(i.getXType() == viewName) {
                        this.cardContainer.getLayout().setActiveItem(idx);
                    }
                }
                ,this
            );
        }
        if(this.store.load(this.params)) {
            // this.getEl().mask(L.Loading, 'x-mask-loading');
        }
    }

    ,onProxyLoad: function (proxy, o, options) {
        this.path = this.store.baseParams.path;
        this.folderProperties = Ext.apply({}, o.result.folderProperties);

        this.folderProperties.id = this.folderProperties.id;
        this.folderProperties.system = parseInt(this.folderProperties.system, 10);
        this.folderProperties.type = parseInt(this.folderProperties.type, 10);
        this.folderProperties.subtype = parseInt(this.folderProperties.subtype, 10);
        this.folderProperties.pathtext = o.result.pathtext;

        this.descendantsButton.toggle(options.params.descendants === true);
        /* updating breadcrumb */
        if(Ext.isDefined(o.result.pathtext)) {
            var b = o.result.pathtext.split('/');
            if(Ext.isEmpty(b[0])) {
                b.shift();
            }
            if((b.length > 0) && Ext.isEmpty(b[b.length-1])) {
                b.pop();
            }
            this.breadcrumb.setValue(b);
        }
        /* end of updating breadcrumb */

        this.fireEvent('viewloaded', proxy, o, options);

        this.updateCreateMenuItems(this.buttonCollection.get('create'));
        this.searchField.setValue(Ext.value(options.params.query, ''));
        this.filtersPanel.updateFacets(o.result.facets, options);

        this.updatePreview();
    }

    ,onStoreLoad: function(store, recs, options) {
        this.getEl().unmask();
        Ext.each(recs, function(r){
            var cfg = Ext.value(r.get('cfg'), {});
            r.set('iconCls', Ext.isEmpty(cfg.iconCls) ? getItemIcon(r.data) : cfg.iconCls );
        }, this);
    }

    ,sameParams: function(params1, params2){
        if(Ext.isEmpty(params1) && Ext.isEmpty(params2)) return true;

        if(Ext.isEmpty(params1)) params1 = {};
        if(Ext.isEmpty(params2)) params2 = {};
        path1 = Ext.value(params1.path, '');
        path2 = Ext.value(params2.path, '');
        while( (path1.length > 0) && (path1[0] == '/') ) path1 = path1.substr(1);
        while( (path2.length > 0) && (path2[0] == '/') ) path2 = path2.substr(1);
        if ((params1.path != params2.path) || !Ext.isDefined(params1.path) ) return false;
        if ((Ext.num(params1.start, 0) != Ext.num(params2.start, 0))) return false;
        if ((!Ext.isEmpty(params1.descendants) || !Ext.isEmpty(params2.descendants) ) && (params1.descendants != params2.descendants) ) return false;
        if ((!Ext.isEmpty(params1.query) || !Ext.isEmpty(params2.query) ) && (params1.query != params2.query) ) return false;
        if ((!Ext.isEmpty(params1.filters) || !Ext.isEmpty(params2.filters) ) && (params1.filters != params2.filters) ) return false;
        if ((!Ext.isEmpty(params1.dateStart) || !Ext.isEmpty(params2.dateStart) ) && (params1.dateStart != params2.dateStart) ) return false;
        if ((!Ext.isEmpty(params1.dateEnd) || !Ext.isEmpty(params2.dateEnd) ) && (params1.dateEnd != params2.dateEnd) ) return false;
        if ((!Ext.isEmpty(params1.view) || !Ext.isEmpty(params2.view) ) && (params1.view != params2.view) ) return false;
        if ((!Ext.isEmpty(params1.search) || !Ext.isEmpty(params2.search) ) && (Ext.encode(params1.search) != Ext.encode(params2.search)) ) return false;
        return true;
    }

    ,onChangeParams: function(params, e){// fired by internal view
        if(e && e.stopPropagation) e.stopPropagation();
        this.setParams(params);
    }

    ,changeSomeParams: function(paramsSubset){
        var p = Ext.apply({}, this.params);

        if(!Ext.isDefined(paramsSubset.start)) {
            paramsSubset.start = 0;
        }
        if(!Ext.isDefined(paramsSubset.descendants)) {
            paramsSubset.descendants = false;
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

        var newParams = Ext.decode(Ext.encode(params));//, this.params
        var sameParams = this.sameParams(
            this.params
            ,newParams
        );

        this.loadParamsTask.cancel();

        if(sameParams) {
            this.updatePreview(newParams);
            return;
        }

        this.requestParams = newParams;

        this.spliceHistory();

        this.loadParamsTask.delay(500);
    }

    ,loadParams: function(){
        if(this.sameParams(this.params, this.requestParams)) {
            return;
        }

        if(!Ext.isDefined(this.historyIndex)){
            if(!Ext.isEmpty(this.requestParams)){
                this.history.push(Ext.apply({}, this.requestParams));
                if(this.history.length > 99) {
                    this.history.shift();
                }
                this.actions.back.setDisabled(this.history.length < 2);
                this.actions.forward.setDisabled(true);
            }
        }
        this.params = Ext.apply({}, this.requestParams);
        this.reloadView();
    }

    ,updateCreateMenuItems: function(menuButton) {
        updateMenu(
            menuButton
            ,getMenuConfig(
                this.folderProperties.id
                ,this.folderProperties.path
                ,this.folderProperties.template_id
            )
            ,this.onCreateObjectClick
            ,this
        );
        menuButton.setDisabled(menuButton.menu.items.getCount() < 1);
    }

    ,onRightPanelViewChangeClick: function(b, e){
        this.rightPanel.getLayout().setActiveItem(b.itemIndex);
        this.rightPanel.show();
        this.rightPanel.syncSize();

        // loading last selected object into objects panel
        var s = this.cardContainer.getLayout().activeItem.currentSelection;
        if(!Ext.isEmpty(s)) {
            if(this.rightPanel.getLayout().activeItem == this.objectPanel){
                this.objectPanel.requestedLoadData = s[0];
                this.objectPanel.doLoad();
            }
        }

    }

    ,onBreadcrumbItemClick: function(el, idx, ev) {
        var pt = this.folderProperties.pathtext.split('/');
        var p = this.folderProperties.path.split('/');

        if(Ext.isEmpty(pt[0])) {
            pt.shift();
        }
        if((pt.length > 0) && Ext.isEmpty(pt[pt.length-1])) {
            pt.pop();
        }
        if(Ext.isEmpty(p[0])) {
            p.shift();
        }
        if((p.length > 0) && Ext.isEmpty(p[p.length-1])) {
            p.pop();
        }

        p = p.slice(0, idx + 1 + p.length - pt.length);
        p = p.join('/');
        if(p.substr(0, 1) !== '/') {
            p = '/' + p;
        }
        this.changeSomeParams({'path': p});
    }

    ,onDescendantsClick: function(b, e) {
        this.changeSomeParams({
            descendants: b.pressed
            ,start: 0
        });
    }

    ,onObjectsSelectionChange: function(objectsDataArray){
        this.cardContainer.getLayout().activeItem.currentSelection = objectsDataArray;

        if(Ext.isEmpty(objectsDataArray)) {
            this.actions.cut.setDisabled(true);
            this.actions.copy.setDisabled(true);
            this.actions.takeOwnership.setDisabled(true);
            this.actions.mergeFiles.setDisabled(true);
            // this.actions.createShortcut.setDisabled(true);

            this.actions.download.setDisabled(true);
            this.actions.download.hide();

            this.actions['delete'].setDisabled(true);
            this.actions['delete'].hide();
            // this.actions.rename.setDisabled(true);
        } else {

            this.actions.cut.setDisabled(false);
            this.actions.copy.setDisabled(false);

            var canDownload = true;
            for (var i = 0; i < objectsDataArray.length; i++) {
                if(CB.DB.templates.getType(objectsDataArray[i].template_id) !== 'file') {
                    canDownload = false;
                }
            }

            this.actions.download.setDisabled(!canDownload);

            if(canDownload) {
                this.actions.download.show();
            } else {
                this.actions.download.hide();
            }
            this.actions.mergeFiles.setDisabled(!canDownload || (objectsDataArray.length < 2));

            this.actions['delete'].setDisabled(false);
            if(this.cardContainer.getLayout().activeItem.isXType('CBBrowserViewGrid')) {
                this.actions['delete'].show();
            }
        }

        this.updatePreview();
    }

    ,updatePreview: function(customParams) {
        if(Ext.isEmpty(this.folderProperties)) {
            return;
        }
        var data = customParams;

        //if custom params are empty then try to load current view selection
        //or the currently opened object
        if(Ext.isEmpty(data)) {
            var s = this.cardContainer.getLayout().activeItem.currentSelection;
            data = Ext.isEmpty(s)
                ? {
                    id: this.folderProperties.id
                    ,name: this.folderProperties.name
                    ,template_id: this.folderProperties.template_id
                }
                : {
                    id: Ext.value(s[0].nid, s[0].id)
                    ,name: s[0].name
                    ,template_id: s[0].template_id
                    ,can: s[0].can
                };
        }
        this.objectPanel.load(data);
    }

    ,onObjectsOpenEvent: function(objData, e) {
        if(e && e.stopPropagation) {
            e.stopPropagation();
            e.stopEvent();
        }

        if(CB.DB.templates.getType(objData.template_id) == 'file') {
            if(App.isWebDavDocument(objData.name)) {
                App.openWebdavDocument(objData);
            } else {
                this.onDownloadClick();
            }
            return;
        }

        var path = this.folderProperties.path;
        if(path.substr(-1, 1) !== '/') {
            path += '/';
        }
        path += objData.nid;
        this.changeSomeParams({
            path: path
            ,query: null
            ,search: null
        });
    }

    ,onFiltersChange: function(filters){
        this.changeSomeParams({filters: filters});
    }

    ,onSearchQuery: function(query, e){
        this.changeSomeParams({query: query});
    }

    ,onCreateObjectEvent: function(objectData, e) {
        if(Ext.isEmpty(objectData.pid)) {
            objectData.pid = this.folderProperties.id;
        }
        if(Ext.isEmpty(objectData.path)) {
            objectData.path = this.folderProperties.path;
        }
        this.buttonCollection.get('preview').toggle(true);
        this.objectPanel.edit(objectData);
    }

    ,onCreateObjectClick: function(b, e) {
        // var tplRec = CB.DB.templates.getById(b.data.template_id);
        // if(tplRec && tplRec.data && tplRec.data.cfg && (tplRec.data.cfg.createMethod == 'inline')) {
        //     //to decide what's the best method for creating inline objects:
        //     // - in modal window
        //     // - inside active view or let the view decide the creation method
        //     // - on the right side panel as usual objects creations
        //     return;
        // }
        this.buttonCollection.get('preview').toggle(true);
        b.data.pid = this.folderProperties.id;
        b.data.path = this.folderProperties.path;
        this.objectPanel.edit(b.data);
    }

    ,onUploadClick: function(b, e) {
        this.fireEvent(
            'fileupload'
            ,{
                pid: Ext.value(this.folderProperties.id, this.folderProperties.path)
                ,uploadType: b.uploadType
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
        Ext.Msg.confirm(
            L.DeleteConfirmation
            ,(selection.length == 1)
                ? L.DeleteConfirmationMessage + ' "' + selection[0].name + '"?'
                : L.DeleteSelectedConfirmationMessage
            ,this.onDelete
            ,this
        );
    }

    ,onDelete: function (btn) {
        if(btn !== 'yes') {
            return;
        }
        var selection = this.cardContainer.getLayout().activeItem.currentSelection;
        if(Ext.isEmpty(selection)) {
            return;
        }
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        var ids = [];
        for (var i = 0; i < selection.length; i++) {
            ids.push(selection[i].nid);
        }
        CB_BrowserView['delete'](ids, this.processDelete, this);
    }

    ,processDelete: function(r, e){
        this.getEl().unmask();
        App.mainViewPort.fireEvent('objectsdeleted', r.ids, e);
    }

    ,onObjectsDeleted: function(ids, e) {
        var idx;

        for (var i = 0; i < ids.length; i++) {
            idx = this.store.findExact('nid', String(ids[i]));
            if(idx >= 0) {
                this.store.removeAt(idx);
            }
        }
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
                ,subtype: selection[i].subtype
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
            : s[0].nid;
        App.mainViewPort.openPermissions(id);
    }

    ,onMergeFilesClick: function(buttonOrKey, e) {
        if(this.actions.mergeFiles.isDisabled()) {
            return;
        }
        var selection = this.cardContainer.getLayout().activeItem.currentSelection;
        if(Ext.isEmpty(selection) || (selection.length <2) ) return;
        rez = [];
        for (var i = 0; i < s.length; i++) {
            rez.push(selection[i].nid);
        }
        Ext.Msg.confirm( L.MergingFiles, L.MergeFilesConfirmation, function(b){
            if(b == 'yes') CB_Files.merge(rez, this.processMergingFiles, this);
        }, this );
    }

    ,processMergingFiles: function(r, e){
        this.onReloadClick();
        App.mainViewPort.onProcessObjectsDeleted(r, e);
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
    ,onObjectChanged: function(objData){
        if(
            isNaN(this.folderProperties.id) ||
            (objData.pid == this.folderProperties.id)
        ) {
            App.locateObjectId = objData.id;
            this.onReloadClick();
        }
    }
    ,onObjectsAction: function(action, r, e){
        if(Ext.isEmpty(r.processedIds)) {
            return;
        }
        // if(pids.indexOf(this.folderProperties.id) >=0 ) this.onReloadClick();

        switch(action){
            case 'copy':
                if(r.targetId == this.folderProperties.id){
                    this.onReloadClick();
                }
                break;
            case 'move':
                if(r.targetId == this.folderProperties.id){
                    this.onReloadClick();
                } else {
                    // remove moved record
                    for (var i = 0; i < r.processedIds.length; i++) {
                        idx = this.store.findExact('nid', parseInt(r.processedIds[i], 10));
                        if(idx > -1) {
                            this.store.removeAt(idx);
                        }
                    }
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
});

Ext.reg('CBBrowserViewContainer', CB.browser.ViewContainer);
