Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Charts', {
    extend: 'CB.browser.view.Interface'
    ,xtype: 'CBBrowserViewCharts'

    ,border: false
    ,tbarCssClass: 'x-panel-white'
    ,layout: 'border'
    ,params: {
        from: 'charts'
        ,facets: 'general'
        // ,rows: 0
    }

    ,initComponent: function(){
        this.instanceId = this.refOwner.instanceId;

        this.facetsCombo = new Ext.form.ComboBox({
            xtype: 'combo'
            ,itemId: 'facetscombo'
            ,forceSelection: true
            ,triggerAction: 'all'
            ,lazyRender: true
            ,queryMode: 'local'
            ,fieldLabel: 'Facets'
            ,cls: 'fs12'
            ,labelWidth: 'auto'
            ,editable: false
            ,store: new Ext.data.JsonStore({
                model: 'Generic2'
                ,data: []
            })
            ,displayField: 'name'
            ,valueField: 'id'
            ,listeners: {
                scope: this
                ,select: this.onFacetChange
            }
        });

        this.sortButton = new Ext.Button({
            itemId: 'chartsSortButton'
            ,iconCls: 'im-sort'
            ,style: 'margin-left: 10px'
            ,text: ''
            ,menu: [
                {
                    text: L.SortByNameAsc
                    ,sort: 'name'
                    ,direction: 'asc'
                    ,scope: this
                    ,handler: this.onSortButtonClick
                },{
                    text: L.SortByNameDesc
                    ,sort: 'name'
                    ,direction: 'desc'
                    ,scope: this
                    ,handler: this.onSortButtonClick
                },{
                    text: L.SortByCountAsc
                    ,sort: 'count'
                    ,direction: 'asc'
                    ,scope: this
                    ,handler: this.onSortButtonClick
                },{
                    text: L.SortByCountDesc
                    ,sort: 'count'
                    ,direction: 'desc'
                    ,scope: this
                    ,handler: this.onSortButtonClick
                }
            ]
        });

        this.refOwner.buttonCollection.addAll(
            new Ext.Button({
                text: L.Bar //L.ChartArea
                ,itemId: 'barchart'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-chart-bar'
                ,toggleGroup: 'cv' + this.instanceId
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.Column //L.ChartArea
                ,itemId: 'columnchart'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-chart-column'
                // ,iconAlign:'top'
                ,toggleGroup: 'cv' + this.instanceId
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.Pie //L.ChartPie
                ,itemId: 'piechart'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-chart-pie'
                // ,iconAlign:'top'
                ,toggleGroup: 'cv' + this.instanceId
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,this.facetsCombo
            ,this.sortButton
        );

        this.seriesStyles = [];
        for (var i = 0; i < App.colors.length; i++) {
            this.seriesStyles.push({
                color: App.colors[i]
            });
        }

        this.chartDataStore = new Ext.data.JsonStore({
            autoDestroy: false
            // ,fields: ['id', 'name', {name: 'count', type: 'int'}]
            ,model: 'GenericCount'
        });

        this.chartConfigs = {
            'barchart': {
                store: this.chartDataStore
                ,colors: App.colors
                ,axes: [
                    {
                        type: 'numeric'
                        ,position: 'bottom'
                        ,fields: 'count'
                        ,grid: true
                    },{
                        type: 'category'
                        ,position: 'left'
                        ,fields: 'name'
                        ,grid: true
                    }
                ]
                ,series: [{
                    type: 'bar'
                    ,axis: 'bottom'
                    ,xField: 'name'
                    ,yField: 'count'
                    ,style: {
                        opacity: 0.80
                        ,minGapWidth: 10
                    }
                    ,highlight: {
                        fillStyle: 'rgba(249, 204, 157, 1.0)'
                        ,strokeStyle: 'black'
                        ,radius: 10
                    }
                    ,label: {
                        field: 'count'
                        ,display: 'insideEnd'
                    }
                    ,listeners: {
                        scope: this
                        ,itemclick: this.onChartItemClick
                    }
                }]
            }
            ,'columnchart': {
                store: this.chartDataStore
                ,colors: App.colors
                ,axes: [{
                        type: 'numeric'
                        ,position: 'left'
                        ,adjustByMajorUnit: true
                        ,fields: ['count']
                        ,grid: true
                    }, {
                        type: 'category'
                        ,position: 'bottom'
                        ,fields: ['name']
                        ,grid: true
                    }
                ]
                ,series: [{
                    type: 'column'
                    ,xField: 'name'
                    ,yField: ['count']
                    ,stacked: true
                    ,highlight: {
                        fillStyle: 'yellow'
                    }
                    ,label: {
                        field: 'count'
                        ,display: 'insideEnd'
                    }
                    ,listeners: {
                        scope: this
                        ,itemclick: this.onChartItemClick
                    }
                }]
            }
            ,'piechart': {
                store: this.chartDataStore
                ,series: [{
                    type: 'pie',
                    angleField: 'count',
                    label: {
                        field: 'name',
                        display: 'outside',
                        calloutLine: true
                    },
                    showInLegend: true,
                    highlight: true,
                    highlightCfg: {
                        'stroke-width': 20,
                        stroke: '#fff'
                    }
                    ,listeners: {
                        scope: this
                        ,itemclick: this.onChartItemClick
                    }
                }]
            }
        };

        this.chartContainer = new Ext.Panel({
            region: 'center'
            ,layout: 'fit'
            ,border: false
        });

        Ext.apply(this, {
            title: L.Charts
            ,header: false
            ,items: this.chartContainer
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });
        CB.browser.view.Charts.superclass.initComponent.apply(this, arguments);
        this.currentButton = this.refOwner.buttonCollection.get('barchart');

        this.selectedFacets = [];
        this.store.on('load', this.onStoreLoad, this);
    }

    ,getViewParams: function() {
        return this.params;
    }

    ,onActivate: function() {
        this.selectedFacets = [];

        this.fireEvent(
            'settoolbaritems'
            ,[
                'facetscombo'
                ,'chartsSortButton'
                ,'->'
                // ,'linechart'
                ,'barchart'
                // ,'stackedbarchart'
                ,'columnchart'
                // ,'stackedcolumnchart'
                ,'piechart'
                ,'-'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }

    ,onChangeChartClick: function(b, e) {
        b.toggle(true);
        this.currentButton = b;
        this.loadChartData();

        this.chartContainer.removeAll(true);

        this.chart = Ext.create(
            'Ext.chart.Chart'
            ,this.chartConfigs[b.itemId]
        );

        this.chartContainer.add(this.chart);
    }

    ,loadAvailableFacets: function() {
        var data = [];

        Ext.iterate(
            this.data
            ,function(key, val, o) {
                if(Ext.isEmpty(this.selectedFacets)) {
                    this.selectedFacets = [key];
                }
                data.push({
                    id: key
                    ,name: Ext.htmlDecode(Ext.valueFrom(val['title'], L['facet_'+key]))
                });
            }
            ,this
        );

        /* there is some Ext bug on first combobox display
            When expanding it - it has no data
        */
        var st = this.facetsCombo.store;
        st.removeAll();
        st.loadData(data);

        this.facetsCombo.setValue(this.selectedFacets[0]);
    }

    ,loadChartData: function() {
        var data = {}
            ,sorter = null;

        sorter = this.detectSorter(Ext.valueFrom(this.viewParams, {}));

        Ext.iterate(
            this.data
            ,function(key, val, o) {
                data[key] = CB.FacetList.prototype.getFacetData(key, val.items);

                for (var i = 0; i < data[key].length; i++) {
                    if(Ext.isObject(data[key][i].items)) {
                        data[key][i].name = data[key][i].items.name;
                        data[key][i].count = data[key][i].items.count;
                    } else {
                        data[key][i].count = data[key][i].items;
                    }
                    data[key][i].name = htmlEntityDecode(App.shortenString(data[key][i].name, 30));
                }

                if(sorter) {
                    data[key] = Ext.Array.sort(data[key], sorter);
                }
            }
            ,this
        );
        this.chartData = data;

        if(data[this.selectedFacets[0]]) {
            var d = Ext.clone(data[this.selectedFacets[0]]);
            this.chartDataStore.loadData(d);
        } else {
            this.chartDataStore.removeAll();
        }
    }

    ,onStoreLoad: function(store, recs, successful, eOpts) {
        if(!this.rendered ||
            !this.getEl().isVisible(true) ||
            (successful !== true)
        ) {
            return;
        }

        this.data = store.proxy.reader.rawData.facets;

        if(this.viewParams) {
            var vp = this.viewParams;
            if(!Ext.isEmpty(vp.facet)) {
                this.selectedFacets = [vp.facet];
            }
            if(!Ext.isEmpty(vp.chart_type)) {
                var b = this.refOwner.buttonCollection.get(vp.chart_type + 'chart');
                if(b) {
                    this.currentButton = b;
                }
            }
        }

        this.loadAvailableFacets();
        this.onChangeChartClick(this.currentButton);
    }

    ,onChartItemClick: function(o, event){
        var params = {
            view: 'grid'
            ,filters: Ext.apply({}, this.store.extraParams.filters)
        };
        var filterBy = this.facetsCombo.getValue();
        params['filters'][filterBy] = [{
            f: filterBy
            ,mode: 'OR'
            ,values: [o.storeItem.get('id')]
        }];

        this.fireEvent('changeparams', params);
    }

    ,onFacetChange: function(combo, records, index) {
        var record = Ext.isArray(records)
            ? records[0]
            : records;

        this.selectedFacets[0] = record.get('id');
        this.onChangeChartClick(this.currentButton);
    }

    /**
     * handler for sort button items
     * @param  object b
     * @param  event e
     * @return void
     */
    ,onSortButtonClick: function(b, e) {
        var params = Ext.valueFrom(this.viewParams, {});

        params.sort = b.sort;
        params.direction = b.direction;

        this.viewParams = params;

        this.loadChartData();

        this.sortButton.setText(b.text);
    }

    /**
     * detect sorter to be used according to given params
     * default is sort by name ascending
     *
     * @param  object params
     * @return function | null
     */
    ,detectSorter: function(params) {
        if(Ext.isEmpty(params.sort)) {
            params.sort = 'name';
        }
        if(Ext.isEmpty(params.direction)) {
            params.direction = 'asc';
        }

        var translationIndex = 'SortBy' + Ext.String.capitalize(params.sort) + Ext.String.capitalize(params.direction);

        this.sortButton.setText(L[translationIndex]);

        return this.callParent(arguments);
    }
});
