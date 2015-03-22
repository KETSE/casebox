Ext.namespace('CB');
Ext.define('CB.Facet', {
    extend: 'Ext.Panel'
    ,title: 'facet'
    ,autoHeight: true
    ,closable: false
    ,collapsible: true
    ,animCollapse: false
    ,titleCollapse: true
    ,hideCollapseTool: true
    ,cls: 'facet'
    ,border: false
    ,mode: 'OR'
    ,modeToggle: false
    ,bodyStyle: 'background: none'

    ,sorters: {
        name: {
            asc: function(o1, o2) {
                var a = o1.name
                    ,b = o2.name;

                if(a < b) {
                    return -1;
                }

                if(a > b) {
                    return 1;
                }

                return 0;
            }

            ,desc: function(o1, o2) {
                var a = o1.name
                    ,b = o2.name;

                if(a < b) {
                    return 1;
                }

                if(a > b) {
                    return -1;
                }

                return 0;
            }
        }

        //count sorter is used by charts to sort its data on load
        ,count: {
            asc: function(o1, o2) {
                var a = o1.count
                    ,b = o2.count;

                return a - b;
            }
            ,desc: function(o1, o2) {
                var a = o1.count
                    ,b = o2.count;

                return b - a;
            }
        }

        ,items: {
            asc: function(o1, o2) {
                var a = o1.items
                    ,b = o2.items;

                return a - b;
            }
            ,desc: function(o1, o2) {
                var a = o1.items
                    ,b = o2.items;

                return b - a;
            }
        }
    }

    ,initComponent: function(config){
        Ext.apply(this, config);

        this.initActions();

        Ext.apply(
            this
            ,{
                tools: this.getToolButtons()
            }
        );

        this.callParent(arguments);

        this.enableBubble(['facetchange', 'modechange']);
    }

    /**
     * init actions used for the facet
     * @return object
     */
    ,initActions: function() {
        this.actions = {
            sortByName: new Ext.Action({
                text: L.SortByName
                ,itemId: 'sortname'
                ,scope: this
                ,handler: this.onSortByNameClick
            })
            ,sortByCount: new Ext.Action({
                text: L.SortByCount
                ,itemId: 'sortcount'
                ,scope: this
                ,handler: this.onSortByCountClick
            })
        };

        return this.actions;
    }

    /**
     * basic loading data method. Descendant classes should add more logic here
     * @param  array data
     * @return void
     */
    ,loadData: function(data) {
        this.rawData = data;
        delete this.lastSort;
    }

    /**
     * get buttons to be set in top right side of the facet
     * @return array
     */
    ,getToolButtons: function() {
        var rez = [];

        //add button for adding a manual date period
        if(this.manualPeriod) {
            rez.push({
                itemId: 'period'
                ,name: 'period'
                ,xtype: 'button'
                // ,html: '<span onclick="document.getElementById(\'addDatePeriod\').style.display = \'block\'" style="cursor: pointer; padding-left: 19px; background: url(/i4/16/calendar_mono.png) 0px 0px no-repeat; vertical-align: top;">Period</span>'
                ,callback: this.onPeriodAddClick
                ,scope: this
                ,qtip: 'Add period'
            });
        }

        //add button for filtering mode change (AND/OR)
        if(this.modeToggle) {
            rez.push({
                name: 'unchain'
                ,itemId: 'unchain'
                ,cls: 'x-tool-unchain'
                ,handler: this.onModeToggle
                ,scope: this
                ,qtip: L.searchSwitchModeMessage
            });
        }

        //create menu for points button
        this.moreMenu = new Ext.menu.Menu({
            items: [
                this.actions.sortByName
                ,this.actions.sortByCount
            ]
        });

        //add points menu button
        rez.push({
            itemId: 'points'
            ,type: 'points'
            ,scope: this
            ,callback: this.onPointsTollClick
        });

        return rez;
    }

    ,onPointsTollClick: function(b, e) {
        this.moreMenu.showAt(e.getXY());
    }

    ,onSortByNameClick: function(b, e) {
        var sortDir = (this.lastSort != 'nameasc')
            ? 'asc'
            : 'desc';

        var data = Ext.Array.sort(this.rawData, this.sorters['name'][sortDir]);

        this.store.loadData(data);

        this.lastSort = 'name' + sortDir;
    }

    ,onSortByCountClick: function(b, e) {
        var sortDir = (this.lastSort != 'countdesc')
            ? 'desc'
            : 'asc';

        var data = Ext.Array.sort(this.rawData, this.sorters['items'][sortDir]);

        this.store.loadData(data);

        this.lastSort = 'count' + sortDir;
    }

    ,setModeVisible: function(visible){
        if(!this.rendered) {
            return;
        }
        this.getEl().removeCls('multivalued');
        if(visible) {
            this.getEl().addCls('multivalued');
        }
    }

    ,onModeToggle: function(ev, toolEl, panel, tc){
        if (toolEl.hasCls('x-tool-chain')) {
            toolEl.replaceClass('x-tool-chain', 'x-tool-unchain');
            this.mode = 'OR';
        } else {
            toolEl.replaceClass('x-tool-unchain', 'x-tool-chain');
            this.mode = 'AND';
        }
        this.fireEvent('modechange', this, ev);
    }

    // ,onPeriodAddClick: function(ev, el, panel, tc) {
    //     var coord = el.getXY();
    //     var w = new Ext.Panel({
    //         title: 'Date select'
    //         ,floating: true
    //         ,closable: true
    //         ,width: 100
    //         ,height: 50
    //         ,items: [{
    //             xtype: 'label'
    //             ,text: 'Date'
    //         }]
    //         ,renderTo: Ext.getBody()
    //     });
    //     w.setPosition(coord[0]-100,coord[1]);
    //     w.show();
    // }
}
);
