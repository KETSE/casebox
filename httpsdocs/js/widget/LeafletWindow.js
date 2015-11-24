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
            var a = d.value.split(',');
            lat = a[0];
            lng = a[1];
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
        this.valueEditor.setValue(e.latlng.lat.toFixed(4) + ',' + e.latlng.lng.toFixed(4));
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
}
);
