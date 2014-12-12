Ext.namespace('CB');

Ext.define('CB.file.edit.Window', {
    extend: 'CB.object.edit.Window'

    ,alias: 'CBFileEditWindow'

    ,xtype: 'CBFileEditWindow'

    ,width: 600
    ,height: 550

    /**
     * init this component actions
     * @return void
     */
    ,initActions: function() {
        this.callParent(arguments);

        Ext.apply(this.actions, {
            download: new Ext.Action({
                text: L.Download
                ,iconCls: 'icon-download'
                ,scope: this
                ,handler: this.onDownloadClick
            })
        });
    }

    /**
     * method that should return top toolbar buttons
     * @return array
     */
    ,getToolbarButtons: function() {
        this.downloadSeparator = Ext.create({xtype: 'tbseparator'});
        return [
            this.actions.edit
            ,this.actions.save
            ,this.actions.cancel
            ,this.downloadSeparator
            ,this.actions.download
            ,'->'
            ,this.actions.showInfoPanel
        ];
    }

    /**
     * initialize containers used
     * @return void
     */
    ,initContainerItems: function() {
        this.callParent(arguments);

        Ext.destroy(this.complexFieldContainer);

        this.complexFieldContainer = Ext.create({
            xtype: 'panel'
            ,border: false
            ,layout: 'fit'
            ,flex: 1
            ,items: []
        });
    }

    /**
     * function that should return items structure based on template config
     * @return array
     */
    ,getLayoutItems: function() {
        this.templateCfg.layout = 'horizontal';

        rez = [
            {
                region: 'center'
                ,border: true
                ,autoScroll: true
                ,layout: {
                    type: 'vbox'
                    ,align: 'stretch'

                }
                ,items: [
                    this.titleContainer
                    ,this.complexFieldContainer
                ]
            }, {
                region: 'east'
                ,itemId: 'infoPanel'
                ,header: false
                // ,border: false
                ,autoScroll: true
                ,collapsible: true
                ,collapseMode: 'mini'
                ,width: 200
                ,items: [
                    ,this.gridContainer
                    ,this.pluginsContainer
                ]
            }
        ];

        return rez;
    }

    /**
     * loading preview panel of the file
     * @return void
     */
    ,processLoadPreviewData: function(r, e) {
        this.callParent(arguments);

        var autoScroll = (getFileExtension(this.data.name) !== 'pdf');

        this.previewPanel = new CB.object.view.Preview({
            border: false
            ,autoScroll: autoScroll
            ,bodyStyle: 'padding: 5px'
        });

        this.complexFieldContainer.removeAll(true);
        this.complexFieldContainer.update('');
        this.complexFieldContainer.add(
            this.previewPanel
        );


        this.previewPanel.loadPreview(this.data.id);
    }

    /**
     * method for processing server data on editing item
     * @return void
     */
    // ,processLoadEditData: function(r, e) {
    //     this.callParent(arguments);

    // }

    ,updateComplexFieldContainer: function() {
        this.editType = detectFileEditor(this.data.name);

        switch(this.editType) {
            case 'text':
                this.contentEditor = new Ext.ux.AceEditor();
                break;

            case 'html':
                this.contentEditor = new Ext.ux.HtmlEditor({
                    border: false
                    ,listeners: {
                        scope: this
                        ,change: this.onEditorChangeEvent
                        ,sync: this.onEditorChangeEvent
                    }
                });
                break;
        }

        if(this.contentEditor) {
            this.complexFieldContainer.add(this.contentEditor);

            this.loadContent();
        }
    }

    ,loadContent: function() {
        CB_Files.getContent(this.data.id, this.onLoadContent, this);
    }

    ,onLoadContent: function(r, e) {
        if(r.success !== true) {
            plog('Error loading file content ', this.data);
            return;
        }

        if(this.contentEditor) {
            this.contentEditor.setValue(r.data);
        }
    }

    ,updateButtons: function() {
        this.editType = detectFileEditor(this.data.name);

        this.callParent(arguments);

        this.downloadSeparator.setHidden(this.actions.cancel.isHidden());
        this.actions.edit.setHidden((this.viewMode == 'edit') || (this.editType === false));
        this.actions.save.setDisabled(false);
    }

    /**
     * handler for edit toolbar button
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onEditClick: function(b, e) {
        switch(this.editType) {
            case 'text':
            case 'html':
                this.viewMode = 'edit';
                this.doLoad();
                break;

            case 'webdav':

                App.openWebdavDocument(this.data);
                break;
        }
    }

    ,onSaveClick: function(b, e) {
        this.saveContent();

        if(!this._isDirty) {
            return;
        }

        this.readValues();

        this.getEl().mask(L.Saving + ' ...', 'x-mask-loading');

        this.complexFieldContainer.getForm().submit({
            clientValidation: true
            ,loadMask: false
            ,params: {
                data: Ext.encode(this.data)
            }
            ,scope: this
            ,success: this.processSave
            ,failure: this.processSave
        });
    }

    ,saveContent: function() {
        var ed = this.contentEditor
            ,session = ed.getSession
                ? ed.getSession()
                : null;

        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');

        CB_Files.saveContent(
            {
                id: this.data.id
                ,data: session
                    ? session.getValue()
                    : ed.getValue()
            }
            ,this.processSaveContent
            ,this
        );
    }

    ,processSaveContent: function(r, e){
        this.getEl().unmask();
        this.actions.save.setDisabled(Ext.isEmpty(this.contentEditor));
    }

    /**
     * method for pocessing save responce
     * @param  component form
     * @param  object action
     * @return void
     */
    ,processSave: function(form, action) {
        this.getEl().unmask();

        var r = action.result;

        if(r.success !== true) {
            App.showException(action.result);
        } else {
            this._isDirty = false;
            App.fireEvent('objectchanged', r.data, this);
            this.close();
        }
    }

    /**
     * event handler for content editors change
     * @param  component ed [description]
     * @return void
     */
    ,onEditorChangeEvent: function(ed) {
        this.actions.save.setDisabled(false);
    }

    ,readValues: function() {
        this.grid.readValues();

        this.data.data = Ext.apply(
            this.data.data
            ,this.complexFieldContainer.getForm().getFieldValues()
        );

        return this.data;
    }

    /**
     * download button handler
     * @param  Button b
     * @param  EventObject e
     * @return void
     */
    ,onDownloadClick: function(b, e){
        App.downloadFile(this.data.id, false, this.loadedVersionId);
    }

});
