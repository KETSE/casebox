Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Pivot',{
    extend: 'CB.browser.view.Interface'
    ,xtype: 'CBBrowserViewPivot'

    ,border: false
    ,tbarCssClass: 'x-panel-white'
    ,layout: 'border'
    ,scrollable: true

    ,initComponent: function(){

        this.params = Ext.apply(
            {
                from: 'pivot'
                // ,rows: 0
            }
            ,this.params || {}
        );

        this.rowsCombo = new Ext.form.ComboBox({
            xtype: 'combo'
            ,itemId: 'PVrowsCombo'
            ,selectedFacetIndex: 0
            ,forceSelection: true
            ,editable: false
            ,triggerAction: 'all'
            ,lazyRender: true
            ,queryMode: 'local'
            ,fieldLabel: L.Rows
            ,labelWidth: 'auto'
            ,style: 'margin-right: 10px'
            ,store: new Ext.data.JsonStore({
                model: 'Generic2'
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
            ,itemId: 'PVcolsCombo'
            ,selectedFacetIndex: 1
            ,forceSelection: true
            ,editable: false
            ,triggerAction: 'all'
            ,lazyRender: true
            ,queryMode: 'local'
            ,fieldLabel: L.Columns
            ,labelWidth: 'auto'
            ,store: new Ext.data.JsonStore({
                model: 'Generic2'
            })
            ,displayField: 'name'
            ,valueField: 'id'
            ,listeners: {
                scope: this
                ,select: this.onFacetChange
            }
        });

        this.refOwner.buttonCollection.addAll(
            new Ext.form.Label({
                text: 'Stats'
                ,itemId: 'PVStatsLabel'
            })

            ,new Ext.Button({
                text: 'Stats'
                ,itemId: 'PVStatsButton'
                ,scale: 'medium'
                ,menu: []
            })

            ,new Ext.Button({
                qtip: L.Pivot
                ,text: L.Pivot
                ,itemId: 'PVtable'
                ,scale: 'medium'
                ,chart: 'table'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-table'
                ,scope: this
                ,handler: this.onChangeChartButtonClick
            })

            ,new Ext.Button({
                qtip: L.ChartArea
                ,text: L.Bar
                ,itemId: 'PVbarchart'
                ,scale: 'medium'
                ,chart: 'stackedBars'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-chart-bar'
                ,scope: this
                ,handler: this.onChangeChartButtonClick
            })

            ,new Ext.Button({
                qtip: L.ChartArea
                ,text: L.Column
                ,itemId: 'PVcolumnchart'
                ,scale: 'medium'
                ,chart: 'stackedColumns'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-chart-column'
                ,scope: this
                ,handler: this.onChangeChartButtonClick
            })
            ,this.rowsCombo
            ,this.colsCombo
        );

        this.chartBlock = new CB.widget.block.Pivot({
            region: 'center'
            ,scrollable: true
            ,border: false
            ,listeners: {
                scope: this
                ,cellclick: this.onTableCellClick
            }

        });

        Ext.apply(this, {
            title: L.Pivot
            ,header: false
            ,items: this.chartBlock
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });

        this.callParent(arguments);

        this.enableBubble(['reload']);

        this.selectedFacets = [];

        this.store.on('load', this.onStoreLoad, this);
    }

    ,getViewParams: function() {
        this.params.selectedFacets = this.selectedFacets;
        this.params.selectedStat = this.selectedStat;

        return this.params;
    }

    ,onActivate: function() {
        this.selectedFacets = [];

        delete this.chartData;
        delete this.selectedStat;

        this.fireEvent(
            'settoolbaritems'
            ,[
                'PVrowsCombo'
                ,'PVcolsCombo'
                ,'->'
                ,'PVStatsLabel'
                ,'PVStatsButton'
                ,'-'
                ,'PVtable'
                ,'PVbarchart'
                ,'PVcolumnchart'
                ,'-'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }

    ,onChangeChartButtonClick: function(b, e) {
        this.chartData.charts = [b.config.chart];
        // if(b.pressed) {
        //     this.activeCharts.push(b.config.chart);
        // } else {
        //     Ext.Array.remove(this.activeCharts, b.config.chart);
        // }

        this.onChangeChart();
    }

    ,onChangeChart: function() {
        var BC = this.refOwner.buttonCollection
            ,ch = this.chartData.charts;

        BC.get('PVtable').toggle(ch.indexOf('table') > -1, true);
        BC.get('PVbarchart').toggle(ch.indexOf('stackedBars') > -1, true);
        BC.get('PVcolumnchart').toggle(ch.indexOf('stackedColumns') > -1, true);

        this.chartBlock.changeCharts(ch);
    }

    ,onStoreLoad: function(store, recs, successful, eOpts) {
        if(!this.rendered ||
            !this.getEl().isVisible(true) ||
            (successful !== true)
        ) {
            return;
        }

        var rd = store.proxy.reader.rawData
            ,selectedValues = {};

        if(this.chartData) {
            if(this.selectedFacets) {
                selectedValues = {
                    xfield: this.selectedFacets[0]
                    ,yfield: this.selectedFacets[1]
                };
            }

            selectedValues.charts = this.chartData.charts;
        }

        this.chartData = this.chartBlock.loadData(rd, selectedValues);

        this.selectedFacets = [
            this.chartData.xField
            ,this.chartData.yField
        ];

        this.loadAvailableFacets(rd.facets);

        this.updateStatsMenu();

        this.onChangeChart();
    }

    ,loadAvailableFacets: function(facets) {
        var data = [];

        Ext.iterate(
            facets
            ,function(key, val, o) {
                if(Ext.isEmpty(this.selectedFacets)) {
                    this.selectedFacets = [key];
                } else if(this.selectedFacets.length < 2) {
                    this.selectedFacets[1] = key;
                }
                data.push({
                    id: key
                    ,name: Ext.valueFrom(val['title'], L['facet_' + key])
                });
            }
            ,this
        );
        this.rowsCombo.store.loadData(data);
        this.colsCombo.store.loadData(data);
        this.rowsCombo.setValue(this.selectedFacets[0]);
        this.colsCombo.setValue(this.selectedFacets[1]);
    }

    ,updateStatsMenu: function() {
        var BC = this.refOwner.buttonCollection
            ,b = BC.get('PVStatsButton')
            ,l = BC.get('PVStatsLabel')
            ,d = this.chartData;

        this.statsEnabled = !Ext.isEmpty(d.stats);

        b.setHidden(!this.statsEnabled);
        l.setHidden(!this.statsEnabled);

        if (this.statsEnabled) {
            if(!this.selectedStat) {
                if(d.view && d.view.stats) {
                    this.selectedStat = d.view.stats;
                }  else {
                    this.selectedStat = {};
                }
            }

            if(Ext.isEmpty(this.selectedStat.field)) {
                this.selectedStat.field = '';
            }
            if(Ext.isEmpty(this.selectedStat.type)) {
                this.selectedStat.type = 'min';
            }

            var menu = b.getMenu()
                ,statsFunctions = ['min', 'max', 'sum', 'count', 'missing']
                ,items = []
                ,checked;

            //add none value
            d.stats.unshift({title: L.none, field: ''});

            for (var i = 0; i < d.stats.length; i++) {
                checked = (this.selectedStat.field == d.stats[i].field);

                if(checked) {
                    this.selectedStat.title = d.stats[i].title;
                }

                items.push({
                    xtype: 'menucheckitem'
                    ,group: 'StatsField'
                    ,text: d.stats[i].title
                    ,field: d.stats[i].field
                    ,checked: checked
                    ,scope: this
                    ,handler: this.onStatFieldChangeClick
                });
            }

            //add separator
            items.push('-');

            //add available functions to use
            for (i = 0; i < statsFunctions.length; i++) {
                //statsFunctions[i];
                items.push({
                    xtype: 'menucheckitem'
                    ,group: 'StatsType'
                    ,text: statsFunctions[i]
                    ,checked: (this.selectedStat.type == statsFunctions[i])
                    ,scope: this
                    ,handler: this.onStatTypeChangeClick
                });
            }

            menu.removeAll();

            menu.add(items);

            this.updateStatsButtonCaption();
        }
    }

    ,onStatFieldChangeClick: function(b, e) {
        this.selectedStat.field = b.field;
        this.selectedStat.title = b.text;
        this.updateStatsButtonCaption();

        this.fireEvent('reload', this);
    }

    ,onStatTypeChangeClick: function(b, e) {
        this.selectedStat.type = b.text;
        this.updateStatsButtonCaption();

        this.fireEvent('reload', this);
    }

    ,updateStatsButtonCaption: function() {
        var BC = this.refOwner.buttonCollection
            ,b = BC.get('PVStatsButton')
            ,ss = this.selectedStat
            ,txt = '';

            if(ss) {
                txt = Ext.isEmpty(ss.field)
                    ? ss.title
                    : (ss.type + //L['SF' + ss.type] +
                        ' (' + ss.title + ')'
                    );
            }

        b.setText(txt);
    }

    ,onFacetChange: function(combo, records, index) {
        var record = Ext.isArray(records)
            ? records[0]
            : records;

        this.selectedFacets[combo.selectedFacetIndex] = record.get('id');
        this.fireEvent('reload', this);
    }

    ,onTableCellClick: function(ev, el, p) {
        if(Ext.isEmpty(el) || Ext.isEmpty(el.textContent)) {
            return;
        }
        var f = el.attributes.getNamedItem('f');
        if(Ext.isEmpty(f)) {
            return;
        }

        f = f.value.split('|');

        var params = {
            view: 'grid'
            ,from: 'grid'
            ,userViewChange: true
            ,filters: Ext.apply({}, this.store.extraParams.filters)
        };
        if(!Ext.isEmpty(f[0])) {
            params['filters'][this.selectedFacets[0]] = [{
                f: this.selectedFacets[0]
                ,mode: 'OR'
                ,values: [f[0]]
            }];
        }
        if(!Ext.isEmpty(f[1])) {
            params['filters'][this.selectedFacets[1]] = [{
                f: this.selectedFacets[1]
                ,mode: 'OR'
                ,values: [f[1]]
            }];
        }

        this.fireEvent('changeparams', params);
    }
});
