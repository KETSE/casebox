Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.Map',{
    extend: 'CB.browser.view.Interface'

    ,xtype: 'CBBrowserViewMap'

    ,border: false
    ,tbarCssClass: 'x-panel-white'

    ,initComponent: function(){

        this.mapPanel = Ext.create(
            'CB.LeafletPanel'
            ,{
                listeners: {
                    scope: this
                    ,mapready: this.onMapReady
                }
            }
        );

        Ext.apply(this, {
            title: L.Map
            ,viewName: 'map'
            ,header: false
            ,layout: 'fit'
            ,style: 'background-color: #e9eaed'
            ,items: [
                this.mapPanel
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

        // if (visible && this.mapReady) {
        //     this.addItems();
        // }
    }

    ,addItems: function() {
       var ready = (this.store.getCount() === 0);

        this.store.each(
            function(r) {
                var v = r.data.value
                    ,a = v.split(',')
                    ,marker = LL.marker(
                        [a[0], a[1]]
                        ,{
                            icon: LL.icon({
                                iconUrl: 'https://www.fosterly.com/assets/maps/marker-3a77f9ac6522a026170f1aed325f2d19.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 40],
                                popupAnchor: [0, -35]
                            })
                        }
                    ).addTo(this.mapPanel.map);

                marker.bindPopup(r.get('name'));
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

        if (this.mapReady) {
            this.onMapReady(this.mapPanel);
        }
    }

    ,onMapReady: function(p) {
        var vp = this.viewParams
            ,dl = Ext.valueFrom(vp.defaultLocation, {})
            ,lat = Ext.valueFrom(dl.lat, 0)
            ,lng = Ext.valueFrom(dl.lng, 0)
            ,zoom = Ext.valueFrom(dl.zoom, 10);


        p.setView(
            new LL.LatLng(lat, lng)
            ,zoom
            ,{
                reset: true
            }
        );

        this.mapReady = true;

        this.addItems();
    }
});
