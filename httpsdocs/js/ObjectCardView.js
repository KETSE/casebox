Ext.namespace('CB');

CB.ObjectCardView = Ext.extend(Ext.Panel, {
    border: false
    ,layout: 'card'
    ,activeItem: 0
    ,hideBorders: true
    ,tbarCssClass: 'x-panel-white'
    ,loadedData: {}
    ,history: []
    ,initComponent: function() {
        this.instanceId = Ext.id();
        this.actions = {
            back: new Ext.Action({
                iconCls: 'ib-back'
                ,id: 'back' + this.instanceId
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onBackClick
            })
            ,edit: new Ext.Action({
                iconCls: 'ib-edit-obj'
                ,id: 'edit' + this.instanceId
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onEditClick
            })
            ,download: new Ext.Action({
                qtip: L.Download
                ,id: 'download' + this.instanceId
                ,iconAlign:'top'
                ,scale: 'large'
                ,iconCls: 'ib-download'
                ,hidden: true
                ,scope: this
                ,handler: this.onDownloadClick
            })

            ,save: new Ext.Action({
                iconCls: 'ib-save'
                ,id: 'save' + this.instanceId
                ,scale: 'large'
                ,text: L.Save
                ,hidden: true
                ,scope: this
                ,handler: this.onSaveClick
            })
            ,cancel: new Ext.Action({
                iconCls: 'ib-cancel'
                ,id: 'cancel' + this.instanceId
                ,scale: 'large'
                ,text: Ext.MessageBox.buttonText.cancel
                ,hidden: true
                ,scope: this
                ,handler: this.onCancelClick
            })
            ,openInTabsheet: new Ext.Action({
                iconCls: 'ib-external'
                ,id: 'openInTabsheet' + this.instanceId
                ,scale: 'large'
                ,hidden: true
                ,scope: this
                ,handler: this.onOpenInTabsheetClick
            })

            ,completeTask: new Ext.Action({
                iconCls: 'ib-task-complete'
                ,id: 'completetask' + this.instanceId
                ,scale: 'large'
                ,text: L.Done
                ,scope: this
                ,handler: this.onCompleteTaskClick
            })
        };

        this.menuItemConfigs = {
            reload: {
                iconCls: 'i-refresh'
                ,id: 'reload' + this.instanceId
                ,text: L.Refresh
                ,scope: this
                ,handler: this.onReloadClick
            }
            ,addtask: {
                text: L.AddTask
                ,data: {
                    template_id: App.config.default_task_template
                }
                ,scope: this
                ,handler: this.onCreateObjectClick
            }
            ,completetask: {
                iconCls: 'ib-task-complete'
                ,id: 'completetask' + this.instanceId
                ,scale: 'large'
                ,text: L.Done
                ,scope: this
                ,handler: this.onCompleteTaskClick
            }
            ,closetask: {
                text: L.ClosingTask
                ,scope: this
                ,handler: this.onCloseTaskClick
            }
            ,reopentask: {
                text: L.ReopeningTask
                ,scope: this
                ,handler: this.onReopenTaskClick
            }
            ,attachfile: {
                text: L.AttachFile
                ,scope: this
                ,handler: this.onAttachFileClick
            }
            ,webdavlink: {
                text: 'WebDAV Link'
                ,id: 'webdavlink' + this.instanceId
                ,scope: this
                ,handler: this.onWebDAVLinkClick
            }
            ,'new': {
                text: L.New
                ,menu: []
            }
        };

        /* will user BC abreviation for Button Collection */
        this.BC = new Ext.util.MixedCollection();

        this.BC.addAll([
            new Ext.Button(this.actions.back)
            ,new Ext.Button(this.actions.edit)
            ,new Ext.Button(this.actions.download)
            ,new Ext.Button(this.actions.save)
            ,new Ext.Button(this.actions.cancel)
            ,new Ext.Button(this.actions.openInTabsheet)
            ,new Ext.Button(this.actions.completeTask)

            ,new Ext.Button({
                iconCls: 'ib-points'
                ,id: 'more' + this.instanceId
                ,scale: 'large'
                ,scope: this
                ,handler: function(b, e) {
                    this.menu.show(b.getEl());
                }
            })
        ]);

        Ext.apply(this, {
            hideMode: 'offsets'
            ,tbar: [
                this.BC.get('back' + this.instanceId)
                ,this.BC.get('edit' + this.instanceId)
                ,this.BC.get('download' + this.instanceId)
                ,this.BC.get('save' + this.instanceId)
                ,this.BC.get('cancel' + this.instanceId)
                ,this.BC.get('completetask' + this.instanceId)
                ,'->'
                ,this.BC.get('openInTabsheet' + this.instanceId)
                ,this.BC.get('more' + this.instanceId)
            ]
            ,items: [{
                    title: L.Properties
                    ,iconCls: 'icon-infoView'
                    ,header: false
                    ,xtype: 'CBObjectProperties'
                    ,api: CB_Objects.getPluginsData
                    ,listeners: {
                        scope: this
                        ,openpreview: this.onOpenPreviewEvent
                        ,openproperties: this.onOpenPropertiesEvent
                        ,loaded: this.onCardItemLoaded
                    }
                },{
                    title: L.Edit
                    ,iconCls: 'icon-edit'
                    ,header: false
                    ,xtype: 'CBEditObject'
                    ,listeners: {
                        scope: this
                        ,change: function(){
                            this.actions.save.setDisabled(false);
                        }
                        ,clear: function(){
                            this.actions.save.setDisabled(true);
                        }
                        ,loaded: this.onCardItemLoaded
                    }
                },{
                    title: L.Preview
                    ,iconCls: 'icon-preview'
                    ,header: false
                    ,xtype: 'CBObjectPreview'
                    ,listeners: {
                        scope: this
                        ,loaded: this.onCardItemLoaded
                    }
                }
            ]
            ,listeners: {
                scope: this
                ,add: this.onCardItemAdd
                ,afterrender: this.doLoad
                ,lockpanel: this.onLockPanelEvent
                ,saveobject: this.onSaveObjectEvent
            }
        });

        CB.ObjectCardView.superclass.initComponent.apply(this, arguments);

        this.delayedLoadTask = new Ext.util.DelayedTask(this.doLoad, this);

        this.addEvents('filedownload', 'createobject');
        this.enableBubble(['filedownload', 'createobject']);
    }

    ,getButton: function() {
        if(!this.button) {
            this.button = new Ext.SplitButton({
                iconCls: 'ib-app-view'
                ,scale: 'large'
                ,iconAlign:'top'
                ,enableToggle: true
                ,scope: this
                ,toggleHandler: this.onButtonToggle
                ,menu: []
            });
        }
        return this.button;
    }
    ,onButtonToggle: function(b, e){
        if(b.pressed){
            this.show();
            this.load(this.loadedData);
        }else{
            this.hide();
        }
    }
    ,onCardItemAdd: function(container, component, index){
        if(container !== this) {
            return;
        }
        var b = this.getButton();
        b.menu.add({
            text: component.title
            ,iconCls: component.iconCls
            ,scope: this
            ,handler: this.onViewChangeClick
        });
    }
    ,onViewChangeClick: function(buttonOrIndex, autoLoad){
        var currentItemIndex = this.items.indexOf(this.getLayout().activeItem);
        var mb = this.getButton();
        var idx = Ext.isNumber(buttonOrIndex)
            ? buttonOrIndex
            : mb.menu.items.indexOf(buttonOrIndex);
        if(currentItemIndex == idx) {
            return;
        }

        this.getLayout().activeItem.clear();
        this.getLayout().setActiveItem(idx);
        if(!mb.pressed) {
            mb.toggle();
        }
        this.onViewChange();
        if(autoLoad !== false) {
            this.load(this.requestedLoadData);
        }
    }
    ,onViewChange: function() {
        var activeItem = this.getLayout().activeItem;
        var tb = this.getTopToolbar();
        var d = this.loadedData;
        var canDownload = (
            d &&
            d.template_id &&
            (CB.DB.templates.getType(d.template_id) == 'file')
        );

        this.actions.edit.setDisabled(isNaN(d.template_id));
    }

    /**
     * loading an object into the panel in a specific view
     * @param  {[type]} objectData [description]
     * @return {[type]}            [description]
     */
    ,load: function(objectData) {

        if(this.locked) {
            delete this.requestedLoadData;
            return;
        }

        if(!isNaN(objectData)) {
            objectData = {
                id: objectData
            };
        }
        var ai = this.getLayout().activeItem;

        //current view index
        var cvi = this.items.indexOf(ai);

        // check  if a new load is waiting to be loaded
        if(Ext.isEmpty(this.requestedLoadData)) {

            //check if object data are identical to previous loaded object
            if((objectData.id == this.loadedData.id) &&
                (Ext.value(objectData.viewIndex, cvi) == Ext.value(this.loadedData.viewIndex, cvi))
            ) {
                return;
            }

            // save current croll position for history navigation
            if(!Ext.isEmpty(ai.body)) {
                this.loadedData.scroll = ai.body.getScroll();
            }
        } else {
            //check if object data are identical to previous load request
            if((objectData.id == this.requestedLoadData.id) &&
                (Ext.value(objectData.viewIndex, cvi) == Ext.value(this.requestedLoadData.viewIndex, cvi))
                ) {
                return;
            }
        }

        // cancel previous wating request and start a new one
        this.delayedLoadTask.cancel();

        // save requested data
        this.requestedLoadData = Ext.apply({}, objectData);

        //check if we are not in edit mode
        if(this.getLayout().activeItem.getXType() !== 'CBEditObject') {

            //automatic switch to plugins panel
            this.onViewChangeClick(0);

            if(this.skipNextPreviewLoadOnBrowserRefresh) {
                delete this.skipNextPreviewLoadOnBrowserRefresh;
            } else {
                this.items.itemAt(0).clear();

                // instantiate a delay to exclude flood requests
                this.delayedLoadTask.delay(60, this.doLoad, this);
            }
        }
    }

    ,doLoad: function() {
        if(this.locked) {
            delete this.requestedLoadData;
            return;
        }

        var id = this.requestedLoadData
            ? Ext.value(this.requestedLoadData.nid, this.requestedLoadData.id)
            : null;

        this.addParamsToHistory(this.loadedData);

        this.loadedData = Ext.apply({id: id}, this.requestedLoadData);

        if(Ext.isDefined(this.loadedData.viewIndex)) {
            this.onViewChangeClick(this.loadedData.viewIndex, false);
        }

        delete this.requestedLoadData;

        var activeItem = this.getLayout().activeItem;

        this.loadedData.viewIndex = this.items.indexOf(activeItem);
        switch(activeItem.getXType()) {
            case 'CBObjectPreview':
                this.getTopToolbar().setVisible(!Ext.isEmpty(id));
                this.doLayout();
                activeItem.loadPreview(id);
                break;
            case 'CBObjectProperties':
            case 'CBEditObject':
                activeItem.load(this.loadedData);
                break;
        }
        this.onViewChange();
    }

    ,onCardItemLoaded: function(item) {
        this.updateToolbarAndMenuItems();

        if(Ext.isEmpty(this.loadedData) || Ext.isEmpty(this.loadedData.scroll)) {
            return;
        }
        if(item.body) {
            item.body.scrollTo('left', this.loadedData.scroll.left);
            item.body.scrollTo('top', this.loadedData.scroll.top);
        }
    }

    ,updateToolbarAndMenuItems: function() {
        var ai = this.getLayout().activeItem;
        var ti = ai.getContainerToolbarItems();
        var tb = this.getTopToolbar();

        if(this.menu) {
            this.menu.removeAll(false);
            this.menu.destroy();
        }
        this.menu = new Ext.menu.Menu({items:[]});

        if(Ext.isEmpty(ti)) {
            return;
        }

        tb.items.each(
            function(i) {
                if(i.id != 'back') {
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
                    if ((!isFirstItem) &&
                      (v.addDivider == 'top')
                    ) {
                        this.menu.add('-');
                    }

                    var b = this.menuItemConfigs[k];
                    if(b) {
                        var cfg = Ext.apply({}, b);
                        var item = this.menu.add(cfg);
                        isFirstItem = false;

                        if(k == 'new') {
                            updateMenu(
                                item
                                ,getMenuConfig(
                                    this.loadedData.id
                                    ,this.loadedData.path
                                    ,this.loadedData.template_id
                                )
                                ,this.onCreateObjectClick
                                ,this
                            );
                            item.setDisabled(item.menu.items.getCount() < 1);
                        }
                    }
                }
            }
            ,this
        );

        //add "more" button to toolbar config if menu is not empty
        if(this.menu.items.getCount() > 0) {
            ti.tbar['more'] = {};
        }

        // add back button to config (always visible)
        if(!Ext.isDefined(ti.tbar['back'])) {
            ti.tbar['back'] = {};
        }

        // hide all bottons from toolbar
        Ext.iterate(
            ti.tbar
            ,function(k, v, o) {
                var b = this.BC.get(k + this.instanceId);
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

    ,addParamsToHistory: function(p) {
        var ai = this.getLayout().activeItem;
        //current view index
        var cvi = this.items.indexOf(ai);

        if((cvi == 1) || // edit view
            Ext.isEmpty(p) ||
            (Ext.encode(p) == '{}') ||
            (isNaN(p.id)) ||
            this.historyNavigation
        ) {
            delete this.historyNavigation;
            return;
        }
        this.history.push(Ext.apply({}, p));
        this.actions.back.setDisabled(false);
    }

    ,onBackClick: function() {
        if(Ext.isEmpty(this.history)) {
            this.actions.back.setDisabled(true);
            return;
        }
        this.delayedLoadTask.cancel();

        this.historyNavigation = true;
        this.requestedLoadData = this.history.pop();
        if(Ext.isEmpty(this.history)) {
            this.actions.back.setDisabled(true);
        }
        this.doLoad();
    }

    ,edit: function (objectData) {
        if(App.isWebDavDocument(objectData.name)) {
            App.openWebdavDocument(objectData);
            return;
        }
        objectData.viewIndex = 1;
        this.delayedLoadTask.cancel();
        this.requestedLoadData = objectData;
        this.doLoad();
    }
    ,onEditClick: function() {
        if(App.isWebDavDocument(this.loadedData.name)) {
            App.openWebdavDocument(this.loadedData);
            return;
        }
        var p = Ext.apply({}, this.loadedData);
        p.viewIndex = 1;
        this.delayedLoadTask.cancel();
        this.requestedLoadData = p;
        this.doLoad();
    }
    ,onReloadClick: function() {
        this.getLayout().activeItem.reload();
    }

    ,onSaveClick: function() {
        this.getLayout().activeItem.save(
            function(component, form, action){
                var id = Ext.value(action.result.data.id, this.loadedData.id);
                var name = Ext.value(action.result.data.name, this.loadedData.name);

                var p = Ext.apply({}, this.loadedData);
                p.id = id;
                p.name = name;
                if(this.goBackOnSave) {
                    this.onBackClick();
                } else {
                    p.viewIndex = 0;
                    this.requestedLoadData = p;
                    this.doLoad();
                    this.skipNextPreviewLoadOnBrowserRefresh = true;
                }
                delete this.goBackOnSave;
            }
            ,this
        );
    }

    ,onSaveObjectEvent: function(objComp) {
        if(this.actions.save.isDisabled()) {
            return false;
        }
        this.onSaveClick();
    }

    ,onCancelClick: function() {
        if(isNaN(this.loadedData.id)) {
            this.onBackClick();
        } else {
            var p = Ext.apply({}, this.loadedData);
            p.viewIndex = 0;
            this.requestedLoadData = p;
            this.doLoad();
        }
        delete this.goBackOnSave;
    }
    ,onOpenInTabsheetClick: function(b, e) {
        var ai = this.getLayout().activeItem;
        var cai = this.items.indexOf(ai);
        var d = Ext.apply({}, this.loadedData);

        if(ai.getXType() == 'CBEditObject') {
            d = Ext.apply({}, ai.data);
            if(ai.readValues) {
                d = Ext.apply(d, ai.readValues());
            }
        }

        if(cai > 0) {
            ai.clear();
            this.requestedLoadData = Ext.apply({}, this.loadedData);
            this.requestedLoadData.viewIndex = 0;
            this.onViewChangeClick(0);
        }

        switch(CB.DB.templates.getType(d.template_id)) {
            case 'file':
                App.mainViewPort.onFileOpen(d, e);
                break;
            default:
                App.mainViewPort.openObject(d, e);
        }
    }

    ,onOpenPreviewEvent: function(data, ev) {
        if(Ext.isEmpty(data)) {
            data = this.loadedData;
        }
        var p = Ext.apply({}, data);
        p.viewIndex = 2;
        this.delayedLoadTask.cancel();
        this.requestedLoadData = p;
        this.doLoad();
    }
    ,onOpenPropertiesEvent: function(data, sourceCmp, ev) {
        if(Ext.isEmpty(data)) {
            data = this.loadedData;
        }
        this.load(data);
    }
    ,onDownloadClick: function(b, e) {
        this.fireEvent('filedownload', [this.loadedData.id], false, e);
    }

    ,onCreateObjectClick: function(b, e) {
        this.goBackOnSave = true;

        b.data.pid = this.loadedData.id;
        b.data.path = this.loadedData.path;
        this.fireEvent('createobject', b.data, e);
    }

    ,onCloseTaskClick: function(b, e) {
        this.getEl().mask(L.CompletingTask + ' ...', 'x-mask-loading');
        CB_Tasks.close(this.loadedData.id, this.onTaskChanged, this);
    }
    ,onReopenTaskClick: function(b, e) {
        this.getEl().mask(L.ReopeningTask + ' ...', 'x-mask-loading');
        CB_Tasks.reopen(this.loadedData.id, this.onTaskChanged, this);
    }

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

    ,onTaskChanged: function(r, e){
        this.getEl().unmask();
        App.fireEvent('objectchanged', this.loadedData);
    }

    ,onAttachFileClick: function(b, e) {
        this.onViewChangeClick(0);
        var fp = this.findByType('CBObjectsPluginsFiles');
        if(Ext.isEmpty(fp)) {
            return;
        }
        fp = fp[0];
        fp.show();
        fp.onAddClick(b, e);
    }
    ,onWebDAVLinkClick: function(b, e) {
        App.openWebdavDocument(
            this.loadedData
            ,false
        );
    }

    ,onLockPanelEvent: function(status) {
        this.locked = status;
    }
}
);

Ext.reg('CBObjectCardView', CB.ObjectCardView);
