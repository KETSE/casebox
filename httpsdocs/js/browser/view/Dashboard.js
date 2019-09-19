Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Dashboard',{
    extend: 'CB.browser.view.Interface'

    ,xtype: 'CBBrowserViewDashboard'

    ,border: false
    ,tbarCssClass: 'x-panel-white'

    ,scrollable: true

    ,initComponent: function(){

        Ext.apply(this, {
            title: L.Dashboard
            ,viewName: 'dashboard'
            ,header: false
            ,layout: {
                type: 'table'
                ,columns: 1
                ,tableAttrs: {
                    style: {
                        width: '100%'
                    }
                }
            }
            // ,style: 'background-color: #e9eaed'
            ,defaults: {
                // applied to each contained panel
                // bodyStyle: 'padding: 5px; border: 1px solid gray'
                bodyPadding: 5
                ,cellCls: 'vaT taC'
            }
            ,items: [
            ]
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });

        this.store.on(
            'load'
            ,this.onStoreLoad
            ,this
            ,{
                defer: 300
            }
        );

        this.callParent(arguments);
    }

    ,updateToolbarButtons: function() {
        this.refOwner.fireEvent(
            'settoolbaritems'
            ,[
                '->'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }

    ,onStoreLoad: function(store, records, successful, eOpts) {
        var visible = this.getEl().isVisible(true);

        if (!visible) {
            return;
        }
        var rd = store.proxy.reader.rawData
            ,vc = rd.view;

        this.rawData = rd;

        this.removeAll(true);

        this.getLayout().columns = Ext.valueFrom(vc.columns, 1);

        // this.suspendEvents(false);

        this.addItems();

        // this.resumeEvents(true);

        this.updateLayout();
    }

    ,addItems: function() {
        var vc = this.rawData.view;

        Ext.iterate(
            vc.items
            ,function(k, v) {
                var className = 'CB.widget.block.Base'
                    ,cfg = {
                        params: v
                        ,data: this.rawData.blockData[k]
                    };

                if (!Ext.isEmpty(v.tpl)) {
                    className = 'CB.widget.block.Template';
                } else if (['map','pivot','chart','grid'].indexOf(v.type) > -1) {
                    className = 'CB.widget.block.' + Ext.String.capitalize(v.type);
                }


                Ext.copyTo(cfg, v, 'title,cellCls,rowspan,colspan,width,height,minWidth,minHeight,maxWidth,maxHeight');

                this.add(
                    Ext.create(className, cfg)
                );
            }
            ,this
        );

    }

    ,onActivate: function() {
        this.fireEvent(
            'settoolbaritems'
            ,[
                '->'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }
});
