Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Thumb = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){
        var tpl = new Ext.XTemplate(
            '<tpl for=".">'
                ,'<div style="width: 100%; text-align: center; margin: 30px 0">'
                ,'<img class="click preview-thumb {cls}" src="'+Ext.BLANK_IMAGE_URL+'" alt="'+L.Preview +'" />'
                ,'</div>'
            ,'</tpl>'
        );
        this.dataView = new Ext.DataView({
            tpl: tpl
            ,autoHeight:true
            ,itemSelector:'img'
            ,listeners: {
                scope: this
                ,click: this.onThumbClick
            }
        });

        Ext.apply(this, {
            items: this.dataView
        });
        CB.objects.plugins.Thumb.superclass.initComponent.apply(this, arguments);

        this.addEvents('openpreview');
        this.enableBubble(['openpreview']);
    }
    ,onLoadData: function(r, e) {
        if(this.rendered) {
            this.dataView.update(r.data);
        } else {
            this.dataView.data = r.data;
        }
    }
    ,onThumbClick: function( dv, index, el, e) {
        var te = Ext.get(e.getTarget());
        if(!te) {
            return;
        }

        if(te.hasClass('preview-thumb')) {
            this.fireEvent('openpreview', this, e);
        }
    }
});

Ext.reg('CBObjectsPluginsThumb', CB.objects.plugins.Thumb);
