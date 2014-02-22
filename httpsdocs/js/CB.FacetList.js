Ext.namespace('CB');

xtemplate_facetList = new Ext.XTemplate(
    '<ul class="filter_list">'
        ,'<tpl for=".">'
        ,'<li{[ (values.active == 1) ? \' class="active"\' : ""]}>'
        ,   '<span class="{[ (values.active == 1) ? "b" : "t"]}">{items}</span>'
        ,   '<a href="#">{[Ext.value(values.name, "-")]}</a>'
        ,'</li>'
    ,'</tpl></ul>'
);
xtemplate_facetList.compile();

CB.FacetList = Ext.extend( CB.Facet, {
    title: 'List facet'
    ,autoHeight: true
    ,layout: 'fit'
    ,listMode: 'checklist' //radio
    ,initComponent: function(){
        this.store = new Ext.data.JsonStore({
            autoDestroy: true
            ,proxy: new  Ext.data.MemoryProxy()
            ,fields: [ 'id', 'name', { name: 'active', type: 'int'}, 'last', 'items', 'new_items' ]
        });
        if( !Ext.isEmpty( this.data ) ) this.store.loadData( this.data, false );
        var items = [
            new Ext.DataView({
                    autoHeight: true
                    ,store: this.store
                    ,itemSelector: 'li'
                    ,tpl: xtemplate_facetList
                    ,listeners: {
                        click: {
                            scope: this
                            ,fn: this.onItemClick
                        }
                    }
                })
        ];

        if(this.manualPeriod === true) {
            this.addPeriodPanel = new Ext.form.CompositeField({
                height: 'auto'
                ,hidden: true
                ,style: 'background-color: transparent; padding-left: 15px'
                ,items: [{
                    xtype: 'datefield'
                    ,width: 100
                    ,height: 20
                    ,name: 'from'
                },{
                    xtype: 'label'
                    ,width: 15
                    ,html: ' &nbsp;– '
                },{
                    xtype: 'datefield'
                    ,width: 100
                    ,name: 'to'
                },{
                    xtype: 'button'
                    ,width: 20
                    ,iconCls: 'i-check-alt'
                    ,scope: this
                    ,handler: this.onAddPeriodClick
                },{
                    xtype: 'button'
                    ,width: 20
                    ,iconCls: 'i-cancel'
                    ,scope: this
                    ,handler: function() {
                        this.addPeriodPanel.hide();
                    }
                }]
            });

            items.push(this.addPeriodPanel);
        }

        Ext.apply(this, {
            items: items
            ,cachedNames: {}//used by tree_tags
            ,listeners: {
                modechange: {
                    scope: this
                    ,fn: this.onModeChange
                }
            }
        });
        CB.FacetList.superclass.initComponent.apply(this, arguments);
    }
    ,onModeChange: function(o, ev){
        i = this.store.query('active', 1);
        if(i.getCount() < 2) ev.stopPropagation();
        else this.fireEvent('facetchange', this, ev);
    }
    ,loadData: function(data){
        for (var i = 0; i < data.length; i++) {
            this.cachedNames[data[i].id] = data[i].name;
        }
        this.store.loadData(data, false);
        this.setLastField();
        this.setModeVisible(this.getValue().values.length > 1);
        this.doLayout();
    }
    ,processServerData: function(serverData, options){
        this.loadData(this.getFacetData(this.facetId, serverData, options));
    }
    ,getFacetData: function(fid, serverData, options){
        var data = [];
        var values = [];
        var facetField = Ext.value(this.f, fid);
        if(options && options.params && options.params.filters && options.params.filters[fid]){
            Ext.each(
                options.params.filters[fid]
                ,function(f){
                    if(!Ext.isEmpty(f.f)) facetField = f.f;
                    for(i = 0; i < f.values.length; i++) {
                        values.push(f.values[i]);
                    }
                }
                ,this
            );
        }
        this.serverValues = values;

        Ext.iterate(
            serverData
            ,function(k, v){
                this.cachedNames[k] =
                data.push({
                    id: k
                    ,name: L['taskStatus' + k]
                    ,active: (values.indexOf(k+'') >=0) ? 1 : 0
                    ,items: v
                });
            }
            ,this
        );
        //'id', 'name', 'active', 'last', 'items', 'new_items'
        switch(facetField){
            case 'status':
                Ext.iterate(
                    serverData
                    ,function(k, v){
                        if(!Ext.isEmpty(L['taskStatus' + k])) {
                            data.push({
                                id: k
                                ,name: L['taskStatus' + k]
                                ,active: (values.indexOf(k+'') >=0) ? 1 : 0
                                ,items: v
                            });
                        }
                    }
                    ,this
                );
                break;

            case 'importance':
                Ext.iterate(
                    serverData
                    ,function(k, v){
                        data.push({
                            id: k
                            ,name: Ext.value(CB.DB.importance.getName(k), L.noStatus)
                            ,active: (values.indexOf(k+'') >=0)
                                ? 1
                                : 0
                            ,items: v
                        });
                    }
                    ,this
                );
                break;
            case 'template_type':
                Ext.iterate(serverData, function(k, v){ data.push({id: k, name: L['tt_'+k], active: (values.indexOf(k+'') >=0) ? 1 : 0, items: v }); }, this);
                break;
            default:
                Ext.iterate(
                    serverData
                    ,function(k, v){
                        data.push({
                            id: k
                            ,name: Ext.isPrimitive(v) ? k : v['name']
                            ,active: (values.indexOf(k+'') >=0) ? 1 : 0
                            ,items: Ext.isPrimitive(v) ? v : v.count
                        });
                    }
                    ,this
                );
        }
        return data;
    }
    ,setLastField: function(){
        // lr = false;
        // this.store.each(function(r){r.set('last', 0); if(r.get('active') == 1) lr = r }, this);
        // if(lr) lr.set('last', 1);
    }
    ,getValue: function(){
        var r = [];
        var si = -1;
        do {
            si = this.store.findExact('active', 1, si + 1);
            if(si >=0) r.push(this.store.getAt(si).get('id'));
        } while (si > -1);

        if(!Ext.isEmpty(this.serverValues))
        for (var i = 0; i < this.serverValues.length; i++) {
            si = this.store.findExact('id', this.serverValues[i]);
            if(si < 0) r.push(this.serverValues[i]);
        }
        return { f: this.f, mode: this.mode, values: r };
    }

    ,onItemClick: function(dv, idx, el, ev){
        switch(this.listMode) {
            case 'radio':
                r = this.store.getAt(idx);
                var currentlyChecked = (r.get('active') == 1);
                if(!currentlyChecked) {
                    this.reset();
                    r.set('active', 1);
                    this.fireEvent('facetchange', this, ev);
                }

                break;
            default:
                r = this.store.getAt(idx);
                r.set('active', (r.get('active') == 1) ? 0 : 1);
                this.setLastField();
                this.fireEvent('facetchange', this, ev);
        }
    }

    ,uncheck: function(value){
        value = String(value);
        idx = this.store.findExact('id', value );
        if(idx >= 0) {
            this.store.getAt(idx).set('active', 0);
        } else {
            if(!Ext.isEmpty(this.serverValues)) {
                this.serverValues.remove(value);
            }
        }
    }
    ,reset: function(){
        this.store.each(
            function(r){
                r.set('active', 0);
            }
            ,this
        );
    }
    ,onPeriodAddClick: function() {
        this.addPeriodPanel.items.itemAt(0).setValue(new Date());
        this.addPeriodPanel.show();
    }
    ,onAddPeriodClick: function(b, e) {
        var from = this.addPeriodPanel.items.itemAt(0).getValue();
        var to = this.addPeriodPanel.items.itemAt(1).getValue();
        var id = '';
        var name = '';
        if(!Ext.isEmpty(from)) {
            id = from.toISOString();
            name = from.format(App.dateFormat);
        }
        id +='~';
        name += ' - ';

        if(!Ext.isEmpty(to)) {
            id += to.toISOString();
            name += to.format(App.dateFormat);
        }
        if(id == '-') {
            return;
        }
        this.store.loadData(
            [{
                id: id
                ,name: name
                ,active: 1
            }
            ]
            ,true
        );
        this.cachedNames[id] = name;
        this.addPeriodPanel.hide();
        this.fireEvent('facetchange', this, e);
    }
}
);

Ext.reg('CBFacetList', CB.FacetList);
