Ext.namespace('CB.object.widget');

Ext.define('CB.object.TitleView', {
    extend: 'Ext.DataView'

    ,initComponent: function() {
        this.tpl = new Ext.XTemplate(
            '<tpl for=".">'
            ,'<div class="obj-header">{[ Ext.util.Format.htmlEncode(Ext.valueFrom(values.name, \'\')) ]} &nbsp;'
                ,'<div class="info">'
                    ,'{[ this.getTitleInfo(values) ]}'
                ,'</div>'
            ,'</div>'
            ,'</tpl>'
            ,{
                getTitleInfo: this.getTitleInfo
            }
        );

        Ext.apply(this, {
            autoHeight: true
            ,cls: 'obj-plugin-title'
            ,itemSelector: 'div'
            ,data: Ext.valueFrom(this.config.data, {})

        });

        this.callParent(arguments);
    }
    /**
     * get info displayed under the title
     * Ex: TemplateType &#8226; #{id} &#8226; Ubdate by <a href="#">user name</a> time ago
     * @return string
     */
    ,getTitleInfo: function (values) {
        var rez = [];

        rez.push(CB.DB.templates.getName(values.template_id));

        if(values.id) {
            rez.push('#' + values.id);
        }

        if(values.uid) {
            rez.push(
                L.UpdatedBy +
                ' <a href="#">' + CB.DB.usersStore.getName(values.uid) + '</a> ' +
                Ext.valueFrom(values.udate_ago_text, '')
            );
        }

        return rez.join(' &#8226; ');
    }
});
