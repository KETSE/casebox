Ext.namespace('CB');

Ext.define('CB.object.edit.Window', {
    extend: 'Ext.Window'
    ,alias: 'CBObjectEditWindow'

    ,xtype: 'CBObjectEditWindow'

    ,closable: true
    ,minimizable: true
    ,maximizable: true
    ,layout: 'border'
    ,border: false
    ,minWidth: 200
    ,minHeight: 200
    ,width: 400
    ,height: 450
    ,iconCls: 'icon-none'
    ,autoScroll: false

    ,initComponent: function() {

        this.data = Ext.apply({}, this.config.data);
        delete this.data.html;

        if(Ext.isEmpty(this.data.template_id)) {
            return Ext.Msg.alert(
                'Error opening object'
                ,'Template should be specified for object window to load.'
            );
        }

        this.updateWindowTitle();

        this.objectsStore = new CB.DB.DirectObjectsStore({
            listeners:{
                scope: this
                ,add: this.onObjectsStoreChange
                ,load: this.onObjectsStoreLoad
            }
        });

        //init viewMode (preview / edit)
        this.viewMode = Ext.valueFrom(this.data.view, 'preview');

        //get template config
        this.templateCfg = CB.DB.templates.getProperty(this.data.template_id, 'cfg');

        //get template type
        this.templateType = CB.DB.templates.getType(this.data.template_id);

        //prepare interface components
        this.initActions();

        this.initContainerItems();

        //create and add title view
        this.titleView = new CB.object.TitleView();
        this.titleContainer.add(this.titleView);

        Ext.apply(this, {
            cls: 'x-panel-white'
            ,bodyStyle: 'border: 0; padding: 0'

            ,tbar: this.getToolbarButtons()

            ,items: this.getLayoutItems()

            ,stateful: true
            ,stateId: 'oew' + this.data.template_id

            ,listeners: {
                scope: this
                ,'change': this.onChange
                ,'afterrender': this.onAfterRender
                ,'beforeclose': this.onBeforeClose

                ,'openpreview': this.onOpenPreviewEvent
                ,'openproperties': this.onOpenPreviewEvent

                ,'editobject': this.onEditObjectEvent
                ,'editmeta': this.onEditObjectEvent

                ,'getdraftid': this.onGetDraftId
            }
        });

        this.callParent(arguments);

        this.doLoad();
    }

    /**
     * init this component actions
     * @return void
     */
    ,initActions: function() {
        this.actions = {
            edit: new Ext.Action({
                text: L.Edit
                ,iconCls: 'icon-edit'
                ,hidden: true
                ,scope: this
                ,handler: this.onEditClick
            })

            ,save: new Ext.Action({
                text: L.Save
                ,iconCls: 'icon-save'
                ,disabled: true
                ,hidden: true
                ,scope: this
                ,handler: this.onSaveClick
            })

            ,cancel: new Ext.Action({
                text: Ext.MessageBox.buttonText.cancel
                ,iconCls: 'i-cancel'
                ,hidden: true
                ,scope: this
                ,handler: this.close
            })

            ,showInfoPanel: new Ext.Action({
                iconCls: 'i-info'
                ,enableToggle: true
                ,pressed: true
                ,scope: this
                ,handler: this.onShowInfoPanelClick
            })
        };
    }

    /**
     * method that should return top toolbar buttons
     * @return array
     */
    ,getToolbarButtons: function() {
        return [
            this.actions.edit
            ,this.actions.save
            ,this.actions.cancel
            ,'->'
            ,this.actions.showInfoPanel
        ];
    }

    /**
     * initialize containers used
     * @return void
     */
    ,initContainerItems: function() {
        this.titleContainer = Ext.create({
            xtype: 'panel'
            ,border: false
            ,autoHeight: true
            ,items: []
        });

        this.complexFieldContainer = Ext.create({
            xtype: 'form'
            ,border: false
            ,autoHeight: true
            ,labelAlign: 'top'
            ,bodyStyle: 'margin:0; padding: 0 7px'
            ,api: {
                submit: CB_Objects.save
            }
            ,items: []
        });

        this.gridContainer = Ext.create({
            xtype: 'panel'
            ,border: false
            ,autoHeight: true
            ,items: []
        });

        this.pluginsContainer = Ext.create({
            xtype: 'CBObjectProperties'
            ,api: CB_Objects.getPluginsData
            ,border: false
            ,autoHeight: true
        });
    }

    /**
     * function that should return items structure based on template config
     * @return array
     */
    ,getLayoutItems: function() {
        var rez = [
            {
                region: 'center'
                ,autoScroll: true
                ,items: [
                    this.titleContainer
                    ,this.gridContainer
                    ,this.complexFieldContainer
                    ,{
                        itemId: 'infoPanel'
                        ,border: false
                        ,bodyStyle: 'padding-top: 15px'
                        ,autoHeight: true
                        ,items: [
                            this.pluginsContainer
                        ]
                    }
                ]
            }
        ];

        if((this.templateCfg.layout === 'horizontal') || (this.templateType == 'file')) {
            rez = [
                {
                    region: 'center'
                    ,border: true
                    ,autoScroll: true
                    ,items: [
                        this.titleContainer
                        ,this.complexFieldContainer
                    ]
                }, {
                    region: 'east'
                    ,itemId: 'infoPanel'
                    ,header: false
                    ,border: false
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
        }

        return rez;
    }

    ,onAfterRender: function(c) {
        // this.editForm.load(this.data);

        // map multiple keys to multiple actions by strings and array of codes
        var map = new Ext.KeyMap(
            c.getEl()
            ,[{
                key: "s"
                ,ctrl:true
                ,shift:false
                ,stopEvent: true
                ,scope: this
                ,fn: this.onSaveObjectEvent
            }]
        );

    }

    /**
     * clear containers method
     * @return void
     */
    ,clearContainers: function() {
        this.complexFieldContainer.removeAll(true);
        this.complexFieldContainer.update('');

        this.gridContainer.removeAll();
        this.gridContainer.update('');
    }

    /**
     * redirection method to corresponding load method depending on current viewModeSet
     * @return void
     */
    ,doLoad: function() {

        this.clearContainers();
        this['load' + Ext.util.Format.capitalize(this.viewMode) + 'Data']();

    }

    /**
     * method for loading preview data for current item
     * @return void
     */
    ,loadPreviewData: function() {
        CB_Objects.getPluginsData(
            {
                id: this.data.id
                // ,from: 'window'
            }
            ,this.processLoadPreviewData
            ,this
        );
        this.updateButtons();
    }

    /**
     * method for loading data into edit mode
     * @return void
     */
    ,loadEditData: function() {

        var data = this.data;

        // for a new object we just load template locally
        if(isNaN(data.id)) {
            if(Ext.isEmpty(data.name)) {
                data.name = L.New + ' ' + CB.DB.templates.getName(data.template_id);
            }

            this.processLoadEditData({
                    success: true
                    ,data: data
                }
            );
        } else {
            CB_Objects.load(
                {id: this.data.id}
                ,this.processLoadEditData
                ,this
            );
        }

        this.pluginsContainer.doLoad({
            id: this.data.id
            ,template_id: this.data.template_id
            ,from: 'window'
        });

        this.updateButtons();
    }

    /**
     * method for processing server data on loading preview
     * @return void
     */
    ,processLoadPreviewData: function(r, e) {
        if(r.success !== true) {
            return;
        }

        var objProperties  = Ext.valueFrom(r.data.objectProperties, {}).data
            ,preview = Ext.valueFrom(objProperties, {}).preview;

        //delete preview property from object data if set
        if(preview) {
            delete objProperties.preview;
        }

        Ext.apply(this.data, objProperties);

        this.titleView.update(this.data);

        delete r.data.objectProperties;
        delete r.data.thumb;

        if(preview) {
            if(this.gridContainer.rendered) {
                this.gridContainer.update(preview[0]);
            } else {
                this.gridContainer.html = preview[0];
            }

            var cfp = Ext.valueFrom(preview[1], '');
            if(this.complexFieldContainer.rendered) {
                this.complexFieldContainer.update(cfp);
            } else {
                this.complexFieldContainer.html = cfp;
            }
        } else {
            this.gridContainer.hide();
            if(this.complexFieldContainer.rendered) {
                this.complexFieldContainer.update('');
            } else {
                this.complexFieldContainer.html = '';
            }
        }

        this.pluginsContainer.onLoadData(r, e);

        this.postLoadProcess();
    }

    /**
     * method for processing server data on editing item
     * @return void
     */
    ,processLoadEditData: function(r, e) {
        if(r.success !== true) {
            return;
        }

        this.data = r.data;
        if(Ext.isEmpty(this.data.data)) {
            this.data.data = {};
        }

        this.titleView.update(this.data);

        this.objectsStore.proxy.extraParams = {
            id: r.data.id
            ,template_id: r.data.template_id
            ,data: r.data.data
        };

        this.startEditAfterObjectsStoreLoadIfNewObject = true;
        this.objectsStore.reload();

        /* detect template type of the opened object and create needed grid */
        var gridType = (this.templateType == 'search')
            ? 'CBVerticalSearchEditGrid'
            : 'CBVerticalEditGrid';

        if(this.lastgGridType != gridType) {
            this.gridContainer.removeAll(true);
            this.grid = Ext.create(
                gridType
                ,{
                    title: L.Details
                    ,autoHeight: true
                    ,hidden: true
                    ,refOwner: this
                    ,includeTopFields: true
                    ,stateId: 'oevg' //object edit vertical grid
                    ,autoExpandColumn: 'value'
                    ,autoScroll: false
                    ,keys: [{
                        key: "s"
                        ,ctrl:true
                        ,shift:false
                        ,scope: this
                        ,stopEvent: true
                        ,fn: this.onSaveObjectEvent
                    },{
                        key: "esc"
                        ,ctrl:false
                        ,shift:false
                        ,scope: this
                        ,stopEvent: true
                        ,fn: this.close
                    }]
                    ,viewConfig: {
                        forceFit: true
                        ,autoFill: true
                    }
                    ,listeners: {
                        scope: this

                        ,beforeedit: this.saveScroll
                        ,edit: this.restoreScroll

                        ,savescroll: this.saveScroll
                        ,restorescroll: this.restoreScroll
                    }
                }
            );
            this.lastgGridType = gridType;

            this.gridContainer.add(this.grid);
        }

        this.gridContainer.show();
        this.grid.reload();

        if(this.grid.store.getCount() > 0) {
            // var cm = this.grid.getColumnModel();

            var c = this.grid.headerCt.child('[dataIndex="title"]');//cm.findColumnIndex('title');
            var c2 = this.grid.headerCt.child('[dataIndex="value"]');//cm.findColumnIndex('value');

            if(CB.DB.templates.getType(r.data.template_id) == 'case') {
                c.setText('Case Card');
                c2.setText('Details');
            } else {
                c.setText(L.Property);
                c2.setText(L.Value);
            }

            this.grid.show();

            if(this.grid.rendered) {
                this.grid.getView().refresh(true);
                // this.grid.doLayout();
            }
        }

        this.updateComplexFieldContainer();

        this._isDirty = false;

        this.postLoadProcess();
    }

    /**
     * method specific for complex field container update
     * based on loaded data
     * @return void
     */
    ,updateComplexFieldContainer: function() {
        if(this.grid.templateStore) {
            var fields = [];
            this.grid.templateStore.each(
                function(r) {
                    if(r.get('cfg').showIn == 'tabsheet') {
                        var cfg = {
                            border: false
                            ,title: r.get('title')
                            ,isTemplateField: true
                            ,name: r.get('name')
                            ,value: this.data.data[r.get('name')]
                            ,height: Ext.valueFrom(r.get('cfg').height, 200)
                            ,anchor: '100%'
                            // ,style: 'resize: vertical'
                            ,grow: true
                            ,fieldLabel: r.get('title')
                            ,labelAlign: 'top'
                            ,labelCls: 'fwB ttU'
                            ,listeners: {
                                scope: this
                                ,change: function(field, newValue, oldValue) {
                                    this.fireEvent('change', field.name, newValue, oldValue);
                                }
                                ,sync: function(){
                                    this.fireEvent('change');
                                }
                            }
                            ,xtype: (r.get('type') == 'html')
                                ? 'CBHtmlEditor'
                                : 'textarea'
                        };
                        this.complexFieldContainer.add(cfg);
                    }
                }
                ,this
            );
        }

        this.complexFieldContainer.setVisible(this.complexFieldContainer.items.getCount() > 0);
    }

    /**
     * method called after preview or edit data has been loaded
     * @return void
     */
    ,postLoadProcess: function() {
        if(!this.hasLayout && this.updateLayout) {
            this.updateLayout();
        }

        this.updateWindowTitle();

        this.fireEvent('loaded', this);
    }

    /**
     * method for updating window title and icon according to template and data
     * @return void
     */
    ,updateWindowTitle: function() {
        var templatesStore = CB.DB.templates
            ,templateId = this.data.template_id
            ,d = this.data
            ,title = Ext.valueFrom(d.name, d.title);

        if(Ext.isEmpty(title)) {
            title = L.New + ' ' + templatesStore.getProperty(templateId, 'name');
        }
        this.setTitle(title);

        this.setIconCls(getItemIcon(this.data));
    }

    ,updateButtons: function() {
        if(this.viewMode == 'preview') {
            this.actions.edit.show();
            this.actions.save.hide();
            this.actions.cancel.hide();
        } else {
            this.actions.edit.hide();
            this.actions.save.show();
            this.actions.save.setDisabled(!this._isDirty);
            this.actions.cancel.show();
        }
    }

    /**
     * listner method for change field values
     * @param  string fieldName
     * @param  variant newValue
     * @param  variant oldValue
     * @return void
     */
    ,onChange: function(fieldName, newValue, oldValue){
        this._isDirty = true;
        this.actions.save.setDisabled(false);

        if(!Ext.isEmpty(fieldName) && Ext.isString(fieldName)) {
            this.fireEvent('fieldchange', fieldName, newValue, oldValue);
        }
        // this.updateLayout();

        //fire event after change event process
        this.fireEvent('changed', this);
    }

    ,onLoaded: function(editForm) {
        var title = Ext.valueFrom(editForm.data.name, '');

        this.setTitle(Ext.util.Format.htmlEncode(title));
        this.setIconCls(getItemIcon(editForm.data));
        this.updateLayout();
    }

    ,onSaveObjectEvent: function(objComp, ev) {
        ev.stopEvent();
        if(this.actions.save.isDisabled()) {
            return false;
        }
        this.onSaveClick();
    }

    /**
     * handler for edit toolbar button
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onEditClick: function(b, e) {
        this.viewMode = 'edit';
        this.doLoad();
    }

    ,onSaveClick: function(b, e) {
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


        // this.editForm.save(
        //     //callback function
        //     function(component, form, action){
        //         if(action.result.success !== true) {
        //             App.showException(action.result);
        //         } else {
        //             this.actions.save.setDisabled(true);
        //             this.close();
        //         }
        //     }
        //     ,this
        // );
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


    ,onObjectsStoreLoad: function(store, records, options) {
        this.onObjectsStoreChange(store, records, options);

        if(!this.grid.editing) {
            this.grid.getView().refresh();

            if(this.startEditAfterObjectsStoreLoadIfNewObject === true) {
                this.focusDefaultCell();
            }
        }
    }

    ,onObjectsStoreChange: function(store, records, options){
        Ext.each(
            records
            ,function(r){
                r.set('iconCls', getItemIcon(r.data));
            }
            ,this
        );
    }

    /**
     * focus value column in first row, and start editing if it's a new object
     * @return void
     */
    ,focusDefaultCell: function() {
        if(this.grid &&
            !this.grid.editing &&
            this.grid.getEl() &&
            (this.grid.store.getCount() > 0)
        ) {
            var valueCol = this.grid.headerCt.child('[dataIndex="value"]');
            var colIdx = valueCol.getIndex();

            this.grid.getSelectionModel().select({row: 0, column: colIdx});
            this.grid.getNavigationModel().setPosition(0, colIdx);

            if(this.startEditAfterObjectsStoreLoadIfNewObject && isNaN(this.data.id)) {
                this.grid.editingPlugin.startEditByPosition({row: 0, column: colIdx});
            }

            delete this.startEditAfterObjectsStoreLoadIfNewObject;
        }

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
     * set value for a field
     *
     * TODO: review for duplicated fields, and for fields outside of the grid
     *
     * @param varchar fieldName
     * @param variant value
     */
    ,setFieldValue: function (fieldName, value) {
        if(this.grid) {
            this.grid.setFieldValue(fieldName, value);
        }
    }

    ,onBeforeClose: function(){
        if(this._confirmedClosing || !this._isDirty){
            return true;
        }

        Ext.Msg.show({
            title:  L.Confirmation
            ,msg:   L.SavingChangedDataMessage
            ,icon:  'ext-mb-question'
            ,buttons: Ext.Msg.YESNOCANCEL
            ,scope: this
            ,fn: function(b, text, opt){
                switch(b){
                case 'yes':
                    this._confirmedClosing = true;
                    this.onSaveClick();
                    break;
                case 'no':
                    this._confirmedClosing = true;
                    this.close();
                    break;
                }
            }
        }).getEl().center(this);

        return false;
    }

    /**
     * handler for show right panel toolbar button
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onShowInfoPanelClick: function(b, e) {
        var ip = this.queryById('infoPanel');

        if(ip) {
            ip.setVisible(b.pressed);
        }
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

        var p = Ext.apply({}, data);
        // p.view = 'preview'; //default is preview

        App.openObjectWindow(p, e);
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

        p.view = 'edit';

        switch(detectFileEditor(p.name)) {
            case 'webdav':
                App.openWebdavDocument(p);
                break;
            default:
                App.openObjectWindow(p);
                break;
        }
    }

    /**
     * save scroll position method for vertical grid editor
     * @return variant cusrrent scroll position
     */
    ,saveScroll: function() {
        var gc = this.gridContainer.ownerCt;
        this.lastScroll = gc.body.getScroll();

        return this.lastScroll;
    }

    /**
     * restore scroll position method for vertical grid editor
     * @return void
     */
    ,restoreScroll: function() {
        var gc = this.gridContainer.ownerCt;
        gc.body.setScrollLeft(this.lastScroll.left);
        gc.body.setScrollTop(this.lastScroll.top);
    }

    ,onGetDraftId: function(callback, scope) {
        this.getDraftIdCallback = scope
            ? Ext.Function.bind(callback, scope)
            : callback;

        if(!isNaN(this.data.id)) {
            this.getDraftIdCallback(this.data.id);

        } else {
            this.readValues();

            var data = Ext.apply({}, this.data);
            data.draft = true;

            CB_Objects.create(
                data
                ,this.processSaveDraft
                ,this
            );
        }
    }

    ,processSaveDraft: function(r, e) {
        if(r.success !== true) {
            return;
        }

        var id = r.data.id;
        this.data.id = id;

        //update loadedData.id of the plugins container so it will reload automaticly
        //on fileuploaded event
        this.pluginsContainer.loadedParams = {
            id: id
            ,template_id: this.data.template_id
            ,from: 'window'
        };

        this.getDraftIdCallback(id);
    }

});
