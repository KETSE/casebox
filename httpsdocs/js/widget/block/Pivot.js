Ext.namespace('CB');

Ext.define('CB.widget.block.Pivot', {
    extend: 'CB.widget.block.Chart'

    ,alias: 'CBWidgetBlockPivot'

    ,xtype: 'CBWidgetBlockPivot'

    ,width: 400
    ,height: 400

    ,loadData: function(data, overrides) {
        var rez = {
            data: {}
            ,titles: [] // 2 levels, for both facets
            ,xField: ''
            ,yField: ''
            ,charts: []
            ,refs: {}
        }
        ,d = {};

        this.selectedStat = null;

        if (overrides) {
            Ext.apply(rez, overrides);
        }

        if(data.pivot) {
            var key = ''
                ,arr;
            if(Ext.isEmpty(rez.xField) || Ext.isEmpty(rez.yField)) {
                //just get the facets the server returned the pivot for

                Ext.iterate(
                    data.pivot
                    ,function(k, v) {
                        key = k;
                    }
                    ,this
                );

                var q = key.split(',');
                rez.xField = q[0];
                rez.yField = q[1];
            }

            if(data.pivot[key]) {
                Ext.copyTo(rez, data.pivot[key], 'data,titles,stats');
            }
        }

        if(data.view) {
            var vp = data.view;
            rez.view = vp;

            this.selectedStat = vp.selectedStat;

            if(Ext.isEmpty(rez.charts)  && vp.pivotType) {
                rez.charts = Ext.isString(vp.pivotType)
                    ? [vp.pivotType]
                    : vp.pivotType;
            }

            if(Ext.isEmpty(rez.xField) && vp.rows && !Ext.isEmpty(vp.rows.facet)) {
                rez.xField = vp.rows.facet;
            }
            if(Ext.isEmpty(rez.xField) && vp.cols && !Ext.isEmpty(vp.cols.facet)) {
                rez.yField = vp.cols.facet;
            }
        }

        // loading facets list
        Ext.iterate(
            data.facets
            ,function(key, val, o) {
                d[key] = CB.facet.List.prototype.getFacetData(key, val.items);

                for (var i = 0; i < d[key].length; i++) {
                    if(Ext.isObject(d[key][i].items)) {
                        d[key][i].name = d[key][i].items.name;
                        d[key][i].count = d[key][i].items.count;
                    }
                    d[key][i].name = App.shortenString(d[key][i].name, 30);
                }
            }
            ,this
        );

        // create refs object for common usage
        if(!Ext.isEmpty(data.pivot)) {
            var i, j, f1, f2, value, f1t;

            d = data.pivot[rez.xField + ',' + rez.yField];

            if(d && d.data) {
                for (i = 0; i < d.data.length; i++) {
                    f1 = d.data[i];
                    f1t = 0;
                    if(!Ext.isEmpty(f1.pivot)) {
                        for (j = 0; j < f1.pivot.length; j++) {
                            f2 = f1.pivot[j];

                            value = this.getFacetCount(f2);

                            if(value > 0) {
                                rez.refs[f1.value + '_' + f2.value] = value;

                                if(Ext.isEmpty(rez.refs['t_' + f2.value])) {
                                    rez.refs['t_' + f2.value] = 0;
                                }

                                if(Ext.isNumeric(value)) {
                                    rez.refs['t_' + f2.value] += value;
                                    f1t += value;
                                }
                            }
                        }
                    }

                    // value = this.getFacetCount(f1);
                    // if(value > 0) {
                    //     rez.refs[f1.value + '_t'] = this.getFacetCount(f1);
                    // }
                    if(f1t > 0) {
                        rez.refs[f1.value + '_t'] = f1t;
                    }
                }
            }
        }

        if (Ext.isEmpty(rez.charts)) {
            rez.charts = ['table'];
        }

        this.chartData = rez;

        this.changeCharts(rez.charts);

        return rez;
    }

    /**
     * display desired charts
     * @param  array charts
     * @return void
     */
    ,changeCharts: function(charts) {
        var data = this.chartData;

        this.removeAll(true);

        if(charts.indexOf('table') > -1) {
            var columns = [{
                    text: '#'
                    ,locked: true
                    ,tdCls: 'fwB'
                    ,width: 200
                    ,renderer: this.cellRenderer
                    ,dataIndex: 'title'
                }]
                ,fields= [{
                    name: 'fid'
                    ,type: 'number'
                },{
                    name: 'title'
                },{
                    name: 'total'
                    ,type: 'number'
                },]
                ,recs = [];

            //form columns array
            Ext.iterate(
                data.titles[1]
                ,function(k, v, o) {
                    columns.push({
                        text: v
                        ,width: 50
                        ,cls: 'fwB'
                        ,align: 'right'
                        ,renderer: this.cellRenderer
                        ,dataIndex: 'f_' + k
                    });
                    fields.push({
                        name: 'f_' + k
                        ,type: 'number'
                    });
                }
                ,this
            );

            columns.push({
                text: L.Total
                ,width: 50
                ,align: 'right'
                ,cls: 'fwB cG'
                ,tdCls: 'fwB cG'
                ,dataIndex: 'total'
            });

            //form data records
            Ext.iterate(
                data.titles[0]
                ,function(k, v, o) {
                    if(Ext.isEmpty(data.refs[k + '_t'])) {
                        return;
                    }

                    var r = {
                        fid: k
                        ,title: v
                    };

                    Ext.iterate(
                        data.titles[1]
                        ,function(q, z, y) {
                            r['f_' + q] = Ext.valueFrom(data.refs[k + '_' + q], '');
                        }
                        ,this
                    );
                    r['total'] = Ext.util.Format.number(Ext.valueFrom(data.refs[k + '_t'], ''), '0.##');
                    recs.push(r);
                }
                ,this
            );

            //add total row
            var total = 0;
            var r = {
                fid: 'total'
                ,title: L.Total
            };

            Ext.iterate(
                data.titles[1]
                ,function(q, z, y) {
                    var nr = Ext.valueFrom(data.refs['t_' + q], '');
                    r['f_' + q] = Ext.util.Format.number(nr, '0.##');
                    if(Ext.isNumeric(nr)) {
                        total += nr;
                    }
                }
                ,this
            );

            //get stats value if set
            var value = this.getFacetCount(data);
            r['total'] = Ext.util.Format.number(value ? value : total, '0.##');

            recs.push(r);

            //adding grid
            var table = this.add({
                xtype: 'grid'
                ,flex: 1
                ,width: '99%'
                ,padding: 10
                ,store: Ext.create('Ext.data.JsonStore', {
                    fields: fields
                    ,data: recs
                })
                ,columnLines: true
                ,columns: columns
                ,viewConfig: {
                    stripeRows: true
                }
                ,listeners: {
                    scope: this
                    ,celldblclick: this.onCellDblClick
                }
            });
        }

        if(charts.indexOf('stackedBars') > -1) {
            this.addChart('bar');
        }
        if(charts.indexOf('stackedColumns') > -1) {
            this.addChart('column');
        }

        this.updateLayout();
    }

    ,addChart: function(chartType) {
        var d = this.chartData;

        /* create data, stores and charts on the fly */
        var tipsCfg = {
            trackMouse: true
            ,style: 'background: #FFF; overflow: visible'
            ,height: 20
            ,width: 200
            ,renderer: function(storeItem, item) {
                this.setTitle(item.value.join(': '));
            }
        }
        ,series = [
                {
                    xField: d.xField
                    ,yField: []
                    ,title: []
                    ,tips: tipsCfg
                },{
                    xField: d.yField
                    ,yField: []
                    ,title: []
                    ,tips: tipsCfg
                }
            ]
            ,data = [[], []];

        Ext.iterate(
            d.titles[0]
            ,function(k, v, o) {
                //add fields and titles
                series[1].yField.push('f' + k);
                series[1].title.push(v);

                //add data
                var r = {};
                r[d.xField] = htmlEntityDecode(v); //'"' + v + '"';
                Ext.iterate(
                    d.titles[1]
                    ,function(q, z, y) {
                        var w = Ext.valueFrom(d.refs[k + '_' + q], '');
                        r['f' + q] = Ext.isEmpty(w) ? 0 : w;
                    }
                    ,this
                );
                data[0].push(r);
            }
            ,this
        );

        Ext.iterate(
            d.titles[1]
            ,function(k, v, o) {
                //add fields and titles
                series[0].yField.push('f' + k);
                series[0].title.push(v);

                //add data
                var r = {};
                r[d.yField] = htmlEntityDecode(v); //'"' + v + '"';
                Ext.iterate(
                    d.titles[0]
                    ,function(q, z, y) {
                        var w = Ext.valueFrom(d.refs[q + '_' + k], '');
                        r['f' + q] = Ext.isEmpty(w) ? 0 : w;
                    }
                    ,this
                );
                data[1].push(r);
            }
            ,this
        );

        var chartItems = []
            ,i = 0;

        // for (i = 0; i < series.length; i++) {
            var serie = series[i];

            var cfg = {
                height: Math.max(data[i].length * 25, 400)
                ,width: '100%'
                ,store: new Ext.data.JsonStore({
                    fields: [serie.xField].concat(serie.yField)
                    ,proxy: {
                        type: 'memory'
                        ,reader: {
                            type: 'json'
                        }
                    }
                    ,data: data[i]
                })
                ,items: [{
                    type  : 'text',
                    text  : ' ',
                    x : 40, //the sprite x position
                    y : 12  //the sprite y position
                }]
                ,axes: [{
                    type: 'category'
                    ,position: (chartType === 'bar') ? 'left' : 'bottom'
                    ,fields: serie.xField
                    ,grid: true
                    ,label: {
                         rotate: {
                            degrees: (chartType === 'bar')
                                ? 0
                                : 270
                        }
                    }
                    ,minimum: 0
                }, {
                    type: 'numeric'
                    ,position: (chartType === 'column') ? 'left' : 'bottom'
                    ,fields: serie.yField
                    ,grid: true
                }]

                ,legend: (this.showLegend !== false)
                    ? {
                        position: 'right'
                        ,boxStrokeWidth: 0
                    }
                    : false
                ,seriesStyles: this.seriesStyles
                ,series: [
                    Ext.apply(
                        serie
                        ,{
                            type: chartType
                            ,stacked: true
                            ,style: {
                                opacity: 0.80
                            }
                            ,highlight: {
                                'stroke-width': 2
                                ,stroke: '#fff'
                            }
                        }
                    )
                ]
            };

            chartItems.push(
                Ext.create(
                    'Ext.chart.Chart'
                    ,cfg
                )
            );
        // }

        return this.add(chartItems);
    }

    ,cellRenderer: function(value, metaData, record) {
        if(value === 0) {
            return '';
        }

        if(record.get('title') == L.Total) {
            metaData.tdCls = 'fwB cG';
        }

        return value;
    }

    ,onCellDblClick: function(grid, td, cellIndex, record, tr, rowIndex, e, eOpts) {
        var row = record.get('fid')
            ,col = grid.headerCt.visibleColumnManager.getHeaderAtIndex(cellIndex).dataIndex
            ,filter = [
                isNaN(row)
                    ? null
                    : row
                ,(col == 'total')
                    ? null
                    : col.substr(2)
            ];

        this.fireEvent('filterclick', filter);
    }

    ,getFacetCount: function(f) {
        var rez = 0
            ,sf = this.selectedStat;

        if(sf &&
            sf.field &&
            f.stats &&
            f.stats.stats_fields &&
            f.stats.stats_fields[sf.field]
        ) {
            if(f.stats.stats_fields[sf.field][sf.type]) {
                rez = f.stats.stats_fields[sf.field][sf.type];
            }

        } else if(f.count){
            rez = f.count;
        }

        return rez;
    }

    ,getHtml: function() {
        var rez = ''
            ,data = this.chartData
            ,hr = '<th> &nbsp; </th>';

        Ext.iterate(
            data.titles[1]
            ,function(k, v, o) {
                hr += '<th title="' + Ext.String.htmlEncode(v) +'">' + v + '</th>';
            }
            ,this
        );
        rez += '<tr>' + hr + '<th>' + L.Total + '</th></tr>';

        Ext.iterate(
            data.titles[0]
            ,function(k, v, o) {
                if(Ext.isEmpty(data.refs[k + '_t'])) {
                    return;
                }

                var r = '<th style="text-align:left" title="' + Ext.String.htmlEncode(v) + '">' +
                    Ext.String.htmlEncode(App.shortenString(v, 25)) + '</th>';

                Ext.iterate(
                    data.titles[1]
                    ,function(q, z, y) {
                        r += '<td f="' + k + '|' + q + '">' + Ext.valueFrom(data.refs[k + '_' + q], '') + '</td>';
                    }
                    ,this
                );

                rez += '<tr>' + r + '<td class="total" f="'+ k +'|">' + Ext.util.Format.number(Ext.valueFrom(data.refs[k + '_t'], ''), '0.##') + '</td></tr>';
            }
            ,this
        );

        var total = 0;
        var r = '<th>' + L.Total + '</th>';
        Ext.iterate(
            data.titles[1]
            ,function(q, z, y) {
                var nr = Ext.valueFrom(data.refs['t_' + q], '');
                r += '<td class="total" f="|'+ q +'">' + Ext.util.Format.number(nr, '0.##') + '</td>';
                if(Ext.isNumeric(nr)) {
                    total += nr;
                }
            }
            ,this
        );

        //get stats value if set
        var value = this.getFacetCount(data);

        rez += '<tr>' + r + '<td class="total">' + Ext.util.Format.number(value ? value : total, '0.##') + '</td></tr>';

        rez = '<table class="pivot" border="1" style="border-collapse: collapse">' + rez + '</table>';

        return rez;
    }

    ,setLegendVisible: function(visible) {
        this.showLegend = visible;
    }
});
