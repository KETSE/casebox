Ext.namespace('App');
Ext.BLANK_IMAGE_URL = '/css/i/s.gif';

var clog = function(){
    if(typeof(console) !== 'undefined') {
        console.log(arguments);
    }
}
,plog = clog
,App;

// application main entry point
Ext.onReady(function(){

    App = new Ext.util.Observable();

    App.controller = Ext.create({
        xtype: 'browsingcontroller'
    });

    //set shortcuts to methods that were moved to controller
    //for backward compatibility. To be removed later
    App.locateObject = Ext.Function.bind(App.controller.locateObject, App.controller);
    App.openPath = Ext.Function.bind(App.controller.openPath, App.controller);

    // used for charts
    App.colors = ["#3A84CB", "#94ae0a", "#115fa6","#a61120", "#ff8809", "#ffd13e", "#a61187", "#24ad9a", "#7c7474", "#a66111"];

    App.historyController = Ext.create({
        xtype: 'historycontroller'
    });

    initApp();

    Ext.Date.use24HourTime = true;

    Ext.direct.Manager.addProvider(Ext.app.REMOTING_API);

    Ext.state.Manager.setProvider(
        new CB.state.DBProvider()
    );

    Ext.Direct.on('login', function(r, e){
        Ext.Msg.alert(L.Error, L.SessionExpired);
    });
    Ext.Direct.on('exception', App.showException);
    Ext.QuickTips.init();
    Ext.apply(Ext.QuickTips.getQuickTip(), {showDelay: 1500});

    setTimeout(function(){
        Ext.get('loading').remove();
    }, 10);

    CB_User.getLoginInfo( function(r, e){
        if(!r || (r.success !== true)) {
            return;
        }

        /* use this session id as appended query string for images that reload form session to session
            Such kind of images are user photos that could be updated.
        */
        App.sid = '&qq=' + Date.parse(new Date());
        App.config = r.config;
        App.loginData = r.user;

        // App.loginData.iconCls = 'icon-user-' + Ext.valueFrom(r.user.sex, '');
        App.loginData.iconCls = 'icon-user-account';

        if(App.loginData.cfg.short_date_format) {
            App.dateFormat = App.loginData.cfg.short_date_format;
        }

        if(App.loginData.cfg.long_date_format) {
            App.longDateFormat = App.loginData.cfg.long_date_format;
        }

        if(App.loginData.cfg.time_format) {
            App.timeFormat = App.loginData.cfg.time_format;
        }

        App.mainViewPort = new CB.ViewPort({
            rtl: (App.config.rtl === true)
        });

        App.mainViewPort.doLayout();
        App.mainViewPort.initCB(r, e);
    });


    //Monitor mouse down/up for grid view to avoid selection change when dragging
    App.mouseDown = 0;
    document.body.onmousedown = function(ev) {
        App.lastMouseButton = ev.button;
        ++App.mouseDown;
    };

    document.body.onmouseup = function() {
        --App.mouseDown;
    };

});

//-------------------------------------------- application initialization function
function initApp() {
    App.dateFormat = 'd.m.Y';
    App.longDateFormat = 'j F Y';
    App.timeFormat = 'H:i';

    App.shortenString = function (st, maxLen) {
        if(Ext.isEmpty(st)) {
            return '';
        }
        st = Ext.util.Format.stripTags(st);
        return Ext.util.Format.ellipsis(st, maxLen);
    };

    App.shortenStringLeft = function (st, maxLen) {
        if(Ext.isEmpty(st)) {
            return '';
        }
        st = Ext.util.Format.stripTags(st);
        st = st.split('').reverse().join('');
        st = Ext.util.Format.ellipsis(st, maxLen);
        return st.split('').reverse().join('');
    };

    App.PromtLogin = function (e){
        if (!this.loginWindow || this.loginWindow.isDestroyed) {
            this.loginWindow = new CB.Login({});
        }

        this.loginWindow.show();
    };

    App.formSubmitFailure = function(form, action){
        var msg;
        if(App.hideFailureAlerts) {
            return;
        }

        switch (action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
                msg = 'Form fields may not be submitted with invalid values';
                break;

            case Ext.form.Action.CONNECT_FAILURE:
                msg = 'Ajax communication failed';
                break;

            case Ext.form.Action.SERVER_INVALID:
               msg = Ext.valueFrom(action.msg, action.result.msg);
               msg = Ext.valueFrom(msg, L.ErrorOccured);
        }
        Ext.Msg.alert(L.Error, msg);
    };

    App.includeJS = function(file){
        if (document.createElement && document.getElementsByTagName) {
            var head = document.getElementsByTagName('head')[0];

            var script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', file);

            head.appendChild(script);
        } else {
            alert('Your browser can\'t deal with the DOM standard. That means it\'s old. Go fix it!');
        }
    };

    App.xtemplates = {
        cell: new Ext.XTemplate( '<ul class="thesauri_set"><tpl for="."><li>{.}</li></tpl></ul>' )
        ,object: new Ext.XTemplate( '<ul class="clean"><tpl for="."><li class="case_object" object_id="{id}">{[Ext.isEmpty(values.name) ? \'&lt;'+L.noName+'&gt; (id: \'+values.id+\')\' : values.name]}</li></tpl></ul>' )
    };
    App.xtemplates.cell.compile();
    App.xtemplates.object.compile();

    App.customRenderers = {
        thesauriCell: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) {
                return '';
            }
            var va = v.split(',');
            var vt = []
                ,thesauriId = grid.helperTree.getNode(record.get('id')).data.templateRecord.get('cfg').thesauriId;

            if(Ext.isEmpty(thesauriId) && store.thesauriIds) {
                thesauriId = store.thesauriIds[record.id];
            }

            if(!Ext.isEmpty(thesauriId)){
                var ts = getThesauriStore(thesauriId)
                    ,idx;
                for (var i = 0; i < va.length; i++) {
                    idx = ts.findExact('id', parseInt(va[i], 10));
                    if(idx >=0) {
                        vt.push(ts.getAt(idx).get('name'));
                    }
                }
            }

            return App.xtemplates.cell.apply(vt);
        }

        ,relatedCell: function(v, metaData, record, rowIndex, colIndex, store) { }

        ,combo: function(v, metaData, record, rowIndex, colIndex, store) {
            if(Ext.isEmpty(v)) {
                return '';
            }

            var ed = this.editor
                ,r = ed.store.findRecord(ed.valueField, v, 0, false, false, true);

            if(!r) {
                return '';
            }

            return r.get(ed.displayField);
        }

        ,objectsField: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) {
                return '';
            }

            store = null;

            var rec
                ,row
                ,ri
                ,r = []
                ,va = toNumericArray(v)
                ,cfg = grid.helperTree.getNode(record.get('id')).data.templateRecord.get('cfg')
                ,source = (Ext.isEmpty(cfg.source))
                    ? 'tree'
                    : cfg.source;
            if(Ext.isEmpty(va) && Ext.isPrimitive(v)) {
                va = [v];
            }

            switch(source){
                case 'thesauri':
                    store = isNaN(cfg.thesauriId) ? CB.DB.thesauri : getThesauriStore(cfg.thesauriId);
                    break;
                case 'users':
                    store = CB.DB.usersStore;
                    break;
                case 'groups':
                    store = CB.DB.groupsStore;
                    break;
                default:
                    var cw = null;
                    if(grid && grid.findParentByType) {
                        cw = grid.refOwner || grid.findParentByType(CB.Objects);
                    }
                    if(!cw || !cw.objectsStore) {
                        return '';
                    }
                    store = cw.objectsStore;
            }

            switch(cfg.renderer){
                case 'listGreenIcons':
                    for(i=0; i < va.length; i++){
                        row = store.findRecord('id', va[i], 0, false, false, true);
                        if(row) {
                            r.push('<li class="lh16 icon-padding icon-element">'+row.get('name')+'</li>');
                        }
                    }
                    return '<ul class="clean">'+r.join('')+'</ul>';
                case 'listObjIcons':
                    for(i=0; i < va.length; i++){
                        row = store.findRecord('id', va[i], 0, false, false, true);
                        if(row) {
                            var icon = row.get('cfg');
                            if(!Ext.isEmpty(icon)) {
                                icon = icon.iconCls;
                            }
                            if(Ext.isEmpty(icon)) {
                                icon = row.get('iconCls');
                            }
                            r.push('<li class="lh16 icon-padding '+icon+'">'+row.get('name')+'</li>');
                        }
                    }
                    return '<ul class="clean">'+r.join('')+'</ul>';

                default:
                    for(i=0; i < va.length; i++){
                        rec = store.findRecord('id', va[i], 0, false, false, true);

                        if(rec) {
                            r.push(rec.get('name'));
                        } else {
                            r.push(va[i]); //display id if nothing found (useful for custom sources)
                        }
                    }
                    return r.join(', ');
            }

        }

        ,languageCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) {
                return '';
            }

            var ri = CB.DB.languages.findExact('id', parseInt(v, 10));

            if(ri < 0) {
                return '';
            }

            return CB.DB.languages.getAt(ri).get('name');
        }

        ,sexCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) {
                return '';
            }
            var ri = CB.DB.sex.findExact('id', v);

            if(ri < 0) {
                return '';
            }

            return CB.DB.sex.getAt(ri).get('name');
        }

        ,shortDateFormatCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) {
                return '';
            }

            var ri = CB.DB.shortDateFormats.findExact('id', v);

            if(ri < 0) {
                return '';
            }

            return CB.DB.shortDateFormats.getAt(ri).get('name');
        }

        ,thesauriCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) {
                return '';
            }
            var node = grid.helperTree.getNode(record.get('id'))
                ,tr = node.data.templateRecord
                ,th = tr.get('cfg').thesauriId;

            if(th === 'dependent'){
                th = grid.helperTree.getParentValue(node, tr.get('pid'));
            }
            var ts = getThesauriStore(th)
                ,ri = ts.findExact('id', parseInt(v, 10));

            if(ri < 0) {
                return '';
            }

            return ts.getAt(ri).get('name');
        }

        ,checkbox: function(v){
            if(v == 1) {
                return L.yes;
            }

            if(v == -1) {
                return L.no;
            }

            return '';
        }

        ,date: function(v){
            var rez = '';
            if(Ext.isEmpty(v)) {
                return rez;
            }

            rez = Ext.Date.format(
                Ext.isPrimitive(v)
                    ? Ext.Date.parse(v.substr(0,10), 'Y-m-d')
                    : v
                ,App.dateFormat
            );

            return rez;
        }
        /**
         * [datetime description]
         * @param  varchar v
         * @param  {[type]} showZeroTime [description]
         * @return {[type]}              [description]
         */
        ,datetime: function(v, showZeroTime){
            var rez = '';
            if(Ext.isEmpty(v)) {
                return rez;
            }

            rez = Ext.isPrimitive(v)
                ? date_ISO_to_local_date(v)
                : v;

            var s = rez.toISOString();
            if(s.substr(-14) === 'T00:00:00.000Z') {
                rez = Ext.Date.clearTime(rez, true);
            }

            rez = Ext.Date.format(rez, App.dateFormat + ' ' + App.timeFormat);
            if(Ext.isEmpty(rez)) {
                return '';
            }

            if(showZeroTime === false) {
                if(rez.substr(-5, 5) === '00:00') {
                    rez = rez.substr(0, rez.length - 6);
                }
            }

            return rez;
        }

        ,time: function(v, meta){
            if(Ext.isEmpty(v)) {
                return '';
            }

            if(Ext.isPrimitive(v)) {
                v = Ext.Date.parse(v, 'H:i:s');
            }

            var format = (meta.fieldConfig && meta.fieldConfig.format)
                ? meta.fieldConfig.format
                : App.timeFormat;

            return Ext.Date.format(v, format);
        }

        ,filesize: function(v){
            if(isNaN(v) || Ext.isEmpty(v) || (v === '0') || (v <= 0)) {
                return '';
            }

            if(v <= 0) {
                return  '0 KB';
            } else if(v < 1024) {
                return '1 KB';
            } else if(v < 1024 * 1024) {
                return (Math.round(v / 1024) + ' KB');
            } else {
                var n = v / (1024 * 1024);
                return (n.toFixed(2) + ' MB');
            }
        }

        ,tags: function(v, m, r, ri, ci, s){
            if(Ext.isEmpty(v)) {
                return '';
            }

            var rez = [];

            Ext.each(
                v
                ,function(i){
                    rez.push(i.name);
                }
                ,this
            );

            rez = rez.join(', ');

            m.attr = 'name="' + rez.replace(/"/g, '&quot;') + '"';

            return rez;
        }

        ,tagIds: function(v){
            if(Ext.isEmpty(v)) {
                return '';
            }

            var rez = [];

            v = String(v).split(',');

            Ext.each(
                v
                ,function(i){
                    rez.push(CB.DB.thesauri.getName(i));
                }
                ,this
            );

            rez = rez.join(', ');

            return rez;
        }

        ,importance: function(v){
            if(Ext.isEmpty(v)) {
                return '';
            }

            return CB.DB.importance.getName(v);
        }

        ,timeUnits: function(v){
            if(Ext.isEmpty(v)) {
                return '';
            }

            return CB.DB.timeUnits.getName(v);
        }

        ,taskStatus: function(v, m, r, ri, ci, s){
            if(Ext.isEmpty(v)) {
                return '';
            }
            return '<span class="taskStatus'+v+'">'+L['taskStatus'+parseInt(v, 10)]+'</span>';
        }

        ,text: function(v, m, r, ri, ci, s){
            if(Ext.isEmpty(v)) {
                return '';
            }
            return '<pre style="white-space: pre-wrap">' + v + '</pre>';
        }
        ,titleAttribute: function(v, m, r, ri, ci, s){
            m.tdAttr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace(/"/g,"&quot;")+'"';
            return v;
        }
        ,userName: function(v){ return CB.DB.usersStore.getName(v);}
        ,iconcombo: function(v){
            if(Ext.isEmpty(v)) {
                return '';
            }
            return '<img src="/css/i/s.gif" class="icon '+v+'" /> '+v;
        }
    };

    App.getCustomRenderer = function(fieldType){
        switch(fieldType){
            case 'checkbox':
                return App.customRenderers.checkbox;
            case 'date':
                return App.customRenderers.date;
            case 'datetime':
                return App.customRenderers.datetime;
            case 'time':
                return App.customRenderers.time;
            case '_objects':
                return App.customRenderers.objectsField;
            case 'combo':
            case '_language':
                return App.customRenderers.languageCombo;
            case '_sex':
                return App.customRenderers.sexCombo;
            case 'importance':
                return App.customRenderers.importance;
            case 'timeunits':
                return App.customRenderers.timeUnits;
            case '_templateTypesCombo':
                return Ext.Function.bind(CB.DB.templateTypes.getName, CB.DB.templateTypes);
            case '_fieldTypesCombo':
                return Ext.Function.bind(CB.DB.fieldTypes.getName, CB.DB.fieldTypes);
            case '_short_date_format':
                return App.customRenderers.shortDateFormatCombo;
            case 'memo':
            case 'text':
                return App.customRenderers.text;
            default: return null;
        }
    };

    App.getTemplatesXTemplate = function(template_id){

        template_id = String(template_id);

        if(!Ext.isDefined(App.templatesXTemplate)) {
            App.templatesXTemplate = {};
        }

        if(App.templatesXTemplate[template_id]) {
            return App.templatesXTemplate[template_id];
        }

        var idx = CB.DB.templates.findExact('id', template_id);

        if(idx >= 0){
            var r = CB.DB.templates.getAt(idx)
                ,it = r.get('info_template');

            if(!Ext.isEmpty(it)){
                App.templatesXTemplate[template_id] = new Ext.XTemplate(it);
                App.templatesXTemplate[template_id].compile();
                return App.templatesXTemplate[template_id];
            }
        }

        return App.xtemplates.object;
    };

    App.findTab = function(tabPanel, id, xtype){
        var tabIdx = -1
            ,i = 0;

        if(!Ext.isEmpty(id)) {
            while((tabIdx == -1) && (i < tabPanel.items.getCount())){
                var o = tabPanel.items.get(i);
                if(Ext.isEmpty(xtype) || ( o.isXType && o.isXType(xtype) ) ){
                    if(Ext.isDefined(o.params) && Ext.isDefined(o.params.id) && (o.params.id == id)) {
                        tabIdx = i;
                    } else {
                        if(!Ext.isEmpty(o.data) && !Ext.isEmpty(o.data.id) && (o.data.id == id)) {
                            tabIdx = i;
                        }
                    }
                }
                i++;
            }
        }

        return tabIdx;
    };

    App.findTabByType = function(tabPanel, type){
        var tabIdx = -1
            ,i= 0;

        if(!Ext.isEmpty(type)) {
            while((tabIdx == -1) && (i < tabPanel.items.getCount())){
                var o = tabPanel.items.get(i);
                if(Ext.isDefined(o.isXType) && o.isXType(type)) {
                    tabIdx = i;
                }
                i++;
            }
        }

        return tabIdx;
    };

    App.activateTab = function(tabPanel, id, xtype){
        if(Ext.isEmpty(tabPanel)) {
            tabPanel = App.mainTabPanel;
        }

        var tabIdx = App.findTab(tabPanel, id, xtype);

        if(tabIdx < 0) {
            return false;
        }
        tabPanel.setActiveTab(tabIdx);

        return tabPanel.items.getAt(tabIdx);
    };

    App.addTab = function(tabPanel, o){
        if(Ext.isEmpty(tabPanel)) {
            tabPanel = App.mainTabPanel;
        }

        var c = tabPanel.add(o);
        o.show();
        tabPanel.setActiveTab(c);

        return c;
    };

    App.getHtmlEditWindow = function(config){
        if(!App.htmlEditWindow) {
            App.htmlEditWindow = new CB.HtmlEditWindow();
        }

        App.htmlEditWindow = Ext.apply(App.htmlEditWindow, config);

        return App.htmlEditWindow;
    };

    App.openObjectWindow = function(config) {
        //at least template should be defined in config
        if(Ext.isEmpty(config)) {
            return;
        }

        if(Ext.isEmpty(config.template_id)) {
            return Ext.Msg.alert(
                'Error opening object'
                ,'Template should be specified for object window to load.'
            );
        }

        config.id = Ext.valueFrom(config.target_id, config.id);

        var templateType = CB.DB.templates.getType(config.template_id)
            ,wndCfg = {
                xtype: (templateType === 'file'
                    ? 'CBFileEditWindow'
                    : 'CBObjectEditWindow'
                )
                ,data: config
                ,modal: Ext.valueFrom(config.modal, false)
            };

        wndCfg.id = 'oew-' +
            (Ext.isEmpty(config.id)
                ? Ext.id()
                : config.id
            );

        var w = App.openWindow(wndCfg)
            ,winHeight = window.innerHeight;

        if(w) {
            if((winHeight > 0) && (w.getHeight() > winHeight)) {
                w.setHeight(winHeight - 20);
            }

            if(templateType === 'file') {
                w.center();

                if(config.name && (detectFileEditor(config.name) !== false)) {
                    w.maximize();
                }
            } else {
                if(config.alignWindowTo) {
                    App.alignWindowToCoords(w, config.alignWindowTo);

                } else if(!w.existing) {
                    App.alignWindowNext(w);
                }
            }

            delete w.existing;
        }
    };

    App.openWindow = function(wndCfg) {
        var w = Ext.getCmp(wndCfg.id);

        if(w) {
            App.mainStatusBar.setActiveButton(w.taskButton);
            App.mainStatusBar.restoreWindow(w);
            //set a flag that this was an existing window
            w.existing = true;

        } else {
            w = Ext.create(wndCfg);
            w.show();

            w.taskButton = App.mainStatusBar.addTaskButton(w);
        }

        return w;
    };


    App.alignWindowNext = function (w) {
        w.alignTo(App.mainViewPort.getEl(), 'br-br?');

        //get anchored position
        var pos = w.getXY();
        //move above status bar and a bit from right side
        pos[0] -= 15;
        pos[1] -= 5;

        //position to the left of an active window if any
        var x = pos[0];
        App.mainStatusBar.windowBar.items.each(
            function(btn) {
                if(btn.win && (btn.win != w) && btn.win.isVisible() && !btn.win.maximized && (btn.win.xtype !== 'CBSearchEditWindow')) {
                    var wx = btn.win.getX() - btn.win.el.getWidth() - 15;
                    if(x > wx) {
                        x = wx;
                    }
                }
            }
            ,this
        );
        if(x < 15) {
            x = 15;
        }
        pos[0] = x;

        w.setXY(pos);
    };

    App.alignWindowToCoords = function (win, coords) {
        var vpEl = App.mainViewPort.getEl();
        win.alignTo(vpEl, 'br-br?');

        //get anchored position
        var pos = win.getXY()
            ,w = win.getWidth()
            ,h = win.getHeight();

        //move above status bar and a bit from right side
        pos[0] -= 15;
        pos[1] -= 5;

        //position to center and below of given coords
        var x = pos[0];

        pos[0] = coords[0] - w / 2;
        pos[1] = coords[1] + 10;

        // check if window didnt go outside of viewport
        if (pos[0] + w > vpEl.getWidth()) {
            pos[0] = vpEl.getWidth() - w - 10;
        }

        if (pos[1] + h > vpEl.getHeight()) {
            pos[1] = vpEl.getHeight() - h - 20;
        }

        win.setXY(pos);
    };

    App.isFolder = function(template_id){
        return (App.config.folder_templates.indexOf( String(template_id) ) >= 0);
    };

    App.isWebDavDocument = function(name){
        if(!Ext.isPrimitive(name) || Ext.isEmpty(name) || Ext.isEmpty(App.config['files.edit'].webdav)) {
            return false;
        }
        var ext = name.split('.').pop();
        return (App.config['files.edit'].webdav.indexOf(ext) >= 0);
    };

    App.openWebdavDocument = function(data, checkCookie){
        var url = window.location.origin + '/dav/' + App.config.coreName + '/';

        url += 'edit-' + Ext.valueFrom(data.id, data.nid);
        url += '/' + data.name;
        App.confirmLeave = false;

        if((checkCookie !== false) &&
            (Ext.util.Cookies.get('webdavHideDlg') == 1)
        ) {
            window.open('cbdav:' + url, '_self');
        } else {
            var w = new CB.WebdavWindow({
                data: {link: url}
            });
            w.show();
            w.center();
        }
    };

    App.activateBrowserTab = function(){
        var tab = App.mainTabPanel.getActiveTab();

        if(tab.isXType('CBBrowserViewContainer')) {
            return tab;
        }
        App.mainTabPanel.setActiveTab(App.explorer);
        return App.explorer;
    };


    App.downloadFile = function(fileId, zipped, versionId){
        if(Ext.isElement(fileId)){
            //retreive id from html element
            fileId = fileId.id;
            zipped = false;
        }

        var url = '/' + App.config.coreName + '/download/' + fileId;

        if(!Ext.isEmpty(versionId)) {
            url += '&v='+versionId;
        }

        if(zipped) {
            url += '&z=1';
        }

        window.open(url, 'cbfd' + fileId);
    };

    App.getTypeEditor = function(type, e){
        var editorCfg = {
            //enable key events by default
            enableKeyEvents: true
        };

        var objData = {
            ownerCt: e.ownerCt
            ,record: e.record
            ,fieldRecord: e.fieldRecord
            ,objFields: e.objFields
            ,duplicationIndexes: e.duplicationIndexes
            ,grid: e.grid
            ,pidValue: e.pidValue
            ,objectId: e.objectId
            ,objectPid: e.objectPid
            ,path: e.path
        };
        var w, th, ed, rez = null;
        var tr = e.fieldRecord;
        var cfg = tr.get('cfg');
        var objectWindow = e.ownerCt
            ? e.ownerCt
            : (e.grid
                ? (
                    e.grid.refOwner
                        ? e.grid.refOwner
                        : e.grid.findParentByType(CB.Objects)
                )
                : null
            );

        var expandHandler = function(cmp) {
                if (cmp && cmp.expand) {
                    cmp.expand();
                }
            },
            autoExpand = {'boxready': expandHandler};

        switch(type){
            case '_objects':
                //e should contain all necessary info
                switch(cfg.editor){
                    case 'form':
                        if(e && e.grid){
                            e.cancel = true;
                            e.value = e.record.get('value');

                            var formEditor = new CB.object.field.editor.Form({
                                data: objData
                                ,value: e.record
                                    ? e.value
                                    : Ext.valueFrom(e.value, null)
                                ,listeners: {
                                    scope: e
                                    ,setvalue: function(value, editor) {
                                        var objStore = (this.grid)
                                            ? this.grid.refOwner.objectsStore
                                            : null;

                                        if(objStore && editor.selectedRecordsData) {
                                            objStore.checkRecordsExistance(editor.selectedRecordsData);
                                        }

                                        this.originalValue = this.value;
                                        this.value = editor.getValue().join(',');

                                        this.record.set('value', this.value);

                                        if(this.grid.onAfterEditProperty) {
                                            this.grid.onAfterEditProperty(editor, this);
                                        } else {
                                            this.grid.fireEvent('change', this);
                                        }
                                        if(this.grid.gainFocus) {
                                            this.grid.gainFocus();
                                        }
                                    }

                                    ,destroy: function(ed) {
                                        if(this.grid) {
                                            this.grid.focus(false, 100);
                                        }
                                    }
                                }
                            });

                            formEditor.show();

                        } else {
                            rez = new CB.ObjectsTriggerField({
                                enableKeyEvents: true
                                ,data: objData
                            });
                        }
                        break;

                    case 'text':
                        ed = new Ext.form.Text({
                            data: objData

                            ,plugins: [{
                                ptype: 'CBPluginFieldDropDownList'
                                ,commands: [
                                    {
                                        prefix: ' '
                                        ,regex: /^([\w\d_\.]+)/i

                                        ,insertField: 'info'

                                        ,handler: CB.plugin.field.DropDownList.prototype.onAtCommand
                                    }
                                ]
                            }]
                        });

                        //overwrite setValue and getValue function to transform ids to user names and back
                        ed._setValue = ed.setValue;
                        ed._getValue = ed.getValue;

                        ed.setValue = function(value) {
                            var v = toNumericArray(value);
                            for (var i = 0; i < v.length; i++) {
                                v[i] = CB.DB.usersStore.getUserById(v[i]);
                            }

                            this._setValue(v.join(', '));
                        };

                        ed.getValue = function() {
                            var value = this._getValue();
                            value = Ext.util.Format.trim(String(value).replace(/[\n\r,]/g, ' '));

                            if(Ext.isEmpty(value)) {
                                return '';
                            }

                            var rez = [];
                            var v = value.split(' ');
                            for (var i = 0; i < v.length; i++) {
                                if(!Ext.isEmpty(v[i])) {
                                    var id = CB.DB.usersStore.getIdByUser(v[i]);
                                    if(!Ext.isEmpty(id) && (rez.indexOf(id) < 0)) {
                                        rez.push(id);
                                    }
                                }
                            }

                            return rez.join(',');
                        };

                        return ed;

                    case 'tagField':
                        ed = new CB.object.field.editor.Tag({
                            objData: objData
                            ,valueField: 'id'
                            ,displayField: 'name'
                            ,forceSelection: true
                            ,typeAhead: true
                            ,queryMode: 'remote'
                            ,autoLoadOnValue: true
                            ,autoSelect: false
                            ,multiSelect: true
                            ,minChars: 2
                            // ,stacked: true
                            ,pinList: false
                            ,filterPickList: true
                        });

                        return ed;
                    default:
                        return new CB.ObjectsComboField({
                            enableKeyEvents: true
                            ,data: objData
                            ,listeners: autoExpand
                        });
                }

                break;
            case 'checkbox':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,triggerAction: 'all'
                    ,queryMode: 'local'
                    ,editable: false
                    ,store: CB.DB.yesno
                    ,displayField: 'name'
                    ,valueField: 'id'
                });
                break;

            case 'timeunits':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,editable: false
                    ,store: CB.DB.timeUnits
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case 'importance':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,editable: false
                    ,store: CB.DB.importance
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case 'date':
                rez = new Ext.form.DateField({
                    enableKeyEvents: true
                    ,format: App.dateFormat
                    ,width: 100
                    ,listeners: autoExpand
                });
                break;

            case 'datetime':
                rez = new Ext.form.DateField({
                    enableKeyEvents: true
                    ,format: App.dateFormat+' ' + App.timeFormat
                    ,width: 130
                    ,listeners: autoExpand
                });
                break;

            case 'time':
                rez = new Ext.form.field.Time({
                    enableKeyEvents: true
                    ,format: App.timeFormat
                    ,listeners: autoExpand
                });
                break;

            case 'int':
                rez = new Ext.form.NumberField({
                    enableKeyEvents: true
                    ,allowDecimals: false
                    ,width: 90
                });
                break;

            case 'float':
                var fieldCfg = {
                    enableKeyEvents: true
                    ,allowDecimals: true
                    ,width: 90
                };

                Ext.copyTo(fieldCfg, cfg, 'decimalPrecision');

                rez = new Ext.form.NumberField(fieldCfg);
                break;

            case 'combo':
                th = cfg.thesauriId;
                if(th === 'dependent'){
                    th = e.pidValue;
                }

                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: getThesauriStore(th)
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case 'iconcombo':
                th = cfg.thesauriId;
                if(th === 'dependent'){
                    th = e.pidValue;
                }
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: false
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: getThesauriStore(th)
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,iconClsField: 'name'
                    ,listeners: autoExpand
                });
                break;

            case '_language':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.languages
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case '_sex':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.sex
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case '_templateTypesCombo':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.templateTypes
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case '_fieldTypesCombo':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,autoSelect: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.fieldTypes
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case '_short_date_format':
                rez = new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.shortDateFormats
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,listeners: autoExpand
                });
                break;

            case 'memo':
                var height = Ext.valueFrom(cfg.height, 50);
                height = parseInt(height, 10);
                if(e.grid) {
                    var rowEl = e.grid.getView().getRow(e.row);
                    if(rowEl) {
                        var rowHeight = Ext.get(rowEl).getHeight() - 12;
                        if(height < rowHeight) {
                            height = rowHeight;
                        }
                    }
                }

                var edConfig = {
                    enableKeyEvents: true
                    ,height: height
                    ,plugins: []
                };

                if (cfg.maxLength) {
                    edConfig.maxLength = cfg.maxLength;
                    edConfig.enforceMaxLength = true;
                    edConfig.plugins.push({
                        ptype: 'CBPluginFieldRemainingCharsHint'
                    });
                }

                if(cfg.mentionUsers) {
                    edConfig.plugins.push({
                        ptype: 'CBPluginFieldDropDownList'
                    });

                }

                rez = new Ext.form.TextArea(edConfig);
                break;

            case 'text':
                e.cancel = true;
                rez = new CB.TextEditWindow({
                    title: tr.get('title')
                    ,editor: tr.get('cfg').editor
                    ,mode: tr.get('cfg').mode
                    ,data: {
                        value: e.record.get('value')
                        ,scope: e
                        ,callback: function(w, v){
                            this.originalValue = this.record.get('value');
                            this.value = v;
                            this.record.set('value', v);
                            if(this.grid.onAfterEditProperty) {
                                this.grid.onAfterEditProperty(this, this);
                            }
                        }
                    }
                });
                rez.on('destory', e.grid.gainFocus, e.grid);
                rez.show();

                break;

            case 'html':
                e.cancel = true;
                rez = App.getHtmlEditWindow({
                    title: tr.get('title')
                    ,data: {
                        value: e.record.get('value')
                        ,scope: e
                        ,callback: function(w, v){
                            this.originalValue = this.record.get('value');
                            this.value = v;
                            this.record.set('value', v);

                            if (this.grid.onAfterEditProperty) {
                                this.grid.onAfterEditProperty(this);
                            }

                            this.grid.fireEvent('change');
                        }
                    }
                });

                if(!Ext.isEmpty(e.grid)) {
                    w.on('hide', e.grid.gainFocus, e.grid);
                }
                rez.show();
                break;

            case 'geoPoint':
                if(tr && (tr.get('cfg').editor === 'form')) {
                    e.cancel = true;

                    rez = Ext.create('CB.LeafletWindow', {
                        title: L.Map
                        ,data: {
                            value: e.record.get('value')
                            ,cfg: tr.get('cfg')
                            ,scope: e
                            ,callback: function(w, v){
                                this.originalValue = this.record.get('value');
                                this.value = v;
                                this.record.set('value', v);
                                if(this.grid.onAfterEditProperty) {
                                    this.grid.onAfterEditProperty(this, this);
                                }
                            }
                        }
                    });
                    rez.on('destory', e.grid.gainFocus, e.grid);
                    rez.show();

                } else {
                    rez = new Ext.form.TextField({
                        enableKeyEvents: true
                        ,maskRe: /[\-\d\.,]/
                    });

                }
                break;

            default:
                rez = new Ext.form.TextField({
                    enableKeyEvents: true
                });
        }

        return rez;
    };

    App.successResponse = function(r){
        if(r && (r.success === true)) {
            return true;
        }
        Ext.Msg.alert(L.Error, Ext.valueFrom(r.msg, L.ErrorOccured));
        return false;
    };

    App.showTestingWindow = function(){
        if(!App.testWindow) {
            App.testWindow = new CB.TestingWindow({ closeAction: 'hide' });
        }

        App.testWindow.show();
    };

    App.openUniqueTabbedWidget = function(type, tabPanel, options){
        if(Ext.isEmpty(tabPanel)) {
            tabPanel = App.mainTabPanel;
        }
        var tabIdx = App.findTabByType(tabPanel, type)
        ;
        if(Ext.isEmpty(options)) {
            options = {};
        }
        var rez = null;
        if(tabIdx < 0) {
            rez = Ext.create(type, options);
            App.addTab(tabPanel, rez);
        } else {
            rez = tabPanel.get(tabIdx);
        }
        tabPanel.setActiveTab(rez);
        return rez;
    };

    App.showException = function(e){
        App.hideFailureAlerts = true;
        var msg = '';

        if(e) {
            msg = e.msg;
        }

        if(Ext.isEmpty(msg) && e.message) {
            msg = e.message;
        }

        if(Ext.isEmpty(msg) && e.result) {
            msg = e.result.msg;
        }

        if(Ext.isEmpty(msg) && e.result) {
            msg = L.ErrorOccured;
        }

        if(!App.errorMsgDiv) {
            App.errorMsgDiv = App.getNotificationDiv();
        }

        App.errorMsgDiv.update('<div class="content">' +  msg + '</div>');
        App.errorMsgDiv.show();
        App.errorMsgDiv.getEl().fadeIn();

        App.errorMsgDiv.task.delay(5000);

        var dhf = function(){
            delete App.hideFailureAlerts;
        };
        Ext.Function.defer(dhf, 1500);
    };

    App.hideException = function() {
        App.errorMsgDiv.fadeOut();
    };

    App.getNotificationDiv = function() {
        var rez = Ext.create('Ext.Component', {
            html: ''
            ,padding: 5
            ,floating: true
            ,y: 1
            ,hideMode: 'offsets'
            ,width: '100%'
            ,shadow: false
            ,cls: 'error-msg-div'
            ,style: {
                textAlign: 'center'
            }
            ,renderTo: Ext.getBody()
        });

        rez.task = new Ext.util.DelayedTask(
            rez.getEl().fadeOut
            ,rez.getEl()
        );

        return rez;
    };

    App.clipboard = new CB.Clipboard();

    /* disable back button */
    var o = Ext.isIE ? document : window;
    o.onkeydown = function(e, t) {
        if(Ext.isEmpty(t)) {
            t = e.target;
        }
        if ((e.keyCode == Ext.event.Event.BACKSPACE) &&
             e.stopEvent &&
                (
                    (!/^input$/i.test(t.tagName) &&
                    !/^textarea$/i.test(t.tagName)
                ) || t.disabled || t.readOnly)) {
            e.stopEvent();
        }
    };

    /* disable back button */

    /* upload files methods*/
    App.getFileUploader = function(){
        if(this.Uploader) return this.Uploader;
        this.Uploader = new CB.Uploader({
            listeners: {
            }
        });
        if(this.Uploader.init() === false){
            delete this.Uploader;
            return null;
        }
        return this.Uploader;
    };

    App.addFilesToUploadQueue = function(FileList, options){
        var fu = App.getFileUploader();
        if(fu) {
            fu.addFiles(FileList, options);
        } else {
            Ext.Msg.alert(L.Info, L.BrowserNoDDUpload);
        }
    };

    App.onComponentActivated = function(component){
        plog('component activated', arguments, this);
    };

    /**
     * generic method to rename an object
     * @param  object p containing path, name, callback, scope
     * @return void
     */
    App.promptRename = function(p) {
        App.promptRenameData  = p;

        Ext.Msg.prompt(
            L.Rename
            ,L.Name
            ,function(btn, text, opt) {
                if(btn !== 'ok') {
                    return;
                }

                CB_BrowserView.rename(
                    {
                        path: App.promptRenameData.path
                        ,name: text
                    }
                    ,function(r, e){
                        if(!r || (r.success !== true)) {
                            return;
                        }

                        App.fireEvent(
                            'objectchanged'
                            ,{
                                id: parseInt(r.data.id, 10)
                                ,pid: r.data.pid
                            }
                            ,e
                        );

                        var rd = App.promptRenameData;
                        if(rd.callback) {
                            if(rd.scope) {
                                rd.callback = Ext.Function.bind(rd.callback, rd.scope);
                            }
                            rd.callback(r, e);
                        }
                    }
                    ,this
                );
            }
            ,this
            ,false
            ,App.promptRenameData.name
        ).setWidth(400).center();

    };
}

window.onbeforeunload = function() {
    if (App.confirmLeave === false) {
        delete App.confirmLeave;
    } else {
        return "You work will be lost.";
    }
};

window.ondragstart = function(e){
    window.dragFromWindow = true;
    return true;
};

window.ondragenter = function(e){
    e.dataTransfer.dropEffect = 'copy';
    e.preventDefault();
    if(!window.dragFromWindow){
        App.fireEvent('dragfilesenter', e);
    }
    return false;
};

window.ondragover = function(e){
    e.dataTransfer.dropEffect = 'copy';
    e.preventDefault();
    return false;
};

window.ondrop = function(e){
    e.stopPropagation();
    e.preventDefault();
    if(!window.dragFromWindow){
        App.fireEvent('filesdrop', e);
    }
    return false;
};

window.ondragleave = function(e){
    if(!window.dragFromWindow && ( (e.pageX === '0') && (e.pageY === '0') ) ){
        App.fireEvent('dragfilesleave', e);
    }
    return false;
};
window.ondragend = function(e){
    delete window.dragFromWindow;
};

// window.onerror = function(message, url, linenumber)
// {
//    var errors = {};
//    errors.message    = message;
//    errors.url        = url;
//    errors.linenumber = linenumber;
//    clog('ERROR:', errors);
//   // jQuery.ajax({
//   //     type: "POST",
//   //     url: "/scripts/error_report.php",
//   //     dataType: "json",
//   //     data: errors
//   //  });

//   return true;
// };
