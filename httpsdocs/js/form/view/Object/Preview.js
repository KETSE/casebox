Ext.namespace('CB.form.view.object');

CB.form.view.object.Preview = Ext.extend(Ext.Panel, {
    xtype: 'panel'
    ,autoScroll: true
    ,html: ''
    ,tbarCssClass: 'x-panel-white'
    ,loadMask: false
    ,padding: 0
    ,width: 300
    ,layout: 'fit'
    ,initComponent: function(){
        Ext.apply(this, {
            listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });
        CB.form.view.object.Preview.superclass.initComponent.apply(this, arguments);
    }

    ,onAfterRender: function(){
        this.getUpdater().showLoading = Ext.emptyFn;
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
        this.newVersionId = Ext.value(versionId, '');
        // if( (this.newId != this.data.id) || (this.newVersionId != this.loadedVersionId) ) {
        this.delayReload(50);
        // }
    }

    ,delayReload: function(ms){
        if(!this.delayedReloadTask) {
            this.delayedReloadTask = new Ext.util.DelayedTask(this.reload, this);
        }
        this.delayedReloadTask.delay(Ext.value(ms, 50), this.reload, this);

    }

    ,reload: function(){
        if(Ext.isEmpty(this.newId) || isNaN(this.newId) || !this.getEl().isVisible(true)) {
            return this.clear();
        }
        this.doLoad(this.newId, this.newVersionId);
    }
    ,doLoad: function(id, vId) {
        this.load({
            url: '/preview/'+ id +'_' + vId + '.html'
            ,callback: this.processLoad
            ,scope: this // optional scope for the callback
            ,discardUrl: false
            ,nocache: true
            // ,text: L.Loading
            ,scripts: false

        });
    }

    ,processLoad: function(el, success, r, e){
        this.data = {id: this.newId};
        this.loadedVersionId = this.newVersionId;
        this.body.scrollTo('top', 0);
        switch(r.responseText){
            case '&#160':
                this.update('<div style="margin: 10px" class="icon-padding icon-loading">'+L.processing+' ...</div>');
                this.delayReload();
                break;
            case 'PDF':
                elId = this.body.id;
                success = new PDFObject({ url: "/download.php?pw=&amp;id="+this.data.id }).embed(elId);
                break;
        }
        this.attachEvents();
    }

    ,attachEvents: function(){
        a = this.getEl().query('a.locate');
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

        a = this.getEl().query('a.task');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        App.mainViewPort.fireEvent(
                            'taskedit'
                            ,{
                                data: {
                                    id: el.attributes.getNamedItem('nid').value
                                }
                            }
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
    }
    ,onTaskChanged: function(r, e){
        this.getEl().unmask();
        this.reload();
        App.mainViewPort.fireEvent('taskupdated', this, e);
    }

    ,clear: function(){
        delete this.data;
        delete this.loadedVersionId;
        this.update('<div class="x-preview-mask">Select an item for preview</div>');
        if(this.getEl().isVisible(true)) this.body.scrollTo('top', 0);
    }

});

Ext.reg('CBObjectPreview', CB.form.view.object.Preview);
