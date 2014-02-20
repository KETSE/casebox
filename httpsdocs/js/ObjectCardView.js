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
        this.actions = {
            back: new Ext.Action({
                iconCls: 'ib-back'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onBackClick
            })
            ,edit: new Ext.Action({
                iconCls: 'ib-edit-obj'
                ,scale: 'large'
                // ,disabled: true
                ,scope: this
                ,handler: this.onEditClick
            })
            ,reload: new Ext.Action({
                iconCls: 'ib-refresh'
                ,scale: 'large'
                ,scope: this
                ,handler: this.onReloadClick
            })
            ,download: new Ext.Action({
                qtip: L.Download
                ,iconAlign:'top'
                ,scale: 'large'
                ,iconCls: 'ib-download'
                ,hidden: true
                ,scope: this
                ,handler: this.onDownloadClick
            })

            ,save: new Ext.Action({
                iconCls: 'ib-save'
                ,scale: 'large'
                ,text: L.Save
                ,hidden: true
                ,scope: this
                ,handler: this.onSaveClick
            })
            ,cancel: new Ext.Action({
                iconCls: 'ib-cancel'
                ,scale: 'large'
                ,text: Ext.MessageBox.buttonText.cancel
                ,hidden: true
                ,scope: this
                ,handler: this.onCancelClick
            })
            ,openInTabsheet: new Ext.Action({
                iconCls: 'ib-external'
                ,scale: 'large'
                ,hidden: true
                ,scope: this
                ,handler: this.onOpenInTabsheetClick
            })

            // ,pin: new Ext.Action({
            //     iconCls: 'icon-pin'
            //     ,scope: this
            //     ,handler: this.onPinClick
            // })

        };

        Ext.apply(this, {
            hideMode: 'offsets'
            ,defaults: {
                hideMode: 'offsets'
            }
            ,tbar: [
                this.actions.back
                ,this.actions.edit
                ,this.actions.reload
                ,this.actions.download
                ,this.actions.save
                ,this.actions.cancel
                ,'->'
                ,this.actions.openInTabsheet
                // ,this.actions.pin
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
                        loaded: this.onCardItemLoaded
                    }
                }
            ]
            ,listeners: {
                scope: this
                ,add: this.onCardItemAdd
                ,afterrender: this.doLoad
            }
        });

        CB.ObjectCardView.superclass.initComponent.apply(this, arguments);

        this.delayedLoadTask = new Ext.util.DelayedTask(this.doLoad, this);

        this.addEvents('filedownload');
        this.enableBubble(['filedownload']);
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
        switch(activeItem.getXType()) {
            case 'CBObjectProperties':
                tb.setVisible(true);
                this.actions.edit.show();
                this.actions.reload.show();
                this.actions.save.hide();
                this.actions.cancel.hide();
                this.actions.openInTabsheet.hide();
                // this.actions.pin.hide();
                this.actions.download.setHidden(!canDownload);
                break;
            case 'CBEditObject':
                tb.setVisible(true);
                this.actions.edit.hide();
                this.actions.reload.hide();
                this.actions.save.show();
                this.actions.cancel.show();
                this.actions.openInTabsheet.show();
                this.actions.download.setHidden(true);
                // this.actions.pin.hide();
                break;
            case 'CBObjectPreview':
                tb.setVisible(true);
                this.actions.edit.show();
                this.actions.reload.show();
                this.actions.save.hide();
                this.actions.cancel.hide();
                this.actions.openInTabsheet.hide();
                this.actions.download.setHidden(!canDownload);
                // this.actions.pin.hide();
                //this.load(this.loadedData);
                break;
            default:
                tb.setVisible(false);
        }
    }

    /**
     * loading an object into the panel in a specific view
     * @param  {[type]} objectData [description]
     * @return {[type]}            [description]
     */
    ,load: function(objectData) {
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
        var id = this.requestedLoadData
            ? Ext.value(this.requestedLoadData.nid, this.requestedLoadData.id)
            : null;

        if(Ext.isEmpty(id)) {
            return;
        }

        this.addParamsToHistory(this.loadedData);

        this.loadedData = Ext.apply({}, this.requestedLoadData);

        if(Ext.isDefined(this.loadedData.viewIndex)) {
            this.onViewChangeClick(this.loadedData.viewIndex, false);
        }

        delete this.requestedLoadData;

        var activeItem = this.getLayout().activeItem;

        this.loadedData.viewIndex = this.items.indexOf(activeItem);

        switch(activeItem.getXType()) {
            case 'CBObjectPreview':
                if(Ext.isEmpty(this.requestedLoadData)) {
                    return;
                }
                this.getTopToolbar().setVisible(!Ext.isEmpty(id));
                this.doLayout();
                activeItem.loadPreview(id);
                break;
            case 'CBObjectProperties':
            case 'CBEditObject':
                activeItem.load({id: id});
                break;
        }
        this.onViewChange();
    }

    ,onCardItemLoaded: function(item) {
        if(Ext.isEmpty(this.loadedData.scroll)) {
            return;
        }
        item.body.scrollTo('left', this.loadedData.scroll.left);
        item.body.scrollTo('top', this.loadedData.scroll.top);
    }

    ,addParamsToHistory: function(p) {
        if(Ext.isEmpty(p) ||
            (Ext.encode(p) == '{}') ||
            (isNaN(p.id)) ||
            this.historyNavigation
        ) {
            delete this.historyNavigation;
            return;
        }
        this.history.push(p);
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
                p.viewIndex = 0;
                this.requestedLoadData = p;
                this.doLoad();
                // this.items.itemAt(0).load(id);
                // this.onViewChangeClick(0, false);
                this.skipNextPreviewLoadOnBrowserRefresh = true;
            }
            ,this
        );
    }
    ,onCancelClick: function() {
        var p = Ext.apply({}, this.loadedData);
        p.viewIndex = 0;
        this.requestedLoadData = p;
        this.doLoad();
    }
    ,onOpenInTabsheetClick: function(b, e) {
        var d = Ext.apply({}, this.getLayout().activeItem.data);
        var ai = this.getLayout().activeItem;
        if(ai.readValues) {
            d = Ext.apply(d, ai.readValues());
        }
        ai.clear();
        this.onViewChangeClick(0);

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
        this.getLayout().setActiveItem(2);
        this.onViewChange(2);
        this.getLayout().activeItem.loadPreview(data.id);
        this.loadedData = data;
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

}
);

Ext.reg('CBObjectCardView', CB.ObjectCardView);
