Ext.namespace('CB');
Ext.define('CB.Facet', {
    extend: 'Ext.Panel'
    ,title: 'facet'
    ,autoHeight: true
    ,closable: false
    ,collapsible: true
    ,titleCollapse: true
    ,hideCollapseTool: true
    ,cls: 'facet'
    ,border: false
    ,mode: 'OR'
    ,modeToggle: false
    ,bodyStyle: 'background: none'
    ,initComponent: function(config){
        Ext.apply(this, config);
        var tools = [];

        if(this.manualPeriod) {
            tools.push({
                id: 'period'
                ,name: 'period'
                // ,text: 'Period'
                ,xtype: 'button'
                // ,html: '<span onclick="document.getElementById(\'addDatePeriod\').style.display = \'block\'" style="cursor: pointer; padding-left: 19px; background: url(/i4/16/calendar_mono.png) 0px 0px no-repeat; vertical-align: top;">Period</span>'
                ,handler: this.onPeriodAddClick
                ,scope: this
                ,qtip: 'Add period'
            });
        }

        if(this.modeToggle) {
            tools.push({
                name: 'unchain'
                ,cls: 'x-tool-unchain'
                ,handler: this.onModeToggle
                ,scope: this
                ,qtip: L.searchSwitchModeMessage
            });
        }


        Ext.apply(
            this
            ,{
                tools: tools
            }
        );

        CB.Facet.superclass.initComponent.apply(this, arguments);

        this.enableBubble(['facetchange', 'modechange']);
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
