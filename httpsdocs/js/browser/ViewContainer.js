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
                ,disabled: true
                ,scope: this
                ,handler: this.onUploadClick
            })

            ,download: new Ext.Action({
                text: L.Download
                ,id: 'download'
                ,iconAlign:'top'
                ,scale: 'large'
                ,iconCls: 'ib-download'
                ,disabled: true
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
                ,disabled: true
                ,scope: this
                ,handler: this.onDeleteClick
            })
        };

        this.buttonCollection = new Ext.util.MixedCollection();

        this.buttonCollection.addAll([
            new Ext.Button({
                text: L.Apps
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
                ,menu: [
                ]
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
        });

        this.containerToolbar = new Ext.Toolbar({
            autoWidth: true
            ,region: 'east'
            ,defaults: {
                iconAlign:'top'
                ,scale: 'large'
            }
            ,items: [
                this.buttonCollection.get('filter')
                ,this.buttonCollection.get('preview')
                // ,this.buttonCollection.more

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
        });
        this.breadcrumb = new CB.Breadcrumb({
            listeners: {
                scope: this
                ,click: this.onBreadcrumbItemClick
            }
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
                ,'->'
                ,this.searchField
            ]
        });

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
            ,hideBorders: true
            ,region: 'east'
            ,width: 300
            ,layout: 'card'
            ,activeItem: 1
            ,items: [
                this.filtersPanel
                ,this.objectPanel
            ]
            ,listeners: {
                scope: this
                ,
            }
        });


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
            ,reader: new Ext.data.JsonReader({
                successProperty: 'success'
                ,idProperty: 'nid'
                ,root: 'data'
                ,messageProperty: 'msg'
                ,fields: [
                    {name: 'nid'}
                    ,{name: 'pid', type: 'int'}
                    ,{name: 'system', type: 'int'}
                    ,{name: 'status', type: 'int'}
                    ,{name: 'template_id', type: 'int'}
                    ,'template_type'
                    ,'path'
                    ,'name'
                    ,'hl'
                    ,'iconCls'
                    ,{name: 'date', type: 'date'}
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
            ]
            }
            )
            ,listeners: {
                scope: this
                ,beforeload: function(store, options) {
                    options = {facets: 'general'};
                    Ext.apply(options, Ext.value(this.params, {}));
                    Ext.apply(options, this.cardContainer.getLayout().activeItem.getViewParams());
                    store.baseParams = options;
                }
                ,load: this.onStoreLoad
            }
        });

        this.cardContainer = new Ext.Panel({
            layout: 'card'
            ,activeItem: 0
            ,region: 'center'
            ,items: [
                new CB.browser.view.Grid({
                    iconCls: 'icon-grid-view'
                    ,refOwner: this
                    ,store: this.store
                    ,listeners: {
                        scope: this
                        ,selectionchange: this.onObjectsSelectionChange
                        ,objectopen: this.onObjectsOpenEvent
                    }
                })
                ,new CB.browser.view.Calendar({
                    iconCls: 'icon-calendar-view'
                    ,refOwner: this
                    ,store: this.store
                    ,listeners: {
                        scope: this
                        ,selectionchange: this.onObjectsSelectionChange
                        ,objectopen: this.onObjectsOpenEvent
                    }
                })
                ,new CB.browser.view.Charts({
                    iconCls: 'icon-chart'
                    ,refOwner: this
                    ,store: this.store
                    ,listeners: {
                        scope: this
                        ,selectionchange: this.onObjectsSelectionChange
                        ,objectopen: this.onObjectsOpenEvent
                    }
                })
                // ,new CB.browser.view.TasksGrid({ iconCls: 'icon-task-view' })
            ]
            ,listeners: {
                scope: this
                ,add: function(o, c, idx) {
                    if(c.isXType('CBBrowserViewInterface')) {
                        this.buttonCollection.get('apps').menu.add({
                            text: c.title
                            ,iconCls: c.iconCls
                            ,scope: this
                            ,viewIndex: idx
                            ,handler: this.onCardItemChangeClick
                        });
                    }
                }
            }
        });

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
            }
        });

        CB.browser.ViewContainer.superclass.initComponent.apply(this, arguments);
    }

    ,onSetToolbarItems: function(buttonsArray) {
        clog('setting toolbar items', buttonsArray);
        // this.viewToolbar.removeAll(false); // this method does not work as expected
        var b;
        while(this.viewToolbar.items.getCount() > 0) {
            b = this.viewToolbar.items.itemAt(0);
            b.hide();
            this.viewToolbar.remove(b, b.isXType('tbseparator'));
        }
        this.viewToolbar.doLayout(false);
        if(buttonsArray.indexOf('apps') < 0) {
            b = this.buttonCollection.get('apps');
            b.setVisible(true);
            this.viewToolbar.add(b);
        }


        for (var i = 0; i < buttonsArray.length; i++) {
            if(buttonsArray[i] == '-') {
                this.viewToolbar.add('-');
            } else {
                b = this.buttonCollection.get(buttonsArray[i]);
                clog('b', buttonsArray[i], b);
                if(b) {
                    b.setVisible(true);
                    this.viewToolbar.add(b);
                }
            }
        }
        this.viewToolbar.doLayout();
    }
    ,onCardItemChangeClick: function(b, e) {
        this.cardContainer.getLayout().setActiveItem(b.viewIndex);
        this.onReloadClick();
    }
    ,onBackClick: function(b, e) {
        if(this.actions.back.isDisabled()) return;
        this.historyIndex = (!Ext.isDefined(this.historyIndex)) ? this.history.length - 2 : this.historyIndex - 1;
        this.setParams(this.history[this.historyIndex]);
        this.actions.back.setDisabled(this.historyIndex <= 0);
        this.actions.forward.setDisabled(false);
    }

    ,onForwardClick: function(b, e) {
        if(this.actions.forward.isDisabled()) return;
        this.historyIndex = this.historyIndex + 1;
        this.setParams(this.history[this.historyIndex]);
        this.actions.back.setDisabled(false);
        this.actions.forward.setDisabled(this.historyIndex >= (this.history.length -1));
    }

    ,onReloadClick: function(){
        if(Ext.isEmpty(this.reloadTask)) {
            this.reloadTask = new Ext.util.DelayedTask(this.reloadView, this);
        }
        this.reloadTask.delay(500);
    }

    ,reloadView: function(){
        this.getEl().mask(L.Loading, 'x-mask-loading');
        this.store.reload();
    }

    ,onProxyLoad: function (proxy, o, options) {
        this.path = this.store.baseParams.path;
        this.folderProperties = Ext.apply({}, o.result.folderProperties);

        this.folderProperties.id = this.folderProperties.id;
        this.folderProperties.system = parseInt(this.folderProperties.system, 10);
        this.folderProperties.type = parseInt(this.folderProperties.type, 10);
        this.folderProperties.subtype = parseInt(this.folderProperties.subtype, 10);
        this.folderProperties.pathtext = o.result.pathtext;

        /* updating breadcrumb */
        var b = o.result.pathtext.split('/');
        if(Ext.isEmpty(b[0])) {
            b.shift();
        }
        if((b.length > 0) && Ext.isEmpty(b[b.length-1])) {
            b.pop();
        }
        b.unshift(L.Home);
        this.breadcrumb.setValue(b);
        /* end of updating breadcrumb */
        // this.fireEvent('viewloaded', proxy, o, options);

        this.updateCreateMenuItems(this.buttonCollection.get('create'));
        this.filtersPanel.updateFacets(o.result.facets, options);
    }

    ,onStoreLoad: function(store, recs, options) {
        this.getEl().unmask();
        Ext.each(recs, function(r){
            cfg = Ext.value(r.get('cfg'), {});
            r.set('iconCls', Ext.isEmpty(cfg.iconCls) ? getItemIcon(r.data) : cfg.iconCls );
        }, this);

        // pt = this.grid.getBottomToolbar();
        // pagingVisible = (store.reader.jsonData.total > pt.pageSize);
        // if(pagingVisible) pt.show();
        // else pt.hide();
        // this.grid.syncSize();
        // this.syncSize();
        // App.mainViewPort.selectGridObject(this.grid);
    }

    ,sameParams: function(params1, params2){
        if(Ext.isEmpty(params1) && Ext.isEmpty(params2)) return true;

        if(Ext.isEmpty(params1)) params1 = {};
        if(Ext.isEmpty(params2)) params2 = {};
        path1 = Ext.value(params1.path, '');
        path2 = Ext.value(params2.path, '');
        while( (path1.length > 0) && (path1[0] == '/') ) path1 = path1.substr(1);
        while( (path2.length > 0) && (path2[0] == '/') ) path2 = path2.substr(1);
        if( (params1.path != params2.path) || !Ext.isDefined(params1.path) ) return false;
        if( (!Ext.isEmpty(params1.descendants) || !Ext.isEmpty(params2.descendants) ) && (params1.descendants != params2.descendants) ) return false;
        if( (!Ext.isEmpty(params1.query) || !Ext.isEmpty(params2.query) ) && (params1.query != params2.query) ) return false;
        if( (!Ext.isEmpty(params1.filters) || !Ext.isEmpty(params2.filters) ) && (params1.filters != params2.filters) ) return false;
        return true;
    }

    ,onChangeParams: function(params, e){// fired by internal view
        if(e && e.stopPropagation) e.stopPropagation();
        this.spliceHistory();
        this.setParams(params);
    }

    ,changeSomeParams: function(paramsSubset){
        var p = Ext.apply({}, this.params);
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

        var sameParams = this.sameParams(
            this.params
            ,Ext.apply({}, params, this.params)
        );

        if(sameParams) {
            return;
        }

        this.requestParams = Ext.apply({}, params, this.params);

        if(Ext.isEmpty(this.loadParamsTask)) {
            this.loadParamsTask = new Ext.util.DelayedTask(this.loadParams, this);
        }
        this.loadParamsTask.delay(500);
    }

    ,loadParams: function(){
        if(this.sameParams(this.params, this.requestParams)) {
            return;
        }

        if(!Ext.isDefined(this.historyIndex)){
            if(!Ext.isEmpty(this.requestParams)){
                this.history.push(Ext.apply({}, this.requestParams));
                if(this.history.length > 99) this.history.shift();
                this.actions.back.setDisabled(this.history.length < 2);
                this.actions.forward.setDisabled(true);
            }
        }
        Ext.apply(this.params, this.requestParams);
        this.reloadView();
    }

    ,updateCreateMenuItems: function(menuButton) {
        updateMenu(menuButton, getMenuConfig(this.folderProperties.id, this.folderProperties.path, this.folderProperties.template_id), this.onCreateObjectClick, this);
        menuButton.setDisabled(menuButton.menu.items.getCount() < 1);
    }

    ,onRightPanelViewChangeClick: function(b, e){
        this.rightPanel.getLayout().setActiveItem(b.itemIndex);
        this.rightPanel.show();
        this.rightPanel.syncSize();
    }

    ,onBreadcrumbItemClick: function(el, idx, ev) {
        clog('processing!', arguments);
        var v = this.folderProperties.path.split('/');
        v = v.slice(0, idx+1);
        v = v.join('/');
        this.changeSomeParams({'path': v});
    }

    ,onObjectsSelectionChange: function(objectsDataArray){
        if(Ext.isEmpty(objectsDataArray)) {
            this.objectPanel.load(null);
        } else {
            this.objectPanel.load(objectsDataArray[0].nid);
        }
    }

    ,onObjectsOpenEvent: function(objData) {
        if(App.isFolder(objData.template_id)) {
            this.changeSomeParams({path: objData.nid});
        } else {
            this.objectPanel.edit(objData.nid);
        }
    }

    ,onFiltersChange: function(filters){
        this.changeSomeParams({filters: filters});
    }

    ,onSearchQuery: function(query, e){
        this.changeSomeParams({query: query});
    }
});

Ext.reg('CBBrowserViewContainer', CB.browser.ViewContainer);
