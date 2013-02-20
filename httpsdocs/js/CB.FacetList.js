Ext.namespace('CB');

xtemplate_facetList = new Ext.XTemplate(
	'<ul class="filter_list">'
		,'<tpl for=".">'
		,'<li{[ (values.active == 1) ? \' class="active"\' : ""]}>'
		,	'<span class="{[ (values.active == 1) ? "b" : "t"]}">{items}</span>'
		//,	'<span class="n">{new_items}</span>'
		,	'<a href="#">{[Ext.value(values.title, "-")]}</a>'
		,'</li>'
	,'</tpl></ul>'
);
xtemplate_facetList.compile();

CB.FacetList = Ext.extend( CB.Facet, {
	title: 'List facet'
	,autoHeight: true
	,layout: 'fit'
	,initComponent: function(){
		this.store = new Ext.data.JsonStore({
			autoDestroy: true
			,proxy: new  Ext.data.MemoryProxy()
			,fields: [ 'id', 'title', { name: 'active', type: 'int'}, 'last', 'items', 'new_items' ]
		});
		if( !Ext.isEmpty( this.data ) ) this.store.loadData( this.data, false );
		
		Ext.apply(this, {
			items: new Ext.DataView({
				autoHeight: true
				,store: this.store
				,itemSelector: 'li'
				,tpl: xtemplate_facetList
				,listeners: {
					click: {scope: this, 
						fn: function(dv, idx, el, ev){
							r = this.store.getAt(idx);
							r.set('active', (r.get('active') == 1) ? 0 : 1);
							//this.store.sort([ { field : 'active', direction: 'DESC' } ], 'ASC');//, { field : 'title', direction: 'ASC' }
							this.setLastField();
							this.fireEvent('facetchange', this, ev);
						}
					}
				}
			})
			,listeners: {
				modechange: {
					scope: this
					,fn: this.onModeChange
				}
			}
		})
		CB.FacetList.superclass.initComponent.apply(this, arguments);
	}
	,onModeChange: function(o, ev){
		i = this.store.query('active', 1);
		if(i.getCount() < 2) ev.stopPropagation();
		else this.fireEvent('facetchange', this, ev);
	}
	,loadData: function(data){
		this.store.loadData(data, false);
		this.setLastField();
		this.setModeVisible(this.getValue().values.length > 1);
		this.doLayout();
	}
	,processServerData: function(serverData, options){
		this.setTitle(Ext.value(this.facetTitle, L['facet_'+this.facetId]) );
		
		this.loadData(this.getFacetData(this.facetId, serverData, options));
	}
	,getFacetData: function(fid, serverData, options){
		data = [];
		values = [];
		facetField = Ext.value(this.f, fid);
		if(options && options.params && options.params.filters && options.params.filters[fid]){
			Ext.each(options.params.filters[fid], function(f){ 
				if(!Ext.isEmpty(f.f)) facetField = f.f;
				for(i = 0; i < f.values.length; i++) values.push(f.values[i])
			}, this)
		}
		this.serverValues = values;
		switch(facetField){
			case 'due':
			case 'date':
			case 'cdate':
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: L['due_' + k.substr(1)], active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'status':
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: L['taskStatus' + k], active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'category_id':
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: Ext.value(CB.DB.thesauri.getName(k), L.noStatus), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'sys_tags':
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: Ext.value(CB.DB.thesauri.getName(k), L.noStatus), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'importance':
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: Ext.value(CB.DB.tasksImportance.getName(k), L.noStatus), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'assigned':
			case 'owner':
			case 'cid':
				Ext.iterate(serverData, function(k, v){ 
					title = (k == -1) ? L.Unassigned : App.usersStore.getName(k);
					data.push({ id: k, title: title, active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'type':
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.objectTypes.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'subtype':
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.templateTypes.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			/*case 'date_end':
				this.setTitle(L.Deadline);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: L[k.substr(1)], active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'case_status':
				this.setTitle(L.Status);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: Ext.value(CB.DB.thesauri.getName(k), L.noStatus), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'missed':
				this.setTitle(L.Missed);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: (k == 'true') ? L.yes: L.no, active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'critical':
				this.setTitle(L.Critical);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: (k == 'true') ? L.yes: L.no, active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'privacy':
				this.setTitle(L.Private);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: k ? L.yes: L.no, active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'lawyer_ids':
				this.setTitle(L.Lawyers);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: App.usersStore.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'responsible_party_id':
				this.setTitle(L.ResponsibleParty);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.thesauri.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'office_ids':
				this.setTitle(L.Office);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.thesauri.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'sys_tags':
				this.setTitle(L.SystemTags);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.thesauri.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'user_tags':
				this.setTitle(L.UserTags);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.thesauri.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'internal_sys_tags':
			case 'internal_user_tags':
				this.setTitle(L[fid]);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.thesauri.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'case_id':
				this.setTitle(L.Case);
				data = serverData;
				Ext.each(data, function(i){ i.active = (values.indexOf(i.id+'') >=0) ? 1 : 0; }, this)
				break;
			case 'template_id':
				this.setTitle(L.Type);
				Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.templates.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;
			case 'type':
				this.setTitle(L.Type);
				Ext.iterate(serverData, function(k, v){
					title = L.noData;
					switch(parseInt(k)){
					case 4: title = L.Contact; break;
					//case 5: title = L.Organization; break;
					}
					data.push({id: k, title: title, active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v })
				}, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				break;/**/
			default:
				//if(isNaN(this.facetId)){
					Ext.iterate(serverData, function(k, v){ data.push({id: k, title: k, active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				/*}else{
					this.setTitle(CB.DB.tagGroups.getName(fid));
					Ext.iterate(serverData, function(k, v){ data.push({id: k, title: CB.DB.thesauri.getName(k), active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }) }, this) ; //'id', 'title', 'active', 'last', 'items', 'new_items' 
				}/**/
		}
		return data;
	}
	,setLastField: function(){
		return;
		lr = false;
		this.store.each(function(r){r.set('last', 0); if(r.get('active') == 1) lr = r }, this);
		if(lr) lr.set('last', 1);
	}
	,getValue: function(){
		r = [];
		si = -1;
		do{
			si = this.store.findExact('active', 1, si + 1);
			if(si >=0) r.push(this.store.getAt(si).get('id'));
		}while(si > -1)
		
		if(!Ext.isEmpty(this.serverValues))
		for (var i = 0; i < this.serverValues.length; i++) {
			si = this.store.findExact('id', this.serverValues[i]);
			if(si < 0) r.push(this.serverValues[i]);
		};
		return { f: this.f, mode: this.mode, values: r };
	}
	,uncheck: function(value){
		value = String(value); //parseInt(value)
		idx = this.store.findExact('id', value );
		if(idx >= 0) this.store.getAt(idx).set('active', 0);
		else if(!Ext.isEmpty(this.serverValues) ) this.serverValues.remove(value);
	}
	,reset: function(){
		this.store.each(function(r){r.set('active', 0)}, this)
	}
}
)

Ext.reg('CBFacetList', CB.FacetList);