Ext.namespace('CB.objects.plugins');

CB.objects.plugins.ObjectProperties = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){
        Ext.apply(this, {
            html: ''
            ,listeners: {
                scope: this
                ,afterrender: this.attachEvents
            }
        });
        CB.objects.plugins.ObjectProperties.superclass.initComponent.apply(this, arguments);
    }

    ,onLoadData: function(r, e) {
        if(this.rendered) {
            this.update(r.data.html);
        } else {
            this.html = r.data.html;
        }
    }

    ,attachEvents: function(){
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
                                            CB_Tasks.close(this.params.id, this.onTaskChanged, this);
                                        }
                                    }
                                    ,scope: this
                                });
                                break;
                            case 'reopen':
                                Ext.Msg.confirm( L.ReopeningTask, L.ReopenTaskConfirmationMsg, function(b){
                                        if(b == 'yes'){
                                            this.getEl().mask(L.ReopeningTask + ' ...', 'x-mask-loading');
                                            CB_Tasks.reopen(this.params.id, this.onTaskChanged, this);
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
                                                    id: this.params.id
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
                                                    id: this.params.id
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
                                                    id: this.params.id
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

    }
    ,onTaskChanged: function(r, e){
        this.getEl().unmask();
        App.fireEvent('objectchanged', this.params);
    }
});

Ext.reg('CBObjectsPluginsObjectProperties', CB.objects.plugins.ObjectProperties);
