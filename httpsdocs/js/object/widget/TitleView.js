Ext.namespace('CB.object.widget');

Ext.define('CB.object.TitleView', {
    extend: 'Ext.DataView'

    ,initComponent: function() {
        this.tpl = new Ext.XTemplate(
            '<tpl for=".">'
            ,'<div class="obj-header"><span class="{titleCls}">{[ Ext.valueFrom(values.name, \'\') ]}</span> &nbsp;'
                ,'{[ this.getStatusInfo(values) ]}'
                ,'<div class="info">'
                    ,'{[ this.getTitleInfo(values) ]}'
                ,'</div>'
            ,'</div>'
            ,'</tpl>'
            ,{
                getStatusInfo: this.getStatusInfo
                ,getTitleInfo: this.getTitleInfo
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
     * get status info displayed next to the title
     * @return string
     */
    ,getStatusInfo: function (values) {
        if(Ext.isEmpty(values.status)) {
            return '';
        }

        var rez = '<div class="dIB fs12 ' + Ext.valueFrom(values.statusCls, '') + '"">' +
            values.status + '</div>';

        return rez;
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
