Ext.namespace('CB');
Ext.define('CB.Favorites', {
    extend: 'Ext.util.Observable'

    ,constructor: function(config){
        this.store = new Ext.data.DirectStore({
            autoLoad: false
            ,autoDestroy: true
            ,autoSave: true
            ,model: 'ObjectsRecord' //temporary
            ,proxy: {
                type: 'direct'
                ,paramsAsHash: true
                ,api: {
                    create: CB_Favorites.create
                    ,read: CB_Favorites.read
                    ,update: CB_Favorites.update
                    ,destroy: CB_Favorites.destroy
                }
                ,reader: {
                    type: 'json'
                    ,successProperty: 'success'
                    ,idProperty: 'id'
                    ,rootProperty: 'data'
                    ,messageProperty: 'msg'
                }
            }
            ,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: false})
            ,listeners:{
                scope: this
                ,load: this.onLoad
                ,save: this.onSave
                ,write: function( store, action, result, res, rs ){
                    if(Ext.isEmpty(rs)) return;
                    if(!Ext.isArray(rs)) rs = [rs];
                    Ext.each(
                        rs
                        ,function(r){
                            r.set('id', parseInt(r.get('id'), 10));
                            r.data.id = parseInt(r.data.id, 10);
                        }
                        ,this
                    );
                }
            }
        });

        Ext.apply(this, arguments);

        // Call our superclass constructor to complete construction process.
        CB.Favorites.superclass.constructor.call(this, config);
    }
    ,inFavorites: function(id){
        return (this.store.findExact('id', parseInt(id, 10)) >= 0);
    }
    ,add: function(id){
        var idx = this.store.findExact('id', parseInt(id, 10));
        var data = {id: parseInt(id, 10), type: null, name: ''};
        var r = Ext.create(
            this.store.getModel().getName()
            ,data
        );
         
        if(idx < 0) {
            this.store.add(r);
        }
    }
    ,remove: function(id){
        var idx = this.store.findExact('id', parseInt(id, 10));
        if(idx >= 0) {
            this.store.removeAt(idx);
        }
    }
    ,load: function(){
        this.store.load();
    }
    ,onLoad: function(store, recs, options){
        this.fireEvent('change', this);
    }
    ,onSave: function(store, batch, data){
        this.fireEvent('change', this);
    }
});

Ext.define('CB.FavoritesMenuItem', {
    extend: 'Ext.Button'
    ,iconCls: 'icon-fav'
    ,menu: []
    ,activeItem: null
    ,initComponent: function(){
        this.actions = {
            addToFavorites: new Ext.Action({
                iconCls: 'icon-star-plus'
                ,text: L.AddToFavorites
                ,scope: this
                ,handler: this.onAddClick
            })
            ,removeFromFavorites: new Ext.Action({
                iconCls: 'icon-star-minus'
                ,text: L.RemoveFromFavorites
                ,scope: this
                ,handler: this.onRemoveClick
            })
        };

        Ext.apply(this, arguments);

        Ext.apply(this, {
            listeners: {
                scope: this
                ,menushow: this.onMenuShow
            }
        });

        CB.FavoritesMenuItem.superclass.initComponent.apply(this, arguments);
    }

    ,onMenuShow: function(m){
        m.removeAll(true);
        App.Favorites.store.each(function(r){
            m.add({
                text: r.get('path') + r.get('name')
                ,data: { id: r.get('id')}
                ,scope: this
                ,handler: this.onItemClick
            });
        }, this);

        if(!Ext.isEmpty(this.activeItem)){
            if(m.items.getCount() > 0) m.add('-');
            m.add(App.Favorites.inFavorites(this.activeItem)
                ? this.actions.removeFromFavorites
                : this.actions.addToFavorites
            );
        }
    }
    ,onAddClick: function(b, e){
        if(isNaN(this.activeItem)) return;
        App.Favorites.add(this.activeItem);
    }
    ,onRemoveClick: function(b, e){
        if(isNaN(this.activeItem)) return;
        App.Favorites.remove(this.activeItem);
    }
    ,setActiveItem: function(id){
        this.activeItem = parseInt(id, 10);
    }
    ,onItemClick: function(b, e){
        this.fireEvent('select', b.data.id);
    }
});
