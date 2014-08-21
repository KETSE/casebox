Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Versions = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){
        this.actions = {
           restore: new Ext.Action({
                text: L.Restore
                ,scope: this
                ,handler: this.onRestoreClick
            })
        };

        this.store = new Ext.data.JsonStore({
            autoDestroy: true
            ,proxy: new  Ext.data.MemoryProxy()
            ,fields: [
                {name: 'id', type: 'int'}
                ,{name: 'pid', type: 'int'}
                ,'name'
                ,{name: 'template_id', type: 'int'}
                ,{name: 'oid', type: 'int'}
                ,{name: 'cid', type: 'int'}
                ,'size'
                ,'cdate'
                ,'ago_text'
                ,'iconCls'
                ,'user'
                ,'cls'
            ]
        });

        this.dataView = new Ext.DataView({
            tpl: this.getTemplate()
            ,store: this.store
            ,autoHeight: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,click: this.onItemClick
            }
        });

        Ext.apply(this, {
            title: Ext.value(this.title, L.VersionsHistory)
            ,items: [
                this.dataView
            ]
        });

        CB.objects.plugins.Versions.superclass.initComponent.apply(this, arguments);

        this.addEvents('openversion');
        this.enableBubble(['openversion']);
    }

    ,getTemplate: function(){
        return new Ext.XTemplate(
            '<table class="block-plugin versions">'
            ,'<tpl for=".">'
            ,'<tr class="{cls}">'
            ,'    <td class="obj">'
            ,'        <div><img class="i32" src="/' + App.config.coreName + '/photo/{cid}.jpg{[ CB.DB.usersStore.getPhotoParam(values.cid) ]}" title="{user}"></div>'
            ,'    </td>'
            ,'    <td>'
            ,'        <span class="click">{name}</span><br />'
            ,'        <span class="gr" title="{[ displayDateTime(values.cdate) ]}">{[ App.customRenderers.filesize(values.size) ]}, {ago_text}</span>'
            ,'    </td>'
            ,'    <td class="elips">'
            ,'        <span class="click menu"></span>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</table>'
        );
    }

    ,onLoadData: function(r, e) {
        if(Ext.isEmpty(r.data)) {
            return;
        }

        for (var i = 0; i < r.data.length; i++) {
            r.data[i].iconCls = getItemIcon(r.data[i]);
        }
        this.store.loadData(r.data);
    }

    ,onItemClick: function ( dv, index, el, e) {
        var te = Ext.get(e.getTarget());
        if(!te) {
            return;
        }

        if(te.hasClass('menu')) {
            this.selectedRecord = this.store.getAt(index);
            this.showActionsMenu(e.getXY());
        } else if(te.hasClass('click')) {
            this.fireEvent('openversion', this.store.getAt(index).data, this);
        }
    }

    ,showActionsMenu: function(coord){
        if(Ext.isEmpty(this.puMenu)) {
            this.puMenu = new Ext.menu.Menu({
                items: [this.actions.restore]
            });
        }

        this.puMenu.showAt(coord);
    }

    ,onRestoreClick: function(b, e) {
        CB_Files.restoreVersion(
            this.selectedRecord.get('id')
            ,function(r, e){
                App.fireEvent('objectchanged', r.data, this);
            }
            ,this
        );
    }

    ,setSelectedVersion: function(params) {
        this.store.each(
            function(r) {
                r.set('cls', (r.get('id') == params.versionId) ? 'sel' : '');
            }
            ,this
        );
    }
});

Ext.reg('CBObjectsPluginsVersions', CB.objects.plugins.Versions);
