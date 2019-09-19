Ext.namespace('CB.facet');

xtemplate_facetList = new Ext.XTemplate(
    '<ul class="filter_list">'
        ,'<tpl for=".">'
        ,'<li class="item {[ (xindex > 10) ? \'more\' : ""]}">'
        ,   '<b class="tick {[ (values.active == 1) ? \'active\' : ""]} {cls}"'
        ,   ' style="{[ (values.color) ? \'background-color: \' + values.color : ""]}"'
        ,       '></b>'
        ,   '<span class="{[ (values.active == 1) ? "b" : "t"]}">{items}</span>'
        ,   '<a href="#">{[Ext.valueFrom(values.name, "-")]}</a>'
        ,'</li>'
        ,'<tpl if="xcount &gt; 10 && xindex == xcount">'
        ,'<li class="toggle"><u class="click">' + L.ShowAll + '</u></li>'
        ,'</tpl>'
    ,'</tpl></ul>'
);
xtemplate_facetList.compile();

Ext.define('CB.facet.List', {
    extend: 'CB.facet.Base'

    ,xtype: 'CBFacetList'
    ,alias: 'CB.Facet.List'

    ,title: 'List facet'
    ,autoHeight: true
    ,layout: 'fit'
    ,listMode: 'checklist' //radio
    ,itemsTemplate: xtemplate_facetList

    ,initComponent: function(){
        this.store = new Ext.data.JsonStore({
            autoDestroy: true
            ,model: 'Facet'
            ,proxy: {
                type: 'memory'
            }
        });

        if(!Ext.isEmpty(this.data)) {
            this.store.loadData(this.data, false);
        }

        var items = [
            new Ext.DataView({
                autoHeight: true
                ,store: this.store
                ,itemSelector: 'li.item'
                ,tpl: this.itemsTemplate
                ,listeners: {
                    scope: this
                    ,itemclick: this.onItemClick
                    ,containerclick: this.onContainerClick
                }
            })
        ];

        if (this.config.manualPeriod === true) {
            this.addPeriodPanel = new Ext.form.FieldContainer({
                height: 'auto'
                ,hidden: true
                ,layout: 'hbox'
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
            ,cachedNames: {}
            ,listeners: {
                modechange: {
                    scope: this
                    ,fn: this.onModeChange
                }
            }
        });

        this.callParent(arguments);
    }

    ,onModeChange: function(o, ev){
        var i = this.store.query('active', 1);
        if(i.getCount() < 2) {
            ev.stopPropagation();
        } else {
            this.fireEvent('facetchange', this, ev);
        }
    }

    ,loadData: function(data){
        this.removeCls('facet-expanded');

        this.callParent(arguments);

        for (var i = 0; i < data.length; i++) {
            this.cachedNames[data[i].id] = data[i].name;
        }

        this.store.loadData(data, false);
        this.setLastField();
        this.setModeVisible(this.getValue().values.length > 1);
    }

    ,processServerData: function(serverData, options){
        this.loadData(this.getFacetData(this.facetId, serverData, options));
    }

    ,getFacetData: function(fid, serverData, options){
        var data = []
            ,values = []
            ,facetField = Ext.valueFrom(this.f, fid);

        if(options && options.filters && options.filters[fid]){
            Ext.each(
                options.filters[fid]
                ,function(f){
                    if(!Ext.isEmpty(f.f)) {
                        facetField = f.f;
                    }

                    for(var i = 0; i < f.values.length; i++) {
                        values.push(f.values[i]);
                    }
                }
                ,this
            );
        }
        this.serverValues = values;

        //'id', 'name', 'active', 'last', 'items', 'new_items'
        switch(facetField){
            case 'task_status':
                Ext.iterate(
                    serverData
                    ,function(k, v){
                        if(!Ext.isEmpty(L['taskStatus' + k])) {
                            data.push({
                                id: k
                                ,name: L['taskStatus' + k]
                                ,active: (values.indexOf(k + '') >= 0) ? 1 : 0
                                ,items: v
                            });
                        }
                    }
                    ,this
                );
                break;

            case 'template_type':
                Ext.iterate(
                    serverData
                    ,function(k, v){
                        data.push({
                            id: k
                            ,name: L['tt_'+k]
                            ,active: (values.indexOf(k+'') >=0) ? 1 : 0
                            ,items: v
                        });
                    }, this);
                break;

            default:
                Ext.iterate(
                    serverData
                    ,function(k, v){
                        var d = {
                            id: k
                            ,name: Ext.isPrimitive(v) ? k : v['name']
                            ,active: (values.indexOf(k + '') >= 0) ? 1 : 0
                            ,items: Ext.isPrimitive(v) ? v : v.count
                        };
                        if(!Ext.isEmpty(v.color)) {
                            d.color = v.color;
                        }
                        if(!Ext.isEmpty(v.cls)) {
                            d.cls = v.cls;
                        }

                        data.push(d);
                    }
                    ,this
                );
        }

        return data;
    }

    ,setLastField: function(){
        var lr = false;
        this.store.each(
            function(r){
                r.set('last', 0);
                if(r.get('active') == 1) {
                    lr = r;
                }
            }
            ,this
        );

        if(lr) {
            lr.set('last', 1);
        }
    }

    ,getValue: function(){
        var r = [];
        var si = -1;
        do {
            si = this.store.findExact('active', 1, si + 1);
            if(si >=0) {
                r.push(this.store.getAt(si).get('id'));
            }
        } while (si > -1);

        if(!Ext.isEmpty(this.serverValues)) {
            for (var i = 0; i < this.serverValues.length; i++) {
                si = this.store.findExact('id', this.serverValues[i]);
                if(si < 0) {
                    r.push(this.serverValues[i]);
                }
            }
        }

        return {f: this.f, mode: this.mode, values: r};
    }

    ,onItemClick: function(cmp, record, item, index, e, eOpts){//dv, idx, el, ev
        var r;

        switch(this.listMode) {
            case 'radio':
                r = this.store.getAt(index);
                var currentlyChecked = (r.get('active') == 1);
                if(!currentlyChecked) {
                    this.reset();
                    r.set('active', 1);
                    this.fireEvent('facetchange', this, ev);
                }

                break;

            default:
                r = this.store.getAt(index);
                r.set('active', (r.get('active') == 1) ? 0 : 1);
                this.setLastField();
                this.fireEvent('facetchange', this, e);
        }
    }

    ,onContainerClick: function(view, e, eOpts) {
        var el = e.getTarget();

        if(el) {
            //check Show all click
            if(el.className === 'click') {
                this.addCls('facet-expanded');
                this.updateLayout();
            }
        }
    }

    ,uncheck: function(value){
        value = String(value);
        var idx = this.store.findExact('id', value );

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
        this.addPeriodPanel.items.getAt(0).setValue(new Date());
        this.addPeriodPanel.show();
    }

    ,onAddPeriodClick: function(b, e) {
        var from = this.addPeriodPanel.items.getAt(0).getValue();
        var to = this.addPeriodPanel.items.getAt(1).getValue();
        var id = '';
        var name = '';

        if(!Ext.isEmpty(from)) {
            id = date_local_to_ISO_string(from);
            name = Ext.Date.format(from, App.dateFormat);
        }
        id +='~';
        name += ' - ';

        if(!Ext.isEmpty(to)) {
            id += date_local_to_ISO_string(to);
            name += Ext.Date.format(to, App.dateFormat);
        }
        if(id === '-') {
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
