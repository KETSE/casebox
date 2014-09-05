Ext.namespace('CB.objects.plugins');

Ext.define('CB.objects.plugins.CurrentVersion', {
    extend: 'CB.objects.plugins.Versions'
    ,alias: 'CBObjectsPluginsCurrentVersion'

    ,initComponent: function(){

        Ext.apply(this, {
            title: L.CurrentVersion
        });

        CB.objects.plugins.CurrentVersion.superclass.initComponent.apply(this, arguments);
    }

    ,getTemplate: function(){
        return new Ext.XTemplate(
            '<table class="block-plugin versions">'
            ,'<tpl for=".">'
            ,'<tr class="{cls}">'
            ,'    <td class="obj">'
            ,'        <div><img class="i32" src="/' + App.config.coreName + '/photo/{cid}.jpg?32={[ CB.DB.usersStore.getPhotoParam(values.cid) ]}" title="{user}"></div>'
            ,'    </td>'
            ,'    <td>'
            ,'        <span class="click">{name}</span><br />'
            ,'        <span class="gr" title="{[ displayDateTime(values.cdate) ]}">{[ App.customRenderers.filesize(values.size) ]}, {ago_text}</span>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</table>'
        );
    }

    ,onItemClick: function (cmp, record, item, index, e, eOpts) {// dv, index, el, e
        var te = Ext.get(e.getTarget());
        if(!te) {
            return;
        }

        if(te.hasCls('click')) {
            this.fireEvent('openversion', {id: null}, this);
        }
    }

    ,setSelectedVersion: function(params) {
        this.store.each(
            function(r) {
                r.set('cls', Ext.isEmpty(params.versionId) ? 'sel' : '');
            }
            ,this
        );
    }

});
