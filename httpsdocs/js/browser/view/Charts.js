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
            ,scale: 'medium'
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
                ,scale: 'medium'
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
                ,scale: 'medium'
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
                ,scale: 'medium'
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

        this.chartBlock = new CB.widget.block.Chart({
            region: 'center'
            ,scrollable: true
            ,border: false
            ,listeners: {
                scope: this
                ,itemclick: this.onChartItemClick
            }
        });

        Ext.apply(this, {
            title: L.Charts
            ,header: false
            ,layout: 'fit'
            ,items: [
                this.chartBlock
            ]
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });

        this.callParent(arguments);

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
        this.chartData.charts = [b.itemId.split('chart').shift()];

        this.onChangeChart();
    }

    ,onChangeChart: function() {
        var BC = this.refOwner.buttonCollection
            ,ch = this.chartData.charts;

        BC.get('barchart').toggle(ch.indexOf('bar') > -1, true);
        BC.get('columnchart').toggle(ch.indexOf('column') > -1, true);
        BC.get('piechart').toggle(ch.indexOf('pie') > -1, true);

        this.chartBlock.changeCharts(ch);
    }

    ,onStoreLoad: function(store, recs, successful, eOpts) {
        if(!this.rendered ||
            !this.getEl().isVisible(true) ||
            (successful !== true)
        ) {
            return;
        }

        this.loadRemoteData(store.proxy.reader.rawData);
    }

    ,loadRemoteData: function(rd) {
        var selectedValues = {};

        rd.sorter = this.detectSorter(Ext.valueFrom(rd.view, {}));

        if(this.chartData) {
            if(this.selectedFacets) {
                selectedValues = {
                    facet: this.selectedFacets[0]
                };
            }

            selectedValues.charts = this.chartData.charts;
        }

        this.chartData = this.chartBlock.loadData(rd, selectedValues);

        this.selectedFacets = [this.chartData.facet];

        this.loadAvailableFacets(rd.facets);

        // this.onChangeChart();
    }

    ,loadAvailableFacets: function(facets) {
        var data = [];

        Ext.iterate(
            facets
            ,function(key, val, o) {
                // if(Ext.isEmpty(this.selectedFacets)) {
                //     this.selectedFacets = [key];
                // }
                data.push({
                    id: key
                    ,name: Ext.htmlDecode(Ext.valueFrom(val['title'], L['facet_' + key]))
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

        this.loadRemoteData(this.store.proxy.reader.rawData);
    }

    /**
     * handler for sort button items
     * @param  object b
     * @param  event e
     * @return void
     */
    ,onSortButtonClick: function(b, e) {
        var rd = this.store.proxy.reader.rawData;

        rd.sort = b.sort;
        rd.direction = b.direction;

        this.loadRemoteData(rd);

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
