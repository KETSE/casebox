Ext.namespace('CB');

CB.FilesConfirmationWindow = Ext.extend(Ext.Window, {
	autoShow: true
	,bodyBorder: false
	,closable: true
	,closeAction: 'hide'
	,autoHeight: true
	//,layout: 'fit'
	,maximizable: false
	,minimizable: false
	,modal: true
	//,plain: true
	,resizable: false
	,stateful: false
	,title: L.UploadFile
	,minWidth: 550
	,width: 550
	,bodyStyle: 'padding: 10px'
	,buttonAlign: 'center'
	,data:{
		single: true
		,autorenameButton: true 
	}
	,initComponent: function(){
		buttons = []
		if(this.data.allow_new_version) buttons.push({
			text: L.NewVersion
			,name: 'newversion'
			,scope: this
			,handler: this.onButtonClick
		})
		buttons.push({
			text: L.Replace
			,name: 'replace'
			,scope: this
			,handler: this.onButtonClick
		});
		if(!Ext.isEmpty(this.data.suggestedFilename)) buttons.push({
			text: L.Rename
			,name: 'rename'
			,scope: this
			,handler: this.onButtonClick
		});
		
		if(this.data.autorenameButton) buttons.push({
			text: L.AutoRename
			,name: 'autorename'
			,scope: this
			,handler: this.onButtonClick
		});

		buttons.push({
			text: Ext.MessageBox.buttonText.cancel
			,name: 'cancel'
			,scope: this
			,handler: this.onButtonClick
		});

		Ext.apply(this, {
			items: [{xtype: 'label', text: this.data.msg}]
			,buttons: buttons
		})
		CB.FilesConfirmationWindow.superclass.initComponent.apply(this, arguments);

		this.responce = 'cancel';
	}
	,onButtonClick: function(b){
		this.responce = b.name;
		this.hide();
	}

})/**/

CB.FileUploadWindow = Ext.extend(Ext.Window, {
	autoShow: true
	,bodyBorder: false
	,closable: true
	,closeAction: 'hide'
	,data: {uploadType: 'single'} // single/multiple/archive
	,autoHeight: true
	,layout: 'fit'
	,maximizable: false
	,minimizable: false
	,modal: true
	,plain: true
	,resizable: false
	,stateful: false
	//,title: L.UploadFile
	,iconCls: 'icon-upload'
	,width: 370

	,fileOnly: false
	,fieldName: 'file'
	,initComponent: function(){
		
		fieldsetItems = [{
				fieldLabel: L.File
				,inputType: 'file'
				,name: this.fieldName
				,xtype: 'textfield'
			},{xtype: 'textfield'
				,fieldLabel: L.Title
				,name: 'title'
			},{ fieldLabel: '&nbsp;', labelSeparator: '', name: 'addFileButton',xtype: 'dataview', data: [], tpl: '<a href="#" class="cBl">'+L.addFile+'</a>', itemSelector: 'a', listeners: { click: {scope: this, fn: this.onAddFileFieldClick} } 
			},{
				xtype: 'datefield'
				,fieldLabel: L.Date
				,name: 'date'
				,altFormats: 'Y-m-d'
				,format: App.dateFormat
			},{	xtype : "checkbox"
				,name : "is_default"
				,inputValue : 1
				,boxLabel : L.byDefault
			}
		]
		if(App.loginData.cfg.system_tags && !this.fileOnly)
			fieldsetItems.push({
				xtype: 'CBTagField'
				,fieldLabel: L.Tags
				,iconCls: 'icon-tag'
				,tooltip: L.Tags
				,name: 'tags'
				,groupField: 'groupId'
				,store: CB.DB.groupedTags
				,filter: function(r){ return ((r.get('system') == 0) || (r.get('system') == 6))}
				,api:{search: UsersGroups.searchSysTags, searchGroup: 6}
			})
		if(App.loginData.cfg.personal_tags && !this.fileOnly)
			fieldsetItems.push({
				xtype: 'CBTagField'
				,fieldLabel: L.UserTags
				,iconCls: 'icon-tag-label'
				,tooltip: L.UserTags
				,name: 'user_tags'
				,store: CB.DB.userTags
				,api: {create: UsersGroups.addUserTag, search: UsersGroups.searchUserTags}
			})
		Ext.apply(this, {
			items: {
				border: false
				,labelWidth: 90
				,fileUpload: true
				,xtype: 'form'
				,autoHeight: true
				,monitorValid: true
				,items: {
					border: false
					,xtype: 'fieldset'
					,style: 'margin-top: 10px'
					,autoHeight: true
					,defaults: {anchor: '100%'}
					,items: fieldsetItems
				}
				,buttons: [{text: L.Upload, handler: this.doSubmit,  plugins: 'defaultButton', scope: this}
						  ,{text: Ext.MessageBox.buttonText.cancel, handler: function(){this.hide()}, scope: this}]
				,api: {submit: Ext.value(this.api, Browser.saveFile) }
				,paramOrder: ['id']
				,listeners:{
					actioncomplete: {
						scope: this
						,fn: function(f, a){this.el.unmask();}
					},actionfailed: {
						scope: this
						,fn: function(f, a){this.el.unmask();}
					},beforeaction: {
						scope: this
						,fn: function(f, a){this.el.mask(L.fileUploadingMessage + ' ...', 'x-mask-loading');}
					}

				}
			}
			,listeners: {
				show: {
					scope: this
					,fn: function(f, a){this.find('inputType', 'file')[0].reset();this.findByType('checkbox')[0].reset(); this.syncSize();}
				}
			}
		});
		
		CB.FileUploadWindow.superclass.initComponent.apply(this, arguments);
		this.on('show', this.onShow, this);
		this.addEvents('submitsuccess');
	},onShow: function(){
		switch(this.data.uploadType){
			case 'archive': this.setTitle( Ext.value(this.title, L.UploadArchive) ); break;
			case 'multiple': this.setTitle( Ext.value(this.title, L.UploadMultipleFiles) ); break;
			default: this.data.uploadType = 'single'; this.setTitle( Ext.value(this.title, L.UploadFile) ); break;
		}
		if(!Ext.isEmpty(this.data.id)) this.findByType('form')[0].api.submit = Ext.value(this.api, Browser.uploadNewVersion)
		cb = this.find('name', 'title')[0];
		cb.setVisible( !this.fileOnly && (this.data.uploadType == 'single') )
		cb = this.find('name', 'date')[0];
		cb.setVisible( !this.fileOnly )
		cb = this.find('name', 'addFileButton')[0];
		cb.setVisible( !this.fileOnly && (this.data.uploadType == 'multiple') )
		cb = this.find('name', 'is_default')[0];
		cb.setVisible( !this.fileOnly && !Ext.isEmpty(this.data.object_id))
		this.find('name', this.fieldName)[0].allowBlank = !Ext.isEmpty(this.data.id);
		if(Ext.isEmpty(this.data.id) && Ext.isEmpty(this.data.date)) this.data.date = new Date();
		this.findByType('form')[0].getForm().setValues(this.data);
		ed = this.find('name', 'tags');
		if(!Ext.isEmpty(ed)){
			ed[0].setValue(this.data.sys_tags)
			ed[0].setVisible( !this.fileOnly );
		}
		ed = this.find('name', 'user_tags');
		if(!Ext.isEmpty(ed)){
			ed[0].setValue(this.data.user_tags);
			ed[0].setVisible( !this.fileOnly );
		}
		
		App.focusFirstField(this);
	},doSubmit: function(){
		f = this.findByType('form')[0];
		if(f.getForm().isValid()){
			tags = {}
			if(App.loginData.cfg.system_tags) tags[3] = this.find('name', 'tags')[0].getValue();
			if(App.loginData.cfg.personal_tags) tags[4] = this.find('name', 'user_tags')[0].getValue();
			f.getForm().submit({
				clientValidation: true
				,params: {
					id: this.data.id
					,case_id: this.data.case_id
					,pid: this.data.pid
					,tags: Ext.encode( tags )
					,uploadType: this.data.uploadType
				}
				,scope: this
				,success: this.onSubmitSuccess
				,failure: this.onSubmitFailure
			})
		}
	},onSubmitSuccess: function(form, action){
		/*on success actions*/
		this.serverResponce = action.result;
		this.fireEvent('submitsuccess', this, this.serverResponce.data);
		this.hide();
		if(!Ext.isEmpty(this.serverResponce.msg)){
			if(this.serverResponce.prompt_to_open)
				Ext.Msg.confirm(L.Info, this.serverResponce.msg, function(b){ if(b == 'yes') App.mainViewPort.fireEvent('fileopen', {id: this.serverResponce.data.id})}, this);
			else Ext.Msg.alert(L.Info, this.serverResponce.msg);
		}
	},onSubmitFailure: function(form, action){
		/*on failure actions*/
		if(action.result.type == 'filesexist'){
			this.serverResponce = action.result;
			w = new CB.FilesConfirmationWindow({
				title: L.FileExists
				,icon: Ext.MessageBox.QUESTION
				,data:{
					msg: this.serverResponce.msg
					,single: (this.serverResponce.count == 1)
					,allow_new_version: this.serverResponce.allow_new_version
					,suggestedFilename: this.serverResponce.suggestedFilename
					,autorenameButton: true
				}
				,listeners: {
					scope: this
					,hide: this.onConfirmResponce
				}
			})
			w.show();
			
			// case 'fileexists': 
			// 	this.filename = action.result.filename;
			// 	w = new CB.FilesConfirmationWindow({
			// 		title: L.FileExists
			// 		,msg: action.result.msg //L.FilenameExistsInTarget.replace('[fileName]', '"' + this.filename + '"')
			// 		,icon: Ext.MessageBox.QUESTION
			// 		,listeners: {
			// 			scope: this
			// 			,hide: this.onConfirmResponce
			// 		}
			// 	})
			// 	w.show();
			// 	break;
			// case 'multiplefileexists': 
			// 	w = new CB.FilesConfirmationWindow({
			// 		title: L.FileExists
			// 		,msg: action.result.msg //L.SomeFilenamesExistsInTarget
			// 		,icon: Ext.MessageBox.QUESTION
			// 		,data: {single: false, autorenameButton: true}
			// 		,listeners: {
			// 			scope: this
			// 			,hide: this.onConfirmResponce
			// 		}
			// 	})
			// 	w.show();
			// 	break;
			// default: App.formSubmitFailure(form, action);
		}else App.formSubmitFailure(form, action);
	},onConfirmResponce: function(w){
		if(w.responce == 'rename'){
			Ext.Msg.prompt(L.Rename, L.NewFileName, function(btn, text){
				if( (btn == 'ok') && !Ext.isEmpty(text) ) Browser.confirmUploadRequest({responce: 'rename', newName: text}, this.onConfirmResponceProcess, this);
				else Browser.confirmUploadRequest({responce: 'cancel'}, this.onConfirmResponceProcess, this);
			}, this, false, this.serverResponce.suggestedFilename);
		}else Browser.confirmUploadRequest({responce: w.responce}, this.onConfirmResponceProcess, this)
		w.destroy();
	},onConfirmResponceProcess: function(r, e){
		if(r.success == true) this.onSubmitSuccess(this.findByType('form')[0], {result: r});
		else this.onSubmitFailure(this.findByType('form')[0], {result: r});
	},onAddFileFieldClick: function(f, idx, d, e){
		fs = this.findByType('fieldset')[0];
		a = fs.find('inputType', 'file');
		fs.insert(a.length, {fieldLabel: L.File, inputType: 'file', name: this.fieldName +a.length, xtype: 'textfield'});
		f.setVisible(a.length < 9)
		fs.doLayout();
		this.syncSize();
	}
});

Ext.reg('CBFileUploadWindow', CB.FileUploadWindow);