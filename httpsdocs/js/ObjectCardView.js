Ext.namespace('CB');

CB.ObjectCardView = Ext.extend(Ext.Panel, {
    border: false
    ,layout: 'card'
    ,activeItem: 0
    ,hideBorders: true
    ,tbarCssClass: 'x-panel-white'
    ,initComponent: function() {
        this.actions = {
            edit: new Ext.Action({
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
                this.actions.edit
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
                    }
                },{
                    title: L.Preview
                    ,iconCls: 'icon-preview'
                    ,header: false
                    ,xtype: 'CBObjectPreview'
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
                clog('sethidden', !canDownload)
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

    ,load: function(objectData) {
        if(!isNaN(objectData)) {
            objectData = {
                id: objectData
            };
        }
        this.delayedLoadTask.cancel();
        this.requestedLoadData = Ext.apply({}, objectData);
        if(this.getLayout().activeItem.getXType() !== 'CBEditObject') {
            this.onViewChangeClick(0);
            if(this.skipNextPreviewLoadOnBrowserRefresh) {
                delete this.skipNextPreviewLoadOnBrowserRefresh;
            } else {
                this.items.itemAt(0).clear();
                this.delayedLoadTask.delay(60, this.doLoad, this);
            }
        }
    }
    ,doLoad: function() {
        this.loadedData = Ext.apply({}, this.requestedLoadData);
        var activeItem = this.getLayout().activeItem;
        var id = this.requestedLoadData
            ? Ext.value(this.requestedLoadData.nid, this.requestedLoadData.id)
            : null;

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
    ,edit: function (objectData) {
        if(App.isWebDavDocument(objectData.name)) {
            App.openWebdavDocument(objectData);
            return;
        }
        this.onViewChangeClick(1, false);
        this.getLayout().activeItem.load(objectData);
        // this.loadedData.id = objectData.id;
    }
    ,onEditClick: function() {
        if(App.isWebDavDocument(this.loadedData.name)) {
            App.openWebdavDocument(this.loadedData);
            return;
        }
        this.onViewChangeClick(1);
        // this.actions.save.setDisabled(true);
        this.getLayout().activeItem.load(this.loadedData.id);
    }
    ,onReloadClick: function() {
        this.getLayout().activeItem.reload();
    }

    ,onSaveClick: function() {
        this.getLayout().activeItem.save(
            function(component, form, action){
                var id = Ext.value(action.result.data.id, this.loadedData.id);
                var name = Ext.value(action.result.data.name, this.loadedData.name);
                this.requestedLoadData.id = id;
                this.requestedLoadData.name = name;
                this.items.itemAt(0).load(id);
                this.onViewChangeClick(0, false);

                this.skipNextPreviewLoadOnBrowserRefresh = true;
            }
            ,this
        );
    }
    ,onCancelClick: function() {
        this.requestedLoadData = this.loadedData;
        this.onViewChangeClick(0);
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
