Ext.namespace('CB.Favorites');

Ext.define('CB.Favorites.Panel', {
    extend: 'Ext.panel.Panel'

    ,xtype: 'CBFavoritesPanel'

    ,constructor: function(config){
        this.store = new Ext.data.DirectStore({
            autoLoad: true
            ,autoDestroy: true
            ,autoSave: true
            ,model: 'FavoriteRecord'
            ,proxy: {
                type: 'direct'
                ,paramsAsHash: true
                ,directFn: CB_Favorites.read
                ,reader: {
                    type: 'json'
                    ,successProperty: 'success'
                    ,idProperty: 'id'
                    ,rootProperty: 'data'
                    ,messageProperty: 'msg'
                }
            }
        });

        this.callParent(arguments);

        this.actions = {
            browse: new Ext.Action({
                text: L.Browse
                ,scope: this
                ,handler: this.onBrowseClick
            })

            ,edit: new Ext.Action({
                text: L.Edit
                ,scope: this
                ,handler: this.onEditClick
            })

            ,unstar: new Ext.Action({
                text: L.Unstar
                ,iconCls: 'i-unstar'
                ,scope: this
                ,handler: this.onUnstarClick
            })
        };

    }


    ,initComponent: function () {

        Ext.apply(this, {
            layout: 'fit'
            ,border: false
            ,iconCls: 'icon-fav'
            ,tabConfig: {
                tooltip: L.Favorites
            }
            ,items: [
                {
                    xtype: 'grid'
                    ,store: this.store
                    ,hideHeaders: true
                    ,bodyStyle: 'border-width: 0'
                    ,viewConfig: {
                        loadMask: false
                    }
                    ,columns: [
                        {
                            text: 'Name'
                            ,dataIndex: 'data'
                            ,flex: 1
                            ,renderer: function(v, m, r) {
                                m.css = 'icon-grid-column-top ' + Ext.valueFrom(v.iconCls, '');
                                //set path as title attribute
                                App.customRenderers.titleAttribute(v.pathText, m);

                                var rez = '<span class="n">' + v.name + '</span>';

                                return rez;
                            }
                        }
                    ]
                    ,listeners:{
                        scope: this
                        ,itemcontextmenu: this.onItemContextMenu
                        ,itemclick: this.onItemClick
                    }
                }
            ]
        });

        this.callParent(arguments);
    }

    ,isStarred: function(nodeId) {
        var rez = this.store.findExact('node_id', String(nodeId));

        return (rez >= 0);
    }

    ,setStarred: function(data){
        if(!this.isStarred(data.id)) {
            Ext.Msg.prompt(
                L.Star
                ,L.SetStarNameMsg
                ,function(b, name) {
                    if(b === 'ok') {
                        data.name = name;

                        var d = {
                            node_id: data.id
                            ,data: data
                        };

                        CB_Favorites.create(
                            d
                            ,this.processSetStarred
                            ,this
                        );
                    }
                }
                ,this
                ,false
                ,data.name
            );
        } else {
            Ext.Msg.alert(
                L.Star
                ,L.AlreadyStarred
            );
        }
    }

    ,processSetStarred: function(r, e) {
        if(!r || (r.success !== true)) {
            return;
        }

        r.data.node_id = String(r.data.node_id);

        var rec = Ext.create(
            this.store.getModel().getName()
            ,r.data
        );

        this.store.add(rec);
        this.fireEvent('change', this);
    }

    ,setUnstarred: function(nodeId){
        if(this.isStarred(nodeId)) {
            CB_Favorites['delete'](
                nodeId
                ,this.processSetUnstarred
                ,this
            );
        }
    }

    ,processSetUnstarred: function(r, e){
        if (!r || (r.success !== true)) {
            return;
        }

        var s = this.store
            ,idx = s.findExact('node_id', String(r.node_id));

        s.removeAt(idx);
        this.fireEvent('change', this);
    }

    ,onItemContextMenu: function(grid, record, item, index, e, eOpts) {
        if(Ext.isEmpty(this.contextMenu)){
            this.contextMenu = new Ext.menu.Menu({
                items: [
                    this.actions.browse
                    ,this.actions.edit
                    ,'-'
                    ,this.actions.unstar
                ]
            });
        }

        e.stopEvent();
        this.contextMenu.record = record;

        this.actions.edit.setDisabled(Ext.isEmpty(record.data.data.template_id));

        this.contextMenu.showAt(e.getXY());
    }

    ,onBrowseClick: function(b, e) {
        var r = this.contextMenu.record
            ,d = Ext.clone(r.data.data);
        d.id = r.data.node_id;
        App.openPath(d);
    }

    ,onEditClick: function(b, e) {
        var r = this.contextMenu.record
            ,d = Ext.clone(r.data.data);
        d.id = r.data.node_id;
        App.openObjectWindow(d);
    }

    ,onUnstarClick: function(b, e) {
        this.setUnstarred(this.contextMenu.record.data.node_id);
    }

    ,onItemClick: function (grid, record, item, index, e, eOpts) {
        var d = Ext.clone(record.data.data);
        d.id = record.data.node_id;
        App.openPath(d);
    }
});
