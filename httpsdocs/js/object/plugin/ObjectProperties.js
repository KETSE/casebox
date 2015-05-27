Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.ObjectProperties', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginObjectProperties'

    ,initComponent: function(){
        Ext.apply(this, {
            html: ''
            ,cls: ''
            ,bodyStyle: 'margin-bottom: 30px'
            ,listeners: {
                scope: this
                ,afterrender: this.attachEvents
            }
        });

        this.callParent(arguments);
    }

    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }

        Ext.apply(this.params, r.data);

        var html = Ext.isArray(r.data.preview)
            ? r.data.preview.join(' ')
            : r.data.preview[0];

        if(this.rendered) {
            this.update(html);
        } else {
            this.html = html;
        }
    }

    ,attachEvents: function(){
        a = this.getEl().query('a.locate');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        this.fireEvent('openproperties', {
                            id: el.attributes.getNamedItem('nid').value
                            ,template_id: el.attributes.getNamedItem('template_id').value
                            ,path: el.attributes.getNamedItem('path').value
                        });
                    }
                    ,this
                );
            }
            ,this
        );


        a = this.getEl().query('a.obj-ref');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        this.openObjectProperties({
                            id: el.attributes.itemid.value
                            ,template_id: el.attributes.templateid.value
                        });
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
                            this.params.id
                            ,el.attributes.getNamedItem('path').value
                        );
                    }
                    ,this
                );
            }
            ,this
        );

        a = this.getEl().query('a.task-action');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){
                        switch(el.attributes.getNamedItem('action').value) {
                            case 'close':
                                this.getEl().mask(L.CompletingTask + ' ...', 'x-mask-loading');
                                CB_Tasks.close(this.params.id, this.onTaskChanged, this);
                                break;
                            case 'reopen':
                                this.getEl().mask(L.ReopeningTask + ' ...', 'x-mask-loading');
                                CB_Tasks.reopen(this.params.id, this.onTaskChanged, this);
                                break;
                            case 'complete':
                                CB_Tasks.complete(
                                    {
                                        id: this.params.id
                                        ,message: ''
                                    }
                                    ,this.onTaskChanged
                                    ,this
                                );
                                break;
                            case 'markcomplete':
                                this.forUserId = el.attributes.getNamedItem('uid').value;
                                CB_Tasks.setUserStatus(
                                    {
                                        id: this.params.id
                                        ,user_id: this.forUserId
                                        ,status: 1
                                        ,message: ''
                                    }
                                    ,this.onTaskChanged
                                    ,this
                                );
                                break;
                            case 'markincomplete':
                                this.forUserId = el.attributes.getNamedItem('uid').value;
                                CB_Tasks.setUserStatus(
                                    {
                                        id: this.params.id
                                        ,user_id: this.forUserId
                                        ,status: 0
                                        ,message: ''
                                    }
                                    ,this.onTaskChanged
                                    ,this
                                );
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
        App.fireEvent('objectchanged', this.params, this);
    }

    ,getContainerToolbarItems: function() {
        rez = {
            tbar: {}
            ,menu: {}
        };

        /*if(this.params) {
            if(this.params.can) {
                if(this.params.can.complete) {
                    rez['tbar']['completetask'] = {};
                }
                if(this.params.can.close) {
                    rez['menu']['closetask'] = {order: 2};
                }
                if(this.params.can.reopen) {
                    rez['menu']['reopentask'] = {order: 3};
                }
            }
        }/**/

        return rez;
    }

});
