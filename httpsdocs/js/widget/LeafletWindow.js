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
                text: L.Cancel
                ,iconCls: 'i-cancel'
                ,scope: this
                ,handler: this.close
            })

            ,set: new Ext.Action({
                text: L.Set
                ,scope: this
                ,handler: this.onSetClick
            })
        };

        this.mapPanel = Ext.create('CB.LeafletPanel', {
            listeners: {
                scope: this
                ,mapready: this.onMapReady
                ,mapclick: this.onMapClick
            }
        });

        this.latEd = new Ext.form.TextField({
            enableKeyEvents: true
            ,fieldLabel: L.Latitude
            ,labelWidth: 50
            ,width: 120
            ,maskRe: /[\-\d\.]/
            ,listeners: {
                specialkey: this.onEditorEnterPress
                ,scope: this
            }
        });

        this.longEd = new Ext.form.TextField({
            enableKeyEvents: true
            ,fieldLabel: L.Longitude
            ,labelWidth: 55
            ,width: 125
            ,maskRe: /[\-\d\.]/
            ,listeners: {
                specialkey: this.onEditorEnterPress
                ,scope: this
            }
        });

        Ext.apply(this, {
            layout: 'fit'
            ,border: false
            // ,bodyBorder: false
            ,cls: 'x-panel-white'
            ,tbar: [
                this.actions.save
                ,this.actions.cancel
                ,'->'
                ,this.latEd
                ,{
                    xtype: 'tbspacer'
                    ,width: 10
                }
                ,this.longEd
                ,this.actions.set
            ]
            ,items: [
                this.mapPanel
            ]
            ,bbar: [
                {
                    xtype: 'label'
                    ,text: 'Location'
                }
            ]
        });

        this.callParent(arguments);
    }

    ,onMapReady: function(p) {
        var d = this.initialConfig.data
            ,cfg = Ext.valueFrom(d.cfg, {});

        if (!Ext.isEmpty(d.value)) {

            var a = d.value.split(',')
                ,lat = a[0]
                ,lng = a[1];

            cfg.defaultLocation = {
                lat: lat
                ,lng: lng
            };

            this.latEd.setValue(lat);
            this.longEd.setValue(lng);

            this.getMarker().setLatLng(new LL.LatLng(lat, lng));
        }

        p.setViewConfig(cfg);
    }

    ,onMapClick: function(p, e) {
        var ll = e.latlng.wrap()
            ,lat = ll.lat.toFixed(4)
            ,lng = ll.lng.toFixed(4)
            ,marker = this.getMarker();

        this.latEd.setValue(lat);
        this.longEd.setValue(lng);

        marker.setLatLng(ll);
        this.actions.save.setDisabled(false);
    }

    ,onSaveClick: function() {
        var d  = this.initialConfig.data;

        if (d.callback) {
            var f = Ext.Function.bind(
                d.callback
                ,Ext.valueFrom(d.scope, this)
                ,[
                    this
                    ,this.latEd.getValue() + ',' + this.longEd.getValue()
                ]
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

        this.latEd.setValue(lat);
        this.longEd.setValue(lng);
        this.actions.save.setDisabled(false);
    }

    ,onEditorEnterPress: function (ed, e) {
        if (e.getKey() == e.ENTER) {
            this.onSetClick();
        }
    }

    ,onSetClick: function(b, e) {
        var lat = this.latEd.getValue()
            ,lng = this.longEd.getValue();

        if (!Ext.isEmpty(lat) && !Ext.isEmpty(lng)) {
            var ll = new LL.LatLng(lat, lng);
            this.getMarker().setLatLng(ll);
            this.mapPanel.map.setView(ll);
        }
    }
}
);
