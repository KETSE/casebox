Ext.namespace('CB');

CB.FilterPanel = Ext.extend(Ext.Panel, {
    xtype: 'panel'
    ,autoScroll: true
    ,bodyStyle: 'padding: 10px 0'
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
        });
        CB.FilterPanel.superclass.initComponent.apply(this, arguments);
        this.addEvents('change');
    }

    ,updateFacets: function(data, options){
        //hide all facets by default
        this.items.each(
            function(i){
                i.setVisible(false);
            }
            ,this
        );

        this.facetIndex = 1;
        Ext.iterate(data, function(key, value, obj){
            facet = this.find('facetId', key)[0];
            if(Ext.isEmpty(facet)){
                facet = new CB.FacetList({
                    modeToggle: Ext.value(value.boolMode, true)
                    ,facetId: key
                    ,title: value.title
                    ,f: Ext.isEmpty(value.f) ? key: value.f
                    ,manualPeriod: value.manualPeriod
                });
                this.insert(this.facetIndex, facet);
            }
            facet.processServerData(value.items, options);
            facet.setVisible(facet.store.getCount() > 0) ;
            this.facetIndex++;
        }, this);
        this.updateActiveFiltersFacet(options);

        if(this.rendered) {
            this.syncSize();
        }

    }

    ,updateActiveFiltersFacet: function(options){
        if(Ext.isEmpty(this.activeFileterFacet)) {
            return;
        }

        var af_data = [];
        Ext.iterate(
            options.params.filters
            ,function(key, val, obj){
                var facet = this.find('facetId', key)[0];
                if(!Ext.isEmpty(facet)){
                    var vals = [];
                    Ext.each(
                        val
                        ,function(f){
                            for(i = 0; i < f.values.length; i++) {
                                if(vals.indexOf(f.values[i]) < 0) {
                                    vals.push(f.values[i]);
                                }
                            }
                        }
                        ,this
                    );

                    for (var i = 0; i < vals.length; i++){
                        af_data.push({
                            id: Ext.id()
                            ,facetId: key
                            ,value: vals[i]
                            ,name: facet.cachedNames[vals[i]]
                        });
                    }
                }
            }, this);
        this.activeFileterFacet.loadData(af_data);
        if(this.activeFileterFacet.store.getCount() > 0){
            this.activeFileterFacet.store.loadData([{id: -1, value: -1, name: L.ResetAll}], true);
            this.activeFileterFacet.setVisible(true);
            if(this.bindButton) {
                this.bindButton.setIconClass(this.bindButton.initialConfig.activeIconCls);
            }
        }else {
            if(this.bindButton) {
                this.bindButton.setIconClass(this.bindButton.initialConfig.iconCls);
            }
        }
    }

    ,onFacetChange: function(o, e){
        e.stopPropagation();
        var p = this.getFacetsValues();
        this.fireEvent('change', p, e);
    }

    ,getFacetsValues: function(){
        result = {};
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
            this.fireEvent('change', fv);
        }
    }
});

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
            ,fields: [ 'id', 'facetId', 'value', 'name' ]
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
                        ,   '<a href="#">{[Ext.value(values.name, "-")]}</a>'
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
        });
        CB.FacetActiveFilters.superclass.initComponent.apply(this, arguments);
        this.addEvents('itemclick');
    }

    ,loadData: function(data){
        this.store.loadData(data, false);
        this.doLayout();
    }
}
);

Ext.reg('CBFacetActiveFilters', CB.FacetActiveFilters);
