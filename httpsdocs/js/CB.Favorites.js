Ext.namespace('CB');
CB.Favorites = Ext.extend(Ext.util.Observable, { 
	constructor: function(config){
		this.store = new Ext.data.DirectStore({
			autoLoad: false
			,autoDestroy: true
			,autoSave: true
			,proxy: new  Ext.data.DirectProxy({
				paramsAsHash: true
				,api: {
					create: Favorites.create
					,read: Favorites.read
					,update: Favorites.update
					,destroy: Favorites.destroy 
				}
			})
			,reader: new Ext.data.JsonReader({
					successProperty: 'success'
					,idProperty: 'id'
					,root: 'data'
					,messageProperty: 'msg'
				},[ {name: 'id', type: 'int'}, {name: 'type', type: 'int'}, 'name', 'path' ]
			)
			,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: false})
			,listeners:{
				scope: this
				,load: this.onLoad
				,save: this.onSave
				,write: function( store, action, result, res, rs ){
					if(Ext.isEmpty(rs)) return;
					if(!Ext.isArray(rs)) rs = [rs];
					Ext.each(rs, function(r){r.set('id', parseInt(r.get('id'))); r.data.id = parseInt(r.data.id); }, this)
				}
			}
		})

		Ext.apply(this, arguments)
		this.addEvents({
		    	"changed" : true
		});

		// Call our superclass constructor to complete construction process.
		CB.Favorites.superclass.constructor.call(this, config)
	}
	,inFavorites: function(id){
		return (this.store.findExact('id', parseInt(id)) >= 0 )
	}
	,add: function(id){
		idx = this.store.findExact('id', parseInt(id));
		data = {id: parseInt(id), type: null, name: ''};
		r = new this.store.recordType(data);
		if(idx < 0) this.store.add(r);
	}
	,remove: function(id){
		idx = this.store.findExact('id', parseInt(id));
		if(idx >= 0) this.store.removeAt(idx);
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
})
Ext.reg('CBFavorites', CB.Favorites);


CB.FavoritesMenuItem = Ext.extend(Ext.Button, { 
	iconCls: 'icon-fav'
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
		}

		Ext.apply(this, arguments)
		Ext.apply(this, {
			listeners: {
				scope: this
				,menushow: this.onMenuShow
			}
		})
		CB.FavoritesMenuItem.superclass.initComponent.apply(this, arguments)
		this.addEvents('select');
	}
	,onMenuShow: function(m){
		m.removeAll(true);
		App.Favorites.store.each(function(r){
			m.add({
				text: r.get('path') + r.get('name')
				,data: { id: r.get('id')} 
				,scope: this
				,handler: this.onItemClick
			})
		}, this)
		if(!Ext.isEmpty(this.activeItem)){
			if(m.items.getCount() > 0) m.add('-');
			m.add(App.Favorites.inFavorites(this.activeItem) ? this.actions.removeFromFavorites : this.actions.addToFavorites)
		}
	}
	,onAddClick: function(b, e){
		if(isNaN(this.activeItem)) return;
		App.Favorites.add(this.activeItem)
	}
	,onRemoveClick: function(b, e){
		if(isNaN(this.activeItem)) return;
		App.Favorites.remove(this.activeItem)
	}
	,setActiveItem: function(id){
		this.activeItem = parseInt(id);
	}
	,onItemClick: function(b, e){
		this.fireEvent('select', b.data.id);
	}
})
Ext.reg('CBFavoritesMenuItem', CB.FavoritesMenuItem);
