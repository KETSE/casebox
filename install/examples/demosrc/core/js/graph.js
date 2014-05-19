Ext.namespace('Demosrc.view');

Demosrc.view.Graph = Ext.extend(CB.browser.view.Interface,{
    title: L.Graph
    ,hideBorders: true
    ,iconCls: 'icon-graph'
    ,padding: 0
    ,autoScroll: true
    ,initComponent: function(){

        Ext.apply(this,{
            html: '...'
            ,listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });

        Demosrc.view.Graph.superclass.initComponent.apply(this, arguments);

        this.addEvents('selectionchange');
        this.enableBubble(['selectionchange']);

    }
    ,getViewParams: function(loadingParams) {
        this.params = Ext.apply({}, loadingParams);
        this.reload();
        return false;
    }
    ,onAfterRender: function(){
        // this.params.caseId = this.data.caseId
        // this.reload();
    }
    ,reload: function(){
        this.getEl().mask(L.Processing, 'x-mask-loading');
        Demosrc_Graph.load(this.params, this.onGraphLoad, this);
    }
    ,onGraphLoad: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) {
            return;
        }

        this.nodes = r.data.nodes || [];
        this.update(r.data.html, true);

        this.attachClickEvents();

    }
    ,onDownloadClick: function(b, e){
        // window.open('/get.php?graph='+this.params.caseId+'&d=1' + (this.params.titles ? '&titles=1' :''), 'downloadgraph');
    }
    ,attachClickEvents: function(){
        a = this.getEl().query('a');
        for (var i = 0; i < a.length; i++) {
         el = Ext.get(a[i]);
         el.un('click', this.onLinkClick, this);
         el.on('click', this.onLinkClick, this);
        }
    }
    ,onLinkClick: function(e, el, o){
        ael = e.getTarget('a');
        if(ael && ael.href && ael.href.baseVal){
            this.clickedObjectId = String(ael.href.baseVal).substr(1);
            /* locate object */
            for (var i = 0; i < this.nodes.length; i++) {
                 if(this.nodes[i].id == this.clickedObjectId) {
                    this.fireEvent(
                        'selectionchange'
                        ,[this.nodes[i]]
                    );
                }
            }
        }
    }
});

Ext.reg('DemosrcViewGraph', Demosrc.view.Graph);
