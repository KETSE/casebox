Ext.namespace('CB.browser.view');

CB.browser.view.Charts = Ext.extend(CB.browser.view.Interface,{
    hideBorders: true
    ,tbarCssClass: 'x-panel-white'
    ,layout: 'border'
    ,params: {
        rows:0
        ,facets: 'general'
    }

    ,initComponent: function(){
        var viewGroup = Ext.id();
        Ext.chart.Chart.CHART_URL = '/libx/ext/resources/charts.swf';

        this.facetsCombo = new Ext.form.ComboBox({
            xtype: 'combo'
            ,id: 'facetscombo'
            ,forceSelection: true
            ,triggerAction: 'all'
            ,lazyRender: true
            ,mode: 'local'
            ,fieldLabel: 'Facets'
            ,editable: false
            ,store: new Ext.data.JsonStore({
                fields:['id', 'name']
            })
            ,displayField: 'name'
            ,valueField: 'id'
            ,listeners: {
                scope: this
                ,select: this.onFacetChange
            }
        });

        this.refOwner.buttonCollection.addAll(
            new Ext.Button({
                text: L.ChartLine
                ,id: 'linechart'
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-chart-line'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.ChartArea
                ,id: 'barchart'
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-chart-bar'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.ChartArea
                ,id: 'stackedbarchart'
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-chart-bar-stacked'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.ChartArea
                ,id: 'columnchart'
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-chart-column'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.ChartBar
                ,id: 'stackedcolumnchart'
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-chart-column-stacked'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.ChartPie
                ,id: 'piechart'
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-chart-pie'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.ChartTable
                ,id: 'pivotgrid'
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-chart-table'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.form.Label({
                id: 'facetslabel'
                ,text: 'Facets: '
            })
            ,this.facetsCombo
        );

        this.chartDataStore = new Ext.data.JsonStore({
            fields:['id', 'name', {name:'items', type: 'int'}],
            data: []
        });

        this.chartConfigs = {
            'linechart': {
                xtype: 'linechart'
                ,store: this.chartDataStore
                ,xField: 'name'
                ,yField: 'items'
                ,listeners: {
                    scope: this
                    ,itemclick: this.onChartItemClick
                }
            }
            ,'barchart': {
                xtype: 'barchart'
                ,store: this.chartDataStore
                ,yField: 'name'
                ,xAxis: new Ext.chart.NumericAxis({
                    stackingEnabled: true
                })
                ,series: [{
                    xField: 'items'
                    ,displayName: 'Items'
                }]
            }
            ,'stackedbarchart': {
                xtype: 'barchart'
                ,store: this.chartDataStore
                ,yField: 'name'
                ,xAxis: new Ext.chart.NumericAxis({
                    stackingEnabled: true
                })
                ,series: [{
                    xField: 'items'
                    ,displayName: 'Items'
                }]
            }
            ,'columnchart': {
                xtype: 'columnchart'
                ,store: this.chartDataStore
                ,xField: 'name'
                ,yField: 'items'
                ,listeners: {
                    scope: this
                    ,itemclick: this.onChartItemClick
                }
            }
            ,'stackedcolumnchart': {
                xtype: 'columnchart'
                ,store: this.chartDataStore
                ,xField: 'name'
                ,yField: 'items'
                ,series: [{
                    yField: 'items'
                    ,displayName: 'Items'
                }]
                ,listeners: {
                    scope: this
                    ,itemclick: this.onChartItemClick
                }
            }
            ,'piechart': {
                xtype: 'piechart'
                ,store: this.chartDataStore
                ,dataField: 'items'
                ,categoryField: 'name'
                ,listeners: {
                    scope: this
                    ,itemclick: this.onChartItemClick
                }
            }
            ,'pivotgrid': {
                xtype: 'pivotgrid'
                ,border: false
                ,autoScroll: true
                ,store: this.chartDataStore
                ,aggregator: 'sum'
                ,measure   : 'items'
                ,leftAxis: [
                    {
                        width: 60
                        ,dataIndex: 'name'
                        ,direction: 'ASC'
                    }
                ]
                ,topAxis: [
                    {
                        dataIndex: 'items'
                        ,direction: 'ASC'
                    }
                ]
                ,listeners: {
                    scope: this
                    ,itemclick: this.onChartItemClick
                    ,afterrender: function(c){
                        c.view.refresh(true);
                    }
                }
            }
        };

        this.chartContainer = new Ext.Panel({
            region: 'center'
            ,layout: 'fit'
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
        this.currentButton = this.refOwner.buttonCollection.get('linechart');
        this.store.proxy.on('load', this.onProxyLoad, this);
    }

    ,getViewParams: function() {
        return this.params;
    }

    ,onActivate: function() {
        this.fireEvent(
            'settoolbaritems'
            ,[
                ,'linechart'
                ,'barchart'
                ,'stackedbarchart'
                ,'columnchart'
                ,'stackedcolumnchart'
                ,'piechart'
                ,'pivotgrid'
                ,'-'
                ,'facetslabel'
                ,'facetscombo'
            ]
        );
    }

    ,onChangeChartClick: function(b, e) {
        b.toggle(true);
        this.loadChartData();
        if(Ext.isEmpty(this.chart) || (this.currentChartType != b.id)) {
            this.chartContainer.removeAll(true);
            this.currentChartType = b.id;
            this.chart = new Ext.create(this.chartConfigs[b.id]);
            this.chartContainer.add(this.chart);
        }
        this.chartContainer.syncSize();
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
                    ,name: Ext.value(val['name'], L['facet_'+key])
                });
            }
            ,this
        );
        this.facetsCombo.store.loadData(data);
        this.facetsCombo.setValue(this.selectedFacets[0]);
    }

    ,loadChartData: function() {
        var data = {};
        Ext.iterate(
            this.data
            ,function(key, val, o) {
                data[key] = CB.FacetList.prototype.getFacetData(key, val.items);
            }
            ,this
        );
        this.chartData = data;
        this.chartDataStore.loadData(data[this.selectedFacets[0]]);
    }

    ,onProxyLoad: function(proxy, o, options) {
        if(!this.rendered ||
            !this.getEl().isVisible(true) ||
            (o.result.success !== true)
        ) {
            return;
        }
        this.data = o.result.facets;
        this.loadAvailableFacets();
        this.onChangeChartClick(this.currentButton);
    }

    ,onChartItemClick: function(o){
        var rec = this.chartDataStore.getAt(o.index);
        Ext.example.msg('Item Selected', 'You chose {0}.', rec.get('name'));
    }

    ,onFacetChange: function(combo, record, index) {
        this.selectedFacets[0] = record.get('id');
        this.loadChartData();
    }
});

Ext.reg('CBBrowserViewCharts', CB.browser.view.Charts);
