Ext.namespace('CB.form.view.object');

Ext.define('CB.object.view.Preview', {
    extend: 'Ext.Panel'
    ,alias: 'widget.CBObjectPreview'
    ,scrollable: true
    ,html: ''
    ,tbarCssClass: 'x-panel-white'
    ,loadMask: false
    ,padding: 0
    ,width: 300
    ,layout: 'fit'
    ,fitImagePreview: true
    ,loader: {
        autoLoad: false
    }

    ,initComponent: function(){
        Ext.apply(this, {
            listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });

        this.callParent(arguments);

        App.on('objectchanged', this.onObjectChanged, this);
    }

    ,onAfterRender: function(){
        // this.getUpdater().showLoading = Ext.emptyFn;
    }

    ,loadPreview: function(id, versionId){
        var el = this.getEl();
        if(Ext.isEmpty(el) || !el.isVisible(true)) {
            return;
        }
        if(this.delayedReloadTask) {
            this.delayedReloadTask.cancel();
        }
        this.newId = id;
        this.newVersionId = Ext.valueFrom(versionId, '');
        this.delayReload(100);
    }

    ,delayReload: function(ms){
        if(!this.delayedReloadTask) {
            this.delayedReloadTask = new Ext.util.DelayedTask(this.reload, this);
        }
        this.delayedReloadTask.delay(Ext.valueFrom(ms, 1000), this.reload, this);

    }

    ,reload: function(){
        var el = this.getEl();

        if(Ext.isEmpty(this.newId) || isNaN(this.newId) || !el || !el.isVisible(true)) {
            return this.clear();
        }

        this.doLoad(this.newId, this.newVersionId);
    }

    ,doLoad: function(id, vId) {

        this.loader.load({
            url: '/' + App.config.coreName + '/view/'+ id +'_' + vId + '/?i=1'
            ,callback: this.processLoad
            ,scope: this // optional scope for the callback
            ,discardUrl: false
            ,nocache: true
            ,scripts: false

        });
    }

    ,processLoad: function(el, success, r, e){
        this.data = {id: this.newId};
        this.loadedVersionId = this.newVersionId;
        this.body.scrollTo('top', 0);
        switch(r.responseText){
            case '<authenticate />':
                window.location.reload();
                break;
            case '&#160':
                this.update(
                    '<div style="margin-top: 40px; text-align:center; color: 555; font-weight: bold">'+
                    '<img src="'+Ext.BLANK_IMAGE_URL+'" class="i16 d-loader" style="vertical-align:middle; margin-right: 5px"> '+L.generatingPreview+' &hellip; </div>'
                );
                this.delayReload();
                break;
            case 'PDF':
                var url = '/' + App.config.coreName + "/download/" + this.data.id + "/?pw=";
                this.update(
                    '<object data="' + url + '" type="application/pdf" width="100%" height="100%">' +
                    'alt : <a href="' + url + '">' + this.data.name + '</a></object>'
                );
                break;
        }
        this.attachEvents();
        this.fireEvent('loaded', this);
        if(this.params) {
            switch(detectFileEditor(this.params.name)) {
                 // case 'html':
                 case 'text':
                    hljs.highlightBlock(this.body.dom);
                    break;
            }
        }
    }

    ,attachEvents: function(){
        var a = this.getEl().query('a.locate');

        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        App.locateObject(
                            el.attributes.getNamedItem('nid').value
                            ,el.attributes.getNamedItem('path').value
                        );
                    }
                    ,this
                );
            }
            ,this
        );

        a = this.getEl().query('a.taskA');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        switch(el.attributes.getNamedItem('action').value) {
                            case 'close':
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
                                            CB_Tasks.close(this.data.id, this.onTaskChanged, this);
                                        }
                                    }
                                    ,scope: this
                                });
                                break;
                            case 'reopen':
                                Ext.Msg.confirm( L.ReopeningTask, L.ReopenTaskConfirmationMsg, function(b){
                                        if(b == 'yes'){
                                            this.getEl().mask(L.ReopeningTask + ' ...', 'x-mask-loading');
                                            CB_Tasks.reopen(this.data.id, this.onTaskChanged, this);
                                        }
                                    }
                                    ,this
                                );
                                break;
                            case 'complete':
                                Ext.Msg.show({
                                    title: L.CompletingTask
                                    ,msg: L.Message
                                    ,width: 400
                                    ,height: 200
                                    ,buttons: Ext.MessageBox.OKCANCEL
                                    ,multiline: true
                                    ,fn: function(b, message){
                                        if(b == 'ok') {
                                            CB_Tasks.complete(
                                                {
                                                    id: this.data.id
                                                    ,message: message
                                                }
                                                ,this.onTaskChanged
                                                ,this
                                            );
                                        }
                                    }
                                    ,scope: this
                                });
                                break;
                            case 'markcomplete':
                                this.forUserId = el.attributes.getNamedItem('uid').value;
                                Ext.Msg.show({
                                    title: L.SetCompleteStatusFor+ ' ' + CB.DB.usersStore.getName(this.forUserId)
                                    ,msg: L.Message
                                    ,width: 400
                                    ,height: 200
                                    ,buttons: Ext.MessageBox.OKCANCEL
                                    ,multiline: true
                                    ,fn: function(b, message){
                                        if(b == 'ok') {
                                            CB_Tasks.setUserStatus(
                                                {
                                                    id: this.data.id
                                                    ,user_id: this.forUserId
                                                    ,status: 1
                                                    ,message: message
                                                }
                                                ,this.onTaskChanged
                                                ,this
                                            );
                                        }
                                    }
                                    ,scope: this
                                });
                                break;
                            case 'markincomplete':
                                this.forUserId = el.attributes.getNamedItem('uid').value;
                                Ext.Msg.show({
                                    title: L.SetIncompleteStatusFor + CB.DB.usersStore.getName(this.forUserId)
                                    ,msg: L.Message
                                    ,width: 400
                                    ,height: 200
                                    ,buttons: Ext.MessageBox.OKCANCEL
                                    ,multiline: true
                                    ,fn: function(b, message){
                                        if(b == 'ok') {
                                            CB_Tasks.setUserStatus(
                                                {
                                                    id: this.data.id
                                                    ,user_id: this.forUserId
                                                    ,status: 0
                                                    ,message: message
                                                }
                                                ,this.onTaskChanged
                                                ,this
                                            );
                                        }
                                    }
                                    ,scope: this
                                });
                                break;
                        }
                    }
                    ,this
                );
            }
            ,this
        );

        a = this.getEl().query('a.path');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        App.locateObject(
                            this.data.id
                            ,el.attributes.getNamedItem('path').value
                        );
                    }
                    ,this
                );
            }
            ,this
        );
        a = this.getEl().query('.file-unknown a');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        App.mainViewPort.fireEvent(
                            'fileopen'
                            ,{
                                id: el.attributes.getNamedItem('nid').value
                            }
                        );
                    }
                    ,this
                );
            }
            ,this
        );

        //detect if it's a image preview
        this.viewingImage = null;
        a = this.getEl().query('img.fit-img');
        Ext.each(
            a
            ,function(t){
                this.viewingImage = Ext.get(t);
                if(!this.fitImagePreview) {
                    this.viewingImage.dom.setAttribute('class', '');
                }
            }
            ,this
        );
    }

    ,onTaskChanged: function(r, e){
        this.getEl().unmask();
        this.reload();
        App.fireEvent('objectchanged', this.data, this);
        // App.mainViewPort.fireEvent('taskupdated', this, e);
    }

    ,clear: function(){
        delete this.data;
        delete this.loadedVersionId;

        this.update('<div class="x-preview-mask">' + L.SelectPreviewItem + '</div>');

        if(this.getEl().isVisible(true)) {
            this.body.scrollTo('top', 0);
        }
    }

    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {
            }
            ,menu: {
                reload: {}
            }
        };

        if(this.params) {
            rez.tbar['openExternal'] = {};

            if(CB.DB.templates.getType(this.params.template_id) == 'file') {
                if(this.viewingImage) {
                    rez.tbar['fitImage']  = {
                        allowToggle: true
                        ,pressed: this.fitImagePreview
                    };
                } else if(detectFileEditor(this.params.name)) {
                    rez.tbar['edit']  = {};
                }

                rez.tbar['download']  = {};
                rez.tbar['preview']  = {};

                rez.menu['delete'] = { addDivider: 'top' };
                rez.menu['webdavlink']  = { addDivider: 'top' };
                rez.menu['permalink']  = {};
            }
        }

        return rez;
    }

    ,onObjectChanged: function(data, component) {
        if(!isNaN(data)) {
            data = {id: data};
        }
        if(!Ext.isEmpty(this.data)) {
            if(data.id == this.data.id) {
                this.reload();
            }
        }
    }

    ,onFitImageClick: function(b, e) {
        if(this.viewingImage) {
            if(this.fitImagePreview) {
                this.fitImagePreview = false;
                this.viewingImage.dom.setAttribute('class', '');
            } else {
                this.fitImagePreview = true;
                this.viewingImage.dom.setAttribute('class', 'fit-img');
            }
        }
    }
});
