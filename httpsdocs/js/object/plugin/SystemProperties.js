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
            ,'<tr><td class="k">'+L.Created+'</td><td>{cid_text}<br><span class="dttm" title="{[ displayDateTime(values.cdate) ]}">{cdate_ago_text}</span></td></tr>'
            ,'<tr><td class="k">'+L.Modified+'</td><td>{uid_text}<br><span class="dttm" title="{[ displayDateTime(values.udate) ]}">{udate_ago_text}</span></td></tr>'
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
                ,afterrender: this.attachEvents
            }
        });

        Ext.apply(this, {
            title: L.Properties
            ,items: this.dataView
        });

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

    ,attachEvents: function(){
        var a = this.getEl().query('a.path');
        Ext.each(
            a
            ,function(t){
                Ext.get(t).addListener(
                    'click'
                    ,this.onPathClick
                    ,this
                );
            }
            ,this
        );
    }

    /**
     * handler to open path when clicked
     * @param  event ev
     * @param  element el
     * @return void
     */
    ,onPathClick: function(ev, el){
        App.openPath(this.params.path);
    }

    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {}
        };

        if(this.params) {

            if(CB.DB.templates.getType(this.params.template_id) === 'file') {
                rez.menu['metadata']  = {order: 17};
                rez.menu['webdavlink']  = {order: 18};
                rez.menu['permalink']  = {order: 19};
            }
        }

        return rez;
    }
});
