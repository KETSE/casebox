Ext.namespace('CB.form.view.object');

Ext.define('CB.object.view.Properties', {
    extend: 'CB.plugin.Panel'

    ,alias: 'widget.CBObjectProperties'

    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {
                reload: {order: 12, addDivider: 'top'}
                ,rename: {order: 13}
                ,permissions: {order: 14}
                ,'delete': {order: 20, addDivider: 'top'}
            }
        };

        var objType = '';
        if(!Ext.isEmpty(this.loadedParams)) {
            objType = CB.DB.templates.getType(this.loadedParams.template_id);
            if((objType != 'file') || detectFileEditor(this.loadedParams.name)) {
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
    }

    ,clear: function(){
        this.callParent(arguments);
        this.update('<div class="x-preview-mask">' + L.SelectPreviewItem + '</div>');
    }

});
