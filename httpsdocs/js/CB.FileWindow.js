Ext.namespace('CB');
CB.FileVersionsView = Ext.extend(Ext.DataView, {
    width: 250
    ,split: true
    ,style: 'background-color: #F4F4F4'
    ,emptyText: '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.RevisionHistory+'</h3>'
    ,autoHeight: true
    ,initComponent: function(){

        Ext.apply(this, {
            tpl: new Ext.XTemplate(

                '<table class="versions">'
                ,'<tbody>'
                ,'<tpl for=".">'
                    ,'<tpl if="xindex == 1">'
                        ,'<tr><th colspan="2"><h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.CurrentVersion+'</h3></th></tr>'
                    ,'</tpl>'
                    ,'<tpl if="xindex == 2">'
                        ,'<tr><th colspan="2"><h3 style="padding: 35px 5px 10px 5px; font-size: 14px">'+L.RevisionHistory+'</h3></th></tr>'
                    ,'</tpl>'
                    ,'<tr class="item {cls}">'
                        ,'<td class="user"><img class="photo50" src="/photo/{cid}.jpg"></td>'
                        ,'<td><b>{username}</b><br><span class="dttm" title="{ago_date}">{ago_text}, {size}</span>'
                        ,'<br /><p class="gr fn">{name}</p>'
                        ,'<p class="actions">'
                            ,'<span style="float: right">'
                                ,'<a class="del" title="'+L.Delete+'">&nbsp;</a>'
                                ,'<a class="download" title="'+L.Download+'">&nbsp;</a>'
                            ,'</span>'
                            ,'<a class="restore">' + L.Restore + '</a>'
                        ,'</p>'
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
                    {name:'id', type: 'int'}
                    ,{name:'date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,'name'
                    ,'size'
                    ,{name:'cid', type: 'int'}
                    ,{name:'uid', type: 'int'}
                    ,{name:'cdate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,{name:'udate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,'ago_date'
                    ,'ago_text'
                    ,'username'
                    ,'cls'
                ]
                ,listeners:{
                    scope: this
                    ,load: function(store, records, options){
                        Ext.each(records, function(r){
                            r.set('username', CB.DB.usersStore.getName(r.get('cid')))
                            r.set('size', App.customRenderers.filesize(r.get('size')) )
                        }, this)
                    }
                }
            })
            ,itemSelector: '.item'
            ,overClass:'item-over'
            ,singleSelect: true
            ,selectedClass: 'sel'
            ,listeners: {
                scope: this
                ,click: this.onItemClick
            }

        })

        CB.FileVersionsView.superclass.initComponent.apply(this, arguments);
        this.addEvents('versionselected');
    }
    ,onItemClick: function(el, index, ev){
        if(!Ext.isElement(el)) return;
        target = ev.getTarget();
        if(!Ext.isEmpty(target))
            switch(target.className){
                case 'del': return this.onDeleteClick(index); break;
                case 'restore': return this.onRestoreClick(index); break;
                case 'download': return this.onDownloadClick(index); break;
            }
        if(this.isSelected(el)) return;
        a = this.getSelectedNodes();
        for (var i = 0; i < a.length; i++) Ext.get(a[i]).removeClass(this.selectedClass);
        this.select(el, false);
        this.fireEvent('versionselected', index, ev);

    }
    ,onDeleteClick: function(index){
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        Ext.Msg.confirm(L.DeleteConfirmation, L.versionDeleteConfirmation,
            function(b){
                if(b == 'yes') CB_Files.deleteVersion(r.get('id'), this.processDelete, this);
            }
            , this
        )
    }
    ,processDelete: function(r, e){
        idx = this.store.findExact('id', parseInt(r.id));
        if(idx >= 0 ) this.store.removeAt(idx);
        //this.
    }
    ,onRestoreClick: function(index){
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        CB_Files.restoreVersion(r.get('id'), function(r, e){ App.mainViewPort.fireEvent('fileuploaded', {data: r.data}) }, this)
    }
    ,onDownloadClick: function(index){
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        App.downloadFile(-1, false, r.get('id'))
    }

 })

CB.FileWindow = Ext.extend(Ext.Panel, {
        closable: true
    ,layout: 'fit'
    ,hideBorders: true
    ,initComponent: function() {
            this.actions = {
            save: new Ext.Action({
                text: L.Save
                ,iconAlign:'top'
                ,iconCls: 'icon32-save'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,hidden: true
                ,handler: this.onSaveClick
            })
            ,'delete': new Ext.Action({
                text: L.Delete
                ,iconAlign:'top'
                ,iconCls: 'icon32-del'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onDeleteClick
            })
            ,createTask: new Ext.Action({
                text: L.NewTask
                ,iconCls: 'icon32-task-new'
                ,iconAlign:'top'
                ,scale: 'large'
                //,disabled: true
                ,scope: this
                ,handler: this.onCreateTaskClick
            })
            ,paste: new Ext.Action({
                tooltip: L.PasteFromClipboard
                ,text: L.PasteFromClipboard
                ,disabled: true
                ,scope: this
                ,handler: this.onPasteClick
            })
            ,attachUpload: new Ext.Action({
                text: L.Upload
                ,tooltip: L.UploadFile
                ,iconAlign:'top'
                ,iconCls: 'icon-drive-upload'
                ,disabled: true
                ,scope: this
                ,handler: this.onAttachUploadClick
            })
            ,upload: new Ext.Action({
                text: L.Upload
                ,tooltip: L.UploadFile
                ,iconAlign:'top'
                ,iconCls: 'icon32-upload'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onUploadClick
            })
            ,download: new Ext.Action({
                text: L.Download
                ,tooltip: L.Download
                ,iconAlign:'top'
                ,iconCls: 'icon32-download'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onDownloadClick
            })
            ,expand: new Ext.Action({
                text: L.Expand
                ,iconAlign:'top'
                ,iconCls: 'icon32-expand'
                ,scale: 'large'
                ,enableToggle: true
                ,scope: this
                ,handler: this.onExpandClick
            })
            ,newWindow: new Ext.Action({
                text: L.NewWindow
                ,iconAlign:'top'
                ,iconCls: 'icon32-external'
                ,scale: 'large'
                ,disabled: true
                ,scope: this
                ,handler: this.onNewWindowClick
            })
            }

        this.previewPanel = new CB.form.view.object.Preview({
            region: 'center'
            ,bodyStyle: 'padding: 5px'
        });

        this.versionsView = new CB.FileVersionsView({
            listeners: {
                scope: this
                ,versionselected: this.onVersionSelect
            }
        });
        this.duplicatesView = new CB.FileDuplicatesViewPanel();
        this.propertiesPanel = new CB.ObjectsPropertiesPanel({
            style: 'margin-top: 25px'
            ,listeners:{
                scope: this
                ,pathclick: this.onPathClick
            }
        });

            Ext.apply(this, {
            listeners: {
                scope: this
                ,beforedestroy: this.onBeforeDestroy
            }
        });

        CB.FileWindow.superclass.initComponent.apply(this, arguments);
        this.addEvents( 'taskcreate', 'fileupload', 'filedownload', 'fileupload');
        this.enableBubble([ 'taskcreate', 'fileupload', 'filedownload', 'fileupload']);
        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
        App.mainViewPort.on('fileuploaded', this.onFileUploaded, this);
        App.mainViewPort.on('taskcreated', this.onTaskChange, this);
        App.mainViewPort.on('taskupdated', this.onTaskChange, this);
        App.clipboard.on('change', this.onClipboardChange, this);
    }

    ,afterRender: function() {
            // call parent
            CB.FileWindow.superclass.afterRender.apply(this, arguments);
            this.loadProperties()
        }
        ,onBeforeDestroy: function(){
        if(this.grid) this.grid.destroy();
        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
        App.mainViewPort.un('fileuploaded', this.onFileUploaded, this);
        App.clipboard.un('change', this.onClipboardChange, this);
        }
        ,loadProperties: function(){
        CB_Files.getProperties(this.data.id, this.processLoadProperties, this);
        }
    ,processLoadProperties: function(r, e){
        if(r.success !== true) return;
        this.data = r.data;
        this.data.cdate = date_ISO_to_date(this.data.cdate);
        this.data.udate = date_ISO_to_date(this.data.udate);
        this.data.id = parseInt(this.data.id);
        this.setIconClass(getFileIcon(r.data.name));
        this.setTitle(r.data.name);
        if( !this.loaded ){
            this.prepareInterface();
            this.loaded = true;
        }

        if(this.grid) this.grid.reload();

        this.actions.save.setDisabled(true);
        this.actions.attachUpload.setDisabled(false);
        this.actions.download.setDisabled(false);
        this.actions.upload.setDisabled(false);
        this.actions.newWindow.setDisabled(false);
        this.actions['delete'].setDisabled(false);


        this.previewPanel.clear();
        this.previewPanel.loadPreview(this.data.id);

        if(Ext.isEmpty(this.data.versions)) this.data.versions = [];
        this.data.cls = 'current';
        this.versionsView.store.loadData([this.data].concat(this.data.versions), false);

        this.items.last().items.first().syncSize();

        this.duplicatesView.reload();
        this.propertiesPanel.update(this.data);
    }
    ,prepareInterface: function(){
        /* find out if need to show properties panel */
        this.showPropertiesPanel = false;
        if( !Ext.isEmpty( this.data.template_id) ){
            templateStore = CB.DB['template'+this.data.template_id];
            if(templateStore && (templateStore.getCount() > 0) ) this.showPropertiesPanel = true;

        }
        /* end of find out if need to show properties panel */

        toolbarItems = []

        /* insert create menu if needed */
        menuConfig = getMenuConfig(this.data.id, this.data.path, this.data.template_id);
        if( !Ext.isEmpty(menuConfig) ){
            createButton = new Ext.Button({
                text: L.Create
                ,iconCls: 'icon32-create'
                ,iconAlign:'top'
                ,scale: 'large'
                            ,menu: [ ]
                        })
            updateMenu(createButton, menuConfig, this.onCreateObjectClick, this);
            toolbarItems.push(createButton, '-')
        }
        /**/
        toolbarItems.push(this.actions.save);

        if(!this.hideDeleteButton) toolbarItems.push(this.actions['delete']);

        toolbarItems.push('-',{text: 'Attach', iconCls: 'icon32-attach', scale: 'large', iconAlign:'top'
                    ,menu: [
                this.actions.attachUpload
                ,'-'
                ,this.actions.paste
            ]
            })

        toolbarItems.push(this.actions.createTask
                    ,'-'
            ,this.actions.upload
            ,this.actions.download
            ,'->'
            ,this.actions.expand
            ,this.actions.newWindow)
        /* */

        this.actions.save.setHidden( !this.showPropertiesPanel );

        contentItems = [ this.previewPanel ];
        if(this.showPropertiesPanel){
            this.previewPanel.title = L.Preview;
            this.grid = new CB.VerticalEditGrid({
                title: L.Properties
                ,refOwner: this
                ,autoHeight: true
                ,viewConfig: {autoFill: true, forceFit: true}
                ,listeners: {
                    scope: this
                    ,change: function(){
                        this.actions.save.setDisabled(false);
                    }
                }
            })
            contentItems = [{
                xtype: 'tabpanel'
                ,plain: true
                ,headerCfg: {cls: 'mainTabPanel'}
                ,bodyStyle: 'background-color: #FFF'
                ,region: 'center'
                ,activeItem: 0
                ,items: [
                    this.previewPanel
                    ,this.grid
                ]
            }]
        }

        contentItems.push({
            region: 'east'
            ,width: 300
            ,split: 'true'
            ,bodyStyle: 'background-color: #f4f4f4'
            ,autoScroll: true
            ,items: [{
                    xtype: 'panel'
                    ,layout: 'fit'
                    ,bodyStyle: 'background-color: #f4f4f4; margin-bottom: 25px'
                    ,padding: 0
                    ,border: false
                    ,autoHeight: true
                    ,items: [this.versionsView]
                }
                ,this.duplicatesView
                ,this.propertiesPanel
            ]
        })

        this.add({
            layout: 'border'
            ,tbarCssClass: 'x-panel-white'
            ,hideBorders: true
            ,tbar: toolbarItems
            ,items: contentItems
        })
        this.doLayout();
    }
    ,onVersionSelect: function(idx, e){

        vr = this.versionsView.store.getAt(idx);
        if(Ext.isEmpty(vr)) return;
        this.previewPanel.loadPreview(this.data.id, (idx ==0) ? '' : vr.get('id'));
    }
    ,onClipboardChange: function(cb){
        this.actions.paste.setDisabled(cb.isEmpty());
    }
    ,onCreateObjectClick: function(b, e) {
        data = Ext.apply({}, {
            pid: this.data.id
            ,path: this.data.path+'/'+this.data.id
            ,pathtext: this.data.pathtext+'/'+this.data.name
        }, b.data);
        App.mainViewPort.createObject(data, e);
    }
    ,onAttachUploadClick: function(b, e) {
        this.fireEvent('fileupload', {pid: this.data.id, uploadType: 'single'}, e)
    }
    ,onPasteClick: function(b, e) {
        App.clipboard.paste(this.data.id, null, this.onPasteProcess, this);
    }
    ,onPasteProcess: function(pids){
    }
    ,onCreateTaskClick: function(o, e){
        this.fireEvent(
            'taskcreate'
            ,{
                data: {
                    template_id: App.config.default_task_template
                    ,pid: this.data.id
                    ,path: this.data.path+'/'+this.data.id
                    ,pathtext: this.data.pathtext+ this.data.name
                }
            }
        )
    }
    ,onTaskChange: function(r){
        if(r.data && (r.data.pid == this.data.id) ){
            this.previewPanel.clear();
            this.previewPanel.loadPreview(this.data.id);
        }
    }
    ,onUploadClick: function(b, e){
        this.fireEvent('fileupload', this.data, e);
    }
    ,onFileUploaded: function(data){
        if(data.data.id == this.data.id) this.loadProperties();
    }
    ,onDownloadClick: function(b, e){
        this.fireEvent('filedownload', this.data.id)
    }
    ,onSaveClick: function(b, e){
        if(Ext.isEmpty(this.grid)) return;
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        this.grid.readValues();
        CB_Files.saveProperties(this.data, this.processSaveClick, this);
    }
    ,processSaveClick: function(r, e){
        this.getEl().unmask();
        this.actions.save.setDisabled(true);

    }
    ,onDeleteClick: function(){
        Ext.Msg.confirm( L.DeleteConfirmation, L.fileDeleteConfirmation// + ' "' + this.data.name + '"?'
            , this.onDelete, this )
    }
    ,onDelete: function (btn) {
        if(btn !== 'yes') return;
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_BrowserTree['delete'](this.data.id, this.processDelete, this);
    }
    ,processDelete: function(r, e){
        this.getEl().unmask();
        App.mainViewPort.onProcessObjectsDeleted(r, e);
    }
    ,onObjectsDeleted: function(ids){
        if( ids.indexOf(this.data.id) >=0 ) this.destroy();
    }
    ,onExpandClick: function (b, e) {
                App.mainViewPort.toggleWestRegion(!b.pressed);
        this.items.first().items.last().setVisible(!b.pressed);
        //this.syncSize();
    }
    ,onNewWindowClick: function(b, e){
        window.open('/preview/'+this.data.id+'_.html');
    }
    ,onPathClick: function(){
        App.locateObject( this.data.pid, this.data.path );
     }
});

Ext.reg('CBFileWindow', CB.FileWindow); // register xtype

CB.ActionFilesView = Ext.extend(Ext.DataView, {
    style: 'background-color: #F4F4F4'
    ,emptyText: '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Files+'</h3>'
    ,initComponent: function(){

        Ext.apply(this, {
            tpl: new Ext.XTemplate(
                '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Files+'</h3>'
                ,'<table class="versions">'
                ,'<tbody>'
                ,'<tpl for=".">'
                    ,'<tr class="item {cls}">'
                        ,'<td class="user"><img class="{iconCls}" src="'+Ext.BLANK_IMAGE_URL+'"></td>'
                        ,'<td><b>{name}</b><br><span class="dttm" title="{ago_date}">{ago_text}, {size}</span>'
                        ,'<p class="actions">'
                            ,'<span style="float: right">'
                                ,'<a name="menu" class="icon-arrdown">&nbsp;</a>'
                            ,'</span>'
                            ,' <a href="#" name="open" class="icon-open">' + L.Open + '</a>'
                            ,' <a href="#" name="download" class="icon-download">'+L.Download+'</a>'
                        ,'</p>'
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
                    , {name: 'subtype', type: 'int'}
                    ,'size'
                    ,{name:'cid', type: 'int'}
                    ,{name:'uid', type: 'int'}
                    ,{name:'cdate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,{name:'udate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,'ago_date'
                    ,'ago_text'
                    ,'username'
                    ,'iconCls'
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
        CB.FileVersionsView.superclass.initComponent.apply(this, arguments);
        this.addEvents('fileopen')
        this.enableBubble(['fileopen'])
        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this)
    }
    ,onItemClick: function(el, index, ev){
        if(!Ext.isElement(el)) return;
        target = ev.getTarget();
        if(!Ext.isEmpty(target))
            switch(target.name){
                case 'open': return this.onOpenClick(index, ev); break;
                case 'download': return this.onDownloadClick(index, ev); break;
                case 'menu':  return this.onMenuClick(index, ev); break;
            }
        if(this.isSelected(el)) return;
        a = this.getSelectedNodes();
        for (var i = 0; i < a.length; i++) Ext.get(a[i]).removeClass(this.selectedClass);
        this.select(el, false);

    }
    ,onOpenClick: function(index, e){
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        this.fireEvent('fileopen', {id: r.get('nid')}, e);
    }
    ,onDownloadClick: function(index, e){
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        App.downloadFile(r.get('nid'), false)
    }
    ,onMenuClick: function(index, e){
        if(Ext.isEmpty(this.filesMenu))
            this.filesMenu = new Ext.menu.Menu({
                items: [{
                    text: L.Cut
                    ,scope: this
                    ,handler: this.onCutClick
                }
                ,{
                    text: L.Copy
                    ,scope: this
                    ,handler: this.onCopyClick
                },{
                    text: L.Delete
                    ,iconCls: 'icon-trash'
                    ,scope: this
                    ,handler: this.onDeleteClick
                },'-',{
                    text: L.NewWindow
                    ,iconCls: 'icon-open'
                    ,scope: this
                    ,handler: this.onNewWindowClick
                }
                ]
            })
        this.filesMenu.itemIndex = index;
        this.filesMenu.showAt(e.getXY())

    }
    ,onCutClick: function(buttonOrKey, e) {
        this.onCopyClick(buttonOrKey, e)
        App.clipboard.setAction('move');
    }
    ,onCopyClick: function(buttonOrKey, e) {
        index = this.filesMenu.itemIndex
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        rez = [{
            id: r.get('nid')
            ,name: r.get('name')
            ,system: r.get('system')
            ,type: r.get('type')
            ,subtype: r.get('subtype')
            ,iconCls: r.get('iconCls')
        }]
        App.clipboard.set(rez, 'copy');
    }
    ,onDeleteClick: function(b, e){
        index = this.filesMenu.itemIndex
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        Ext.Msg.confirm(L.DeleteConfirmation, L.fileDeleteConfirmation//L.DeleteConfirmationMessage + ' "' + r.get('name') + '"?',
            ,function(b){
                if(b == 'yes') {
                    CB_Browser['delete'](
                        r.get('nid')
                        ,function(r, e){
                            App.mainViewPort.onProcessObjectsDeleted(r, e)
                        }
                        ,this
                    );
                }
            }
            , this
        )
    }
    ,onObjectsDeleted: function(ids, e){
        if(Ext.isEmpty(this.store)) return;
        for (var i = 0; i < ids.length; i++) {
            idx = this.store.findExact('nid', parseInt(ids[i]));
            if(idx >= 0 ) this.store.removeAt(idx);
        }
    }
    ,onNewWindowClick: function(b, e){
        index = this.filesMenu.itemIndex
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        window.open('/preview/'+r.get('nid')+'_.html');
    }
})
CB.ActionFilesPanel = Ext.extend(Ext.Panel, {
    border: false
    ,hideBorders: true
    ,autoHeight: true
    ,initComponent: function(){
        this.filesView = new CB.ActionFilesView({ autoHeight: true });
        Ext.apply(this, {
            layout: 'fit'
            ,items: this.filesView
            ,listeners: {
                scope: this
                ,beforedestroy: function(){
                    App.mainViewPort.un('fileuploaded', this.onFileUploaded, this)
                    this.filesView.destroy();
                }
            }
        })
        CB.ActionFilesPanel.superclass.initComponent.apply(this, arguments);
        App.mainViewPort.on('fileuploaded', this.onFileUploaded, this)
    }
    ,getCaseObjectId: function(){
        p = this.findParentByType(CB.Objects);
        if(Ext.isEmpty(p)) return;
        id = p.data.id;
        if(isNaN(id)) return;
        return id;
    }
    ,reload: function(){
        this.filesView.store.removeAll();
        id = this.getCaseObjectId();
        if(Ext.isEmpty(id)) return;
        CB_BrowserView.getChildren({pid: id, template_types: 'file'}, this.processFilesLoad, this)
    }
    ,processFilesLoad: function(r, e){
        if(r.success !== true) return;
        for (var i = 0; i < r.data.length; i++) {
            r.data[i].size = App.customRenderers.filesize(r.data[i].size);
            r.data[i].cdate = date_ISO_to_date(r.data[i].cdate);
            r.data[i].iconCls = getFileIcon32(r.data[i].name);
        };
        this.filesView.store.loadData(r.data);
        if(this.filesView.store.getCount() > 0) this.setVisible(true);
        else return this.setVisible(false);

    }
    ,onFileUploaded: function(r, e){
        if(r.data.pid == this.getCaseObjectId()) this.reload();
    }

})

CB.FileDuplicatesView = Ext.extend(Ext.DataView, {
    style: 'background-color: #F4F4F4'
    ,emptyText: '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Duplicates+'</h3>'
    ,initComponent: function(){

        Ext.apply(this, {
            tpl: new Ext.XTemplate(
                '<h3 style="padding: 5px 5px 10px 5px; font-size: 14px">'+L.Duplicates+'</h3>'
                ,'<p style="padding: 0px 7px" class="gr">The current version of this file has duplicates, click on the path to locate the duplicate. Duplicates under a different filename are highlighted in red.</p>'
                ,'<table class="duplicates">'
                ,'<tbody>'
                ,'<tpl for=".">'
                    ,'<tr class="item"><td class="k">{[ xindex ]}.</td><td><a class="path" href="#">{pathtext}</a>'
                    ,'{[ Ext.isEmpty(values.name) ? "" : \'<br /><span style="color: maroon">\'+values.name+\'</span>\']}'
                    ,'<p class="gr"><span class="dttm" title="cdate">{[values.cdate.format("Y, F j")]}</span>, {[ CB.DB.usersStore.getName(values.cid)]}</p>'
                    ,'</td></tr>'
                ,'</tpl>'
                ,'</tbody>'
                ,'</table>'
                ,{compiled: true}
            )
            ,store: new Ext.data.JsonStore({
                root: ''
                ,fields: [
                    {name:'id', type: 'int'}
                    ,'name'
                    ,{name:'cid', type: 'int'}
                    ,{name:'cdate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                    ,'path'
                    ,'pathtext'
                ]
                ,data: []
            })
            ,itemSelector: 'a'
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
        CB.FileDuplicatesView.superclass.initComponent.apply(this, arguments);
        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this)
    }
    ,onItemClick: function(el, index, ev){
        r = this.store.getAt(index);
        if(Ext.isEmpty(r)) return;
        App.locateObject(r.get('id'), r.get('path'))
    }
    ,onObjectsDeleted: function(ids, e){
        for (var i = 0; i < ids.length; i++) {
            idx = this.store.findExact('id', parseInt(ids[i]));
            if(idx >= 0 ) this.store.removeAt(idx);
        }
    }
})

CB.FileDuplicatesViewPanel = Ext.extend(Ext.Panel, {
    border: false
    ,hideBorders: true
    ,autoHeight: true
    ,initComponent: function(){
        this.view = new CB.FileDuplicatesView({ autoHeight: true });
        Ext.apply(this, {
            layout: 'fit'
            ,items: this.view
        })
        CB.FileDuplicatesViewPanel.superclass.initComponent.apply(this, arguments);
    }
    ,getFileId: function(){
        p = this.findParentByType(CB.FileWindow);
        if(Ext.isEmpty(p)) return;
        id = p.data.id;
        if(isNaN(id)) return;
        return id;
    }
    ,reload: function(){
        this.view.store.removeAll();
        id = this.getFileId();
        if(Ext.isEmpty(id)) return;
        CB_Files.getDuplicates(id, this.processFilesLoad, this)
    }
    ,processFilesLoad: function(r, e){
        if(r.success !== true) return;
        for (var i = 0; i < r.data.length; i++) {
            r.data[i].cdate = date_ISO_to_date(r.data[i].cdate);
        };
        this.view.store.loadData(r.data);
        if(this.view.store.getCount() > 0) this.setVisible(true);
        else return this.setVisible(false);

    }
})
