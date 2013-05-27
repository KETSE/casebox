Ext.namespace('CB');

CB.FilterPanel = Ext.extend(Ext.Panel, {
	xtype: 'panel'
	,autoScroll: true
        ,bodyStyle: 'background-color: #F4F4F4'
        ,padding:0
	,initComponent: function(){
		this.activeFileterFacet = new CB.FacetActiveFilters({
			listeners:{
				scope: this
				,itemclick: this.onActiveFiltersItemClick
			}
		});
		Ext.apply(this, {
			items: [this.activeFileterFacet]
			,listeners:{
				scope: this
				,facetchange: this.onFacetChange
			}
		})
		CB.FilterPanel.superclass.initComponent.apply(this, arguments);
		this.addEvents('change');
	}
	,updateFacets: function(data, options){
		this.items.each(function(i){ i.setVisible(false) }, this);
		this.facetIndex = 1;
		Ext.iterate(data, function(key, value, obj){
			facet = this.find('facetId', key)[0];
			if(Ext.isEmpty(facet)){
				facet = new CB.FacetList({
					modeToggle: false
					,facetId: key
					,facetTitle: value.title
					,f: Ext.isEmpty(value.f) ? key: value.f
				})
				this.insert(this.facetIndex, facet);
			}
			facet.processServerData(value.items, options);
			facet.setVisible(facet.store.getCount() > 0) ;
			this.facetIndex++;
		}, this);
		this.updateActiveFiltersFacet(options)
		this.syncSize();

	}
	,updateActiveFiltersFacet: function(options){
		if(Ext.isEmpty(this.activeFileterFacet)) return;
		af_data = [];
		Ext.iterate(options.params.filters, function(key, val, obj){
			vals = {};
			Ext.each(val, function(f){ for(i = 0; i < f.values.length; i++) vals[f.values[i]] = 1; }, this)
			fd = CB.FacetList.prototype.getFacetData(key, vals, options);
			for (var i = 0; i < fd.length; i++){
				af_data.push({id: Ext.id(), facetId: key, value: fd[i].id, title: fd[i].title} )
			}
		}, this);
		this.activeFileterFacet.loadData(af_data);
		if(this.activeFileterFacet.store.getCount() > 0){
			this.activeFileterFacet.store.loadData([{id: -1, value: -1, title: L.ResetAll}], true);
			this.activeFileterFacet.setVisible(true);
			if(this.bindButton) this.bindButton.setIconClass(this.bindButton.initialConfig.activeIconCls);
		}else if(this.bindButton) this.bindButton.setIconClass(this.bindButton.initialConfig.iconCls);
	}
	,onFacetChange: function(o, e){
		e.stopPropagation()
		this.fireEvent('change', this.getFacetsValues() )
	}
	,getFacetsValues: function(){
		result = {}
		this.items.each(function(fe){
			if(!Ext.isEmpty(fe.facetId)){
				fid = Ext.value(fe.facetId, fe.f);
				if(Ext.isEmpty(result[fid])) result[fid] = [];
				result[fid].push(fe.getValue());
			}
		}, this);
		return result;
	}
	,onActiveFiltersItemClick: function(idx, data, e){
		if(data.id == -1) return this.fireEvent('change', {} );
		i = this.find('facetId', data.facetId)[0];
		if(i){
			i.uncheck(data.value);
			fv = this.getFacetsValues();
			this.fireEvent('change', fv )
		}
	}
})

Ext.reg('CBFilterPanel', CB.FilterPanel);


CB.FacetActiveFilters = Ext.extend( Ext.Panel, {
	title: L.ActiveFilters
	,cls: 'facet activeFilters'
	,autoHeight: true
	,layout: 'fit'
	,border: false
	,style: 'border:0'
	,bodyStyle: 'background: none'
	,initComponent: function(){
		this.store = new Ext.data.JsonStore({
			autoDestroy: true
			,proxy: new  Ext.data.MemoryProxy()
			,fields: [ 'id', 'facetId', 'value', 'title' ]
		});
		if( !Ext.isEmpty( this.data ) ) this.store.loadData( this.data, false );
		
		Ext.apply(this, {
			items: new Ext.DataView({
				autoHeight: true
				,store: this.store
				,itemSelector: 'li'
				,tpl: [
					'<ul class="filter_list">'
						,'<tpl for=".">'
						,'<li{[ (values.id == -1) ? \' class="reset"\' : ""]}>'
						,	'<a href="#">{[Ext.value(values.title, "-")]}</a>'
						,'</li>'
					,'</tpl></ul>'
				]
				,listeners: {
					click: {scope: this, 
						fn: function(dv, idx, el, ev){
							r = this.store.getAt(idx);
							this.fireEvent('itemclick', idx, r.data, ev);
						}
					}
				}
			})
		})
		CB.FacetActiveFilters.superclass.initComponent.apply(this, arguments);
		this.addEvents('itemclick');
	}
	,loadData: function(data){
		this.store.loadData(data, false);
		this.doLayout();
	}
}
)

Ext.reg('CBFacetActiveFilters', CB.FacetActiveFilters);