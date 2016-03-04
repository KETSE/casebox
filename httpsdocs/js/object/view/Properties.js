Ext.namespace('CB.form.view.object');

Ext.define('CB.object.view.Properties', {
    extend: 'CB.plugin.Panel'

    ,alias: 'widget.CBObjectProperties'


    ,initComponent: function(){
        this.callParent(arguments);

        this.on('timespentclick', this.onTimeSpentClick, this);
        this.on('addtimespentclick', this.onAddTimeSpentClick, this);
    }

    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {
                reload: {order: 12, addDivider: 'top'}
                ,rename: {order: 13}
                ,permalink: {order: 14}
                ,setOwner: {order: 15}
                ,permissions: {order: 16}
                ,'delete': {order: 20, addDivider: 'top'}
            }
        };

        var objType = '';
        if(!Ext.isEmpty(this.loadedParams)) {
            objType = CB.DB.templates.getType(this.loadedParams.template_id);
            if((objType !== 'file') || detectFileEditor(this.loadedParams.name)) {
                rez.tbar['edit'] = {};
            }
        }
        rez.tbar['openExternal'] = {};

        if(!Ext.isEmpty(this.loadedParams)) {
            switch(objType) {
                case 'file':
                    rez.tbar['download'] = {};
                    rez.tbar['preview'] = {};
                    break;
                case 'search':
                    rez.tbar['search'] = {};
                    break;
            }
        }

        this.items.each(
            function(i) {
                var pi = i.getContainerToolbarItems();

                if(pi.tbar) {
                    rez.tbar = Ext.apply(rez.tbar, pi.tbar);
                }
                if(pi.menu) {
                    /* adding dividesrs for first item of each plugin */
                    var isFirstItem = true;
                    Ext.iterate(
                        pi.menu
                        ,function(key, value){
                            if(isFirstItem) {
                                value.addDivider = 'top';
                                isFirstItem = false;
                            } else {
                                return false;
                            }
                        }
                        ,this
                    );

                    rez.menu = Ext.apply(rez.menu, pi.menu);
                }
            }
            ,this
        );

        // sort menu elements by order property
        var a = []
            ,sortedMenu = {};

        Ext.iterate(
            rez.menu
            ,function(k, v, o) {
                var order = Ext.valueFrom(v.order, 0);
                if(!Ext.isDefined(a[order])) {
                    a[order] = {};
                }
                a[order][k] = v;
            }
            ,this
        );

        for (var i = 0; i < a.length; i++) {
            Ext.apply(sortedMenu, a[i]);
        }

        rez.menu = sortedMenu;

        return rez;
    }

    ,setSelectedVersion: function(params) {
        this.items.each(
            function(i) {
                if(i.setSelectedVersion) {
                    i.setSelectedVersion(params);
                }
            }
            ,this
        );
    }

    ,onLoadData: function(r, e) {
        this.update('');
        this.callParent(arguments);

        var ttp = this.down('CBObjectPluginTimeTracking');

        if(!Ext.isEmpty(ttp)) {
            ttp.hide();
        }
    }

    ,clear: function(){
        this.callParent(arguments);
        this.update('<div class="x-preview-mask">' + L.SelectPreviewItem + '</div>');
    }

    ,getCommentComponent: function() {
        return this.down('textarea[cls=comment-input]');
    }

    ,getCommentValue: function() {
        var ci = this.getCommentComponent();

        if(Ext.isEmpty(ci)) {
            return '';
        }

        return Ext.valueFrom(ci.getValue(), '');
    }

    ,setCommentValue: function(value) {
        var ci = this.getCommentComponent();

        if(!Ext.isEmpty(ci)) {
            ci.setValue(value);
        }
    }

    ,onTimeSpentClick: function(cmp) {
        var ttp = this.down('CBObjectPluginTimeTracking');

        if(Ext.isEmpty(ttp)) {
            return;
        }
        if(!ttp.getEl().isVisible(true)) {
            ttp.show();
            this.updateLayout();
            ttp.getEl().scrollIntoView(this.body, false, false, true);
            this.body.scrollBy(0, 40, false);
            ttp.focus(false, 100);
        } else {
            ttp.getEl().scrollIntoView(this.body, false, false, true);
        }
    }

    ,onAddTimeSpentClick: function(cmp, e) {
        var ttp = this.down('CBObjectPluginTimeTracking');

        if(Ext.isEmpty(ttp)) {
            return;
        }

        ttp.onAddClick(cmp, e);
    }
});
