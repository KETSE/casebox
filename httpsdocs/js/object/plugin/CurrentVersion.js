Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.CurrentVersion', {
    extend: 'CB.object.plugin.Versions'
    ,alias: 'CBObjectPluginCurrentVersion'

    ,initComponent: function(){

        Ext.apply(this, {
            title: L.CurrentVersion
        });

        this.callParent(arguments);
    }

    ,getTemplate: function(){
        return new Ext.XTemplate(
            '<table class="block-plugin versions">'
            ,'<tpl for=".">'
            ,'<tr class="{cls}">'
            ,'    <td class="obj">'
            ,'        <div><img class="i32" src="/' + App.config.coreName + '/photo/{[this.getUserId(values)]}.jpg?32={[this.getUserName(values)]}" title="{user}"></div>'
            ,'    </td>'
            ,'    <td>'
            ,'        <span class="click">{name}</span><br />'
            ,'        <span class="gr" title="{[this.getDate(values)]}">{[ App.customRenderers.filesize(values.size) ]}, {ago_text}</span>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</table>'
            ,{
                getUserId: function(values){
                    return Ext.valueFrom(values.uid, values.cid);
                }
                ,getUserName: function(values){
                    return CB.DB.usersStore.getPhotoParam(this.getUserId(values));
                }
                ,getDate: function(values){
                    return Ext.valueFrom(values.udate, values.cdate);
                }
            }
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
