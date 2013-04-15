Ext.namespace('CB');

CB.PreviewPanel = Ext.extend(Ext.Panel, {
	xtype: 'panel'
	,autoScroll: true
	,html: ''
	,tbarCssClass: 'x-panel-white'
	,loadMask: true
	,padding:0
	,layout: 'fit'
	,initComponent: function(){
		CB.PreviewPanel.superclass.initComponent.apply(this, arguments);
	}

	,loadPreview: function(id, versionId){
		if(this.delayedReloadTask) this.delayedReloadTask.cancel();
		this.newId = id
		this.newVersionId = Ext.value(versionId, '');
		if( (this.newId != this.loadedId) || (this.newVersionId != this.loadedVersionId) ) this.delayReload(300);
	}
	,delayReload: function(ms){
		if(!this.delayedReloadTask) this.delayedReloadTask = new Ext.util.DelayedTask(this.reload, this);
		this.delayedReloadTask.delay(Ext.value(ms, 3000), this.reload, this); 

	}
	,reload: function(){
		if(Ext.isEmpty(this.newId) || isNaN(this.newId) || !this.getEl().isVisible(true)) return this.clear();
		this.load({
			url: '/preview/'+this.newId+'_'+this.newVersionId+'.html'
			,callback: this.processLoad
			,scope: this // optional scope for the callback
			,discardUrl: false
			,nocache: true
			,text: L.Loading
			,scripts: false
		})
	}
	,processLoad: function(el, success, r, e){
		this.loadedId = this.newId;
		this.loadedVersionId = this.newVersionId;
		this.body.scrollTo('top', 0);
		switch(r.responseText){
			case '&#160':
				this.update('<div style="margin: 10px" class="icon-padding icon-loading">'+L.processing+' ...</div>');
				this.delayReload();
				break;
			case 'PDF':
				elId = this.body.id;
				var success = new PDFObject({ url: "/download.php?pw=&amp;id="+this.loadedId }).embed(elId);
				break;
		}
		this.attachEvents();
	}
	,attachEvents: function(){
		a = this.getEl().query('a.locate');
		Ext.each(a, function(t){Ext.get(t).addListener('click', function(ev, el){ App.mainViewPort.openPath(el.attributes.getNamedItem('path').value, el.attributes.getNamedItem('nid').value) }, this)}, this)
		a = this.getEl().query('a.task');
		Ext.each(a, function(t){Ext.get(t).addListener('click', function(ev, el){ App.mainViewPort.fireEvent('taskedit', {data: {id: el.attributes.getNamedItem('nid').value}}) }, this)}, this)
		a = this.getEl().query('a.path');
		Ext.each(a, function(t){Ext.get(t).addListener('click', function(ev, el){ App.mainViewPort.openPath(el.attributes.getNamedItem('path').value, this.loadedId) }, this)}, this)
		a = this.getEl().query('.file-unknown a');
		Ext.each(a, function(t){Ext.get(t).addListener('click', function(ev, el){ App.mainViewPort.fireEvent('fileopen', {id: el.attributes.getNamedItem('nid').value} ) }, this)}, this)
	}
	,clear: function(){
		delete this.loadedId;
		delete this.loadedVersionId;
		this.update('');
		if(this.getEl().isVisible(true)) this.body.scrollTo('top', 0);
	}

})

Ext.reg('CBPreviewPanel', CB.PreviewPanel);
