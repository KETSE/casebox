Ext.namespace('CB.plugins');
Ext.define('CB.plugins.FilesDropZone', {
    extend: 'Ext.util.Observable'
    ,alias: 'plugin.CBPluginsFilesDropZone'
    ,pidPropety: 'nid'

    ,constructor: function(config){
        Ext.apply(this, {
            dropZoneConfig:{
                text: 'Drop files here'
            }
        });

        if(config) {
            Ext.apply(this, config);
        }
    }

    ,init: function(owner) {
        this.owner = owner;
        owner.on('render', this.onRender, this);
        if(owner.dropZoneConfig) {
            Ext.apply(this.dropZoneConfig, owner.dropZoneConfig);
        }
    }

    ,onRender: function(grid){
        el = grid.getEl();
        el.on('dragleave', this.onDragLeave, this);
        el.on('dragover', this.onDragOver, this);
        el.on('drop', this.onDrop, this);
        App.on('dragfilesenter', this.showDropZone, this);
        App.on('dragfilesover', this.showDropZone, this);
        App.on('dragfilesleave', this.hideDropZone, this);
        App.on('filesdrop', this.hideDropZone, this);
    }

    ,onBeforeDestroy: function(){
        App.un('dragfilesenter', this.showDropZone, this);
        App.un('dragfilesover', this.showDropZone, this);
        App.un('dragfilesleave', this.hideDropZone, this);
        App.un('filesdrop', this.hideDropZone, this);
        if(this.dropZoneEl){
            this.dropZoneEl.removeAllListeners();
            this.dropZoneEl.remove();
        }
    }

    ,getTarget: function(e){
        var te = this.owner.getEl();
        var ce = e.getTarget('.x-grid-row');
        if(!Ext.isEmpty(ce)){
             ce = Ext.get(ce);
             if(te.contains(ce)) {
                te = ce;
            }
        }

        return te;
    }

    ,getTargetData: function(e){
        var te = this.getTarget(e);
        this.targetId = null;
        this.targetPath = null;
        if(te.hasCls('x-grid-row')){
            var rel = this.owner.findTargetByEvent(e);
            var rec = this.owner.getRecord(rel);
            if(rec){
                this.targetId = rec.get(this.pidPropety);
                this.targetPath = rec.get('path') + rec.get('name')+'/';
            }
        } else {
            var cmp = Ext.getCmp(te.id);
            if(cmp.grid && !Ext.isDefined(cmp.getProperty)) {
                cmp = cmp.grid;
            }
            this.targetId = cmp.getProperty(this.pidPropety);
            this.targetPath = cmp.getProperty('pathtext');
        }
    }

    ,onDragEnter: function(e){ // dataTransfer info is not available on drag enter, it's only available on drop
        this.getTarget(e).addCls('drop-target');
    }

    ,onDragLeave: function(e){ // dataTransfer info is not available on drag enter, it's only available on drop
        te = this.getTarget(e);
        te.removeCls('drop-target');
    }

    ,onDragOver: function(e, el, o){
        e.browserEvent.dataTransfer.dropEffect = 'copy';

        var te = this.getTarget(e);
        if(Ext.isEmpty(te)) {
            return false;
        }
        te.addCls('drop-target');

        if(this.lastEl == te) {
            return true;
        }

        if(!Ext.isEmpty(this.lastEl)) {
            this.lastEl.removeCls('drop-target');
        }

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

        if( Ext.isEmpty(dt.items) ) return this.processGetRecursiveFileList(dt.files);

        var length = dt.items.length;
        var entries = [];
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
        var el = this.owner.getEl();

        if(Ext.isEmpty(el) || Ext.isEmpty(el.dom) ){
            this.onBeforeDestroy();
            return;
        }
        if( !el.isVisible(true) ) {
            return;
        }

        if(!this.dropZoneEl){
            this.dropZoneEl = this.owner.getEl().appendChild(document.createElement('div'));
            this.dropZoneEl.addCls('desktop-drop-zone');
            this.dropZoneEl.update(this.dropZoneConfig.text);
            this.dropZoneEl.on('dragenter', function(e, el){Ext.get(el).addCls('grid-drop-zone-over');});
            this.dropZoneEl.on('dragleave', function(e, el){Ext.get(el).removeCls('grid-drop-zone-over');});

            this.dropZoneEl.addCls('grid-drop-zone');
        }
        this.dropZoneEl.applyStyles("display:block");
    }

    ,hideDropZone: function(e){
        var a = Ext.query('.desktop-drop-zone');
        if(!Ext.isEmpty(a)) {
            for (var i = a.length - 1; i >= 0; i--) {
                a = Ext.get(a);
                a.applyStyles("display:none");
                a.removeCls('grid-drop-zone-over');
            }
        }
        // if(this.dropZoneEl){
        //     this.dropZoneEl.applyStyles("display:none");
        //     this.dropZoneEl.removeCls('grid-drop-zone-over');
        // }
    }
});
