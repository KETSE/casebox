Ext.namespace('CB');

CB.Objects = Ext.extend(CB.GenericForm, {
    title: L.NewObject
    ,padding: 0
    ,initComponent: function(){

        this.objectsStore = new CB.DB.DirectObjectsStore({
            baseParams: {
                id: this.data.id
                ,template_id: this.data.template_id
                ,data: this.data.data
            }
            ,listeners:{
                scope: this
                ,add: this.onObjectsStoreChange
                ,load: this.onObjectsStoreChange
            }
        });

        this.objectsStore.load();

        this.topFieldSet = new Ext.form.FieldSet({
            columnWidth: 0.9
            ,autoHeight: true
            ,xtype: 'fieldset'
            ,border: false
            ,labelWidth: 130
            ,bodyStyle: 'padding: 10px'
            ,cls: 'spacy-fields'
            ,defaults:{
                minWidth: 90
                ,anchor: '95%'
                ,boxMaxWidth: 800
                ,bubbleEvents: ['change']
            }
            ,items: []
            ,listeners: {
                scope: this
                ,add: function(f, c, i){ c.enableBubble('change'); }
            }
        });

        this.tabPanel = new Ext.TabPanel({
            xtype: 'tabpanel'
            ,region: 'center'
            ,headerCfg: {cls: 'whiteTabPanel'}
            ,activeItem: 0
            ,enableTabScroll: true
            ,tabMargin: 120
        });

        this.actions = {
            save: new Ext.Action({
                text: L.Save
                ,iconAlign:'top'
                ,iconCls: 'ib-save'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onSaveClick
            })

            ,cancel: new Ext.Action({
                text: Ext.MessageBox.buttonText.cancel
                ,iconAlign:'top'
                ,iconCls: 'ib-cancel'
                ,scale: 'large'
                ,scope: this
                ,handler: this.onCancelClick
            })

            ,createTask: new Ext.Action({
                text: L.NewTask
                ,iconCls: 'ib-task-new'
                ,iconAlign:'top'
                ,scale: 'large'
                //,disabled: true
                ,scope: this
                ,handler: this.onCreateTaskClick.createInterceptor(this.autoSaveObjectInterceptor, this)
            })
            ,upload: new Ext.Action({
                tooltip: L.UploadFile
                ,iconCls: 'icon-drive-upload'
                ,text: L.Upload
                //,disabled: true
                ,scope: this
                ,handler: this.onUploadClick.createInterceptor(this.autoSaveObjectInterceptor, this)
            })
            ,paste: new Ext.Action({
                tooltip: L.PasteFromClipboard
                ,text: L.PasteFromClipboard
                ,disabled: true
                ,scope: this
                ,handler: this.onPasteClick.createInterceptor(this.autoSaveObjectInterceptor, this)
            })

            ,'delete': new Ext.Action({
                text: L.Delete
                ,disabled: true
                ,scope: this
                ,handler: this.onDeleteClick
            })

            ,'security': new Ext.Action({
                text: L.Security
                ,disabled: true
                ,scope: this
                ,handler: this.onSecurityClick
            })

        };

        Ext.apply(this, {
            layout: 'fit'
            ,initialConfig:{
                api: {
                    load: CB_Objects.load
                    ,submit: CB_Objects.save
                    ,waitMsg: L.LoadingData + ' ...'
                }
                ,paramsAsHash: true
            }
            ,listeners:{
                afterlayout: {scope: this, fn: function(){
                    if(this.loaded) return;
                    this.getEl().mask(L.Downloading + ' ...', 'x-mask-loading');
                }}

                ,activate: function(){
                    ep = this.find('region', 'center');
                    if(!Ext.isEmpty(ep)) ep[0].syncSize();
                }

                ,change: function(fieldName, newValue, oldValue){

                    if(!Ext.isEmpty(fieldName) && Ext.isString(fieldName)) {
                        this.fireEvent('fieldchange', fieldName, newValue, oldValue);
                    } else {
                        if(fieldName && fieldName.isXType && (fieldName.isXType('combo')) ){
                            this.updateDependentFields(fieldName.name, v);
                        }
                    }

                    this.setDirty(true);
                    this.onObjectChanged();
                }

                ,savesuccess: this.onObjectSaved
                ,beforedestroy: {
                    scope: this
                    ,fn: function(){
                        this.getBubbleTarget().un('filesdeleted', this.onFilesDeleted, this);
                        this.getBubbleTarget().un('fileuploaded', this.onFileUploaded, this);
                        if(this.grid){
                            this.grid.destroy();
                            delete this.grid;
                        }
                        if(this.filesGrid){
                            this.filesGrid.destroy();
                            delete this.filesGrid;
                        }
                        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
                        App.clipboard.un('change', this.onClipboardChange, this);
                        delete this.filesDropPlugin;

                    }
                }
            }
        });

        this.dropZoneConfig = {};//text: 'Drop files here'
        this.filesDropPlugin = new CB.plugins.FilesDropZone({pidPropety: 'id'});
        this.filesDropPlugin.init(this);

        CB.Objects.superclass.initComponent.apply(this, arguments);

        this.addEvents('deleteobject', 'associateObject', 'deassociateObject', 'fileupload', 'filedownload');//, 'filesdelete'
        this.enableBubble(['deleteobject', 'fileupload', 'filedownload']);//, 'filesdelete'

        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
        App.clipboard.on('change', this.onClipboardChange, this);
        App.fireEvent('objectinit', this);
    }

    ,getProperty: function(propertyName){
        if(this.data && this.data[propertyName]){
            if(propertyName == 'pathtext') return this.data[propertyName]+this.data.name+'/';
            return this.data[propertyName];
        }
        return null;
    }

    ,autoSaveObjectInterceptor: function(){
        if(isNaN(this.data.id)){
            this.interceptorArguments = arguments;
            this.onSaveClick();
            return false;
        }
        return true;
    }

    ,onFormLoaded: function(r, e){
        this.data.cdate = date_ISO_to_local_date(this.data.cdate);
        this.data.udate = date_ISO_to_local_date(this.data.udate);
    }

    ,onObjectsStoreChange: function(store, records, options){
        Ext.each(
            records
            ,function(r){
                r.set('iconCls', getItemIcon(r.data));
            }
            ,this
        );
        if(this.grid && !this.grid.editing && this.grid.getEl()) {
            this.grid.getView().refresh();
        }
    }

    ,prepareInterface: function(){
        toolbarItems = [];

        toolbarItems.push(this.actions.save, this.actions.cancel);

        var moreItems = [
            this.actions.upload
            ,'-'
        ];
        if(!this.hideDeleteButton) {
            moreItems.push(this.actions['delete'], '-');
        }

        moreItems.push(this.actions.security);


        /* insert create menu if needed */
        // menuConfig = getMenuConfig(this.data.id, this.data.path, this.data.template_id);
        // if( !Ext.isEmpty(menuConfig) ){
        //     createButton = new Ext.menu.Item({
        //         text: L.Create
        //         ,menu: [ ]
        //     });
        //     updateMenu(createButton, menuConfig, this.onCreateObjectClick.createInterceptor(this.autoSaveObjectInterceptor, this), this);
        //     moreItems.push('-', createButton);
        // }
        /**/

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

        toolbarItems.push('->');
        toolbarItems.push(moreButton);

        northRegionItems = [this.topFieldSet];

        this.grid = Ext.create({
            title: L.Details
            ,show_files: this.templateData.cfg.files
            ,refOwner: this
        }, Ext.value(this.templateData.cfg.gridJsClass, 'CBVerticalEditGrid'));

        //placing content elements
        contentItems = [this.tabPanel];
        if( (this.topFieldSet.items.getCount() > 0) )
            contentItems.unshift({
                xtype: 'panel'
                ,autoHeight: true
                ,region: 'north'
                ,border: false
                ,layout: 'fit'
                ,padding: 0
                ,items: northRegionItems
        });

        this.objectPanel = new CB.ObjectCardView({
            region: 'east'
            ,width: 300
            ,split: true
            ,bodyStyle: 'background-color: #f4f4f4'

            ,listeners: {
                scope: this
                ,loaded: function(objectPanel, activeViewItem) {
                    if(Ext.isEmpty(objectPanel.loadedData) ||
                        Ext.isEmpty(objectPanel.loadedData.id) ||
                        (objectPanel.loadedData.id == this.data.id)
                    ) {
                        objectPanel.getTopToolbar().hide();
                    } else {
                        objectPanel.getTopToolbar().show();
                    }
                    objectPanel.syncSize();
                    objectPanel.ownerCt.syncSize();
                }
            }
        });

        this.add({
            xtype: 'panel'
            ,tbar: toolbarItems
            ,tbarCssClass: 'x-panel-white'
            ,layout: 'border'
            ,border: false
            ,hideBorders: true
            ,items: [
                {
                    layout: 'border'
                    ,region: 'center'
                    ,border: false
                    ,defaults: {border: false}
                    ,xtype: 'panel'
                    ,items: contentItems
                }
                ,this.objectPanel
            ]
        });
        this.mainToolBar = this.items.first().getTopToolbar();

        this.getEl().unmask();

        this.addEvents('taskcreate');
        this.enableBubble(['taskcreate']);
        this.fireEvent('objectopened', this);
    }

    ,onSaveClick: function(){
        this.saveForm();
    }

    ,onCancelClick: function(){
        this.destroy();
    }

    ,hasMainFile: function(){
        return (this.data.mainFile && !isNaN(this.data.mainFile.id));
    }

    ,getObjectDate: function(){
        idx = this.templateStore.findExact('name', '_date_start');
        if(idx >=0) {
            r = this.templateStore.getAt(idx);
            return this.getCurrentFieldValue(r.get('id'), 0);
        }
        return null;
    }

    ,getFieldValue: function(fieldName, valueIndex) {
        // this.templateData
    }

    /**
     * set a field value in the grid
     * to fields are not processed for now
     * @param varchar fieldName
     * @param variant valueIndex
     */
    ,setFieldValue: function(fieldName, valueIndex) {
        if(this.grid && this.grid.setFieldValue) {
            this.grid.setFieldValue(fieldName, valueIndex);
        }
    }

    ,getCurrentFieldValue: function(field_id, duplication_id){
        ed = this.topFieldSet.find('name', 'f'+ field_id+'_0');
        if(!Ext.isEmpty(ed)) return ed[0].getValue();
        if(Ext.isEmpty(this.grid) || Ext.isEmpty(this.grid.getFieldValue)) return null;
        return this.grid.getFieldValue(field_id, duplication_id);
    }

    ,onDeleteClick: function(b){
        this.fireEvent('deleteobject', this.data);
    }

    ,onObjectsDeleted: function(ids){
        if(ids.indexOf(parseInt(this.data.id, 10)) >=0 ) {
            this.destroy();
        }
    }

    ,onClipboardChange: function(cb){
        this.actions.paste.setDisabled(cb.isEmpty());
    }

    ,getBubbleTarget: function(){
        return App.mainViewPort;
    }

    ,setFormValues: function(){
        var lastActiveTabIndex = this.tabPanel.items.indexOf(this.tabPanel.activeTab);
        if(Ext.isEmpty(this.data.data)) {
            this.data.data = {};
        }
        /* adding top fields and fields editable in tabsheet */
        if(Ext.isDefined(this.topFieldSet)){
            this.topFieldSet.removeAll(true);
        }
        /* remove tabpanel items that have edit position on tabsheet */
        this.tabPanel.items.each(
            function(i){
                if(i.isTemplateField) {
                    this.tabPanel.remove(i, true);
                }
            }
            ,this
        );
        tpInsertIndex = 1;
        /* getting the template store and adding fields, that are set to be edited on top, to the fieldSet.
            Also creating tabs in our tabPanel for the fields that are set to be edited in tabpanel
        */
        /* we admit that the template property is available */
        this.templateData = {};
        idx = CB.DB.templates.findExact('id', parseInt(this.data.template_id, 10));
        if(idx >= 0) this.templateData = CB.DB.templates.getAt(idx).data;

        if(Ext.isEmpty(this.templateData.cfg)) {
            this.templateData.cfg = {};
        }

        this.templateStore = CB.DB['template' + this.data.template_id];
        if(!this.templateStore){
            Ext.Msg.alert(L.Error, 'No template store identified');
            this.doClose();
            return;
        }

        if(!this.helperTree) {
            this.helperTree = new CB.VerticalEditGridHelperTree();
        }
        this.helperTree.loadData(this.data.data, this.templateStore);

        var tabPanelFieldItems = [];
        var v;
        this.templateStore.each(function(r){
            if((r.get('cfg').showIn == 'top') && Ext.isDefined(this.topFieldSet)){
                v = this.data.data
                    ? this.data.data[r.get('name')]
                    : (Ext.isDefined(r.get('cfg').value)
                        ? r.get('cfg').value
                        : ''
                    );

                if (!v) {
                    v = {value: null};
                }
                if (!Ext.isDefined(v.value)) {
                    v = {value: v};
                }

                //if there is a date set for the date field, we are parsing it to a date value
                if ((r.get('type') == 'date') && Ext.isString(v.value) && !Ext.isEmpty(v.value)) {
                    v.value = Date.parseDate(v.value.substr(0,10), 'Y-m-d');
                }

                if ((r.get('type') == 'datetime') && Ext.isString(v.value) && !Ext.isEmpty(v.value)) {
                    v.value = Date.parseDate(v.value, (v.value.indexOf('T') >= 0) ? 'Y-m-dTH:i:s' : 'Y-m-d H:i:s' );
                }

                /* here we are adding fields to the top fieldSet */
                var pidValue = null;
                var disabled = false;
                if( Ext.isDefined(r.get('cfg').dependency) && !Ext.isEmpty(r.get('pid'))){
                    var pidRowIndex = this.templateStore.findExact('id', r.get('pid'));
                    var pidRow = this.templateStore.getAt(pidRowIndex);

                    pidValue = this.data.data
                        ? Ext.value(this.data.data[pidRow.get('name')], {})
                        : null;
                    if(pidValue['value']) {
                        pidValue = pidValue['value'];
                    }
                    disabled = Ext.isEmpty(pidValue);
                }
                ed = App.getTypeEditor(r.get('type'), {
                    ownerCt: this
                    ,fieldRecord: r
                    ,pidValue: pidValue
                    ,objectId: this.data.id
                    ,path: this.data.path
                });
                if(ed){
                    ed.fieldLabel = r.get('title');
                    ed.disabled = disabled;
                    if(!Ext.isEmpty(r.get('cfg').hint)) {
                        ed.fieldLabel = '<span title="'+r.get('cfg').hint+'">'+ed.fieldLabel+'</span>';
                    }
                    ed.name = 'f' + r.get('name');
                    //setting the automatic title of the object
                    if(ed.isXType(Ext.ux.TitleField)) {
                        ed.setValues(this.data.title, v.value);
                    } else {
                        ed.setValue(v.value);
                    }

                    this.topFieldSet.add(ed);
                    //ed.enableBubble('change');
                }
            }else if(r.get('cfg').showIn == 'tabsheet'){
                v = this.data.data
                    ? this.data.data[r.get('name')]
                    : (Ext.isDefined(r.get('cfg').value)
                        ? r.get('cfg').value
                        : {}
                    );
                if(!v) {
                    v = {value: null};
                } else if(!v.value) {
                    v = {value: v};
                }
                var cfg = {
                    border: false
                    ,hideBorders: true
                    ,title: r.get('title')
                    ,isTemplateField: true
                    ,name: 'f'+r.get('name')
                    ,value: v.value
                    ,listeners: {
                        scope: this
                        ,change: function(field, newValue, oldValue){ this.fireEvent('change', field.name, newValue, oldValue); }
                        ,sync: function(){ this.fireEvent('change'); }
                    }
                };
                switch( r.get('type') ){
                    case 'text': tabPanelFieldItems.push(new Ext.form.TextArea(cfg));
                        break;
                    case 'html': tabPanelFieldItems.push(new Ext.ux.HtmlEditor(cfg));
                        break;
                }
            }
        }
        ,this
        );
        /* end of adding top fields and fields editable in tabsheet */

        if(!this.loaded){
            this.loaded = true;
            this.getBubbleTarget().on('filesdeleted', this.onFilesDeleted, this);
            this.getBubbleTarget().on('fileuploaded', this.onFileUploaded, this);
            this.prepareInterface();
        // } else if(this.propertiesPanel && this.propertiesPanel.rendered) {
            // this.propertiesPanel.update(this.data);
        }

        this.grid.reload();

        if( (this.grid.store.getCount() > 0)
            && ( (this.tabPanel.items.getCount() === 0)
                || (this.tabPanel.items.first().items.first() != this.grid)
                )
        ) {
            this.tabPanel.insert(0, {
                title: L.Details
                //,autoScroll:true
                ,layout: 'fit'
                ,items: this.grid
                ,bodyStyle:'margin:0; padding: 0'
                ,listeners: {
                    scope: this
                    ,afterlayout: function(p){
                        w = p.getWidth();
                        this.grid.setWidth(w-9);
                        this.grid.getEl().setWidth(w);

                        o = this.grid.getEl().query('.x-panel-body');
                        var i;
                        for (i = 0; i < o.length; i++) Ext.get(o[i]).setWidth(w);
                        o = this.grid.getEl().query('.x-grid3');
                        for (i = 0; i < o.length; i++) Ext.get(o[i]).setWidth(w);
                        o = this.grid.getEl().query('.x-grid3-scroller');
                        for (i = 0; i < o.length; i++) Ext.get(o[i]).setWidth(w);
                    }
                }
            });//this.tabPanel.items.removeAt(0);
        }

        if(this.topFieldSet){
            if(this.topFieldSet.isRendered){
                this.topFieldSet.syncSize();
            }
        }

        this.doLayout();

        this.items.first().items.first().syncSize();

        this.objectPanel.load({
            id: this.data.id
            ,from: 'window'
        });
        //setting all form values, inclusive in the grid

        Ext.each(
            tabPanelFieldItems
            ,function(i){
                this.tabPanel.insert(tpInsertIndex++, i);
            }, this
        );

        lastActiveTabIndex = (lastActiveTabIndex > 0) ? lastActiveTabIndex : 0;
        p = this.tabPanel.items.itemAt(lastActiveTabIndex);

        if(!p || !p.isVisible()) {
            p = this.tabPanel.items.itemAt(lastActiveTabIndex+1);
            if(!p || !p.isVisible()) {
                p = this.tabPanel.items.itemAt(lastActiveTabIndex-1);
                if(p && p.isVisible()) lastActiveTabIndex--;
            }else {
                lastActiveTabIndex++;
            }
        }
        this.tabPanel.setActiveTab(lastActiveTabIndex);
        this.setDirty(false);
        this.onObjectChanged();
        this.focusFirstField();
        this.fireEvent('loaded', this);
    }

    ,focusFirstField: function(){
        App.focusFirstField(this);
    }

    ,onCreateObjectClick: function(b, e) {
        data = Ext.apply({}, {
            pid: this.data.id
            ,path: this.data.path+'/'+this.data.id
            ,pathtext: this.data.pathtext+'/'+ this.data.name
        }, b.data);
        App.mainViewPort.createObject(data, e);
    }

    ,onCreateTaskClick: function(o, e){
        this.fireEvent(
            'taskcreate'
            ,{
                data: {
                    template_id: App.config.default_task_template
                    ,pid: this.data.id
                    ,path: this.data.path+'/'+this.data.id
                    ,pathtext: this.data.pathtext + this.data.name
                }
            }
        );
    }

    ,getFormValues: function(){
        if(!Ext.isDefined(this.data.data)) {
            this.data.data = {};
        }
        this.grid.readValues(); // grid will reset the this.data.data array to only its values, so we read other values after it will do its data read

        /* reading values from top fieldSet */
        if(Ext.isDefined(this.topFieldSet))
            this.topFieldSet.items.each(function(i){
                fieldName = i.name.substr(1);
                this.data.data[fieldName] = i.getValue();
                if( (i.isXType(Ext.ux.TitleField)) && (!i.hasCustomValue)) {
                    this.data.data[fieldName] = '';
                }
            }, this);
        /* reading values from tabPanel */
        if(this.tabPanel) {
            this.tabPanel.items.each(
                function(i){
                    if(i.isTemplateField) {
                        this.data.data[i.name.substr(1)] = i.getValue();
                    }
                }
                ,this
            );
        }
    }

    ,getFileProperties: function(fileId){
        // return false or file properties if possible
        if((!this.filesGrid) || isNaN(fileId)) return false;
        fielId = parseInt(fileId, 10);
        fs = this.filesGrid.getStore();
        ri = fs.findBy( function(r){ return (r.get('id') == fileId); }, this);
        if(ri < 0) return false;
        return fs.getAt(ri).data;
    }

    ,onFileUploaded: function(data){
        if(data.object_id != this.data.id) return;
    }

    ,onFilesDeleted: function(fileIds){
        st = this.grid.getStore();
        if(st) st.each(function(r){
            if(fileIds.indexOf(r.get('files')) >=0 ){
                r.set('files', null);
                this.fireEvent('change');
            }
        }, this);
    }

    ,getIconClass: function(){
        if(Ext.isEmpty(this.data.template_id)) return;
        idx = CB.DB.templates.findExact('id', parseInt(this.data.template_id, 10));
        if(idx < 0) return;
        return CB.DB.templates.getAt(idx).get('iconCls');
    }

    ,onBeforeCloseObjectsPanel: function(p){
        p.hide();
        this.tabPanel.remove(p, false);
        return false;

    }
    ,onUploadClick: function(b, e) {
        this.fireEvent('fileupload', {pid: this.data.id, uploadType: 'single'}, e);
    }

    ,onPasteClick: function(b, e) {
        App.clipboard.paste(this.data.id, null, this.onPasteProcess, this);
    }

    ,onPasteProcess: function(pids){
        // this.childsPanel.reload();
        // this.filesPanel.reload();
        // this.tasksPanel.reload();
    }


    ,onObjectSaved: function(f, a){
        if(!Ext.isEmpty(this.interceptorArguments)){
            this.interceptorArguments[0].handler.call(
                this
                ,this.interceptorArguments[0]
                ,this.interceptorArguments[1]
            );
            delete this.interceptorArguments;
        }
        App.fireEvent('objectchanged', this);
        this.onObjectChanged();
    }

    ,onObjectChanged: function(){
        this.actions.save.setDisabled(!this._isDirty && !isNaN(this.data.id));
        this.actions['delete'].setDisabled(isNaN(this.data.id));
        this.actions.security.setDisabled(isNaN(this.data.id));
        this.actions.paste.setDisabled(App.clipboard.isEmpty());
    }

    ,onFocusContactField: function(editor){
        if( Ext.isDefined(editor.dependency) || Ext.isEmpty(editor.pid)) return;
        f = editor.name.split('_');
        editor.pidValue = this.getCurrentFieldValue(editor.pid, f[1]);
    }

    ,updateDependentFields: function(fn, newValue){
        pid = fn.split('_')[0].substr(1);
        if(Ext.isDefined(this.topFieldSet)){
            this.templateStore.each(function(r){
                if((r.get('cfg').showIn == 'top') && Ext.isDefined(r.get('cfg').dependency) && (r.get('pid') == pid) ){
                    c = this.topFieldSet.find('name', 'f'+r.get('id')+'_0');
                    if(!Ext.isEmpty(c)){
                        c = c[0];
                        c.setDisabled(Ext.isEmpty(newValue) || (!Ext.isEmpty(r.get('cfg').dependency.pidValues) && !setsHaveIntersection( r.get('cfg').dependency.pidValues, newValue) ) );
                        c.data.record = r;
                        c.data.pidValue = newValue;
                        if(c.updateStore) c.updateStore(c);
                        delete c.lastQuery;
                    }
                }
            }, this);
        }
    }

    ,onPathClick: function(){
        App.locateObject( this.data.id, this.data.path );
     }
});

Ext.reg('CBObjects', CB.Objects); // register xtype

// CB.ObjectsPropertiesPanel = Ext.extend(Ext.Panel, {
//     border: false
//     ,hideBorders: true
//     ,autoHeight: true
//     ,bodyStyle: 'background-color: #F4F4F4'
//     ,initComponent: function(){
//         Ext.apply(this, {
//             tpl: new Ext.XTemplate(
//                 '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Properties+'</h3>'
//                 ,'<table class="item-props">'
//                 ,'{[ Ext.isEmpty(values.id) ? "" : \'<tr><td class="k">ID</td><td>\'+values.id+\'</td></tr>\']}'
//                 ,'{[ Ext.isEmpty(values.name) ? "" : \'<tr><td class="k">'+L.Name+'</td><td>\'+values.name+\'</td></tr>\']}'
//                 ,'<tbody><tr><td class="k">'+L.Path+'</td><td><a class="path" href="#">{pathtext}</a></td></tr>'
//                 ,'{[ Ext.isEmpty(values.size) ? "" : \'<tr><td class="k">'+L.Size+'</td><td>\'+App.customRenderers.filesize(values.size)+\'</td></tr>\']}'
//                 ,'<tr><td class="k">'+L.Created+'</td><td>{[ CB.DB.usersStore.getName(values.cid) ]}<br><span class="dttm" title="Friday, December 14, 2012 at 11:26">{[ Ext.isEmpty(values.cdate) ? "" : values.cdate.format(App.dateFormat) ]}</span></td></tr>'
//                 ,'<tr><td class="k">'+L.Modified+'</td><td>{[ CB.DB.usersStore.getName(values.uid) ]}<br><span class="dttm" title="Friday, December 14, 2012 at 11:26">{[ Ext.isEmpty(values.udate) ? "" : values.udate.format(App.dateFormat) ]}</span></td></tr>'
//                 ,'</tbody></table>'
//                 ,{compiled: true}
//             )
//             ,data: []
//             ,listeners: {
//                 scope: this
//                 ,afterlayout: this.onAfterlayout
//                 ,afterrender: this.onAfterlayout
//             }
//         });
//         CB.ObjectsPropertiesPanel.superclass.initComponent.apply(this, arguments);
//         this.addEvents('pathclick');

//         this._update= this.update;
//         this.update = function(data){
//             this._update(data);
//             this.onAfterlayout();
//         };
//     }
//     ,onAfterlayout: function(){
//         p = this.getEl().query('a.path');
//         if(Ext.isEmpty(p)) return;
//         p = Ext.get(p[0]);
//         p.un('click', this.onPathClick, this);
//         p.on('click', this.onPathClick, this);

//     }
//     ,onPathClick: function(){
//         this.fireEvent('pathclick');
//     }
// });

CB.ActionChildsPanel = Ext.extend(Ext.Panel, {
    border: false
    ,hideBorders: true
    ,autoHeight: true
    ,bodyStyle: 'background-color: #F4F4F4'
    ,initComponent: function(){
        Ext.apply(this, {
            tpl: new Ext.XTemplate(
                '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Actions+'</h3>'
                ,'<ul class="action-list"><tpl for=".">'
                ,'<li><a href="#" nid="{nid}" class="dIB lh16 icon-padding {iconCls}">{name}</a></li>'
                ,'</tpl></ul>'
                ,{compiled: true}
            )
            ,data: []
            ,listeners: {
                scope: this
                ,afterlayout: this.attachListeners
                ,afterrender: this.attachListeners
                ,beforedestroy: function(){
                    App.mainViewPort.un('objectsdeleted', this.onObjectsChange, this);
                    App.un('objectchanged', this.onObjectsChange, this);
                }
            }
        });
        CB.ActionChildsPanel.superclass.initComponent.apply(this, arguments);
        this._update= this.update;
        this.update = function(data){
            this._update(data);
            this.attachListeners();
        };

        App.mainViewPort.on('objectsdeleted', this.onObjectsChange, this);
        App.on('objectchanged', this.onObjectsChange, this);
    }
    ,onObjectsChange: function(){
        this.reload();
    }
    ,attachListeners: function(){
        p = this.getEl().query('a');
        if(Ext.isEmpty(p)) return;
        for (var i = 0; i < p.length; i++) {
            el = Ext.get(p[i]);
            el.un('click', this.onItemClick, this);
            el.on('click', this.onItemClick, this);
        }
    }
    ,getCaseObjectId: function(){
        p = this.findParentByType(CB.Objects);
        if(Ext.isEmpty(p)) return;
        id = p.data.id;
        if(isNaN(id)) return;
        return id;
    }

    ,reload: function(){
        if(this.rendered) {
            this.update([]);
        } else {
            this.data = [];
        }

        id = this.getCaseObjectId();
        if(Ext.isEmpty(id)) return;
        params = {pid: id
            ,template_types: 'object'
            ,folders: false
            ,sort: 'udate'
            ,dir: 'desc'
        };
        p = this.findParentByType(CB.Objects);
        if(!Ext.isEmpty(p)
            && !Ext.isEmpty(p.data.cfg)
            && !Ext.isEmpty(p.data.cfg.templates)
        ) {
            params.templates = p.data.cfg.templates;
        }
        CB_BrowserView.getChildren(params, this.processLoad, this);
    }

    ,processLoad: function(r, e){
        /* add check for cases when objects window is closing but saved its changes.
            In this case, the delay that appears while this component load its remote data
            and tries to render them could result in a js error.
            This is because objects window gets destroyed before this component tries to render.
        */
        if(this.isDestroyed) {
            return;
        }
        if(r.success !== true) return;
        for (var i = 0; i < r.data.length; i++)
            r.data[i].iconCls = getItemIcon(r.data[i]);
        this.update(r.data);
        this.setVisible(r.data.length > 0);
    }

    ,onItemClick: function(ev, el){
        if(Ext.isEmpty(el) || Ext.isEmpty(el.attributes['nid']) || Ext.isEmpty(el.attributes['nid'].value)) return;
        App.mainViewPort.openObject({ id: el.attributes['nid'].value }, ev);
    }
});
