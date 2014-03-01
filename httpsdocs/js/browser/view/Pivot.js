Ext.namespace('CB.browser.view');

CB.browser.view.Pivot = Ext.extend(CB.browser.view.Interface,{
    hideBorders: true
    ,tbarCssClass: 'x-panel-white'
    ,layout: 'border'
    ,activeChart: 'table'
    ,initComponent: function(){

        this.params = Ext.apply(
            {
                from: 'pivot'
                ,rows:0
            }
            ,this.params || {}
        );

        this.instanceId = Ext.id();

        Ext.chart.Chart.CHART_URL = '/libx/ext/resources/charts.swf';

        this.rowsCombo = new Ext.form.ComboBox({
            xtype: 'combo'
            ,id: 'rowsCombo' + this.instanceId
            ,selectedFacetIndex: 0
            ,forceSelection: true
            ,editable: false
            ,triggerAction: 'all'
            ,lazyRender: true
            ,mode: 'local'
            ,fieldLabel: L.Rows
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

        this.colsCombo = new Ext.form.ComboBox({
            xtype: 'combo'
            ,id: 'colsCombo' + this.instanceId
            ,selectedFacetIndex: 1
            ,forceSelection: true
            ,editable: false
            ,triggerAction: 'all'
            ,lazyRender: true
            ,mode: 'local'
            ,fieldLabel: L.Rows
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
                text: L.ChartArea
                ,id: 'barchart' + this.instanceId
                ,config: 'stackedbarchart'
                ,enableToggle: true
                ,allowDepress: true
                ,iconCls: 'ib-chart-bar'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + this.instanceId
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.Button({
                text: L.ChartArea
                ,id: 'columnchart' + this.instanceId
                ,config: 'stackedcolumnchart'
                ,enableToggle: true
                ,allowDepress: true
                ,iconCls: 'ib-chart-column'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + this.instanceId
                ,scope: this
                ,handler: this.onChangeChartClick
            })
            ,new Ext.form.Label({
                id: 'rowsComboLabel' + this.instanceId
                ,text: L.Rows + ': '
            })
            ,this.rowsCombo
            ,new Ext.form.Label({
                id: 'colsComboLabel' + this.instanceId
                ,text: L.Columns + ': '
            })
            ,this.colsCombo
        );

        this.chartDataStore = new Ext.data.JsonStore({
            fields:['id', 'name', {name:'items', type: 'int'}],
            data: []
        });

        // this.chartConfigs = {
        //     'stackedbarchart': {
        //         xtype: 'stackedbarchart'
        //         // ,store: this.chartDataStore
        //         ,yField: 'name'
        //         ,xAxis: new Ext.chart.NumericAxis({
        //             stackingEnabled: true
        //         })
        //         ,series: [{
        //             xField: 'items'
        //             ,displayName: 'Items'
        //         }]
        //     }
        //     ,'stackedcolumnchart': {
        //         xtype: 'columnchart'
        //         ,store: this.chartDataStore
        //         ,xField: 'name'
        //         ,yField: 'items'
        //         ,series: [{
        //             yField: 'items'
        //             ,displayName: 'Items'
        //         }]
        //         ,listeners: {
        //             scope: this
        //             ,itemclick: this.onChartItemClick
        //         }
        //     }
        //     // ,'pivotgrid': {
        //     //     xtype: 'pivotgrid'
        //     //     ,border: false
        //     //     ,autoScroll: true
        //     //     ,store: this.chartDataStore
        //     //     ,aggregator: 'sum'
        //     //     ,measure   : 'items'
        //     //     ,leftAxis: [
        //     //         {
        //     //             width: 60
        //     //             ,dataIndex: 'name'
        //     //             ,direction: 'ASC'
        //     //         }
        //     //     ]
        //     //     ,topAxis: [
        //     //         {
        //     //             dataIndex: 'items'
        //     //             ,direction: 'ASC'
        //     //         }
        //     //     ]
        //     //     ,listeners: {
        //     //         scope: this
        //     //         ,itemclick: this.onChartItemClick
        //     //         ,afterrender: function(c){
        //     //             c.view.refresh(true);
        //     //         }
        //     //     }
        //     // }
        // };

        this.chartContainer = new Ext.Panel({
            region: 'center'
            ,layout: 'fit'
        });

        Ext.apply(this, {
            title: L.Pivot
            ,header: false
            ,items: this.chartContainer
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });

        CB.browser.view.Pivot.superclass.initComponent.apply(this, arguments);

        this.addEvents('reload');
        this.enableBubble(['reload']);

        this.selectedFacets = [];

        this.store.proxy.on('load', this.onProxyLoad, this);
    }

    ,getViewParams: function() {
        this.params.selectedFacets = this.selectedFacets;
        return this.params;
    }

    ,onActivate: function() {
        this.fireEvent(
            'settoolbaritems'
            ,[
                'barchart' + this.instanceId
                ,'columnchart' + this.instanceId
                ,'rowsComboLabel'  + this.instanceId
                ,'rowsCombo'  + this.instanceId
                ,'colsComboLabel'  + this.instanceId
                ,'colsCombo'  + this.instanceId
            ]
        );
    }

    ,onChangeChartClick: function(b, e) {
        var i, j;

        if(!Ext.isEmpty(b)) {
            this.activeChart = !b.pressed
                ? 'table'
                : b.config;
        }

        this.loadChartData();

        this.chartContainer.removeAll(true);

        if(this.activeChart == 'table') {
            var html = '';

            var hr = '<th> &nbsp; </th>';
            Ext.iterate(
                this.pivot.titles[1]
                ,function(k, v, o) {
                    hr += '<th>' + v + '</th>';
                }
                ,this
            );
            html += '<tr>' + hr + '<th>' + L.Total + '</th></tr>';

            Ext.iterate(
                this.pivot.titles[0]
                ,function(k, v, o) {
                    var r = '<th>' + v + '</th>';
                    Ext.iterate(
                        this.pivot.titles[1]
                        ,function(q, z, y) {
                            r += '<td>' + Ext.value(this.refs[k + '_' + q], '') + '</td>';
                        }
                        ,this
                    );

                    html += '<tr>' + r + '<td class="total">' + Ext.value(this.refs[k + '_t'], '') + '</td></tr>';
                }
                ,this
            );

            var total = 0;
            var r = '<th>' + L.Total + '</th>';
            Ext.iterate(
                this.pivot.titles[1]
                ,function(q, z, y) {
                    var nr = Ext.value(this.refs['t_' + q], '');
                    r += '<td class="total">' + nr + '</td>';
                    if(!isNaN(nr)) {
                        total += nr;
                    }
                }
                ,this
            );

            html += '<tr>' + r + '<td class="total">' + total + '</td></tr>';

            html = '<table class="pivot">' + html + '</table>';

            clog('html', html);

            this.chartContainer.add({
                xtype: 'panel'
                ,border: false
                ,autoScroll: true
                ,padding: 10
                ,html: html
            });
        } else {

            /* create data, stores and charts on the fly */
            var fields1 = [this.selectedFacets[0]]
                ,fields2 = [this.selectedFacets[1]]
                ,data1 = []
                ,data2 = []
                ,serie
                ,series1 = []
                ,series2 = []
                ;

            var axis1 = (this.activeChart == 'stackedbarchart') ? 'y' : 'x';
            var axis2 = (axis1 == 'x') ? 'y' : 'x';

            Ext.iterate(
                this.pivot.titles[0]
                ,function(k, v, o) {
                    fields2.push('f' + k);

                    serie = {
                        displayName: v
                    };
                    serie[axis2 + 'Field'] = 'f' + k;
                    series2.push(serie);

                    var r = {};
                    r[this.selectedFacets[0]] = '"' + v + '"';
                    Ext.iterate(
                        this.pivot.titles[1]
                        ,function(q, z, y) {
                            var w = Ext.value(this.refs[k + '_' + q], '');
                            if(!Ext.isEmpty(w)) {
                                r['f' + q] = w;
                            }
                        }
                        ,this
                    );
                    data1.push(r);
                }
                ,this
            );

            Ext.iterate(
                this.pivot.titles[1]
                ,function(k, v, o) {
                    fields1.push('f' + k);

                    serie = {
                        displayName: v
                    };
                    serie[axis2 + 'Field'] = 'f' + k;
                    series1.push(serie);

                    var r = {};
                    r[this.selectedFacets[1]] = '"' + v + '"';
                    Ext.iterate(
                        this.pivot.titles[0]
                        ,function(q, z, y) {
                            var w = Ext.value(this.refs[q + '_' + k], '');
                            if(!Ext.isEmpty(w)) {
                                r['f' + q] = w;
                            }
                        }
                        ,this
                    );
                    data2.push(r);
                }
                ,this
            );

            /*{
                xtype: 'stackedbarchart'
                ,store: new Ext.data.JsonStore({
                    fields: fields1 //['year', 'comedy', 'action', 'drama', 'thriller']
                    ,data: data1
                    // [
                    //     {year: 2005, comedy: 34000000, action: 23890000, drama: 18450000, thriller: 20060000},
                    //     {year: 2006, comedy: 56703000, action: 38900000, drama: 12650000, thriller: 21000000},
                    //     {year: 2007, comedy: 42100000, action: 50410000, drama: 25780000, thriller: 23040000},
                    //     {year: 2008, comedy: 38910000, action: 56070000, drama: 24810000, thriller: 26940000}
                    // ]
                })
                ,yField: 'year',
                ,xAxis: new Ext.chart.NumericAxis({
                    stackingEnabled: true
                    // ,labelRenderer: Ext.util.Format.usMoney
                })
                ,series:
                // [{
                //         xField: 'comedy',
                //         displayName: 'Comedy'
                //     },{
                //         xField: 'action',
                //         displayName: 'Action'
                //     },{
                //         xField: 'drama',
                //         displayName: 'Drama'
                //     },{
                //         xField: 'thriller',
                //         displayName: 'Thriller'
                //     }
                // ]
            }/**/
            // this.chart1 = new Ext.create(this.chartConfigs['stackedbarchart']);
            // // this.chartContainer.add(this.chart);
            // this.chart2 = new Ext.create(this.chartConfigs['stackedcolumnchart']);

            clog('fields1', fields1);
            clog('fields2', fields2);
            clog('data1', data1);
            clog('data2', data2);
            clog('series1', series1);
            clog('series2', series2);

            var chartItems = [
                {
                    xtype: this.activeChart
                    ,store: new Ext.data.JsonStore({
                        fields: fields1
                        ,data: data1
                    })
                    ,yField: this.selectedFacets[0]

                    ,legend: {
                         position: 'bottom',
                         labelFont: 'tahoma'
                    }
                    // ,xAxis: new Ext.chart.NumericAxis({
                    //     stackingEnabled: true
                    //     ,scope: this
                    //     ,labelRenderer: function() {
                    //         clog('label renderer', arguments);
                    //     }
                    // })
                    ,series: series1
                }, {
                    xtype: this.activeChart
                    ,store: new Ext.data.JsonStore({
                        fields: fields2
                        ,data: data2
                    })
                    ,yField: this.selectedFacets[1]
                    // ,xAxis: new Ext.chart.NumericAxis({
                    //     stackingEnabled: true
                    // })
                    ,series: series2
                }
            ];
            chartItems[0][axis1+'Field'] = this.selectedFacets[0];
            chartItems[1][axis1+'Field'] = this.selectedFacets[1];

            this.chartContainer.add({
                xtype: 'panel'
                ,autoScroll: true
                ,items: chartItems
            });
        }
        this.chartContainer.syncSize();
    }

    ,loadAvailableFacets: function() {
        var data = [];
        Ext.iterate(
            this.data.facets
            ,function(key, val, o) {
                if(Ext.isEmpty(this.selectedFacets)) {
                    this.selectedFacets = [key];
                } else if(this.selectedFacets.length < 2) {
                    this.selectedFacets[1] = key;
                }
                data.push({
                    id: key
                    ,name: Ext.value(val['title'], L['facet_'+key])
                });
            }
            ,this
        );
        this.rowsCombo.store.loadData(data);
        this.colsCombo.store.loadData(data);
        this.rowsCombo.setValue(this.selectedFacets[0]);
        this.colsCombo.setValue(this.selectedFacets[1]);
    }

    ,loadChartData: function() {
        var data = {};

        // loading facetsa list
        Ext.iterate(
            this.data.facets
            ,function(key, val, o) {
                data[key] = CB.FacetList.prototype.getFacetData(key, val.items);
                for (var i = 0; i < data[key].length; i++) {
                    if(Ext.isObject(data[key][i].items)) {
                        data[key][i].name = data[key][i].items.name;
                        data[key][i].items = data[key][i].items.count;
                    }
                    data[key][i].name = App.shortenString(data[key][i].name, 30);
                }
            }
            ,this
        );

        // create refs object for commot usage
        this.refs = {};
        if(!Ext.isEmpty(this.data.pivot)) {
            data = this.data.pivot[this.selectedFacets.join(',')];
            if(data && data.data) {
                for (var i = 0; i < data.data.length; i++) {
                    var f1 = data.data[i];
                    if(!Ext.isEmpty(f1.pivot)) {
                        for (var j = 0; j < f1.pivot.length; j++) {
                            var f2 = f1.pivot[j];
                            this.refs[f1.value + '_' + f2.value] = f2.count;
                            if(Ext.isEmpty(this.refs['t_' + f2.value])) {
                                this.refs['t_' + f2.value] = 0;
                            }
                            if(!isNaN(f2.count)) {
                                this.refs['t_' + f2.value] += f2.count;
                            }
                        }
                    }
                    this.refs[f1.value + '_t'] = f1.count;
                }
            }
        }
    }

    ,onProxyLoad: function(proxy, o, options) {
        if(!this.rendered ||
            !this.getEl().isVisible(true) ||
            (o.result.success !== true)
        ) {
            return;
        }
        this.data = o.result;


        this.pivot = {
            data: {}
            ,titles: [] // 2 levels, for both facets
        };

        clog('this.selectedFacets', this.selectedFacets);
        if(this.data.pivot) {
            if(Ext.isEmpty(this.selectedFacets)) {
                //just get the facets the server returned the pivot for
                var key = '';
                Ext.iterate(
                    this.data.pivot
                    ,function(k, v) {
                        key = k;
                    }
                    ,this
                );
                this.selectedFacets = key.split(',');
            }
            if(this.data.pivot[this.selectedFacets.join(',')]) {
                this.pivot.data = this.data.pivot[this.selectedFacets.join(',')].data;
                this.pivot.titles = this.data.pivot[this.selectedFacets.join(',')].titles;
            }
        }

        this.loadAvailableFacets();
        clog(this.selectedFacets, this.pivot);
        this.onChangeChartClick();
    }

    ,onChartItemClick: function(o){
        var rec = this.chartDataStore.getAt(o.index);
        Ext.example.msg('Item Selected', 'You chose {0}.', rec.get('name'));
    }

    ,onFacetChange: function(combo, record, index) {
        this.selectedFacets[combo.selectedFacetIndex] = record.get('id');
        this.fireEvent('reload', this);
        // this.loadChartData();
    }
});

Ext.reg('CBBrowserViewPivot', CB.browser.view.Pivot);
