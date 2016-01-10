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
        var a = this.getEl().query('a.click');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,function(ev, el){

                        el = Ext.get(el);
                        if(el) {
                            if(el.hasCls('link-type-grid')) {
                                App.openPath(el.getAttribute('path'));
                            }
                        }
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

        a = this.getEl().query('a.item-action');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,this.onItemActionClick
                    ,this
                );
            }
            ,this
        );
    }

    ,onItemActionClick: function(ev, el){
        var action = 'onAction'
            + Ext.String.capitalize(el.attributes.getNamedItem('action').value)
            + 'Click';

        if(this[action]) {
            this[action](ev, el);
        }
    }

    ,onActionCloseClick: function(ev, el) {
        this.getEl().mask(L.CompletingTask + ' ...', 'x-mask-loading');
        CB_Tasks.close(this.params.id, this.onItemChange, this);
    }

    ,onActionReopenClick: function(ev, el) {
        this.getEl().mask(L.ReopeningTask + ' ...', 'x-mask-loading');
        CB_Tasks.reopen(this.params.id, this.onItemChange, this);
    }

    ,onActionCompleteClick: function(ev, el) {
        CB_Tasks.complete(
            {
                id: this.params.id
                ,message: ''
            }
            ,this.onItemChange
            ,this
        );
    }

    ,onActionMarkcompleteClick: function(ev, el) {
        this.forUserId = el.attributes.getNamedItem('uid').value;
        CB_Tasks.setUserStatus(
            {
                id: this.params.id
                ,user_id: this.forUserId
                ,status: 1
                ,message: ''
            }
            ,this.onItemChange
            ,this
        );
    }

    ,onActionMarkincompleteClick: function(ev, el) {
        this.forUserId = el.attributes.getNamedItem('uid').value;
        CB_Tasks.setUserStatus(
            {
                id: this.params.id
                ,user_id: this.forUserId
                ,status: 0
                ,message: ''
            }
            ,this.onItemChange
            ,this
        );
    }
    ,onActionUpdateSolrDataClick: function(ev, el) {
        CB_Templates.updateSolrData(
            this.params.id
            ,this.onItemChange
            ,this
        );
    }

    ,onItemChange: function(r, e){
        this.getEl().unmask();
        App.fireEvent('objectchanged', this.params, this);
    }

    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {}
        };

        return rez;
    }

});
