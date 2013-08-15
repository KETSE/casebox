Ext.namespace('CB');

CB.FilesConfirmationWindow = Ext.extend(Ext.Window, {
	autoShow: true
	,border: false
	,bodyBorder: false
	,closable: true
	,closeAction: 'hide'
	,autoHeight: true
	,maximizable: false
	,minimizable: false
	,modal: true
	,plain: true
	,resizable: false
	,stateful: false
	,title: L.UploadFile
	,minWidth: 550
	,width: 550
	,bodyStyle: 'padding: 10px; border: 0'
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
		
		this.renameButton = new Ext.Button({
			text: L.Rename
			,name: 'rename'
			,scope: this
			,handler: this.onButtonClick
		});
		if(!Ext.isEmpty(this.data.suggestedFilename)) buttons.push(this.renameButton);
		
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
		items = [
			{xtype: 'label', text: this.data.msg}
		]
		if(this.data.single == false) items.push({
			xtype: 'checkbox'
			,boxLabel: L.ApplyForAll
			,style: 'margin-top: 25px'
			,listeners:{
				check: function(cb, checked){
					this.forAll = checked;
					this.renameButton.setDisabled(checked);
				}
				,scope: this
			}
		})
		Ext.apply(this, {
			items: items
			,buttons: buttons
		})
		CB.FilesConfirmationWindow.superclass.initComponent.apply(this, arguments);

		this.response = 'cancel';
	}
	,onButtonClick: function(b){
		this.response = b.name;
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
				,submitValue: false
			},{	xtype : "checkbox"
				,name : "is_default"
				,inputValue : 1
				,boxLabel : L.byDefault
			}
		]

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
					,defaults: { anchor: '100%' }
					,items: fieldsetItems
				}
				,buttons: [{text: L.Upload, handler: this.doSubmit,  plugins: 'defaultButton', scope: this}
						  ,{text: Ext.MessageBox.buttonText.cancel, handler: function(){ this.hide() }, scope: this}]
				,api: {submit: Ext.value(this.api, CB_Browser.saveFile) }
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
		if(!Ext.isEmpty(this.data.id)) this.findByType('form')[0].api.submit = Ext.value(this.api, CB_Browser.uploadNewVersion)
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
		d = this.find('name', 'date');
		if(d){
			d = d[0];
			d = d.getValue();
			d = d ? d.toISOString() : null;
		}else d = null;

		f = this.findByType('form')[0];
		if(f.getForm().isValid()){
			f.getForm().submit({
				clientValidation: true
				,params: {
					id: this.data.id
					,pid: this.data.pid
					,uploadType: this.data.uploadType
					,date: d
				}
				,scope: this
				,success: this.onSubmitSuccess
				,failure: this.onSubmitFailure
			})
		}
	},onSubmitSuccess: function(form, action){
		/*on success actions*/
		this.serverResponse = action.result;
		this.fireEvent('submitsuccess', this, this.serverResponse.data);
		this.hide();
		if(!Ext.isEmpty(this.serverResponse.msg)){
			if(this.serverResponse.prompt_to_open)
				Ext.Msg.confirm(L.Info, this.serverResponse.msg, function(b){ if(b == 'yes') App.mainViewPort.fireEvent('fileopen', {id: this.serverResponse.data.id})}, this);
			else Ext.Msg.alert(L.Info, this.serverResponse.msg);
		}
	},onSubmitFailure: function(form, action){
		/*on failure actions*/
		if(action.result.type == 'filesexist'){
			this.serverResponse = action.result;
			w = new CB.FilesConfirmationWindow({
				title: L.FileExists
				,icon: Ext.MessageBox.QUESTION
				,data:{
					msg: this.serverResponse.msg
					,single: (this.serverResponse.count == 1)
					,allow_new_version: this.serverResponse.allow_new_version
					,suggestedFilename: this.serverResponse.suggestedFilename
					,autorenameButton: true
				}
				,listeners: {
					scope: this
					,hide: this.onConfirmResponse
				}
			})
			w.show();
		}else App.formSubmitFailure(form, action);
	},onConfirmResponse: function(w){
		if(w.response == 'rename'){
			Ext.Msg.prompt(L.Rename, L.NewFileName, function(btn, text){
				if( (btn == 'ok') && !Ext.isEmpty(text) ) CB_Browser.confirmUploadRequest({response: 'rename', newName: text}, this.onConfirmResponseProcess, this);
				else CB_Browser.confirmUploadRequest({response: 'cancel'}, this.onConfirmResponseProcess, this);
			}, this, false, this.serverResponse.suggestedFilename);
		}else CB_Browser.confirmUploadRequest({response: w.response}, this.onConfirmResponseProcess, this)
		w.destroy();
	},onConfirmResponseProcess: function(r, e){
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