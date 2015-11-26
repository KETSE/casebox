Ext.namespace('CB');

Ext.define('CB.widget.LeafletWindow', {
    extend: 'Ext.Window'

    ,alias: 'CB.LeafletWindow'

    ,xtype: 'CBLeafletWindow'

    ,height: 400
    ,width: 700
    ,maximizable: true
    ,minimizable: true

    ,initComponent: function(){
        this.actions = {
            save: new Ext.Action({
                text: L.Save
                ,iconCls: 'icon-save'
                ,disabled: true
                ,scope: this
                ,handler: this.onSaveClick
            })

            ,cancel: new Ext.Action({
                text: Ext.MessageBox.buttonText.cancel
                ,iconCls: 'i-cancel'
                ,scope: this
                ,handler: this.close
            })
        };

        this.mapPanel = Ext.create('CB.LeafletPanel', {
            listeners: {
                scope: this
                ,mapready: this.onMapReady
                ,mapclick: this.onMapClick
            }
        });

        this.valueEditor = new Ext.form.TextField({
            enableKeyEvents: true
            ,fieldLabel: L.selectedCoordinates
            ,labelWidth: 105
            ,maskRe: /[\-\d\.,]/
        });

        Ext.apply(this, {
            layout: 'fit'
            ,border: false
            // ,bodyBorder: false
            ,cls: 'x-panel-white'
            ,tbar: [
                this.actions.save
                ,this.actions.cancel
                ,'-'
                ,this.valueEditor
            ]
            ,items: [
                this.mapPanel
            ]
        });

        this.callParent(arguments);
    }

    ,onMapReady: function(p) {
        var d = this.initialConfig.data
            ,cfg = Ext.valueFrom(d.cfg, {})
            ,dl = Ext.valueFrom(cfg.defaultLocation, {})
            ,lat = Ext.valueFrom(dl.lat, 0)
            ,lng = Ext.valueFrom(dl.lng, 0)
            ,zoom = Ext.valueFrom(dl.zoom, 10);

        if (!Ext.isEmpty(d.value)) {
            this.valueEditor.setValue(d.value);

            var a = d.value.split(',');
            lat = a[0];
            lng = a[1];

            this.getMarker().setLatLng(new LL.LatLng(lat, lng));
        }

        if (!Ext.isEmpty(cfg.url)) {
            p.map.eachLayer(
                function(l) {
                    if(l && l.setUrl) {
                        l.setUrl(cfg.url);
                    }
                }
            );
        }

        // start the map in South-East England
        p.setView(
            new LL.LatLng(lat, lng)
            ,zoom
            ,{
                reset: true
            }
        );
    }

    ,onMapClick: function(p, e) {
        var ll = e.latlng.wrap()
            ,lat = ll.lat.toFixed(4)
            ,lng = ll.lng.toFixed(4)
            ,marker = this.getMarker();

        this.valueEditor.setValue(lat + ',' + lng);

        marker.setLatLng(ll);
        this.actions.save.setDisabled(false);
    }

    ,onSaveClick: function() {
        var d  = this.initialConfig.data;

        if (d.callback) {
            var f = Ext.Function.bind(
                d.callback
                ,Ext.valueFrom(d.scope, this)
                ,[this, this.valueEditor.getValue()]
            );

            f();
        }

        this.close();
    }

    ,getMarker: function(ll) {
        if(!this.marker) {
            this.marker = LL.marker(new LL.LatLng(0, 0), {
                icon:  LL.icon({
                    iconUrl: '/css/i/marker.png',
                    iconSize: [25, 41],
                    'marker-color': 'ff8888'
                }),
                draggable: true
            });

            this.marker.on('dragend', this.onMarkerDragEnd, this);

            this.marker.addTo(this.mapPanel.map);
        }

        return this.marker;
    }

    ,onMarkerDragEnd: function(e) {
        var ll =  e.target.getLatLng().wrap()
            ,lat = ll.lat.toFixed(4)
            ,lng = ll.lng.toFixed(4);

        this.valueEditor.setValue(lat + ',' + lng);
        this.actions.save.setDisabled(false);
    }
}
);
