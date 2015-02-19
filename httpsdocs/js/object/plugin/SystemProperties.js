Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.SystemProperties', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginSystemProperties'

    ,initComponent: function(){

        var tpl = new Ext.XTemplate(
            '<tpl for=".">'
            ,'<table class="item-props">'
            ,'<tbody><tr><td class="k">Id</td><td>{id}</td></tr>'
            ,'<tr><td class="k">'+L.Path+'</td><td><a class="click path">{path}</a></td></tr>'
            ,'{[ Ext.isEmpty(values.size) ?\'\' : \'<tr><td class="k">\' + L.Size + \'</td><td>\' + App.customRenderers.filesize(values.size) + \'</td></tr>\']}'
            ,'<tr><td class="k">'+L.Template+'</td><td>{template_name} <span class="dttm">(id: {template_id})</span></td></tr>'
            ,'<tr><td class="k">'+L.Created+'</td><td>{cid_text}<br><span class="dttm" title="{[ displayDateTime(values.cdate) ]}">{cdate_text}</span></td></tr>'
            ,'<tr><td class="k">'+L.Modified+'</td><td>{uid_text}<br><span class="dttm" title="{[ displayDateTime(values.udate) ]}">{udate_text}</span></td></tr>'
            ,'{[ Ext.isEmpty(values.did_text) ?\'\' : \'<tr><td class="k">\' + L.Deleted + \'</td><td>\' + values.did_text + \'<br><span class="dttm" title="\' + displayDateTime(values.ddate) + \'">\' + values.ddate_text + \'</span></td></tr>\']}'
            ,'</tbody></table>'
            ,'</tpl>'
        );

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,data: []
            ,autoHeight: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,itemclick: this.onItemClick
            }
        });

        Ext.apply(this, {
            title: L.Properties
            ,items: this.dataView
        });

        // CB.object.plugin.SystemProperties.superclass.initComponent.apply(this, arguments);
        this.callParent(arguments);

    }

    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }

        this.data = r.data;
        if(this.rendered) {
            this.dataView.apply(r.data);
        } else {
            this.dataView.data = r.data;
        }
    }

    ,onItemClick: function (cmp, record, item, index, e, eOpts) {//dv, index, el, e
        var te = Ext.get(e.getTarget());
        if(!te) {
            return;
        }

        if(te.hasCls('path')) {
            App.openPath(this.params.path);
        }

    }

    ,getContainerToolbarItems: function() {
        rez = {
            tbar: {}
            ,menu: {}
        };


        if(this.data) {
            if(this.data.subscribed == 1) {
                rez.menu.unsubscribe = {order: 15};
            } else {
                rez.menu.subscribe = {order: 16};
            }
        }

        if(this.params) {

            if(CB.DB.templates.getType(this.params.template_id) == 'file') {
                rez.menu['metadata']  = {order: 17};
                rez.menu['webdavlink']  = {order: 18};
                rez.menu['permalink']  = {order: 19};
            }
        }

        return rez;
    }
});
