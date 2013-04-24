Ext.namespace('CB.plugins');
CB.plugins.FilesDropZone =  Ext.extend(Ext.util.Observable, {
	pidPropety: 'nid'
	,dropZoneConfig:{
		text: 'Drop files here'
	}
	,constructor: function(config){
		if(config) Ext.apply(this, config);
	}
	,init: function(owner) {
		this.owner = owner;
		owner.on('render', this.onRender, this);
		// owner.on('destroy', this.onBeforeDestroy, this);
		if(owner.dropZoneConfig) Ext.apply(this.dropZoneConfig, owner.dropZoneConfig);
	}

	,onRender: function(grid){
		el = grid.getEl();
		el.on('dragleave', this.onDragLeave, this);
		el.on('dragover', this.onDragOver, this);
		el.on('drop', this.onDrop, this);
		App.on('dragfilesenter', this.showDropZone, this)
		App.on('dragfilesover', this.showDropZone, this)
		App.on('dragfilesleave', this.hideDropZone, this)
		App.on('filesdrop', this.hideDropZone, this)
	}
	,onBeforeDestroy: function(){
		// this.owner.un('render', this.onRender, this);
		App.un('dragfilesenter', this.showDropZone, this)
		App.un('dragfilesover', this.showDropZone, this)
		App.un('dragfilesleave', this.hideDropZone, this)
		App.un('filesdrop', this.hideDropZone, this)
		if(this.dropZoneEl){
			this.dropZoneEl.removeAllListeners();
			this.dropZoneEl.remove();
		}
	}

	,getTarget: function(e){
		te = this.owner.getEl();
		ce = e.getTarget('.x-grid3-row');
		if(!Ext.isEmpty(ce)){
			 ce = Ext.get(ce);
			 if(te.contains(ce)) te = ce;
		}
		return te;
	}
	,getTargetData: function(e){
		te = this.getTarget(e);
		this.targetId = null;
		this.targetPath = null;
		if(te.hasClass('x-grid3-row')){
			ridx = this.owner.getView().findRowIndex(te.dom);
			if(ridx >=0 ){
				r = this.owner.store.getAt(ridx);
				this.targetId = r.get(this.pidPropety);
				this.targetPath = r.get('path')+r.get('name')+'/';
			}
		}else{
			cmp = Ext.getCmp(te.id);
			this.targetId = cmp.getProperty(this.pidPropety);
			this.targetPath = cmp.getProperty('pathtext');
		}
	}
	,onDragEnter: function(e){ // dataTransfer info is not available on drag enter, it's only available on drop
		this.getTarget(e).addClass('drop-target');
	}
	,onDragLeave: function(e){ // dataTransfer info is not available on drag enter, it's only available on drop
		te = this.getTarget(e);
		te.removeClass('drop-target');
	}
	,onDragOver: function(e, el, o){
		e.browserEvent.dataTransfer.dropEffect = 'copy';
		
		te = this.getTarget(e);
		if(Ext.isEmpty(te)) return false;
		te.addClass('drop-target');
		if(this.lastEl == te) return true;
		
		if(!Ext.isEmpty(this.lastEl)) this.lastEl.removeClass('drop-target');
		
		
		this.lastEl = te;
		
		return true;
	}
	,onDrop: function(e) {
		this.onDragLeave(e);

		if(this.filesCount(e) < 1) return false;
		
		this.getTargetData(e);

		e.stopPropagation();
		e.preventDefault();
		this.hideDropZone();
		this.getRecursiveFileList(e);
	}
	,getRecursiveFileList: function(e){
		dt = e.browserEvent.dataTransfer;
		var length = dt.items.length;
		entries = []
		for (var i = 0; i < length; i++) {
			entries.push( dt.items[i].webkitGetAsEntry() );
		}
		Ext.ux.WebkitEntriesIterator.iterateEntries(entries, this.processGetRecursiveFileList, this);
		return 0;
	}
	,processGetRecursiveFileList: function(filesArray){
		/* adding dorpped files to queue */
		App.addFilesToUploadQueue(filesArray, {
			pid: this.targetId
			,pathtext: this.targetPath
		});
		return true;

	}
	,addFilesToQueue: function(e, targetPid){

	}

	,filesCount: function(e){
		var files = e.browserEvent.dataTransfer.files; // FileList object.
		if(Ext.isEmpty(files)) return 0;
		for (var i = 0, f; f = files[i]; i++){}
		return i;
	}
	,showDropZone: function(e){
		el = this.owner.getEl();
		if( Ext.isEmpty(el.dom) ){
			this.onBeforeDestroy();
			return;
		}
		if( !el.isVisible(true) ) return;
		
		if(!this.dropZoneEl){
			this.dropZoneEl = this.owner.getEl().appendChild(document.createElement('div'));
			this.dropZoneEl.update(this.dropZoneConfig.text);
			this.dropZoneEl.on('dragenter', function(e, el){Ext.get(el).addClass('grid-drop-zone-over')});
			this.dropZoneEl.on('dragleave', function(e, el){Ext.get(el).removeClass('grid-drop-zone-over')});

			this.dropZoneEl.addClass('grid-drop-zone');
		}
		this.dropZoneEl.applyStyles("display:block");
	}
	,hideDropZone: function(e){
		if(this.dropZoneEl){
			this.dropZoneEl.applyStyles("display:none");
			this.dropZoneEl.removeClass('grid-drop-zone-over');
		}
	}
});

Ext.ComponentMgr.registerPlugin('CBPluginsFilesDropZone', CB.plugins.FilesDropZone);