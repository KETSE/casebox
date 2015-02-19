Ext.namespace('CB');

Ext.define('CB.FilterPanel', {
    extend: 'Ext.Panel'

    ,alias: 'widget.CBFilterPanel'

    ,xtype: 'CBFilterPanel'

    ,scrollable: true
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
            ,layout: {
                type: 'vbox'
                ,align: 'stretch'
            }

            ,listeners:{
                scope: this
                ,facetchange: this.onFacetChange
            }
        });
        CB.FilterPanel.superclass.initComponent.apply(this, arguments);
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
        Ext.iterate(
            data
            ,function(key, value, obj){
                var facet = this.query('panel[facetId="' + key + '"]')[0];
                if(Ext.isEmpty(facet)){
                    facet = new CB.FacetList({
                        modeToggle: Ext.valueFrom(value.boolMode, true)
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
            }
            ,this
        );

        this.updateActiveFiltersFacet(options);

        // if(this.rendered) {
            // this.syncSize();
        // }

    }

    ,updateActiveFiltersFacet: function(options){
        if(Ext.isEmpty(this.activeFileterFacet)) {
            return;
        }

        var af_data = [];
        Ext.iterate(
            options.filters
            ,function(key, val, obj){
                var facet = this.child('[facetId="'+ key + '"]');
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
                this.bindButton.setIconCls(this.bindButton.initialConfig.activeIconCls);
            }
        }else {
            if(this.bindButton) {
                this.bindButton.setIconCls(this.bindButton.initialConfig.iconCls);
            }
        }
    }

    ,onFacetChange: function(o, e){
        e.stopPropagation();
        var p = this.getFacetsValues();
        this.fireEvent('change', p, e);
    }

    ,getFacetsValues: function(){
        var rez = {};
        this.items.each(
            function(fe){
                if(!Ext.isEmpty(fe.facetId)){
                    var value = fe.getValue();
                    //add only if no empty selected values
                    if(value && !Ext.isEmpty(value.values)) {
                        var fid = Ext.valueFrom(fe.facetId, fe.f);
                        if(Ext.isEmpty(rez[fid])) {
                            rez[fid] = [];
                        }
                        rez[fid].push(value);
                    }
                }
            }
            ,this
        );

        return rez;
    }

    ,onActiveFiltersItemClick: function(idx, data, e){
        if(data.id == -1) {
            return this.fireEvent('change', {});
        }
        var i = this.child('[facetId="' + data.facetId + '"]');
        if(i){
            i.uncheck(data.value);
            fv = this.getFacetsValues();
            this.fireEvent('change', fv);
        }
    }
});


Ext.define('CB.FacetActiveFilters', {
    extend: 'Ext.Panel'
    ,title: L.ActiveFilters
    ,cls: 'facet activeFilters'
    ,autoHeight: true
    ,layout: 'fit'
    ,border: false
    ,style: 'border:0'
    ,bodyStyle: 'background: none'

    ,initComponent: function(){
        this.store = new Ext.data.JsonStore({
            autoDestroy: true
            ,model: 'Filter'
            ,proxy: {
                type: 'memory'
            }
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
                        ,   '<a href="#">{[Ext.valueFrom(values.name, "-")]}</a>'
                        ,'</li>'
                    ,'</tpl></ul>'
                ]
                ,listeners: {
                    scope: this
                    ,itemclick: function(cmp, record, item, index, e, eOpts){//dv, idx, el, ev
                        r = this.store.getAt(index);
                        this.fireEvent('itemclick', index, r.data, e);
                    }
                }
            })
        });
        CB.FacetActiveFilters.superclass.initComponent.apply(this, arguments);
    }

    ,loadData: function(data){
        this.store.loadData(data, false);
        this.doLayout();
    }
}
);
