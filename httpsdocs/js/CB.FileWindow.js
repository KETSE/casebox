Ext.namespace('CB');


CB.FileWindow = Ext.extend(Ext.Panel, {
    closable: true
    ,layout: 'fit'
    ,hideBorders: true

    ,initComponent: function() {

        this.actions = {
            edit: new Ext.Action({
                text: L.Edit
                ,iconAlign:'top'
                ,iconCls: 'ib-edit-obj'
                ,scale: 'large'
                // ,disabled: true
                ,scope: this
                ,hidden: true
                ,handler: this.onDirectEditLinkClick
            })

            ,save: new Ext.Action({
                text: L.Save
                ,iconAlign:'top'
                ,iconCls: 'ib-save'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,hidden: true
                ,handler: this.onSaveClick
            })

            ,upload: new Ext.Action({
                text: L.Upload
                ,tooltip: L.UploadFile
                ,iconAlign:'top'
                ,iconCls: 'ib-upload'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onUploadClick
            })

            ,download: new Ext.Action({
                text: L.Download
                ,tooltip: L.Download
                ,iconAlign:'top'
                ,iconCls: 'ib-download'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onDownloadClick
            })

            ,restoreVersion: new Ext.Action({
                text: L.Restore
                ,iconAlign:'top'
                ,iconCls: 'ib-restore'
                ,scale: 'large'
                ,hidden: true
                ,scope: this
                ,handler: this.onRestoreVersionClick
            })

            ,expand: new Ext.Action({
                text: L.Expand
                ,iconAlign:'top'
                ,iconCls: 'ib-expand'
                ,scale: 'large'
                ,enableToggle: true
                ,scope: this
                ,handler: this.onExpandClick
            })

            ,newWindow: new Ext.Action({
                text: L.NewWindow
                ,iconAlign:'top'
                ,iconCls: 'ib-external'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onNewWindowClick
            })

            ,'delete': new Ext.Action({
                text: L.Delete
                ,scope: this
                ,handler: this.onDeleteClick
            })

            ,getEditLink: new Ext.Action({
                text: L.DirectEditLink
                ,scope: this
                ,handler: this.onDirectEditLinkClick
            })

        };

        this.separators = {
            save: new Ext.Toolbar.Separator({hidden: true})
            ,edit: new Ext.Toolbar.Separator({hidden: true})
            ,restoreVersion: new Ext.Toolbar.Separator({hidden: true})
        };

        Ext.apply(this, {
            listeners: {
                scope: this
                ,beforedestroy: this.onBeforeDestroy
                ,openversion: this.onOpenVersionEvent
            }
        });

        CB.FileWindow.superclass.initComponent.apply(this, arguments);

        this.addEvents('fileupload', 'filedownload');
        this.enableBubble(['fileupload', 'filedownload']);

        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
        App.mainViewPort.on('fileuploaded', this.onFileUploaded, this);
    }

    /**
     * after render event handler
     * @return void
     */
    ,afterRender: function() {
        // call parent
        CB.FileWindow.superclass.afterRender.apply(this, arguments);
        this.loadProperties();
    }

    /**
     * before destroy handler
     * @return void
     */
    ,onBeforeDestroy: function(){
        if(this.grid) {
            this.grid.destroy();
        }

        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
        App.mainViewPort.un('fileuploaded', this.onFileUploaded, this);
    }

    /**
     * load object data method
     * @return void
     */
    ,loadProperties: function(){
        CB_Files.getProperties(this.data.id, this.processLoadProperties, this);
    }

    /**
     * process responce for loading object data
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,processLoadProperties: function(r, e){
        if(r.success !== true) {
            return;
        }

        //prepare object data
        this.data = r.data;
        this.data.cdate = date_ISO_to_local_date(this.data.cdate);
        this.data.udate = date_ISO_to_local_date(this.data.udate);
        this.data.id = parseInt(this.data.id, 10);
        this.setIconClass(getFileIcon(r.data.name));
        this.setTitle(r.data.name);

        //prepare interface if first time load (could be reloaded)
        if( !this.loaded ){
            this.prepareInterface();
            this.loaded = true;
        }

        //make grid load its data from this this.data
        if(this.grid) {
            this.grid.reload();
        }

        //set default state for actions
        this.actions.save.setDisabled(Ext.isEmpty(this.sourceEditor)); //will be replaced to true when clarify the editors events

        this.actions.download.setDisabled(false);
        this.actions.upload.setDisabled(false);
        this.actions.newWindow.setDisabled(false);
        this.actions['delete'].setDisabled(false);

        this.separators.restoreVersion.setVisible(false);
        this.actions.restoreVersion.setHidden(true);
        this.actions.getEditLink.setHidden(!App.isWebDavDocument(this.data.name));


        this.loadContent();

        this.objectPanel.load({
            id: this.data.id
            ,from: 'window'
        });
    }

    /**
     * prepare interface using loaded object data
     * @return void
     */
    ,prepareInterface: function(){

        var contentItems = [];
        var moreItems = [this.actions.getEditLink];


        if(!this.hideDeleteButton) {
            moreItems.unshift(this.actions['delete']);
        }

        /* insert create menu if needed */
        var menuConfig = getMenuConfig(this.data.id, this.data.path, this.data.template_id);

        if( !Ext.isEmpty(menuConfig) ){
            var createButton = new Ext.menu.Item({
                text: L.Create
                ,menu: []
            });
            updateMenu(createButton, menuConfig, this.onCreateObjectClick, this);
            moreItems.push('-', createButton);
        }

        var moreButton = new Ext.Button({
            iconCls: 'ib-points'
            ,iconAlign:'top'
            ,scale: 'large'
            ,scope: this
            ,text: L.More
            ,menu: new Ext.menu.Menu({items: moreItems})
            ,handler: function(b, e) {
                b.menu.show(b.getEl());
            }
        });

        var toolbarItems = [
            this.actions.save
            ,this.actions.edit
            ,this.separators.edit
            ,this.actions.upload
            ,this.actions.download
            ,this.separators.restoreVersion
            ,this.actions.restoreVersion
            ,'->'
            ,this.actions.expand
            ,this.actions.newWindow
            ,moreButton
        ];
        /* */

        // detect needed editors for this file
        var showSaveButton = true
            ,showEditButton = false;

        this.editType = detectFileEditor(this.data.name);
        switch(this.editType) {
            case 'text':
                this.sourceEditor = new Ext.ux.AceEditor({
                    // mode: 'text'
                    // ,listeners: {
                    //     scope: this
                    //     ,change: this.onEditorChangeEvent
                    // }
                });
                contentItems.push(this.sourceEditor);
                break;

            case 'html':
                this.wysiwygEditor = new Ext.ux.HtmlEditor({
                    border: false
                    ,hideBorders: true
                    ,listeners: {
                        scope: this
                        ,change: this.onEditorChangeEvent
                        ,sync: this.onEditorChangeEvent
                    }
                });

                this.sourceEditor = new Ext.ux.AceEditor({
                    // mode: 'html'
                    // ,listeners: {
                    //     scope: this
                    //     ,change: this.onEditorChangeEvent
                    // }
                });

                contentItems.push(this.wysiwygEditor, this.sourceEditor);
                break;

            case 'webdav':
                showEditButton = true;

            default:
                showSaveButton = false;

                this.previewPanel = new CB.form.view.object.Preview({
                    bodyStyle: 'padding: 5px'
                });

                contentItems.push(this.previewPanel);
                break;
        }

        this.actions.save.setHidden(!showSaveButton);
        this.actions.edit.setHidden(!showEditButton);
        this.separators.edit.setVisible(showSaveButton || showEditButton);

        this.objectPanel = new CB.ObjectCardView({
            region: 'east'
            ,width: 300
            ,split: true
            ,bodyStyle: 'background-color: #f4f4f4'

            ,listeners: {
                scope: this
                ,loaded: function(objectPanel, activeViewItem) {
                    if(!objectPanel || !objectPanel.isXType(CB.ObjectCardView)) {
                        return;
                    }

                    var tb = objectPanel.getTopToolbar();
                    if(tb) {
                        if(Ext.isEmpty(objectPanel.loadedData) ||
                            Ext.isEmpty(objectPanel.loadedData.id) ||
                            (
                                (objectPanel.loadedData.id == this.data.id) &&
                                (Ext.value(objectPanel.loadedData.viewIndex, 0) == 0)
                            )
                        ) {
                            tb.hide();
                        } else {
                            tb.show();
                        }
                    }
                    objectPanel.syncSize();
                    objectPanel.ownerCt.syncSize();
                }
            }
        });

        this.add({
            layout: 'border'
            ,tbarCssClass: 'x-panel-white'
            ,hideBorders: true
            ,tbar: toolbarItems
            ,items: [
                {
                    xtype: 'panel'
                    ,layout: 'card'
                    ,activeItem: 0
                    ,hideBorders: true
                    ,bodyStyle: 'background-color: #FFF'
                    ,region: 'center'
                    ,items: contentItems
                }
                ,this.objectPanel
            ]
        });

        this.cardPanel = this.items.itemAt(0).items.itemAt(0);

        this.doLayout();
    }

    ,onEditorChangeEvent: function(ed) {
        this.actions.save.setDisabled(false);
    }

    ,loadContent: function() {

        if(this.previewPanel) {
            this.previewPanel.clear();
            this.previewPanel.loadPreview(this.data.id);
        }

        CB_Files.getContent(this.data.id, this.onLoadContent, this);
    }

    ,onLoadContent: function(r, e) {
        if(r.success !== true) {
            plog('Error loading file content ', this.data);
            return;
        }

        if(this.wysiwygEditor) {
            this.wysiwygEditor.setValue(r.data);
        }

        if(this.sourceEditor) {
            this.sourceEditor.setValue(r.data);
        }
    }

    ,onOpenVersionEvent: function(data, pluginComponent) {
        this.loadedVersionId = data.id;
        this.previewPanel.loadPreview(this.data.id, data.id);
        this.separators.restoreVersion.setVisible(!Ext.isEmpty(data.id));
        this.actions.restoreVersion.setHidden(Ext.isEmpty(data.id));
        this.objectPanel.setSelectedVersion({id: this.data.id, versionId: this.loadedVersionId});
    }

    /**
     * create button handler
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,onCreateObjectClick: function(b, e) {
        var data = Ext.apply(
            {}
            ,{
                pid: this.data.id
                ,path: this.data.path+'/'+this.data.id
                ,pathtext: this.data.pathtext+'/'+this.data.name
            }
            ,b.data
        );

        App.mainViewPort.createObject(data, e);
    }

    /**
     * upload button click handler
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,onUploadClick: function(b, e){
        this.fireEvent('fileupload', this.data, e);
    }

    /**
     * file uploaded event handler
     * @param  object data
     * @return void
     */
    ,onFileUploaded: function(data){
        if(data.data.id == this.data.id) {
            this.loadProperties();
        }
    }

    /**
     * download button handler
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,onDownloadClick: function(b, e){
        this.fireEvent('filedownload', this.data.id);
    }

    /**
     * save button handler
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,onSaveClick: function(b, e){
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');

        CB_Files.saveContent(
            {
                id: this.data.id
                ,data: this.cardPanel.getLayout().activeItem.getSession().getValue()
            }
            ,this.processSaveClick
            ,this
        );
    }

    /**
     * process remote save responce
     * @param  object r Ext.Direct responce
     * @param  EventObject e
     * @return
     */
    ,processSaveClick: function(r, e){
        this.getEl().unmask();
        this.actions.save.setDisabled(Ext.isEmpty(this.sourceEditor));
    }

    /**
     * on delete button handler
     * @return void
     */
    ,onDeleteClick: function(){
        Ext.Msg.confirm(
            L.DeleteConfirmation
            ,L.fileDeleteConfirmation// + ' "' + this.data.name + '"?'
            ,function (btn) {
                if(btn !== 'yes') {
                    return;
                }
                this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
                CB_BrowserTree['delete'](this.data.id, this.processDelete, this);
            }
            ,this
        );
    }

    ,onDirectEditLinkClick: function(b, e) {
        //
        App.openWebdavDocument(
            this.data
            ,false
        );
    }

    /**
     * process delete reponce
     * @param  object r Ext.direct responce
     * @param  EventObject e
     * @return
     */
    ,processDelete: function(r, e){
        this.getEl().unmask();
        App.mainViewPort.onProcessObjectsDeleted(r, e);
    }

    /**
     * destroy file window if catched a delete event with it's id
     * @param  array ids
     * @return void
     */
    ,onObjectsDeleted: function(ids){
        if( ids.indexOf(this.data.id) >=0 ) {
            this.destroy();
        }
    }

    ,onRestoreVersionClick: function(){
        if(Ext.isEmpty(this.loadedVersionId)) {
            return;
        }

        CB_Files.restoreVersion(
            this.loadedVersionId
            ,function(r, e){
                App.mainViewPort.fireEvent('fileuploaded', {data: r.data});
            }
            ,this
        );
    }

    /**
     * expand file window by collapsing left and right regions
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,onExpandClick: function (b, e) {
        App.mainViewPort.toggleWestRegion(!b.pressed);
        this.items.first().items.last().setVisible(!b.pressed);
    }

    /**
     * open file in new browser window
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,onNewWindowClick: function(b, e){
        window.open('/' + App.config.coreName + '/v-' + this.data.id + '/');
    }

    /**
     * locate a clicked path
     * @return void
     */
    ,onPathClick: function(){
        App.locateObject( this.data.pid, this.data.path );
     }
});

Ext.reg('CBFileWindow', CB.FileWindow); // register xtype
