Ext.namespace('Demosrc.view');

Ext.define('Demosrc.view.Graph', {
    extend: 'CB.browser.view.Interface'

    ,xtype: 'DemosrcViewGraph'

    ,title: L.Graph
    ,hideBorders: true
    ,iconCls: 'icon-graph'
    ,padding: 0
    ,autoScroll: true

    ,initComponent: function(){

        Ext.apply(this,{
            html: '...'
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });

        this.callParent(arguments);

        this.owner.buttonCollection.addAll([
            new Ext.Button({
                text: L.Download
                ,itemId: 'graphdownload'
                ,scale: 'medium'
                ,iconCls: 'im-download'
                ,scope: this
                ,handler: this.onDownloadClick
            }),
            new Ext.Button({
                text: L.showNames
                ,itemId: 'graphshowlabels'
                ,enableToggle: true
                // ,pressed: this.params.titles
                ,scope: this
                ,handler: this.onShowNamesClick
            })
        ]);

        this.enableBubble(['selectionchange']);
    }

    ,onActivate: function() {
        this.fireEvent(
            'settoolbaritems'
            ,[
                'graphdownload'
                ,'graphshowlabels'
                ,'->'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }

    ,getViewParams: function(loadingParams) {
        this.params = Ext.apply({}, loadingParams);
        this.reload();
        return false;
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
        window.open('/' + App.config.coreName + '/get.php?graph='+this.params.id+'&d=1' + (this.params.titles ? '&titles=1' :''), 'downloadgraph');
    }

    ,onShowNamesClick: function(b, e){
        this.params.titles = b.pressed;
        this.reload();
    }

    ,attachClickEvents: function(){
        var a = this.getEl().query('a');
        for (var i = 0; i < a.length; i++) {
            var el = Ext.get(a[i]);
            el.un('click', this.onLinkClick, this);
            el.on('click', this.onLinkClick, this);
        }
    }

    ,onLinkClick: function(e, el, o){
        var ael = e.getTarget('a');
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
