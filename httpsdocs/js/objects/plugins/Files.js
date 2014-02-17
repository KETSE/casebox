Ext.namespace('CB.objects.plugins');

CB.objects.plugins.Files = Ext.extend(CB.objects.plugins.Base, {

    initComponent: function(){

        this.actions = {
           add: new Ext.Action({
                text: L.Add
                ,iconCls: 'i-plus'
                ,scope: this
                ,handler: this.onAddClick
            })
        };

        var tpl = new Ext.XTemplate(
            '<table class="block-plugin">'
            ,'<tpl for=".">'
            ,'<tr>'
            ,'    <td class="obj">'
            ,'        <div><img class="file- {iconCls}" src="'+ Ext.BLANK_IMAGE_URL +'"></div>'
            ,'    </td>'
            ,'    <td>'
            ,'        <span class="click">{name}</span><br />'
            ,'        <span class="gr">{[ App.customRenderers.filesize(values.size) ]}, {ago_text}</span>'
            ,'    </td>'
            ,'    <td class="elips">'
            ,'        <span class="click menu"></span>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</table>'
        );

        this.store = new Ext.data.JsonStore({
            autoDestroy: true
            ,proxy: new  Ext.data.MemoryProxy()
            ,fields: [
                {name: 'id', type: 'int'}
                ,{name: 'pid', type: 'int'}
                ,'name'
                ,{name: 'template_id', type: 'int'}
                ,'size'
                ,'cdate'
                ,'ago_text'
                ,'iconCls'
            ]
        });

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,store: this.store
            ,autoHeight: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,click: this.onItemClick
            }
        });

        this.dragPanel = new Ext.Panel({
            border: false
            ,padding: 0
            ,hidden: true
            ,html: '<div style="display: block; font-weight: bold; color: rgb(85, 85, 85); background-color: rgb(228, 241, 246); margin: 5px 0px; text-align: center; vertical-align: middle; padding: 15px 10px; border-top-width: 1px; border-top-style: dashed; border-top-color: rgb(153, 218, 242); border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: rgb(153, 218, 242);">'+
                'Drag files here, or <a class="click upload">'+L.Upload+'</a> <span class="i-cross click close" style="float: right; display: inline-block; height: 16px; width: 16px;"></span>'+
            '</div>'
            ,listeners: {
                scope: this
                ,afterrender: function(p) {
                    p.getEl().on('click', this.onDragPanelClick, this);
                }
            }
        });

        Ext.apply(this, {
            title: L.Files
            ,items: [
                this.dataView
                ,this.dragPanel
            ]
        });
        CB.objects.plugins.Files.superclass.initComponent.apply(this, arguments);

        this.dropZoneConfig = {text: 'Drop files here'};
        this.filesDropPlugin = new CB.plugins.FilesDropZone({pidPropety: 'id'});
        this.filesDropPlugin.init(this);

    }
    ,onLoadData: function(r, e) {
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
            this.showActionsMenu(e.getXY());
        } else if(te.hasClass('click')) {
            this.openObjectProperties(this.store.getAt(index).data);
        }
    }

    ,showActionsMenu: function(coord){
        if(Ext.isEmpty(this.puMenu)) {
            this.puMenu = new Ext.menu.Menu({
                items: [
                    {
                        text: 'Cut'
                    },{
                        text: 'Copy'
                    },{
                        text: 'Delete'
                        ,iconCls: 'i-trash'
                    },'-',{
                        text: 'In new tab'
                        ,iconCls: 'icon-external'
                    }
                ]
            });
        }

        this.puMenu.showAt(coord);
    }

    ,getToolbarItems: function() {
        return [this.actions.add];
    }
    ,onAddClick: function(b, e) {
        this.dragPanel.show();
    }
    ,onDragPanelClick: function(ev, el) {
        var te = ev.getTarget();
        if(Ext.isEmpty(te)) {
            return;
        }
        te = Ext.get(te);
        if(te.hasClass('close')) {
            this.dragPanel.hide();
        }
        if(te.hasClass('upload')) {
            //upload dialog'
            this.dragPanel.hide();
        }
    }
});

Ext.reg('CBObjectsPluginsFiles', CB.objects.plugins.Files);
