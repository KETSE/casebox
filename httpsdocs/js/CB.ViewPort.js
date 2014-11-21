Ext.namespace('CB');

Ext.define('CB.ViewPort', {
    extend: 'Ext.Viewport'
    ,layout: 'border'
    ,border: false

    ,initComponent: function(){
        App.mainToolBar = new Ext.Toolbar({
                region: 'north'
                ,style:'background: #F0F0F0; border: 0; ' // padding-top: 10px
                ,height: 53
                ,items: [
                    {
                        xtype: 'tbitem'
                        ,width: 212
                        ,height: 30
                        ,html: '<img src="/css/i/casebox-logo-small.png" style="padding: 0 15px; margin-top: -3px" height="30" width="192" />'
                    }
                    ,{
                        xtype: 'ExtuxSearchField'
                        ,emptyText: L.Search + ' Casebox'
                        ,minListWidth: 150
                        // ,height: 30
                        ,width: 300
                        // ,style: 'font: 14px arial,sans-serif; background-color: #fff'
                        ,listeners: {
                            scope: this
                            ,'search': function(query, editor, event){
                                editor.clear();
                                query = String(query).trim();
                                if(Ext.isEmpty(query)) {
                                    return;
                                }
                                if(query.substr(0,1) == '#') {
                                    query = query.substr(1).trim();
                                    if(!isNaN(query)) {
                                        App.locateObject(query);
                                        return;
                                    }
                                }
                                App.activateBrowserTab().setParams({
                                    query: query
                                    ,descendants: !Ext.isEmpty(query)
                                });
                            }
                        }
                    }
                    ,'->'
                    ,{
                        // html: '&nbsp;'
                        // ,xtype: 'tbtext'
                        // ,iconCls: App.loginData.iconCls
                        scale: 'large'
                        ,cls: 'btn-no-glyph'
                        ,iconCls: 'bgs32'
                        ,menu: []
                        ,name: 'userMenu'
                    }
                    ,{
                        text: '<span style="margin-right: 10px">&nbsp;</span>'
                        ,xtype: 'tbtext'
                    }

                ]
        });

        App.mainTabPanel = new Ext.TabPanel({
            tabWidth: 205
            ,minTabWidth: 100
            ,enableTabScroll: true
            ,resizeTabs: true
            ,activeTab: 0
            ,region: 'center'
            ,plain: true
            ,bodyStyle: 'background-color: #FFF'
            ,componentCls: 'mainTabPanel'
            ,border: false
            ,defaults: {
                tabConfig: {
                    textAlign: 'left'
                }
            }
            // ,listeners: {
            //     tabchange: function(tp, p){
            //         tp.syncSize();
            //         p.syncSize();
            //     }
            // }
        });

        App.mainAccordion = new Ext.Panel({
            region: 'west'
            // ,layout: 'accordion'
            ,layout: 'fit'
            ,width: 250
            ,split: true
            ,collapsible: true
            ,collapseMode: 'mini'
            ,header: false
            ,animCollapse: false
            ,fill: true
            ,plain: true
            // ,style: 'border-top: 1px solid #dfe8f6'
            ,style: {
                border: 0
            }
            ,bodyStyle: {
                border: 0
            }
            ,bodyCls: 'main-nav'
            ,defaults: {
                border: false
                ,bodyBoder: false
                ,bodyStyle: 'background-color: #F4F4F4'
                ,lazyrender: true
                ,autoScroll: true
            }
            ,layoutConfig: {
                hideCollapseTool: true
                ,titleCollapse: true
            }
            ,stateful: true
            ,stateId: 'mAc'
            ,stateEvents: ['resize', 'collapse', 'expand']
            ,getState: function(){
                var rez = {
                    collapsed: this.collapsed
                    ,width: this.width
                };

                return rez;
            }
        });

        // App.mainStatusBar = new Ext.Toolbar({
        //         region: 'south'
        //         ,cls: 'x-panel-gray'
        //         ,style:'border-top: 1px solid #aeaeae'
        //         ,height: 25
        //         ,items: [
        //             {xtype: 'tbspacer', width: 610}
        //             ,'->'
        //             ,{xtype: 'uploadwindowbutton'}
        //             ,{xtype: 'tbspacer', width: 20}
        //         ]
        // });
        App.mainStatusBar = new CB.widget.TaskBar({
                region: 'south'
                ,style:'border-top: 1px solid #aeaeae'
                ,height: 25
                ,trayItems: [
                    {xtype: 'uploadwindowbutton'}
                ]
        });

        Ext.apply(this, {
            items: [ App.mainToolBar
                ,App.mainTabPanel
                ,App.mainAccordion
                ,App.mainStatusBar
            ]
            ,listeners: {
                scope: this
                ,login: this.onLogin
                ,fileopen: this.onFileOpen
                ,fileupload: this.onFileUpload
                ,filedownload: this.onFilesDownload
                ,createobject: this.createObject
                ,openobject: this.openObject
                ,deleteobject: this.onDeleteObject
                ,opencalendar: this.openCalendar
                ,favoritetoggle: this.toggleFavorite
                ,useradded: this.onUsersChange
                ,userdeleted: this.onUsersChange
                ,viewloaded: this.onViewLoaded

            }
        });

        CB.ViewPort.superclass.initComponent.apply(this, arguments);
    }

    ,onLogin: function(){
        /* adding menu items */

        var um, umIdx = App.mainToolBar.items.findIndex( 'name', 'userMenu');
        if(umIdx > -1) {
            um = App.mainToolBar.items.getAt(umIdx);
            um.setIcon('/' + App.config.coreName + '/photo/' + App.loginData.id + '.jpg?32=' + CB.DB.usersStore.getPhotoParam(App.loginData.id));
            // um.update('<img src="/' + App.config.coreName + '/photo/' + App.loginData.id + '.jpg?32=' + CB.DB.usersStore.getPhotoParam(App.loginData.id) + '" ' +
            //     'style="margin-top: 4px; width: 32px; height: 32px;" ' +
            //     'title="'+ getUserDisplayName(true) + '" />'
            // );
        }

        um.menu.add(
            {
                text: L.Account
                ,iconCls: 'icon-user-' + App.loginData.sex
                ,handler: function(){
                    App.openUniqueTabbedWidget( 'CBAccount' , null);
                }
            }
            ,'-'
            ,{
                text: L.Exit
                ,iconCls: 'icon-exit'
                ,handler: this.logout, scope: this
            }
        );

        var managementItems = [];
        if(App.loginData.manage) {
            managementItems.push(
                {
                    text: L.Users
                    ,iconCls: 'icon-users'
                    ,handler: function(){
                        var w = new CB.VerifyPassword({
                            listeners:{
                                scope: this
                                ,beforeclose: function(cmp){
                                    if(cmp.success !== true) {
                                        cmp.destroy();
                                    } else {
                                        App.openUniqueTabbedWidget('CBUsersGroups');
                                    }
                                }
                            }
                        });
                        w.show();

                    }
                }
            );
        }

        if(App.loginData.admin) {
            managementItems.push(
                {
                    text: 'Reload templates'
                    ,iconCls: 'icon-templates'
                    ,handler: function(){
                        reloadTemplates();
                    }
                }
            );
        }

        // adding available languages to setting menu
        var langs = [];
        CB.DB.languages.each(
            function(r){
                langs.push({
                    text: r.get('name')
                    ,xtype: 'menucheckitem'
                    ,checked: (r.get('id') == App.loginData.language_id)
                    ,data:{id: r.get('id')}
                    ,scope: this
                    ,handler: this.setUserLanguage
                    ,group: 'language'
                });
            }
            ,this
        );


        // creating menu config for available themes
        var themes = [];
        CB.DB.themes.each(
            function(r){
                themes.push({
                    text: r.get('name')
                    ,xtype: 'menucheckitem'
                    ,checked: (r.get('id') == App.loginData.theme)
                    ,data:{id: r.get('id')}
                    ,scope: this
                    ,handler: this.setUserTheme
                    ,group: 'theme'
                });
            }
            ,this
        );

        if(!Ext.isEmpty(managementItems)) {
            managementItems.unshift('-');
        }

        managementItems.unshift({
            text: L.Theme
            ,menu: themes
        });

        managementItems.unshift({
            text: L.Language
            ,iconCls: 'icon-language'
            ,hideOnClick: false
            ,menu: langs
        });



        App.mainToolBar.insert(
            3
            ,{
                qtip: L.Settings
                ,iconCls: 'ib-settings'
                ,cls: 'btn-no-glyph'
                ,hideOnClick: false
                ,scale: 'large'
                ,menu: managementItems
            }
        );

        App.mainToolBar.doLayout();

        /* end of adding menu items */

        App.Favorites = new CB.Favorites();
        App.Favorites.load();
        this.populateMainMenu();
    }

    ,initCB: function(){
        if( CB.DB && CB.DB.templates && (CB.DB.templates.getCount() > 0) ){
            this.onLogin();
            App.DD = new CB.DD();

            Ext.Function.defer(this.checkUrlLocate, 1500);
        } else {
            Ext.Function.defer(this.initCB, 500, this);
        }
    }

    //check if a locate id is specified in url
    ,checkUrlLocate: function() {
        var locateId = String(window.location.href.split('locate=')[1]).split('&')[0];

        if(!Ext.isEmpty(locateId)) {
            App.locateObject(locateId);
        }
    }

    ,logout: function(){
        return Ext.Msg.show({
            buttons: Ext.Msg.YESNO
            ,title: L.ExitConfirmation
            ,msg: L.ExitConfirmationMessage
            ,fn: function(btn, text){
                if (btn == 'yes')
                    CB_User.logout(function(response, e){
                        if(response.success === true) {
                            App.confirmLeave = false;
                            window.location.reload();
                        }
                    });
            }
        });
    }

    ,populateMainMenu: function(){
        App.mainAccordion.add({
            xtype: 'CBBrowserTree'
            ,data: {
                rootNode: App.config.rootNode
            }
            ,rootVisible:true
        });

        // if(!App.mainAccordion.collapsed) {
        //     App.mainAccordion.syncSize();
        // }

        var trees = App.mainAccordion.query('treepanel');

        if(!Ext.isEmpty(trees)){
            App.mainTree = trees[0];
            var rn = App.mainTree.getRootNode();

            rn.data.name = 'My CaseBox';
            // rn.data.name = 'My CaseBox';
            // App.mainTree.on('afterrender', this.selectTreeRootNode, this);

            for (i = 0; i < trees.length; i++) {
                trees[i].getSelectionModel().on(
                    'selectionchange'
                    ,this.onChangeActiveFolder
                    ,this
                );
                trees[i].on('itemclick', this.onTreeNodeClick, this);
                trees[i].on('afterrename', this.onRenameTreeElement, this);
            }
        }

        this.openDefaultExplorer();

        this.selectTreeRootNode();

        App.mainTabPanel.setActiveTab(0);
    }

    ,selectTreeRootNode: function() {
        if(App.mainTree && App.explorer) {
            if(App.mainTree.rendered) {
                var rn = App.mainTree.getRootNode();
                App.mainTree.selectPath('/'+ rn.get('nid'), 'nid', '/');
            }
        }
    }

    ,onTreeNodeClick: function(tree, record, item, index, e, eOpts){
        if(Ext.isEmpty(item) || Ext.isEmpty(record.getPath)) {
            return;
        }

        if(tree.getSelectionModel().isSelected(record)) {
            this.onChangeActiveFolder(null, [record]);
        }
    }

    ,onChangeActiveFolder: function(sm, selection){
        if( this.syncingTreePathWithViewContainer ||
            Ext.isEmpty(selection) ||
            Ext.isEmpty(selection[0].getPath)
        ) {
            return;
        }

        var node = selection[0];
        var params = {
            id: node.get('nid')
            ,view: Ext.valueFrom(node.get('view'), 'grid')
        };

        App.openPath(node.getPath('nid'), params);
    }

    ,createObject: function(data, e){
        App.activateBrowserTab().editObject(data);
    }

    /**
     * reload the viewcontainer when a tree node is renamed
     * @return void
     */
    ,onRenameTreeElement: function(tree, r, e){
        var node = tree.getSelectionModel().getSelection()[0];

        if(Ext.isEmpty(node) || Ext.isEmpty(node.getPath)) {
            return;
        }

        var tab = App.mainTabPanel.getActiveTab();

        if(tab.isXType('CB.browser.ViewContainer')) {
            tab.onReloadClick();
        }
    }

    ,selectGridObject: function(g){
        if(Ext.isEmpty(g) || Ext.isEmpty(App.locateObjectId)) {
            return false;
        }
        var idx = g.store.findExact('nid', String(App.locateObjectId) );
        if(idx >=0){
            var sm = g.getSelectionModel();
            if( (sm.getCount() > 1) ||
                !sm.isSelected(idx)
            ) {
                sm.select(idx, false);
            }

            var view = g.getView();
            Ext.get(view.getRow(idx)).scrollIntoView(view.scroller);
            delete App.locateObjectId;

            return true;
        }

        return false;
    }

    ,onAccordionLinkClick: function(p, animate){
        p = App.openUniqueTabbedWidget(p.link, null, {iconCls: p.iconCls, title: p.title});
        return false;
    }

    ,openCalendar: function(ev){
        if(ev && ev. stopPropagation) ev.stopPropagation();
        App.openUniqueTabbedWidget('CBCalendarPanel');
    }

    ,openDefaultExplorer: function(rootId){
        if(Ext.isEmpty(rootId) && App.mainTree) {
            rootId = Ext.valueFrom( App.mainTree.rootId, '/' );
        }
        if(!App.activateTab(App.mainTabPanel, 'explorer')) {
            App.explorer = App.addTab(
                App.mainTabPanel
                ,new CB.browser.ViewContainer({
                    rootId: rootId
                    ,data: {id: 'explorer' }
                    ,closable: false
                })
            );
        }
    }

    ,openObject: function(data, e){
        if(e){
            if(e.stopEvent) e.stopEvent();
            if(e.processed === true) {
                return;
            }
        }

        if(App.activateTab(App.mainTabPanel, data.id, CB.Objects)) {
            return true;
        }

        var o = Ext.create(
            'CBObjects'
            ,{
                data: data
                ,iconCls: 'icon-loading'
                ,title: L.LoadingData + ' ...'
            }
        );

        this.fireEvent('objectopened', o);
        return App.addTab(App.mainTabPanel, o);
    }

    ,openPermissions: function(objectId) {
        if(isNaN(objectId)) {
            return;
        }

        App.openWindow({
            xtype: 'CBSecurityWindow'
            ,id: 'opw' + objectId //objects permission window
            ,data: {
                id: objectId
            }
        });

        // if(App.activateTab(null, objectId, CB.SecurityPanel)) {
        //     return;
        // }
        // App.addTab(
        //     null
        //     ,Ext.create(
        //         'CB.SecurityPanel'
        //         ,{
        //             data: {
        //                 id: objectId
        //             }
        //         }
        //     )
        // );
    }

    ,onFileOpen: function(data, e){
        if(e) e.stopEvent();

        if(App.activateTab(App.mainTabPanel, data.id)) {
            return true;
        }

        var o = Ext.create(
            'CBFileWindow'
            ,{
                data: data
                ,iconCls: 'icon-loading'
                ,title: L.LoadingData + ' ...'
            }
        );

        return App.addTab(App.mainTabPanel, o);
    }

    ,setUserLanguage: function(b, e){
        var d = b.config.data;

        if(d.id == App.loginData.language_id) {
            return;
        }

        Ext.Msg.confirm(
            L.LanguageChange
            ,L.LanguageChangeMessage
            ,function(pb){
                if(pb == 'yes') {
                    CB_User.setLanguage(d.id, this.processSetUserOption, this);
                }
                if(d.ownerCt) {
                    d.ownerCt.items.each(
                        function(i){
                            i.setChecked(i.data.id == App.loginData.language_id);
                        }
                        ,this
                    );
                }
            }
            ,this
        );
    }

    ,setUserTheme: function(b, e){
        var d = b.config.data;

        if(d.id == App.loginData.theme) {
            return;
        }

        Ext.Msg.confirm(
            L.Theme
            ,L.ThemeChangeMessage
            ,function(pb){
                if(pb == 'yes') {
                    CB_User.setTheme(d.id, this.processSetUserOption, this);
                }
                if(d.ownerCt) {
                    d.ownerCt.items.each(
                        function(i){
                            i.setChecked(i.data.id == App.loginData.theme);
                        }
                        ,this
                    );
                }
            }
            ,this
        );
    }

    ,processSetUserOption: function(r, e){
        if(r.success === true) {
            App.confirmLeave = false;
            document.location.reload();
        } else {
            Ext.Msg.Alert(L.Error, L.ErrorOccured);
        }
    }

    ,toggleFavorite: function(p){
        CB_Browser.toggleFavorite(p, this.processToggleFavorite, this);
    }

    ,processToggleFavorite: function(r, e){
        this.fireEvent('favoritetoggled', r, e);
    }


    ,focusLastElement: function(){
        if(this.lastFocusedElement){
            this.lastFocusedElement.focus(500);
        }
    }

    ,onUsersChange: function(){
        CB.DB.usersStore.reload();
    }

    ,onDeleteObject: function(data){
        Ext.Msg.confirm(
            L.DeleteConfirmation
            ,L.DeleteConfirmationMessage + ' "' + Ext.valueFrom(data.title, data.name) +'"?'
            ,function(btn){
                if(btn == 'yes') {
                    CB_Browser['delete'](data.id, this.onProcessObjectsDeleted, this);
                }
            }
            ,this
        );

    }

    ,onProcessObjectsDeleted: function(r, e){
        if(r.success !== true) {
            return;
        }
        if(!Ext.isEmpty(r.ids)) {
            this.fireEvent('objectsdeleted', r.ids, e);
        }
    }

    ,onFileUpload: function(data, e){
        if(e) {
            e.stopPropagation();
        }

        if(!this.fileField) {
            this.fileField = document.createElement("INPUT");
            this.fileField.setAttribute("type", "file");
            this.fileField.setAttribute("multiple", "true");

            this.fileField.onchange = Ext.Function.bind(
                function(ev){
                    if(this.fileField.files.length > 0) {
                        App.addFilesToUploadQueue(this.fileField.files, this.fileField.data);
                    }
                }
                ,this
            );
        }
        this.fileField.data = data;

        this.fileField.value = null;
        this.fileField.click();
    }

    ,onFileUploaded: function(w, data){
        this.fireEvent('fileuploaded', {data: data});
    }

    ,onFilesDownload: function(ids, zipped, e){
        if(e) e.stopPropagation();
        if(zipped !== true){
            if(!Ext.isArray(ids)) ids = String(ids).split(',');
            Ext.each(ids, function(id){if(isNaN(id)) return false; App.downloadFile(id);}, this);
        } else {
            if(Ext.isArray(ids)) ids = ids.join(',');
            App.downloadFile(ids, true);
        }
    }

    ,toggleWestRegion: function(visible){
        App.mainAccordion.setVisible(visible === true);
        // App.mainViewPort.syncSize();
    }

    ,onViewLoaded: function(proxy, action, options) {
        var trees  = App.mainAccordion.query('treepanel');
        var activeTree = null;
        Ext.each(
            trees
            ,function(t){
                if(t && t.getEl) {
                    var el = t.getEl();
                    if(el && el.isVisible(true)) {
                        activeTree = t;
                    }
                }
            }
            ,this
        );

        // add flag to avoid reloading viewport on node selection change
        this.syncingTreePathWithViewContainer = true;

        if(activeTree &&
            action &&
            action.folderProperties
        ) {
            activeTree.updateCreateMenu(action.folderProperties.menu);

            //check if rootnode id is set at the beginning of the path
            //its id could be missing if it's a virtual root node
            var p = String(action.folderProperties.path).split('/');
            if(p.indexOf(App.config.rootNode.nid) < 0) {
                if(Ext.isEmpty(p[0])) {
                    p.splice(1, 0, App.config.rootNode.nid);
                } else {
                    p.unshift(App.config.rootNode.nid);
                }
            }
            //select the path in tree
            activeTree.selectPath(
                p.join('/')
                ,'nid'
                ,'/'
                ,function(){
                    delete this.syncingTreePathWithViewContainer;
                }
                ,this
            );
        }
    }
});
