Ext.namespace('CB.object.widget');

Ext.define('CB.object.TitleView', {
    extend: 'Ext.DataView'

    ,initComponent: function() {
        this.tpl = new Ext.XTemplate(
            '<tpl for=".">'
            ,'<div class="obj-header"><span class="{titleCls}">{[ Ext.valueFrom(values.name, \'\') ]}</span> &nbsp;'
                ,'{[ this.getStatusInfo(values) ]}'
                ,'<div class="path fs12">'
                    ,'{[ this.getPath(values) ]}'
                ,'</div>'
                ,'<div class="info">'
                    ,'{[ this.getTitleInfo(values) ]}'
                ,'</div>'
            ,'</div>'
            ,'</tpl>'
            ,{
                getStatusInfo: this.getStatusInfo
                ,getPath: this.getPath
                ,getTitleInfo: this.getTitleInfo
            }
        );

        Ext.apply(this, {
            autoHeight: true
            ,cls: 'obj-plugin-title'
            ,itemSelector: '.none'
            ,data: Ext.valueFrom(this.config.data, {})
            ,listeners: {
                scope: this
                ,containerclick: this.onContainerClick
            }
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
     * get path
     * @return string
     */
    ,getPath: function (values) {
        if(Ext.isEmpty(values.path)) {
            return '';
        }

        var rez = '<a class="click" title="' + values.path + '">' +
            App.shortenStringLeft(values.path, 20) + '</a>';

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
                ' <a class="click">' + CB.DB.usersStore.getName(values.uid) + '</a> ' +
                Ext.valueFrom(values.udate_ago_text, '')
            );
        }

        return rez.join(' &#8226; ');
    }

    ,onContainerClick: function(view, e, eOpts) {
        if(e) {
            var el = e.getTarget('.path');
            if(el) {
                App.openPath(this.data.pids);
            }

        }
    }
});
