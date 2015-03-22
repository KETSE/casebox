Ext.namespace('CB.object.plugin');

Ext.define('CB.object.plugin.Files', {
    extend: 'CB.object.plugin.Base'
    ,alias: 'CBObjectPluginFiles'

    ,xtype: 'CBObjectPluginFiles'
    ,cls: 'obj-plugin'

    ,bodyStyle: 'min-height: 50px; margin-bottom:0; padding-bottom:30px'

    ,initComponent: function(){

        this.actions = {
           add: new Ext.Action({
                // ,text: L.Add
                iconCls: 'i-plus'
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
            ,'        <span class="gr" title="{[ displayDateTime(values.cdate) ]}">{[ App.customRenderers.filesize(values.size) ]}, {ago_text}</span>'
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
            ,model: 'ContentItem'
            ,proxy: new  Ext.data.MemoryProxy()
            ,data: []
        });

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,store: this.store
            ,autoHeight: true
            ,itemSelector:'tr'
            ,listeners: {
                scope: this
                ,itemclick: this.onItemClick
            }
        });

        this.dropPanel = new Ext.Panel({
            border: false
            ,padding: 0
            ,hidden: true
            ,html: ''

            ,listeners: {
                scope: this

                ,afterrender: function(p) {
                    var el = this.getEl();
                    el.on('click', this.onDropPanelClick, this);
                    var ddp = new CB.DD.Panel(
                        el
                        ,{
                            enableDrop: true
                            ,defaultAction: 'shortcut'
                        }
                    );

                    ddp.onNodeDrop = Ext.Function.createInterceptor(
                        ddp.onNodeDrop,
                        function() {
                            Ext.apply(this.params, this.getLoadedObjectProperties());

                            this.hideDropPanel();
                        }
                        ,this
                    );

                    ddp.init(this);
                }
            }
        });

        Ext.apply(this, {
            title: L.Files
            ,items: [
                this.dataView
                ,this.dropPanel
            ]
            ,listeners: {
                scope: this
                ,beforedestroy: this.onBeforeDestroy
            }
        });

        this.callParent(arguments);

        this.enableBubble(['fileupload', 'lockpanel']);

        this.dropZoneConfig = {
            pidPropety: 'id'
            ,dropZoneEl: this.dropPanel.getEl()
        };
        this.filesDropPlugin = new CB.plugin.dd.FilesDropZone({
            pidPropety: 'id'
        });

        this.filesDropPlugin.init(this);

        App.mainViewPort.on('objectsdeleted', this.onObjectsDeleted, this);
    }

    ,onBeforeDestroy: function(c) {
        App.mainViewPort.un('objectsdeleted', this.onObjectsDeleted, this);
    }

    ,onObjectsDeleted: function(ids) {
        this.store.deleteIds(ids);
    }

    ,onLoadData: function(r, e) {
        var dropZoneHtml = L.DropZoneMsg;

        //show upload zone by default when in window
        if(this.params && (this.params.from == 'window')) {
            this.dropPanel.show();
            this.actions.add.setHidden(true);
        } else {
            dropZoneHtml += ' <span class="i-cross click close" style="float: right; display: inline-block; height: 16px; width: 16px;"></span>';
        }

        dropZoneHtml = '<div class="files-drop">'+ dropZoneHtml +'</div>';

        if(this.dropPanel.rendered) {
            this.dropPanel.update(dropZoneHtml);
        } else {
            this.dropPanel.html = dropZoneHtml;
        }

        if(!Ext.isEmpty(r.data)) {
            for (var i = 0; i < r.data.length; i++) {
                r.data[i].iconCls = getItemIcon(r.data[i]);
            }
            this.store.loadData(r.data);
        }
    }

    ,onItemClick: function (cmp, record, item, index, e, eOpts) {//dv, index, el, e
        var te = Ext.get(e.getTarget());

        if(!te) {
            return;
        }

        if(te.hasCls('menu')) {
            this.clickedItemData = this.store.getAt(index).data;
            this.showActionsMenu(e.getXY());
        } else if(te.hasCls('click')) {
            this.openObjectProperties(this.store.getAt(index).data);
        }
    }

    ,showActionsMenu: function(coord){
        if(Ext.isEmpty(this.puMenu)) {
            this.puMenu = new Ext.menu.Menu({
                items: [

                    {
                        text: L.Download
                        ,scope: this
                        ,handler: this.onDownloadClick
                    },'-',{
                        text: L.Cut
                        ,scope: this
                        ,handler: this.onCutItemClick
                    },{
                        text: L.Copy
                        ,scope: this
                        ,handler: this.onCopyItemClick
                    },{
                        text: L.Delete
                        ,iconCls: 'i-trash'
                        ,scope: this
                        ,handler: this.onDeleteItemClick
                    },'-',{
                        text: L.Open
                        ,scope: this
                        ,handler: this.onOpenClick
                    }
                ]
            });
        }

        this.puMenu.showAt(coord);

        Ext.defer(this.puMenu.show, 10, this.puMenu);
    }

    ,onDownloadClick: function(b, e) {
        App.downloadFile(this.clickedItemData.id);
    }

    ,onCutItemClick: function(b, e) {
        App.clipboard.set([this.clickedItemData], 'move');
    }

    ,onCopyItemClick: function(b, e) {
        App.clipboard.set([this.clickedItemData], 'copy');
    }

    ,onDeleteItemClick: function(b, e) {
        App.mainViewPort.onDeleteObject(this.clickedItemData);
    }

    ,onOpenClick: function(b, e) {
        this.openObjectProperties(this.clickedItemData);
    }

    ,getToolbarItems: function() {
        return [this.actions.add];
    }

    ,getContainerToolbarItems: function() {
        var rez = {
            tbar: {}
            ,menu: {}
        };

        // if(this.params) {
        //     if(!this.isVisible()) {
        //         rez.menu['attachfile'] = {};
        //     }
        // }
        return rez;
    }

    ,onAddClick: function(b, e) {
        this.lockPanel(true);
        this.dropPanel.show();
    }

    ,onDropPanelClick: function(ev, el) {
        var te = ev.getTarget();
        if(Ext.isEmpty(te)) {
            return;
        }
        te = Ext.get(te);
        if(te.hasCls('close')) {
            this.hideDropPanel();
        }

        if(te.hasCls('upload')) {
            this.fireEvent(
                'fileupload'
                ,{pid: Ext.valueFrom(this.params.id, this.params.path)}
                ,ev
            );

            this.hideDropPanel();
        }
    }

    /**
     * conditionally hides drop panel if not inside object window
     * @return void
     */
    ,hideDropPanel: function() {
        if(Ext.isEmpty(this.params) || (this.params.from !== 'window')) {
            this.dropPanel.hide();
            this.lockPanel(false);
        }
    }

    ,lockPanel: function (status) {
        this.fireEvent('lockpanel', status, this);
    }

    ,getProperty: function(propertyName) {
        return this.params[propertyName];
    }
});
