Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Thumb', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginThumb'

    ,initComponent: function(){
        var tpl = new Ext.XTemplate(
            '<tpl for=".">'
                ,'<div style="width: 100%; text-align: center; margin: 30px 0">'
                ,'{[ Ext.isEmpty(values.html) ? "<img class=\\"click preview-thumb " + values.cls + "\\" src=\\"'+Ext.BLANK_IMAGE_URL+'\\" alt=\\"'+L.Preview +'\\" />" : values.html ]}'
                ,'</div>'
            ,'</tpl>'
        );
        this.dataView = new Ext.DataView({
            tpl: tpl
            ,autoHeight: true
            ,itemSelector: 'div'
            ,data: []
            ,listeners: {
                scope: this
                ,itemclick: this.onThumbClick
                ,viewready: this.onViewReady
            }
        });

        Ext.apply(this, {
            cls: ''
            ,items: this.dataView
        });

        this.callParent(arguments);

        this.enableBubble(['openpreview']);
    }

    ,onLoadData: function(r, e) {
        if(this.rendered) {
            this.dataView.update(r.data);
            this.updateLayout({defer: true});
        } else {
            this.dataView.data = r.data;
        }
    }

    /**
     * Update component layout when images are loaded
     * because images could be without a specified size
     * and when loaded a part of the image could pass the borders
     *
     * @param  component cmp
     * @param  object eOpts
     * @return void
     */
    ,onViewReady: function(cmp, eOpts) {
        var images = this.getEl().query('img');
        if(Ext.isEmpty(images)) {
            return;
        }
        for (var i = images.length - 1; i >= 0; i--) {
            images[i].onload = Ext.Function.bind(this.updateLayout, this);
            images[i].onclick = Ext.Function.bind(this.onThumbClick, this);
        }

    }

    ,onThumbClick: function(e) {//dv, index, el, e
        var te = Ext.get(e.target);
        if(!te) {
            return;
        }

        if(te.hasCls('click')) { //preview-thumb
            this.fireEvent('openpreview', this.params, e);
        }
    }
});
