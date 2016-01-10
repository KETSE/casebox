Ext.namespace('CB');

Ext.define('CB.widget.LeafletPanel', {
    extend: 'Ext.Panel'

    ,alias: 'CB.LeafletPanel'

    ,xtype: 'CBLeafletPanel'

    ,initComponent: function(){
        Ext.apply(this, {
            border: false
            ,listeners: {
                scope: this
                ,boxready: this.onBoxReady
            }
        });

        this.callParent(arguments);
    }

    ,onBoxReady: function(panel) {
        if (!window.LL) {
            return alert('No Leaflet library');
        }

        var map = LL.map(this.body.id)
            // create the tile layer with correct attribution
            ,osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'

            ,osm = new LL.TileLayer(
                osmUrl
                ,{
                    minZoom: 0
                    ,maxZoom: 18
                }
            );

        this.map = map;

        map.addLayer(osm);

        this.fireEvent('mapready', this);

        map.on('click', this.onMapClick, this);
    }

    /**
     * onMapClick
     * @param  object e
     *          containerPoint: o.Point
     *           latlng: o.LatLng
     *           layerPoint: o.Point
     *           originalEvent: MouseEvent
     *           target: e
     *           type: "click"
     * @return void
     */
    ,onMapClick: function(e) {
        this.fireEvent('mapclick', this, e);
    }

    ,setViewConfig: function(cfg) {
        var dl = Ext.valueFrom(cfg.defaultLocation, {})
            ,lat = Ext.valueFrom(dl.lat, 0)
            ,lng = Ext.valueFrom(dl.lng, 0)
            ,zoom = Ext.valueFrom(dl.zoom, 3);

        if (!Ext.isEmpty(cfg.url)) {
            this.map.eachLayer(
                function(l) {
                    if(l && l.setUrl) {
                        l.setUrl(cfg.url);
                    }
                }
            );
        }

        this.map.setView(
            new LL.LatLng(lat, lng)
            ,zoom
            ,{
                reset: true
            }
        );
    }

    ,setView: function(ll, z, o) {
        this.map.setView(ll, z, o);
    }
}
);
