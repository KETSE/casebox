Ext.namespace('CB'); 
CB.Tasks_ReminderWindow = Ext.extend(Ext.Window, {
    modal: true
    ,autoHeight: true
    ,width: 500
    ,layout: 'fit'
    ,initComponent: function(){
        this.value = this.value ?  (Ext.isArray(this.value) ? this.value : this.value.split('|')) : [1, 10, 1];
        
        this.fsItems = [new Ext.form.NumberField({
            flex: 1
            ,value: this.value[1]
            ,allowBlank: false
        })
        ,new Ext.form.ComboBox({
            value: this.value[2]
            ,store: CB.DB.reminderUnits
            ,typeAhead: true
            ,triggerAction: 'all'
            ,lazyRender:true
            ,mode: 'local'
            ,valueField: 'id'
            ,displayField: 'name'
            ,editable: false
            ,flex: 2
        })
        ]
        
        Ext.apply(this, {
            items: {
                xtype: 'form'
                ,autoHeight: true
                ,border: false
                ,bodyStyle: 'margin: 0; padding: 0'
                ,monitorValid: true
                ,items: {
                    xtype: 'fieldset'
                    ,style: 'margin: 0'
                    ,border: false
                    ,padding: 0
                    ,items: {
                        xtype: 'compositefield'
                        ,defaults: { submitValue: false }
                        ,layout: 'hbox'
                        ,hideLabel: true
                        ,items: this.fsItems
                    }
                }
                ,buttons: [
                    {text: L.Add, handler: this.onSaveClick, scope: this, iconCls: 'icon-save', formBind: true, type: 'submit'}
                    ,{text: Ext.MessageBox.buttonText.cancel, handler: this.close, scope: this, iconCls: 'icon-cancel'}
                ]
            }
        })

        CB.Tasks_ReminderWindow.superclass.initComponent.apply(this, arguments);
    }
    ,onSaveClick: function(b, e){
        if(!this.callback) return;
        this.value = '1|' + this.fsItems[0].getValue() + '|' +this.fsItems[1].getValue();
        this.callback.createDelegate(this.scope, [this.reminderIndex, this.value])();
        this.close();
    }
});

CB.Tasks = Ext.extend( Ext.Window, {
    title: L.Task
    ,closable: true
    ,closeAction: 'destroy'
    ,iconCls: 'icon-calendar'
    ,modal: true
    ,plain: true
    ,border: false
    ,autoHeight: true
    ,width: 460
    ,boxMinWidth: 400
    ,buttonAlign: 'left'
    ,initComponent: function(){
        this.actions = {
            'delete': new Ext.Action({
                text: L.Delete
                ,handler: this.onDeleteClick
                ,scope: this
                ,hidden: true
                ,iconCls: 'icon-minus'
            })
            ,create: new Ext.Action({
                text: L.Create
                ,handler: this.onSaveClick
                ,scope: this
                ,hidden: true
                ,iconCls: 'icon-save'
                ,formBind: true
                ,type: 'submit'
                ,disabled: true
            })
            ,edit: new Ext.Action({
                text: L.Edit
                ,handler: this.onEditClick
                ,scope: this
                ,hidden: true
                ,iconCls: 'icon-pencil'
            })
            ,save: new Ext.Action({
                text: L.Save
                ,handler: this.onSaveClick
                ,scope: this
                ,hidden: true
                ,iconCls: 'icon-save'
                ,disabled: true
            })
            ,close: new Ext.Action({
                text: L.Complete
                ,handler: this.onCloseTaskClick
                ,scope: this
                ,hidden: true
                //,iconCls: 'icon-cancel'
            })
            ,reopen: new Ext.Action({
                text: L.Reopen
                ,handler: this.onReopenTaskClick
                ,scope: this
                ,hidden: true
            })
            ,complete: new Ext.Action({
                text: L.Complete
                ,handler: this.onCompleteTaskClick
                ,scope: this
                ,hidden: true
                //,iconCls: 'icon-cancel'
            })
            ,ok: new Ext.Action({
                text: Ext.MessageBox.buttonText.ok
                ,handler: this.onCloseClick
                ,scope: this
            })
            ,cancel: new Ext.Action({
                text: Ext.MessageBox.buttonText.cancel
                ,handler: this.onCloseClick
                ,scope: this
                ,hidden: true
                ,iconCls: 'icon-cancel'
            })
            ,moreDetails: new Ext.Action({
                text: L.MoreDetails
                ,handler: this.onMoreDetailsClick
                ,scope: this
                ,hidden: true
            })
        }

        this.taskCategoriesStore = [];
        if(!Ext.isEmpty(App.config.task_categories)) this.taskCategoriesStore = getThesauriStore(App.config.task_categories);
        
        this.pathStore = new Ext.data.ArrayStore({
            idIndex: 0
            ,fields: [{name: 'id', type: 'int'}, 'name']
            ,data:  []
        });

        this.remindersTpl = new Ext.XTemplate(
            '<ul>'
            ,'<tpl for=".">'
            ,'<li class="lh20" idx="{[xindex]}">{[this.getReminderHtml(values)]}</li>'
            ,'</tpl>'
            ,'<li class="lh20"><a class="click fwB">'+ L.Add+'</a></li></ul>'
            ,{compiled: true, getReminderHtml: this.getReminderHtml}
        );
        this.contentPanel = new Ext.Panel({ border: false, autoHeight: true });
        this.remindPanel = new Ext.Panel({
            title: L.Reminders
            ,collapsible: false
            ,forceLayout: true
            ,autoHeight: true
            ,border: false
            ,headerCfg: { cls: 'x-panel-header panel-header-nobg' }
            ,defaults: {anchor: '100%'}
            ,padding: 10
            ,buttonAlign: 'left'
            ,hidden: true
            ,name: 'remindsPanel'
            ,items: { xtype: 'dataview'
                ,itemSelector: 'li'
                ,overClass:'item-over'
                ,tpl: this.remindersTpl
                ,data: []
                ,listeners: {
                    scope: this
                    ,click: this.onReminderButtonClick
                }
            }
            ,updateTitle: function(){
                t = this.initialConfig.title;
                if(this.items.getCount() > 0) t = t + ' <span class="cG">[' + this.items.getCount() + ']</span>' + ((this.disabled && this.comment) ? ' - <span class="cB">' + this.comment + '</span>' : '');
                this.setTitle(t);
            }
            ,listeners:{ 
                afterlayout: function(p, l){ p.updateTitle(); } 
                ,disable: function(p){ p.updateTitle(); } 
                ,enable: function(p){ p.updateTitle(); } 
            }
        });
        
        Ext.apply(this, {
            items: this.contentPanel
            ,buttons: [
                this.actions.edit
                ,this.actions.close
                ,this.actions.reopen
                ,this.actions.complete
                ,'->'
                ,this.actions.create
                ,this.actions.ok
                ,this.actions.save
            ]
            ,listeners: {
                scope: this
                ,show: this.load
                ,afterlayout: App.focusFirstField
            }
        });
        if(!this.data) this.data = {};
        this.radioHidden = !Ext.isEmpty(this.data.template_id);
        Ext.applyIf(this.data, {
            privacy: 0
            ,importance: 1
            ,category_id: Ext.isEmpty(this.taskCategoriesStore) ? null : this.taskCategoriesStore.getAt(1).get('id')
            ,reminds: []//'1|10|1'
            ,admin: true
            ,autoclose: 1
            ,allday: 1
            ,has_deadline: !Ext.isEmpty(this.data.date_end)
        })
        CB.Tasks.superclass.initComponent.apply(this, arguments);
        this._dirty = false;
        this.setReminds();
    }
    ,updatePathStore: function(){
        pathIds = String(this.data.path);
        pathTexts = String(this.data.pathtext);
        while(pathIds.substr(-1) == '/') pathIds = pathIds.substr(0, pathIds.length -1)
        while(pathTexts.substr(-1) == '/') pathTexts = pathTexts.substr(0, pathTexts.length -1)
        pathIds = pathIds.split('/');
        pathTexts =  pathTexts.split('/');

        data = []
        for (var i = pathIds.length -1; i > 0; i--) {
            if(!Ext.isEmpty(pathIds[i]));
            t = pathTexts.join('/');
            if(Ext.isEmpty(t)) t = '/';
            data.push([pathIds[i], t]);
            pathTexts.pop()
        };
        this.pathStore.loadData(data);
    }
    ,load: function(){
        if(isNaN(this.data.id)){
            this.updatePathStore();
            this.data.create_in = (this.pathStore.getCount() > 0) ? this.pathStore.getAt(0).get('id') : null;
            return this.processLayout();
        }
        this.getEl().mask(L.LoadingData, 'x-mask-loading');
        CB_Tasks.load(this.data.id, this.onLoad, this);
    }
    ,onLoad: function(r, e){
        if(r.success == true) Ext.apply(this.data, r.data);
        this.getEl().unmask();
        this.status = 'view';
        
        this.data.allday = parseInt(this.data.allday)
        this.data.has_deadline = parseInt(this.data.has_deadline)
        this.data.date_start = date_ISO_to_date(this.data.date_start)
        this.data.date_end = date_ISO_to_date(this.data.date_end)
        this.data.cdate = date_ISO_to_date(this.data.cdate)
        this.data.completed = date_ISO_to_date(this.data.completed)
        this.radioHidden = !Ext.isEmpty(this.data.template_id);
        
        this.updatePathStore();

        /* set canEdit flag */
        this.canEdit = ( (this.data.status != 3) && this.data.admin);
        /* end of set canEdit flag */
        /* set responsibleUsers array */
        this.responsibleUsers = this.data.responsible_user_ids.split(',');
        /* end of set responsibleUsers array */
        /* get and set userNames for responsible users */
        this.userNames = [];
        this.userNames2 = {};
        CB.DB.usersStore.each(function(r){ 
            if(this.responsibleUsers.indexOf(r.get('id')+'') >=0){
                this.userNames.push(r.get('name')); 
                this.userNames2[r.get('id')] = r.get('name');
            }
        }, this)
        /* end of get and set userNames for responsible users */
        
        /* set canComplete flag */
        this.canClose = this.canEdit;
        this.canReopen = (this.data.status == 3) && (this.data.cid == App.loginData.id);
        this.canComplete = ((this.data.status != 3) && (this.responsibleUsers.indexOf(App.loginData.id+'') >=0) );
        if(this.canComplete)
            Ext.each(this.data.users, function(r){
                if(r.id == App.loginData.id){
                    this.canComplete = (r.status == 0);
                    return false;
                }
            }, this);
        /* end of set canComplete flag */
        
        /* get and store owner's name */
        this.ownerName = CB.DB.usersStore.getName(this.data.cid);
        /* end of get and store owner's name */
        /* get and store responsible party's text */
        if(!Ext.isEmpty(this.data.responsible_party_id)){
            st = getThesauriStore(App.config.responsible_party);
            idx = st.findExact('id', this.data.responsible_party_id)
            if(idx >=0) this.responsible_party = st.getAt(idx).get('name');
        }
        /* end of get and store responsible party's text */
        
        this.data.reminds = Ext.isEmpty(this.data.reminds) ? [] : this.data.reminds.split('-');
        
        this.setReminds();
        this.remindPanel.updateTitle();
        this.processLayout();
        this.syncSize();
        this.center();
        this.setDirty(false);
    }
    ,processLayout: function(){
        this.contentPanel.removeAll(true);
        Ext.each(this.actions, function(a){ if(a.setHidden) a.setHidden(true) }, this);
        items = [];
        if(isNaN(this.data.id) && Ext.isEmpty(this.data.date_start)) this.data.date_start = new Date();
        switch(this.status){
            case 'view': 
                this.taskview = new CB.TaskViewDataView({
                    listeners: {
                        scope: this
                        ,click: this.onPreviewElementClick
                    }
                });
                items.push(this.taskview);
                
                this.actions['delete'].setHidden( true || !this.data.admin );// Delete - if is responsible person, office manager or root
                this.actions.close.setHidden(!this.canClose);// close - hidden
                this.actions.reopen.setHidden(!this.canReopen);
                this.actions.complete.setHidden(this.canClose || !this.canComplete);// close - hidden
                this.actions.create.setHidden(!isNaN(this.data.id));// create - if no numeric id isset
                this.actions.edit.setHidden( !this.canEdit );// Edit - if is owner or admin
                this.actions.save.setHidden(true);// save - not visible, all actions from view mode are made remotely
                this.actions.ok.setHidden(false);// 
                this.actions.cancel.setHidden(true);// cancel - hidden
                this.actions.moreDetails.setHidden(false);
                break;
            default:
                
                this.date_start = new Ext.form.DateField({
                    name: 'date_start'
                    ,format: App.dateFormat
                    ,hidden: true
                    ,listeners: {scope: this, change: this.setDirty}
                })
                this.date_end = new Ext.form.DateField({
                    name: 'date_end'
                    ,format: App.dateFormat
                    ,hidden: true
                    ,disabled: true
                    ,listeners: {scope: this, change: this.setDirty}
                })
                this.datetime_start = new Ext.ux.form.DateTimeField({   
                    fieldLabel: L.Start
                        ,name: 'datetime_start'
                        ,timeFormat: App.timeFormat
                        ,dateFormat: App.dateFormat
                        ,picker: {
                            doneText : L.Done,
                            format: App.dateFormat + ' ' + App.timeFormat,
                            todayBtnText : L.Today,
                            timeConfig: {
                            tlp: ''
                            ,fieldLabel : L.Time + ' (' + App.timeFormat + ')'
                            ,strategyConfig : {
                                    format: App.timeFormat
                                    ,incrementValue : 1
                                    ,incrementConstant : Date.HOUR
                                    ,alternateIncrementValue : 5
                                    ,alternateIncrementConstant : Date.MINUTE
                            }
                        }
                    }
                    ,listeners: {scope: this, change: this.setDirty}
                });
                this.datetime_end = new Ext.ux.form.DateTimeField({ 
                    fieldLabel: L.Due
                        ,name: 'datetime_end'
                        ,disabled: true
                    ,allowBlank: true
                        ,timeFormat: App.timeFormat
                        ,dateFormat: App.dateFormat
                        ,picker: {
                            doneText : L.Done,
                            format: App.dateFormat + ' ' + App.timeFormat,
                            todayBtnText : L.Today,
                            timeConfig: {
                            tlp: ''
                            ,fieldLabel : L.Time + ' (' + App.timeFormat + ')'
                            ,strategyConfig : {
                                    format: App.timeFormat
                                    ,incrementValue : 1
                                    ,incrementConstant : Date.HOUR
                                    ,alternateIncrementValue : 5
                                    ,alternateIncrementConstant : Date.MINUTE
                            }
                        }
                    }
                    ,listeners: {scope: this, change: this.setDirty}
                });
                this.taskview = new CB.TaskRemindsDataView({
                    fieldLabel: L.Reminders
                    ,listeners: {
                        scope: this
                        ,click: this.onPreviewElementClick
                    }
                });
                this.filesview = new CB.TaskFilesPanel({ fieldLabel: L.Files });
                
                /* collect distinct task templates*/
                taskItems = []
                taskTemplates = CB.DB.templates.query('type', 'task');
                taskTemplates.each( function( ttr ){ 
                    taskItems.push( {boxLabel: ttr.get('title'), flex: 0, name: 'template_id', inputValue: ttr.get('id')} );
                }, this );
                /* end of collect distinct task templates*/
                fsItems = [];

                fsItems.push({
                        name: 'template_id'
                        ,xtype: 'radiogroup'
                        ,hidden: this.radioHidden
                        ,fieldLabel: ''
                        ,items: taskItems
                        ,listeners:{
                            scope: this
                            ,change: this.onTypeChangeClick
                        }
                    },{ xtype: 'textfield'
                        ,fieldLabel: L.Title
                        ,name: 'title'
                        ,listeners: {scope: this, change: this.setDirty}
                    },{     xtype: 'compositefield'
                        ,fieldLabel: L.Start_End
                        ,defaults: { flex: 1 }
                        ,items: [this.datetime_start, this.date_start
                            ,{xtype: 'displayfield', value: '&ndash;', flex: 0}
                            ,this.datetime_end,this.date_end
                        ]
                    },{ xtype: 'checkbox'
                        ,fieldLabel: L.AllDay
                        ,inputValue: 1
                        ,name: 'allday'
                        ,listeners: {scope: this, check: this.onAllDayClick}
                    },{ xtype: 'checkbox'
                        ,fieldLabel: L.Deadline
                        ,inputValue: 1
                        ,name: 'has_deadline'
                        ,listeners: {scope: this, check: this.onHasDeadlineClick}
                    },{xtype: 'displayfield', value: '&nbsp;'
                    },{ xtype: 'CBThesauriField'
                        ,iconCls: 'icon-users'
                        ,fieldLabel: L.TaskAssigned
                        ,store: this.usersStore
                        ,name: 'responsible_user_ids'
                        ,listeners: {scope: this, change: this.setDirty}
                    },{
                        xtype: 'combo'
                        ,forceSelection: true
                        ,store: this.pathStore
                        ,fieldLabel: L.Path
                        ,name: 'create_in'
                        ,hiddenName: 'create_in'
                        ,triggerAction: 'all'
                        ,lazyRender: true
                        ,mode: 'local'
                        ,displayField: 'name'
                        ,editable: false
                        ,valueField: 'id'
                        ,allowBlank: true
                        ,listeners: {scope: this, change: this.setDirty}
                    },{
                        fieldLabel: L.Importance
                        ,forceSelection: true
                        ,name: 'importance'
                        ,hiddenName: 'importance'
                        ,xtype: 'combo'
                        ,triggerAction: 'all'
                        ,lazyRender: true
                        ,mode: 'local'
                        ,displayField: 'name'
                        ,editable: false
                        ,valueField: 'id'
                        ,store: CB.DB.tasksImportance
                        ,listeners: {scope: this, change: this.setDirty}
                    },new Ext.form.ComboBox({
                        fieldLabel: L.Category
                        ,name: 'category_id'
                        ,hiddenName: 'category_id'
                        ,triggerAction: 'all'
                        ,lazyRender: true
                        ,mode: 'local'
                        ,store: this.taskCategoriesStore
                        ,displayField: 'name'
                        ,editable: false
                        ,valueField: 'id'
                        ,iconClsField: 'iconCls'
                        ,allowBlank: true 
                        ,plugins: [new Ext.ux.plugins.IconCombo()]
                    }),{    xtype: 'textarea'
                        ,fieldLabel: L.Description
                        ,name: 'description'
                        ,height: 70
                        ,enableKeyEvents: true
                        ,listeners: { scope: this, keydown: function(field, e){ if (e.getKey() == e.ENTER) e.stopPropagation(); }, change: this.setDirty }
                        ,allowBlank: true
                        ,style: 'margin-bottom: 20px; resize: vertical; min-height: 50px'
                    },this.filesview
                    ,this.taskview
                    );
                items.push({
                    xtype: 'form'
                    ,autoHeight: true
                    ,border: false
                    ,bodyCssClass: 'task-form'
                    ,api: {submit: CB_Tasks.save}
                    ,fileUpload: true
                    ,labelWidth: 115
                    ,monitorValid: true
                    ,items: [{
                        xtype: 'fieldset'
                        ,autoHeight: true
                        ,bodyStyle: 'padding: 5px 5px 0px 5px; margin: 0'
                        ,style: 'margin: 10px;padding: 0'
                        ,border: false
                        ,defaults: {allowBlank: false, anchor: '100%'}
                        ,items: fsItems
                    }
                    ]
                    ,listeners: {
                        scope: this
                        ,clientvalidation: function(fp, valid){
                            this.actions.save.setDisabled(!valid);
                            this.actions.cancel.setDisabled(!valid || !this._dirty);
                        }
                    }
                });
                this.actions['delete'].setHidden( !this.data.admin || isNaN(this.data.id));// Delete - if is responsible person, office manager or root
                this.actions.create.setHidden(!isNaN(this.data.id));// create - if no numeric id isset
                this.actions.edit.setHidden(true);// Edit - no edit button in edit mode
                this.actions.save.setHidden( !this.data.admin || isNaN(this.data.id) );// save - if is existent task
                this.actions.moreDetails.setHidden(false);
                this.actions.ok.setHidden( true );// save - if is existent task
                
                this.actions.close.setHidden( true );// close - hidden
                this.actions.reopen.setHidden( true );// reopen - hidden
                this.actions.cancel.setHidden(true);// cancel - visible
                App.focusFirstField();
                break;
        }
        this.updateTitle()
        this.contentPanel.add(items);
        this.contentPanel.doLayout();
        this.form = this.findByType('form')[0];
        this.setValues();
        if(this.rendered) this.syncSize();
        this.center();
    }
    ,updateTitle: function(){
        templateTitle = CB.DB.templates.getName(this.data.template_id);
        switch(this.status){
            case 'view': this.setTitle( L.View + ' ' + templateTitle ); break;
            default: this.setTitle( (isNaN(this.data.id) ? L.New : L.Edit) + ' ' + templateTitle ); break;
        }
        this.setIconClass( CB.DB.templates.getIcon(this.data.template_id) );
    }
    ,getValues: function(){
        if(!this.form) return;
        switch(this.status){
            default: delete this.data.privacy;
                delete this.data.autoclose;
                delete this.data.allday;
                delete this.data.has_deadline;
                
                Ext.apply(this.data, this.form.getForm().getValues());
                this.data.responsible_user_ids = null;
                f = this.form.find('name', 'responsible_user_ids')[0];
                if(f){
                    v = f.getValue();
                    if(v) v = v.join(',')
                    this.data.responsible_user_ids = v;
                }
                this.data.parent_ids = null;
                f = this.form.find('name', 'parent_ids')[0];
                if(f){
                    v = f.getValue();
                    if(v) v = v.join(',')
                    this.data.parent_ids = v;
                }
                if(this.data.allday){
                    d = this.date_start.getValue();
                    this.data.date_start = Ext.isEmpty(d) ? null : d.toISOString();
                    if(this.data.has_deadline || (this.data.template_id != App.config.default_event_template) ){
                        d = this.date_end.getValue();
                        this.data.date_end = Ext.isEmpty(d) ? null : d.toISOString();
                    }
                }else{
                    d = this.datetime_start.getValue();
                    this.data.date_start = Ext.isEmpty(d) ? null : d.toISOString();
                    if(this.data.has_deadline || (this.data.template_id != App.config.default_event_template) ){
                        d = this.datetime_end.getValue();
                        this.data.date_end = Ext.isEmpty(d) ? null : d.toISOString();
                    }
                }
                break;
        }
    }
    ,setValues: function(){
        if(this.form){
            this.form.getForm().setValues(this.data);
        }
        if(!Ext.isEmpty(this.data.files))
        for (var i = 0; i < this.data.files.length; i++) this.data.files[i].iconCls = getFileIcon(this.data.files[i].name);

        this.data.reminds_view = [];
        for (var i = 0; i < this.data.reminds.length; i++) {
            r = this.data.reminds[i].split('|');
            this.data.reminds_view.push( [r[1], CB.DB.reminderUnits.getName(r[2])] );
        };
        switch(this.status){
            case 'setstatus': break;
            case 'view': 
                dd = this.data;
                dd.creator_name = CB.DB.usersStore.getName(dd.cid);
                
                if(Ext.isEmpty(dd.users)) dd.users = []
                for (var i = 0; i < dd.users.length; i++) {
                    dd.users[i].name = CB.DB.usersStore.getName(dd.users[i].id)
                    dd.users[i].canEdit = this.canEdit
                };
                /* end of preparing data for view template */
                this.taskview.update(dd);
                break;
            default: //case 'edit': 
                f = this.form.find('name', 'responsible_user_ids')[0];
                if(f) f.setValue(this.data.responsible_user_ids);
                f = this.form.find('name', 'parent_ids')[0];
                if(f) f.setValue(this.data.parent_ids);

                this.date_start.setValue(this.data.date_start);
                this.datetime_start.setValue(this.data.date_start);
                this.date_end.setValue(Ext.isEmpty(this.data.date_end) ? null : this.data.date_end )
                this.datetime_end.setValue(Ext.isEmpty(this.data.date_end) ? null : this.data.date_end )
                if(this.data.allday){
                    this.datetime_start.setVisible(false);
                    this.date_start.setVisible(true);
                    this.datetime_end.setVisible(false);
                    this.date_end.setVisible(true);
                }else{
                    
                    this.datetime_start.setVisible(true);
                    this.date_start.setVisible(false);
                    this.datetime_end.setVisible(true);
                    this.date_end.setVisible(false);
                }
                this.datetime_end.setDisabled(!this.data.has_deadline);
                this.date_end.setDisabled(!this.data.has_deadline);
                this.findByType('compositefield')[0].syncSize();
                if(this.taskview.rendered){
                    this.taskview.update(this.data);
                    this.syncSize();
                }else{ 
                    this.taskview.data = this.data;
                }
                break;
        }
        this.setDirty(false);
    }
    ,onPreviewElementClick: function(dv, idx, el, e){
        a = el.attributes.getNamedItem('name');
        if(Ext.isEmpty(a)) return;
        switch(el.attributes.getNamedItem('name').value){
            case 'complete': 
                this.onSetUserCompletedClick(el.attributes.getNamedItem('uid').value);
                break;
            case 'revoke': 
                this.onSetUserIncompleteClick(el.attributes.getNamedItem('uid').value);
                break;
            case 'rem_add': 
                this.onAddReminderClick()
                break;
            case 'rem_edit': 
                this.onEditReminderClick(el.attributes.getNamedItem('rid').value -1)
                break;
            case 'rem_del': 
                this.onDeleteReminderClick(el.attributes.getNamedItem('rid').value -1)
                break;
            case 'file': 
                App.mainViewPort.fireEvent('fileopen', {id: el.attributes.getNamedItem('fid').value}, e);
                this.destroy();
                break;
            case 'path': 
                App.locateObject(this.data.id, this.data.path);
                this.destroy();
                break;
        }
    }
    ,setReminds: function(){
        this.data.reminds_view = [];
        for (var i = 0; i < this.data.reminds.length; i++) {
            r = this.data.reminds[i].split('|');
            this.data.reminds_view.push( [r[1], CB.DB.reminderUnits.getName(r[2])] );
        };

        if(this.rendered && this.taskview ) this.taskview.update(this.data);
        if(this.rendered) this.syncSize();
    }
    ,onSaveClick: function(b, e){
        if(this.form && !this.form.getForm().isValid()) return ;
        this.getEl().mask(L.SavingChanges, 'x-mask-loading');
        this.getValues();
        if(Ext.isArray(this.data.reminds)) this.data.reminds = this.data.reminds.join('-');
        this.form.getForm().submit({
            clientValidation: true
            ,params:  this.data
            ,scope: this
            ,success: this.processSave
            ,failure: App.formSubmitFailure         
        }
        );
    }
    ,processSave: function(form, action){ 
        r = action.result;
        this.getEl().unmask()
        if(r.success !== true) return;
        this.status = isNaN(this.data.id) ? 'created' : 'updated';
        Ext.apply(this.data, r.data);
        App.mainViewPort.fireEvent('task'+ this.status, r);
        this.destroy(); 
    } 
    ,onEditClick: function(b, e){
        this.getValues();
        this.status = 'edit';
        this.processLayout();
    }
    ,onAddReminderClick: function(b, e){
        w = new CB.Tasks_ReminderWindow({title: L.NewReminder, callback: this.processSaveReminder, scope: this});
        w.show();
    }
    ,onReminderButtonClick: function(b, idx, oel, e){
        idxProperty = oel.attributes.getNamedItem('idx');
        reminderIndex = idxProperty ? idxProperty.value - 1 : -1;
        el = Ext.get(e.getTarget());
        if(el.dom.classList.contains('icon-close-light')) this.onDeleteReminderClick(reminderIndex);
        else if(reminderIndex > -1) this.onEditReminderClick(reminderIndex);
        else if(el.dom.classList.contains('click')) this.onAddReminderClick();
    }
    ,processSaveReminder: function(reminderIndex, newValue){
        if(isNaN(reminderIndex)) this.data.reminds.push(newValue); else this.data.reminds[reminderIndex] = newValue;
        if(this.status == 'view') this.saveReminds(); else{
            this.setDirty(true);
            this.setReminds();
        }
    }
    ,onEditReminderClick: function(reminderIndex){
        w = new CB.Tasks_ReminderWindow({title: L.EditReminder, reminderIndex: reminderIndex, value: this.data.reminds[reminderIndex], callback: this.processSaveReminder, scope: this});
        w.show();
    }
    ,onDeleteReminderClick: function(reminderIndex){
        if(isNaN(reminderIndex)) return;
        this.data.reminds.splice(reminderIndex, 1);
        if(this.status == 'view') this.saveReminds(); else{
            this.setDirty(true);
            this.setReminds();
        }
    }
    ,saveReminds: function(){
        CB_Tasks.saveReminds({id: this.data.id, object_id: this.data.object_id, title: this.data.title, date_end: this.data.date_end, reminds: this.data.reminds.join('-')}
            ,function(r, e){
                if(r.success !== true) return;
                this.data.reminds = r.reminds.split('-');
                this.setReminds();
            }
            ,this
        )
    }
    ,onResponsibleUserClick: function(b, idx, oel, e){
        user_id = oel.attributes.getNamedItem('user_id');
        if(!user_id) return
        user_id =  user_id.value;
        el = Ext.get(e.getTarget());
        if(el.dom.classList.contains('icon-tick-small')) this.onSetUserCompletedClick(user_id);
        else if(el.dom.classList.contains('icon-tick-small-red')) this.onSetUserIncompleteClick(user_id);
        else if(el.dom.classList.contains('click')) this.onSendMessageClick(user_id);/**/
    }
    ,onSetUserCompletedClick: function(user_id){
        Ext.Msg.show({
            title: L.SetCompleteStatusFor+ ' ' + CB.DB.usersStore.getName(user_id)
            ,msg: L.Message
            ,width: 400
            ,height: 200
            ,buttons: Ext.MessageBox.OKCANCEL
            ,multiline: true
            ,fn: function(b, message){ if(b == 'ok') CB_Tasks.setUserStatus({id: this.data.id, user_id: user_id, status: 1, message: message}, this.processSettingUserStatus, this)}
            ,scope: this
        });
    }
    ,onSetUserIncompleteClick: function(user_id){
        Ext.Msg.show({
            title: L.SetIncompleteStatusFor + CB.DB.usersStore.getName(user_id)
            ,msg: L.Message
            ,width: 400
            ,height: 200
            ,buttons: Ext.MessageBox.OKCANCEL
            ,multiline: true
            ,fn: function(b, message){ if(b == 'ok') CB_Tasks.setUserStatus({id: this.data.id, user_id: user_id, status: 0, message: message}, this.processSettingUserStatus, this)}
            ,scope: this
        });
    }
    ,processSettingUserStatus: function(r, e){
        App.mainViewPort.fireEvent('taskupdated', this, e);
        this.load();
    }
    ,onSendMessageClick: function(user_id){
        if(user_id == App.loginData.id) return;
        Ext.Msg.show({
            title: L.SendMessageTo + CB.DB.usersStore.getName(user_id)
            ,msg: L.Message
            ,width: 400
            ,height: 200
            ,buttons: Ext.MessageBox.OKCANCEL
            ,multiline: true
            ,fn: function(b, message){ if(b == 'ok') Msg.send({task_id: this.data.id, user_id: user_id, message: message}, this.processMessageSend, this)}
            ,scope: this
        });
    }
    ,onDeleteClick: function(b, e){
        Ext.Msg.confirm(L.RemovingTask, L.RemovingTaskMessage, function(b){
            if(b == 'yes'){ 
                this.getEl().mask('Удаление ...', 'x-mask-loading');
                CB_Browser['delete'](this.data.id, this.processDelete, this)
            }
        }, this)
    }
    ,processDelete: function(r, e){
        if(r.success !== true)  return Ext.Msg.alert(L.RemovingTask, L.RemovingTaskErrorMessage);
        this.status = 'deleted'; 
        App.mainViewPort.fireEvent('tasksdeleted', r.ids, e);
        this.close();
    }
    ,onSetStatusClick: function(b, e){
        this.status = 'setstatus';
        this.processLayout();
    }
    ,onCloseTaskClick: function(o, e){
        Ext.Msg.show({
            title: L.CompletingTask
            ,msg: L.Message
            ,width: 400
            ,height: 200
            ,buttons: Ext.MessageBox.OKCANCEL
            ,multiline: true
            ,fn: function(b, message){ 
                if(b == 'ok'){
                    this.getEl().mask(L.CompletingTask + ' ...', 'x-mask-loading');
                    CB_Tasks.close(this.data.id, this.doCloseTask, this)
                }
            }
            ,scope: this
        });
    }
    ,doCloseTask: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) {
            Ext.Msg.alert( L.ClosingTask, Ext.value(r.msg, L.ErrorOccured));
        }else{
            this.status = 'closed';
            App.mainViewPort.fireEvent('taskupdated', this, e);
            this.close();
        }
    }
    ,onReopenTaskClick: function(o, e){
        Ext.Msg.confirm( L.ReopeningTask, L.ReopenTaskConfirmationMsg, function(b){ 
                if(b == 'yes'){
                    this.getEl().mask(L.ReopeningTask + ' ...', 'x-mask-loading');
                    CB_Tasks.reopen(this.data.id, this.processTaskCompleting, this)
                }
            }
            ,this
        );
    }
    ,onCloseClick: function(b, e){
        if(this.lowerLevelUsers) this.lowerLevelUsers.destroy();
        this.destroy();
    }
    ,onCompleteTaskClick: function(){
        Ext.Msg.show({
            title: L.CompletingTask
            ,msg: L.Message
            ,width: 400
            ,height: 200
            ,buttons: Ext.MessageBox.OKCANCEL
            ,multiline: true
            ,fn: function(b, message){ if(b == 'ok') CB_Tasks.complete({id: this.data.id, message: message}, this.processTaskCompleting, this)}
            ,scope: this
        });
    }
    ,processTaskCompleting: function(r, e){
        App.mainViewPort.fireEvent('taskupdated', this, e);
        this.load();
    }
    ,getReminderHtml: function(valuesArray){
        if(Ext.isEmpty(valuesArray)) return '';
        if(!Ext.isArray(valuesArray)) valuesArray = valuesArray.split('|');
        html = '';
        idx = CB.DB.reminderTypes.findExact('id', parseInt(valuesArray[0]));
        if(idx >=0){
            r = CB.DB.reminderTypes.getAt(idx);
            html +='<img src="css/i/s.gif" class="icon '+r.get('iconCls')+'" title="'+r.get('name')+'"/> <a class="click">';
        }
        if(!Ext.isEmpty(valuesArray[1])){
            html += valuesArray[1];
            idx = CB.DB.reminderUnits.findExact('id', parseInt(valuesArray[2]));
            if(idx >=0){
                r = CB.DB.reminderUnits.getAt(idx);
                html +=' '+r.get('name');
            }
        }
        html += '</a> <span class="buttons"> ' +
            '<a href="#" class="lh20 icon-close-light" style="display:inline-block; width: 20px;text-decoration: none;background-repeat: no-repeat !important" title="'+L.Delete+'">&nbsp; &nbsp;</a></span>';
        return html;
    }
    ,getReminderLayout: function(valuesArray, edit){
        if(!Ext.isArray(valuesArray)) valuesArray = [1, 10, 1];
        if(edit == true)
            return ;
        else{
            text = '';
            idx = CB.DB.reminderTypes.findExact('id', parseInt(valuesArray[0]));
            if(idx >=0){
                r = CB.DB.reminderTypes.getAt(idx);
                text +='<img src="css/i/s.gif" class="icon '+r.get('iconCls')+'" title="'+r.get('name')+'"/> ';
            }
            if(!Ext.isEmpty(valuesArray[1])){
                text += valuesArray[1];
                idx = CB.DB.reminderUnits.findExact('id', parseInt(valuesArray[2]));
                if(idx >=0){
                    r = CB.DB.reminderUnits.getAt(idx);
                    text +=' '+r.get('name');
                }
            }
            
            return {
                xtype: 'compositefield'
                ,defaults: { submitValue: false }
                ,style: 'margin: 4px 0 2px 0'
                ,overCls: 'item-over'
                ,value: valuesArray.join('|')
                ,items: [{  xtype: 'displayfield'
                        ,value: text
                    },{ xtype: 'dataview'
                        ,width: 40
                        ,cls: 'buttons'
                        ,data: []
                        ,tpl: ['<a href="#" class="lh20 icon-pencil" style="display:inline-block; width: 20px;text-decoration: none;background-repeat: no-repeat !important" title="'+L.Edit+'">&nbsp; &nbsp;</a>',
                            '<a href="#" class="lh20 icon-close-light" style="display:inline-block; width: 20px;text-decoration: none;background-repeat: no-repeat !important" title="'+L.Delete+'">&nbsp; &nbsp;</a>']
                        ,itemSelector: 'a'
                        ,listeners: {
                            scope: this
                            ,click: this.onReminderButtonClick
                        }
                    }
                ]
            };
        }
    
    }
    ,addReminder: function(valuesArray, edit){
        if(this.remindPanel.items && this.remindPanel.items.getCount() > 4) return;
        this.remindPanel.add(this.getReminderLayout(valuesArray, edit));
        if(this.remindPanel.items.getCount() > 4) this.remindPanel.buttons[0].hide();
        this.remindPanel.doLayout();
        this.setDirty();
    }
    ,onTypeChangeClick: function(gr, radio){
        this.data.template_id = radio.inputValue
        f = this.find('name', 'has_deadline')[0];
        if(f){
            f.setVisible(radio.inputValue != App.config.default_event_template);
            this.onHasDeadlineClick(f, f.checked);
        }
        f = this.find('name', 'allday')[0];
        if(f) this.onAllDayClick(f, f.checked);
        f = this.find('name', 'responsible_user_ids')[0];
        if(f) f.setVisible(radio.inputValue != App.config.default_event_template);

        this.syncSize();

    }
    ,onAllDayClick: function(cb, checked){
        if(checked){
            this.datetime_start.setVisible(false); 
            this.date_start.setVisible(true); 
            
            this.datetime_end.setVisible(false); 
            this.date_end.setVisible(true); 
        }else{
            this.date_start.setVisible(false); 
            this.datetime_start.setVisible(true); 
            
            this.date_end.setVisible(false); 
            this.datetime_end.setVisible(true); 
        }
        this.findByType('compositefield')[0].doLayout();
        this.setDirty(true);
    }
    ,onHasDeadlineClick: function(cb, checked){
        this.date_end.setDisabled(!checked && (this.data.template_id != App.config.default_event_template ) ); 
        this.datetime_end.setDisabled(!checked && (this.data.template_id != App.config.default_event_template )); 
        this.setDirty(true);
    }
    ,setDirty: function(dirty){
        this._dirty = (dirty !== false);
        this.actions.create.setDisabled(!this._dirty); //enable save only when changes made
        this.actions.save.setDisabled(!this._dirty); //enable save only when changes made
    }
})

Ext.reg('CBTasks', CB.Tasks);

CB.TaskViewDataView = Ext.extend (Ext.DataView, {
    data: []
    ,emptyText: 'No data to display'
    ,initComponent: function(){
        Ext.apply(this, {
            tpl: CB.TaskViewTemplate
            ,itemSelector: 'a'
        })
        CB.TaskViewDataView.superclass.initComponent.apply(this, arguments)
    }
})
Ext.reg('TaskViewDataView', CB.TaskViewDataView);

CB.TaskFilesPanel = Ext.extend(Ext.Panel, {
    border: false
    ,autoHeight: true
    ,fieldConfig: {
        xtype: 'textfield'
        ,inputType: 'file'
        ,hiddenName : 'files'
        ,style: 'margin-bottom: 5px'
    }
    ,initComponent: function(){
        Ext.apply(this, {
            items: [
                {
                    xtype: 'button'
                    ,cls: 'pt5 pb10'
                    ,html: '<a class="click nlhl fs12 pt10" name="file_add">' + L.AddFile + '</a>'
                    ,scope: this
                    ,handler: this.onFileAddClick
                }       
            ]
            ,listeners:{
                scope: this
            }
        });
        CB.TaskFilesPanel.superclass.initComponent.apply(this, arguments);
    }
    ,onFileAddClick: function(){
        this.insert(this.items.getCount() -1, this.fieldConfig );
        this.newFieldAdded = true;
        this.items.last().setVisible(this.items.getCount() < 8);
        this.syncSize();
    }
});

CB.TaskRemindsDataView = Ext.extend (Ext.DataView, {
    data: { reminds_view: [] }
    ,emptyText: 'No data to display'
    ,initComponent: function(){
        Ext.apply(this, {
            tpl: CB.TaskRemindsViewTemplate
            ,itemSelector: 'a'
        })
        CB.TaskRemindsDataView.superclass.initComponent.apply(this)
    }
})

CB.TaskViewTemplate = new Ext.XTemplate(
    '<tpl for=".">'
    ,'<div class="taskview">'
    ,'<h2{[ (values.status == 3) ? " class=\'completed\'" : \'\']}>{[ Ext.util.Format.nl2br(Ext.util.Format.htmlEncode(values.title))]}</h2>'
    ,'<div class="datetime">{[ values.date_start.format(App.longDateFormat + ( (values.allday==1) ? "" : " "+ App.timeFormat) )]}'
    ,'{[Ext.isEmpty(values.date_end) ? "": " – " + values.date_end.format(App.longDateFormat + ( (values.allday==1) ? "" : " "+ App.timeFormat) )]}</div>'

    ,'<div class="info">{[ Ext.util.Format.nl2br(Ext.util.Format.htmlEncode(values.description) ) ]}</div>'

    ,'<table class="props">'
    ,'<tr><td class="k">'+ L.Status + ':</td><td>'
    ,'<span class="status{status}">'
    ,'{[ L["taskStatus"+values.status] ]}</span>{[ ( (values.status == 3) && !Ext.isEmpty(values.completed)) ? \' <span class="dttm" title="\'+values.completed.format(\'Y, F j\') + \' at \' + values.completed.format(\'H:i\')+\'">\'+values.completed.format(\'Y, F j\') + \' at \' + values.completed.format(\'H:i\')+\'</span>\' : ""]}'
    ,'<!-- <span class="overdue">overdue</span>, active, pending -->'
    ,'</td></tr>'
    ,'<tr><td class="k">' + L.Importance+':</td><td>{[CB.DB.tasksImportance.getName(values.importance)]}</td></tr>'
    ,'<tr><td class="k">' + L.Category + ':</td><td><img src="'+Ext.BLANK_IMAGE_URL+'" class="icon {[CB.DB.thesauri.getIcon(values.category_id)]}"> {[CB.DB.thesauri.getName(values.category_id)]}</td></tr>'
    ,'<tr><td class="k">' + L.Path + ':</td><td><a class="path" name="path" href="#">{pathtext}</a></td></tr>'
    ,'<tr><td class="k">' + L.Owner + ':</td><td>'

    ,'<table class="people">'
    ,'<tr><td class="user"><img class="photo32" src="photo/{cid}.jpg"></td><td><b>{creator_name}</b>'
    ,'<p class="gr">' + L.Created + ': '
    ,'<span class="dttm" title="{[values.cdate]}">{[values.cdate.format("F, d")]}</span></p>'
    ,'</td></tr>'
    ,'</tbody></table>'

    ,'</td></tr>'
    ,'<tpl if="!Ext.isEmpty(values.users)">'
        ,'<tr><td class="k">' + L.TaskAssigned + ':</td><td>'

        ,'<table class="people">'
        ,'<tbody>'
        ,'<tpl for="users">'
            ,'<tr><td class="user">'
            ,'<div style="position: relative">'
            ,'<img class="photo32" src="photo/{id}.jpg" alt="{name}" title="{name}">'
            ,'{[ (values.status == 1) ? \'<img class="done icon icon-tick-circle" src="'+Ext.BLANK_IMAGE_URL+'"/>\': ""]}'
            ,'</div>'
            ,'</td><td><b>{name}</b>'
            ,'<p class="gr">'
            ,'{[ (values.status == 1) ? "' + L.Completed + ': <span class=\'dttm\' title=\'"+values.time+"\'>"+values.time+"</span>" + '
            ,' ( (values.canEdit == 1) ? "<a class=\'bt\' name=\'revoke\' uid=\'"+values.id+"\' href=\'#\'>' + L.revoke + '</a>" : "" )'
            ,' :  "'+L.waitingForAction+' " + '
            ,' ( (values.canEdit == 1) ? "<a class=\'bt\' name=\'complete\' uid=\'"+values.id+"\' href=\'#\'>'+L.complete+'</a>" : "" ) '
            ,']}'
            ,'</p></td></tr>'
        ,'</tpl>'
        ,'</tbody></table>'

        ,'</td></tr>'
    ,'</tpl>'
    
    ,'<tpl if="!Ext.isEmpty(values.files)">'
        ,'<tr><td class="k">' + L.Files + ':</td><td>'
        ,'<ul class="task_files">'
        ,'<tpl for="files">'
            ,'<li><a href="#" name="file" fid="{id}" class="dib lh16 icon-padding file-unknown {iconCls}">{name}</a></li>'
        ,'</tpl>'
        ,'</ul></td></tr>'
    ,'</tpl>'

    ,'<tr><td class="k">' + L.Reminders + ':</td><td>'
    ,'<ul class="reminders">'
    ,'<tpl for="reminds_view">'
    ,'<li><a name="rem_edit" rid="{[xindex]}" href="#">{0} {1}</a> <span class="icon icon-close"><a name="rem_del" rid="{[xindex]}">&nbsp; &nbsp;</a></span></li>'
    ,'</tpl>'
    ,'</ul>'
    ,'<a class="click nlhl" name="rem_add">' + L.AddReminder + '</a>'
    ,'</td>'

    ,'</tr>'
    ,'</table>'

    ,'</div>'
    ,'</tpl>'
    ,{compiled: true}
) 
CB.TaskRemindsViewTemplate = new Ext.XTemplate(
    '<ul class="reminders">'
    ,'<tpl for="reminds_view">'
    ,'<li><a name="rem_edit" rid="{[xindex]}" href="#">{0} {1}</a> <span class="icon icon-close"><a name="rem_del" rid="{[xindex]}">&nbsp; &nbsp;</a></span></li>'
    ,'</tpl>'
    ,'</ul>'
    ,'<a class="click nlhl" name="rem_add">' + L.AddReminder + '</a>'
    ,{compiled: true}
)

CB.ActionTasksView = Ext.extend(Ext.DataView, {
    style: 'background-color: #F4F4F4'
    ,initComponent: function(){

        Ext.apply(this, {
            tpl: new Ext.XTemplate(
                '<table class="versions">'
                ,'<tbody>'
                ,'<tpl for=".">'
                    ,'<tr class="item">'
                        ,'<td class="user"><img class="photo50" src="/photo/{cid}.jpg"></td>'
                        ,'<td>{name} <br /> <span class="dttm" title="{ago_date}">{ago_text} {[ (values.status != 2) ? App.customRenderers.taskStatus(values.status) : "" ]}</span>'
                        ,'</td>'
                    ,'</tr>'
                ,'</tpl>'
                ,'</tbody>'
                ,'</table>'
                ,{compiled: true}
            )
            ,store: new Ext.data.JsonStore({
                root: ''
                ,fields: [ 
                    {name:'nid', type: 'int'}
                    ,{name:'date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,'name'
                    , {name: 'system', type: 'int'}
                    , {name: 'type', type: 'int'}
                    , {name: 'template_id', type: 'int'}
                    , {name: 'status', type: 'int'}
                    ,{name:'cid', type: 'int'}
                    ,{name:'uid', type: 'int'}
                    ,{name:'cdate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,{name:'udate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,'ago_date'
                    ,'ago_text'
                    ,'username'
                ]
                ,listeners:{
                    scope: this
                    ,load: function(store, records, options){
                        Ext.each(records, function(r){
                            r.set('ago_text', r.get('cdate').format(App.dateFormat)  )
                            r.set('username', CB.DB.usersStore.getName(r.get('cid')))
                        }, this)
                    }
                }
                ,data: []
            })
            ,itemSelector: '.item'
            ,overClass:'item-over'
            ,singleSelect: true
            ,selectedClass: 'sel'
            ,listeners: { 
                scope: this
                ,click: this.onItemClick
                ,beforedestroy: function(){
                    App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this)
                }
            }

        })
        CB.ActionTasksView.superclass.initComponent.apply(this, arguments);
        this.addEvents('taskedit')//, 'showactivetasks', 'showowntasks'
        this.enableBubble(['taskedit'])
        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this)
    }
    ,onItemClick: function(el, index, ev){
        if(Ext.isElement(el)) return;
        r = this.store.getAt(index);
        this.fireEvent('taskedit', {data: {id: r.get('nid')}})
    }
    ,onObjectsDeleted: function(ids, e){
        if(Ext.isEmpty(this.store)) return;
        for (var i = 0; i < ids.length; i++) {
            idx = this.store.findExact('nid', parseInt(ids[i]));
            if(idx >= 0 ) this.store.removeAt(idx);
        }
    }

})

CB.ActionTasksPanel = Ext.extend(Ext.Panel, {
    border: false
    ,hideBorders: true
    ,autoHeight: true
    ,initComponent: function(){
        this.totalView = new Ext.DataView({
            autoHeight: true
            ,style: 'background-color: #F4F4F4'
            ,tpl: [
                '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Tasks+'</h3>'
                ,'<p class="tasks-msg">'
                ,L.DisplayOwnTasksMsg
                ,'<tpl if="values.total&gt;0">'
                    ,' '+L.ThereAre+' '
                    ,'<tpl if="values.active&gt;0">'
                        ,'<a class="activetasks" href="#">{[values.active + \' \' + ((values.active ==1) ? L.active1 : L.active2) ]}</a> ' + L.outOf
                    ,'</tpl>'
                    ,' <a class="alltasks" href="#">{[values.total + \' \' + ((values.total ==1) ? L.ofTask : L.tasks) ]}</a>.</p>'
                ,'</tpl>'
            ]
            ,itemSelector: 'a'
            ,data: []
            ,listeners:{
                scope: this
                ,click: this.ontotalViewItemClick
            }
        })
        this.tasksView = new CB.ActionTasksView({ autoHeight: true });
        Ext.apply(this, {
            layout: 'fit'
            ,items: [this.totalView, this.tasksView]
            ,filters: {"status":[{"mode":"OR","values":["1","2"]}],"OR":{ "assigned":[{"mode":"OR","values":[App.loginData.id]}],"cid":[{"mode":"OR","values":[App.loginData.id]}] } }
            ,listeners: {
                scope: this
                ,beforedestroy: function(){
                    App.mainViewPort.un('taskcreated', this.onTaskUpdated, this)
                    App.mainViewPort.un('taskupdated', this.onTaskUpdated, this)
                }
            }
        })
        CB.ActionTasksPanel.superclass.initComponent.apply(this, arguments);
        App.mainViewPort.on('taskcreated', this.onTaskUpdated, this)
        App.mainViewPort.on('taskupdated', this.onTaskUpdated, this)
    }
    ,getCaseObjectId: function(){
        p = this.findParentByType(CB.Objects);
        if(Ext.isEmpty(p)) return;
        id = p.data.id;
        if(isNaN(id)) return;
        return id;
    }
    ,reload: function(){
        this.tasksView.store.removeAll();
        id = this.getCaseObjectId();
        if(Ext.isEmpty(id)) return;
        CB_BrowserView.getChildren({
            pid: id
            ,template_types: ['task']
            ,filters: this.filters
            ,facets:"actiontasks"
            ,sort: ['status asc', 'date_end asc']

        }, this.processLoad, this)
    }
    ,onShowActiveTasksClick: function(view){
        this.filters = { "status":[{"mode":"OR","values":['1', "2"]}] }
        this.reload();
    }
    ,onShowAllTasksClick: function(view){
        this.filters = { }
        this.reload();
    }
    ,processLoad: function(r, e){
        if(r.success !== true) return;
        for (var i = 0; i < r.data.length; i++) {
            r.data[i].cdate = date_ISO_to_date(r.data[i].cdate);
            r.data[i].iconCls = getItemIcon(r.data[i]);
        };
        if(r.facets){
            if(this.totalView.rendered) this.totalView.update(r.facets); else this.totalView.data = r.facets;
        }
        this.tasksView.store.loadData(r.data);
        if( r.facets && (r.facets.total > 0) ) this.setVisible(true);
        else this.setVisible(false);
    }
    ,ontotalViewItemClick: function(c, idx, el, ev){
        switch(el.className){
            case 'activetasks': this.onShowActiveTasksClick(); break;
            case 'alltasks': this.onShowAllTasksClick(); break;
        }
    }
    ,onTaskUpdated: function(r, e){
        if( r.data.pid == this.getCaseObjectId() ) this.reload();
    }
})