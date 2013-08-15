Ext.namespace('CB');

CB.Uploader = Ext.extend(Ext.util.Observable, {
	
	defaultConfig: {
		autoStart: true
		// ,autoRemoveUploaded: false
		,autoShowWindow: true
		,url: 'upload.php'
	}
	,group: 0 // files grouping id 
	,status: 0 // Ready to upload
		//1 - Uploading
		//2 - Upload complete
		//3 - Upload canceled
	,stats:{
		totalSize: 0
		,totalCount: 0
		,totalLoadedSize: 0
		,totalLoadedCount: 0
		,currentFileSize: 0
		,currentLoaded: 0
	}
	,constructor: function(config){
		this.config = config || {};
		Ext.applyIf(this.config, this.defaultConfig);

		this.store = new Ext.data.JsonStore({
			fields:['id'
				,{name:'group', type: 'int'}
				,'name'
				,'type'
				,{name:'size', type: 'int'}
				,{name:'loaded', type: 'int'}
				,'pid'
				,'dir'
				,'pathtext'
				,'file'
				,{name: 'status', type: 'int'}
					/* 0 - ready to upload */
					/* 1 - uploading */
					/* 2 - upload error */
					/* 3 - upload timeout */
					/* 4 - upload abort */
					/* 5 - uploaded */
					/* 6 - skipped */
				,'msg'
				,'md5'
				,{name: 'md5_verified', type: 'int'}
				,'content_id'
				,'response'
				]
		})

		this.xhr = new XMLHttpRequest();
		if(this.xhr.addEventListener){
			this.xhr.addEventListener("loadstart", 		this.onFileUploadStart.createDelegate(this), false);
			this.xhr.upload.addEventListener("progress",	this.onFileUploadProgress.createDelegate(this), false);
			this.xhr.addEventListener("abort",		this.onFileUploadAbort.createDelegate(this), false);
			this.xhr.addEventListener("error",		this.onFileUploadError.createDelegate(this), false);
			this.xhr.addEventListener("load",		this.onFileUploadLoad.createDelegate(this), false);
			this.xhr.addEventListener("timeout",		this.onFileUploadTimeout.createDelegate(this), false);
			this.xhr.addEventListener("loadend",		this.onFileUploadLoadEnd.createDelegate(this), false);
		}else if(this.xhr.attachEvent){
			this.xhr.attachEvent("loadstart", 		this.onFileUploadStart.createDelegate(this), false);
			this.xhr.upload.attachEvent("progress",		this.onFileUploadProgress.createDelegate(this), false);
			this.xhr.attachEvent("abort",			this.onFileUploadAbort.createDelegate(this), false);
			this.xhr.attachEvent("error",			this.onFileUploadError.createDelegate(this), false);
			this.xhr.attachEvent("load",			this.onFileUploadLoad.createDelegate(this), false);
			this.xhr.attachEvent("timeout",			this.onFileUploadTimeout.createDelegate(this), false);
			this.xhr.attachEvent("loadend",			this.onFileUploadLoadEnd.createDelegate(this), false);
		}
		this.addEvents({
			'progresschange': true
			// ,'complete': true
			// ,'error': true
			// ,'abort': true
		});
		this.fileMD5 = new Ext.ux.fileMD5();
		this.fileMD5.on('done', this.onFileMD5Calculated, this);

		CB.Uploader.superclass.constructor.call(this, config)
	}

	,init: function(){
	}
	
	/* XHR listeners */
	,onFileUploadStart: function(e){
		this.uploadingFile.set('status', 1);//uploading
	}

	,onFileUploadProgress: function(e){
		if (e.lengthComputable) {
			// var percentComplete = Math.round(e.loaded * 100 / e.total);
			this.uploadingFile.set('loaded', e.loaded);
			this.stats.currentLoaded = e.loaded;
			// document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
		} this.stats.currentLoaded = -1; //unable to compute
		this.progressChange();
	}

	,onFileUploadAbort: function(e){
		this.targetStatus = 4; //abort
	}

	,onFileUploadError: function(e){
		//Events order: onFileUploadStart, onFileUploadError, onFileUploadLoadEnd
		this.targetStatus = 2; //error
	}

	,onFileUploadLoad: function(e){
	}

	,onFileUploadTimeout: function(e){
		this.targetStatus = 3; //timeout
	}
	,onFileUploadLoadEnd: function(e){
		if(this.uploadingFile){

			this.stats.totalLoadedSize += this.uploadingFile.get('size');
			this.stats.totalLoadedCount++;
			this.stats.currentLoaded = 0;
			this.stats.currentFileSize = 0;
			this.uploadingFile.set('loaded', this.uploadingFile.get('size'));
			this.progressChange();

			r = Ext.util.JSON.decode(e.target.response);
			if(r.success == true){
				this.uploadingFile.set('status', this.targetStatus);
				this.updatedPids.push(r.data.pid);
				this.fireEvent('fileuploadend', this.uploadingFile);
				delete this.uploadingFile;
				this.uploadNextFile();
			}else this.onUploadFailure(r, e);
		}
	}
	/* end of XHR listeners */
	
	,onUploadFailure: function(r, e){
		if(r.type == 'filesexist'){
			r.count = this.getGroupPendingFilesCount(this.uploadingFile.get('group'));
			this.serverResponse = r;
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
		}else{
			this.uploadingFile.set('status', 2); //upload error
			this.uploadNextFile();
		}
	}
	,getGroupPendingFilesCount: function(group){
		rez = 0;
		this.store.each( function(r){
			if( (r.get('group') == group) && (r.get('status') < 2) )
				rez++; 
		}, this);
		return rez;
	}
	
	,onConfirmResponse: function(w){
		this.uploadingFile.set('response', w.response);
		if(w.response == 'rename'){
			Ext.Msg.prompt(L.Rename, L.NewFileName, function(btn, text){
				if( (btn == 'ok') && !Ext.isEmpty(text) ) CB_Browser.confirmUploadRequest({response: 'rename', newName: text}, this.onConfirmResponseProcess, this);
				else{
					this.uploadingFile.set('status', 4);//abort
					CB_Browser.confirmUploadRequest({response: 'cancel'}, this.onConfirmResponseProcess, this);
				}
			}, this, false, this.serverResponse.suggestedFilename);
		}else{
			if(w.forAll) 
				this.store.each(function(r){ 
					if( (r.get('status') < 2) && (r.get('group') == this.uploadingFile.get('group')))
						r.set('response', w.response);
				}, this); //set default response for all files in this group
			CB_Browser.confirmUploadRequest({response: w.response}, this.onConfirmResponseProcess, this);
		}
		w.destroy();
	},onConfirmResponseProcess: function(r, e){
		if(r.success == true){
			this.uploadingFile.set('status', 5); //uploaded
			this.updatedPids.push(r.data.pid);
			this.uploadNextFile();
		}else{
			this.onUploadFailure(r, e);
		}
	}
	/* Uploader methods */
	,progressChange: function(){
		this.fireEvent('progresschange', this, this.status, this.stats);
	}
	,addFiles: function(FilesList, options){
		if(this.config.autoShowWindow) this.showUploadWindow()
		this.group++;
		Ext.each(FilesList, function(f){
			dir = Ext.value(f.fullPath, f.mozFullPath);
			if(!Ext.isEmpty(dir)){
				dir = dir.split('/');
				dir.pop();
				dir = dir.join('/');
			}else dir = '/';
			record = new this.store.recordType({
				id: Ext.id()
				,group: this.group
				,name: f.name
				,type: f.type
				,size: f.size
				,pid: options.pid
				,dir: dir
				,pathtext: options.pathtext
				,file: f
				,status: 0
				,loaded: 0
				,msg:''
				,md5: false
				,md5_verified: 0
			});
			// this.fileMD5.getMD5(f, record);
			this.store.add([record])
			this.stats.totalSize += record.get('size');
			this.stats.totalCount++;
		}, this)
		
		this.progressChange();
		this.calculateFilesMd5()
	}
	,calculateFilesMd5: function(){
		idx = this.store.findExact('md5', false);
		if(idx >= 0){
			this.md5FileRecord = this.store.getAt(idx);
			this.fileMD5.getMD5(this.md5FileRecord.get('file'));
		}else{
			delete this.md5FileRecord;
			this.checkExistentContents();
		}
	}
	,onFileMD5Calculated: function(fileMD5, result){
		this.md5FileRecord.set('md5', result);
		this.calculateFilesMd5();
	}

	,checkExistentContents: function(){
		md5array={};
		i = 0;
		this.store.each(function(r){
			if(Ext.isEmpty(r.get('md5'))){
				r.set('md5_verified', 1);
			}else if(r.get('md5_verified') == 0){ 
				md5array[r.get('id')] = r.get('md5')+'s'+r.get('size');
				i++;
			}
		}, this);
		if(i > 0){
			CB_Files.checkExistentContents(md5array, this.processCheckExistentContents, this);
		}else if(this.config.autoStart) this.start();
	}
	,processCheckExistentContents: function(r, e){
		if(r.success !== true) return;
		Ext.iterate(r.data, function(k, v, o){
			idx = this.store.findExact('id', k);
			if(idx>=0){
				r = this.store.getAt(idx);
				r.set('md5_verified', 1);
				r.set('content_id', v);
			}
		}, this);
		if(this.config.autoStart) this.start();
	}
	,start: function(){
		if(this.status == 1) return; //alreaty uploading
		idx = this.store.findExact('status', 0);
		if(idx < 0) return; // no files to upload
		this.status = 1;
		this.updatedPids = [];
		this.uploadNextFile();
	}

	,uploadNextFile: function(){
		if(this.status != 1) return; //status flag can be changed on Cnacel or abort
		idx = this.store.findExact('status', 0);
		if(idx < 0){ // no files waiting to be uploaded
			this.status = 2; //upload complete
			this.stats = {
				totalSize: 0
				,totalCount: 0
				,totalLoadedSize: 0
				,totalLoadedCount: 0
				,currentFileSize: 0
				,currentLoaded: 0
			};

			this.progressChange();
			if(!Ext.isEmpty(this.updatedPids)) App.fireEvent('filesuploaded', this.updatedPids);
			return;
		}
		r = this.store.getAt(idx);
		this.uploadingFile = r;

		this.stats.currentFileSize = r.get('size');
		this.stats.currentLoaded = 0;
		this.targetStatus = 5; //DONE

		params = {
			name: r.get('name')
			,type: r.get('type')
			,size: r.get('size')
			,pid: r.get('pid')
			,dir: r.get('dir')
			,md5: r.get('md5')
			,content_id: r.get('content_id')
			,response: r.get('response')
		}
		this.xhr.open("POST", 'upload.php', true)
		
		this.xhr.setRequestHeader("X_FILE_OPTIONS", Ext.util.JSON.encode(params));
		if(r.get('content_id') > 0) this.xhr.send('');
		else this.xhr.send(r.get('file'));
		this.progressChange();
	}
	,abort: function(){
		if(this.status != 1) return;
		this.status = 3; //upload canceled
		if(this.xhr.upload) this.xhr.upload.abort();
	}

	,showUploadWindow: function(){
		if(this.uploadWindow && !this.uploadWindow.isDestroyed) return this.uploadWindow.show();
		this.uploadWindow = new CB.UploadWindow({uploader: this});
		this.uploadWindow.show();
	}
	/* end of Uploader methods */
})

/* Uploader window */
CB.UploadWindow = Ext.extend(Ext.Window, {
	title: L.UploadQueue
	,closeAction: 'destroy'
	,width: 640
	,height: 380
	,layout: 'fit'
	,filterIndex: 0
	,initComponent: function(){
		this.uploader = this.uploader || App.getFileUploader()

		this.actions = {
			start: new Ext.Action({
				text: L.Start
				,iconCls: 'icon-control'
				,handler: this.onStartClick
				,scope: this
				,disabled: true
			})
			,stop: new Ext.Action({
				text: L.Stop
				,iconCls: 'control-stop-square'
				,handler: this.onStopClick
				,scope: this
				,hidden: true
			})
			,cancel: new Ext.Action({
				text: Ext.MessageBox.buttonText.cancel
				,iconCls: 'icon-cross'
				,handler: this.onCancelClick
				,scope: this
				,disabled: true
			})
			,cancelAll: new Ext.Action({
				text: L.CancelAll
				,iconCls: 'icon-cross'
				,handler: this.onCancelAllClick
				,scope: this
				,disabled: true
			})
			,clear: new Ext.Action({
				text: L.Clear
				,iconCls: 'icon-eraser'
				,handler: this.onClearClick
				,scope: this
				,hidden: true
			})
		}
		this.statusLabel = new Ext.form.DisplayField({
			value: L.ReadyToUpload
		})

				// ,'name'
				// ,'type'
				// ,{name:'size', type: 'int'}
				// ,{name:'loaded', type: 'int'}
				// ,'pid'
				// ,'pathtext'
				// ,'file'
				// ,{name: 'status', type: 'int'}
				// 	 0 - ready to upload 
				// 	/* 1 - uploading */
				// 	/* 2 - upload error */
				// 	/* 3 - upload timeout */
				// 	/* 4 - upload abort */
				// 	/* 5 - uploaded */
				// ,'msg']

		this.cancelSplitButton = new Ext.SplitButton({
			xtype: 'splitbutton'
			,iconCls: 'icon-cross'
			,text: Ext.MessageBox.buttonText.cancel
			,handler: this.onCancelClick
			,scope: this
			,disabled: true
			,menu: [this.actions.cancelAll]
		})

		this.viewButton = new Ext.Button({
			text: L.Pending
			,iconCls: 'icon-category'
			,menu: [{ 
			    		enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,pressed: true
			    		,text: L.Pending
			    		,filterIndex: 0
			    		,scope: this
			    		,handler: this.onChangeViewClick
				},{ 
			    		enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,text: L.AllCompleted
			    		,filterIndex: 1
			    		,scope: this
			    		,handler: this.onChangeViewClick
				},{
			    		enableToggle: true
			    		,allowDepress: false
			    		,toggleGroup: 'viewMode'
			    		,text: L.All
			    		,filterIndex: -1
			    		,scope: this
			    		,handler: this.onChangeViewClick
				}
			]
		})
		this.storeFilters = [
			function(r){ return (r.get('status') < 2); }
			,function(r){ return (r.get('status') > 1); }
		]
		this.optionsButton = new Ext.Button({
			text: L.Options
			,iconCls: 'icon-gear'
			,menu: [{ 
			    		checked: true
			    		,text: L.AutoshowUpload
			    		,scope: this
			    		,name: 'autoShowWindow'
			    		,handler: this.onOptionsClick
				},{ 
			    		checked: true
			    		,text: L.UploadAutoStart
			    		,scope: this
			    		,name: 'autoStart'
			    		,handler: this.onOptionsClick
				}
			]
		})

		this.grid = new Ext.grid.GridPanel({
			store: this.uploader.store
			,stateful: true
			,multiSelect: true
			// ,hideHeaders: true
			,stateId: 'uploadGrid'
			,border: false
			,columns: [{
				header: 'Name'
				,width: 150
				,sortable: false
				,dataIndex: 'name'
			},{
				header: 'Size'
				,width: 90
				,sortable: true
				,align: 'right'
				,renderer: Ext.util.Format.fileSize
				,dataIndex: 'size'
			},{
				header: 'status'
				,width: 75
				// ,hidden: true
				,sortable: true
				,dataIndex: 'status'
				,renderer: function(v, meta, r){
					return Ext.value(L['fileUploadStatus' + v], '');
					// 0 - ready to upload 
					// /* 1 - uploading */
					// /* 2 - upload error */
					// /* 3 - upload timeout */
					// /* 4 - upload abort */
					// /* 5 - uploaded/**/
				}
			},{
				header: 'Percent'
				,width: 75
				,sortable: true
				// ,hidden: true
				,dataIndex: 'loaded'
				,renderer: function(v, meta, r){
					if(r.get('status') == 0) return '';
					if(v == 0) return '';
					return Math.round(v*100/r.get('size')) + ' %';
				}
			},{
				header: 'Path'
				,width: 200
				,sortable: true
				,dataIndex: 'pathtext'
				,renderer: function(v, m, r){ return v+r.get('dir').substr(1)}
			},{
				header: 'msg'
				,width: 175
				,sortable: true
				,dataIndex: 'msg'
				,hidden: true
			}]
			,viewConfig: {
				stripeRows: true
				,markDirty: false
			}
			,tbar: new Ext.Toolbar({
				enableOverflow: true
				,style: {
					background: 'transparent'
					,border: 'none'
					,padding: '5px 0'
				}
				,items: [
					this.actions.start
					,this.actions.stop
					,this.cancelSplitButton
					,this.actions.clear
					,'->'
					,this.viewButton
					,this.optionsButton

				]
			})
			,bbar: [this.statusLabel]
		})
		this.grid.getSelectionModel().on('selectionchange', this.onSelectionChange, this)

		this.uploader.on('progresschange', this.onProgressChange, this)
		this.uploader.on('fileuploadend', this.filterView, this)

		Ext.apply(this, {
			items: [this.grid]
			,listeners: {
				afterrender: this.onAfterRender
				,beforedestroy: this.onBeforeDestroy
				,scope: this
			}
		})
		

		CB.UploadWindow.superclass.initComponent.apply(this, arguments);
	}
	,onAfterRender: function(){
		this.uploader.store.on('add', this.filterView, this);
	}
	,onBeforeDestroy: function(){
		this.uploader.store.un('add', this.filterView, this);
		this.uploader.un('progresschange', this.onProgressChange, this)
	}
	,onSelectionChange: function(sm){
		this.cancelSplitButton.setDisabled(!sm.hasSelection());
		this.actions.cancelAll.setDisabled(!sm.hasSelection());
	}
	,onProgressChange: function(uploader, status, stats){
		// ,status: 
		// 	0 Ready to upload
		// 	//1 - Uploading
		// 	//2 - Upload complete
		// 	//3 - Upload canceled
		
		// stats:{
		// 	totalSize: 0
		// 	,totalCount: 0
		// 	,totalLoadedSize: 0
		// 	,totalLoadedCount: 0
		// 	,currentFileSize: 0
		// 	,currentLoaded: 0
		// }
		this.actions.start.setDisabled( status == 1 );
		switch(status){
			case 0:
				this.statusLabel.setValue(Ext.value(L.ReadyToUpload, 'Ready to upload') )
				break;
			case 1:
				this.statusLabel.setValue(Ext.value(L.UploadCompleted, 'Uploading ... ') )
				if(stats.currentLoaded < 0){//unable to compute

				}else{
					percent = stats.totalLoadedSize + stats.currentLoaded;
					if(percent > 0) percent = Math.round(percent * 100 / stats.totalSize);
					this.statusLabel.setValue( String.format( Ext.value(L.UploadCompleted, 'Uploading {0}% ({1} out of {2})'), percent, (stats.totalLoadedCount + 1), stats.totalCount) );
				}
				break;
			case 2:
				this.statusLabel.setValue(Ext.value(L.UploadCompleted, 'Upload completed') )
				break;
			case 3:
				this.statusLabel.setValue(Ext.value(L.UploadCanceled, 'Upload canceled') )
				break;
		}

    	}
	,onStartClick: function(b, e){
		this.uploader.start();
	}
	,onStopClick: function(b, e){

	}
	,onCancelClick: function(b, e){
		this.uploader.abort( );
	}
	,onCancelAllClick: function(b, e){
		this.uploader.status = 3;
		this.uploader.abort( );
		/* mark all as aborted*/
	}
	,onClearClick: function(b, e){
		this.uploader.store.each(function(r){if(r.get('status') > 1) this.store.remove(r);});
	}
	,onChangeViewClick: function(b, e){
		// this.viewButton.setIconClass(b.iconCls)
		this.viewButton.setText(b.text)
		this.filterView(b.filterIndex);
		this.actions.clear.setHidden(b.filterIndex == 0);

		// this.uploader.store.removeAll();
	}
	,filterView: function(filterIndex){
		if(!Ext.isNumber(filterIndex)) filterIndex = undefined;
		if(filterIndex !== undefined) this.filterIndex = filterIndex;
		else if(!Ext.isDefined(this.filterIndex)) this.filterIndex = -1;
		if(this.filterIndex < 0) this.uploader.store.clearFilter();
		else this.uploader.store.filterBy(this.storeFilters[this.filterIndex]);
	}
	,onOptionsClick: function(b, e){
		this.uploader.config[b.name] = !b.checked;
	}
})

CB.UploadWindowButton = Ext.extend(Ext.Button, {
	cls: 'upload-btn'
	,initComponent: function(){
		this.uploader = App.getFileUploader();

		this.resetLabelTask = new Ext.util.DelayedTask( this.resetLabel, this );
		Ext.apply(this, {
			text: 'Upload window'
			,handler: this.showUploadWindow
			,scope: this
		})
		CB.UploadWindowButton.superclass.initComponent.apply(this, arguments);
		this.uploader.on('progresschange', this.onProgressChange, this)
	}
	,showUploadWindow: function(b, e){
		this.uploader.showUploadWindow();
	}
	,onProgressChange: function(uploader, status, stats){
		// ,stats:{
		// 	totalSize: 0
		// 	,totalCount: 0
		// 	,totalLoadedSize: 0
		// 	,totalLoadedCount: 0
		// 	,currentFileSize: 0
		// 	,currentLoaded: 0
		// }
		this.resetLabelTask.cancel();
		switch(status){
			case 0: this.setText(L.UploadQueue)
				break;
			case 1: 
				this.setText( L.Uploading + ' ' + stats.totalLoadedCount+ ' ' + L.outOf + ' ' + stats.totalCount );
				pc = Math.round( (stats.totalLoadedSize * 100) / stats.totalSize);
				this.getEl().applyStyles('background-size: '+pc+'% 100%');
				break;
			case 2: this.setText(L.UploadComplete);
				this.resetLabelTask.delay(3000);
				break;
		}
		// this.setText('status ' +status)
	}
	,resetLabel: function(){
		this.getEl().applyStyles('background-size: 0% 100%');
		this.setText(L.UploadQueue);
	}
})

Ext.reg('uploadwindowbutton', CB.UploadWindowButton);