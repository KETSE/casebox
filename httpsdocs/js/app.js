Ext.namespace('App');
Ext.BLANK_IMAGE_URL = '/css/i/s.gif';

clog = function(){
    if(typeof(console) != 'undefined') {
        console.log(arguments);
    }
};

plog = clog;

// application main entry point
Ext.onReady(function(){
    // Ext.Function._bind = Ext.Function.bind;
    // Ext.Function.bind = function(fn, scope, args, appendArgs) {
    //     return Ext.Function._bind(fn, scope, args, appendArgs);
    // };
    App = new Ext.util.Observable();

    // used for charts
    App.colors = [ "#3A84CB", "#94ae0a", "#115fa6","#a61120", "#ff8809", "#ffd13e", "#a61187", "#24ad9a", "#7c7474", "#a66111"];

    // App.addEvents(
    //     'dragfilesenter'
    //     ,'dragfilesover'
    //     ,'dragfilesleave'
    //     ,'filesdrop'
    //     ,'objectsaction'
    //     ,'userprofileupdated'
    // );

    initApp();

    Ext.Date.use24HourTime = true;

    // Ext.Direct.addProvider(Ext.app.REMOTING_API);
    Ext.direct.Manager.addProvider(Ext.app.REMOTING_API);

    Ext.state.Manager.setProvider(
        new CB.state.DBProvider()
    );

    Ext.Direct.on('login', function(r, e){
        /*if(r.method == 'logout') /**/
        window.location.reload();
        /*else App.PromtLogin(); /**/
    });
    Ext.Direct.on('exception', App.showException);
    Ext.QuickTips.init();
    Ext.apply(Ext.QuickTips.getQuickTip(), {showDelay: 1500});

    setTimeout(function(){
        Ext.get('loading').remove();
    }, 10);

    CB_User.getLoginInfo( function(r, e){
        if(r.success !== true) {
            return;
        }

        /* use this session id as appended query string for images that reload form session to session
            Such kind of images are user photos that could be updated.
        */
        App.sid = '&qq=' + Date.parse(new Date());
        App.config = r.config;
        App.loginData = r.user;
        App.loginData.iconCls = 'icon-user-' + Ext.valueFrom(r.user.sex, '');

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
        App.mainViewPort.initCB( r, e );
    });

});

//--------------------------------------------------------------------------- application initialization function
function initApp() {
    App.dateFormat = 'd.m.Y';
    App.longDateFormat = 'j F Y';
    App.timeFormat = 'H:i';

    App.shortenString = function (st, maxLen) {
        if(Ext.isEmpty(st)) return '';
        st = Ext.util.Format.stripTags(st);
        return Ext.util.Format.ellipsis(st, maxLen);
    };

    App.PromtLogin = function (e){
        if( !this.loginWindow || this.loginWindow.isDestroyed ) this.loginWindow = new CB.Login({});
        this.loginWindow.show();
    };

    App.formSubmitFailure = function(form, action){
        if(App.hideFailureAlerts) return;
        switch (action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            Ext.Msg.alert(L.Error, 'Form fields may not be submitted with invalid values'); break;
            case Ext.form.Action.CONNECT_FAILURE:
            Ext.Msg.alert(L.Error, 'Ajax communication failed'); break;
            case Ext.form.Action.SERVER_INVALID:
               msg = Ext.valueFrom(action.msg, action.result.msg);
               msg = Ext.valueFrom(msg, L.ErrorOccured);
               Ext.Msg.alert(L.Error, msg);
           }
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
            var vt = [];
            thesauriId = grid.helperTree.getNode(record.get('id')).data.templateRecord.get('cfg').thesauriId;
            if(Ext.isEmpty(thesauriId) && store.thesauriIds) {
                thesauriId = store.thesauriIds[record.id];
            }
            if(!Ext.isEmpty(thesauriId)){
                ts = getThesauriStore(thesauriId);
                for (var i = 0; i < va.length; i++) {
                    idx = ts.findExact('id', parseInt(va[i], 10));
                    if(idx >=0) vt.push(ts.getAt(idx).get('name'));
                }
            }
            return App.xtemplates.cell.apply(vt);
        }
        ,relatedCell: function(v, metaData, record, rowIndex, colIndex, store) { }
        ,combo: function(v, metaData, record, rowIndex, colIndex, store) {
            if(Ext.isEmpty(v)) return '';
            ed = this.editor;
            ri = ed.store.findExact(ed.valueField, v);
            if(ri < 0) return '';
            return ed.store.getAt(ri).get(ed.displayField);
        }

        ,objectsField: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) {
                return '';
            }
            var r = [];
            store = null;
            v = toNumericArray(v);
            var cfg = grid.helperTree.getNode(record.get('id')).data.templateRecord.get('cfg');
            var source = (Ext.isEmpty(cfg.source))
                ? 'tree'
                : cfg.source;
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
                    cw = null;
                    if(grid && grid.findParentByType) {
                        cw = grid.refOwner || grid.findParentByType(CB.Objects);
                    }
                    if(!cw || !cw.objectsStore) return '';
                    store = cw.objectsStore;
            }
            switch(cfg.renderer){
                case 'listGreenIcons':
                    for(i=0; i < v.length; i++){
                        ri = store.findExact('id', parseInt(v[i], 10));
                        row = store.getAt(ri);
                        if(ri >-1) r.push('<li class="lh16 icon-padding icon-element">'+row.get('name')+'</li>');
                    }
                    return '<ul class="clean">'+r.join('')+'</ul>';
                case 'listObjIcons':
                    for(i=0; i < v.length; i++){
                        ri = store.findExact('id', parseInt(v[i], 10));
                        row = store.getAt(ri);
                        if(ri >-1) {
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
                    for(i=0; i < v.length; i++){
                        ri = store.findExact('id', parseInt(v[i], 10));
                        if(ri >-1) r.push(store.getAt(ri).get('name'));
                    }
                    return r.join(', ');
            }

        }
        ,languageCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) return '';
            ri = CB.DB.languages.findExact('id', parseInt(v, 10));
            if(ri < 0) return '';
            return CB.DB.languages.getAt(ri).get('name');
        }
        ,sexCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) return '';
            ri = CB.DB.sex.findExact('id', v);
            if(ri < 0) return '';
            return CB.DB.sex.getAt(ri).get('name');
        }
        ,shortDateFormatCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) return '';
            ri = CB.DB.shortDateFormats.findExact('id', v);
            if(ri < 0) return '';
            return CB.DB.shortDateFormats.getAt(ri).get('name');
        }
        ,thesauriCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) {
            if(Ext.isEmpty(v)) return '';
            var node = grid.helperTree.getNode(record.get('id'));
            var tr = node.data.templateRecord;
            var th = tr.get('cfg').thesauriId;
            if(th == 'dependent'){
                th = grid.helperTree.getParentValue(node, tr.get('pid'));
            }
            var ts = getThesauriStore(th);
            var ri = ts.findExact('id', parseInt(v, 10));
            if(ri < 0) return '';
            return ts.getAt(ri).get('name');
        }
        ,checkbox: function(v){
            if(v == 1) return L.yes;
            if(v == -1) return L.no;
            return '';
        }
        ,date: function(v){
            var rez = '';
            if(Ext.isEmpty(v)) {
                return rez;
            }
            rez = Ext.Date.format(Ext.isPrimitive(v) ? Ext.Date.parse(v.substr(0,10), 'Y-m-d') : v, App.dateFormat);
            return rez;
        }
        ,datetime: function(v){
            var rez = '';
            if(Ext.isEmpty(v)) {
                return rez;
            }

            rez = Ext.isPrimitive(v)
                ? date_ISO_to_local_date(v)
                : v;

            // var s = date_local_to_ISO_string(rez);
            var s = rez.toISOString();
            if(s.substr(-14) == 'T00:00:00.000Z') {
                rez = Ext.Date.clearTime(rez, true);
            }

            rez = Ext.Date.format(rez, App.dateFormat+' '+App.timeFormat);
            if(Ext.isEmpty(rez)) {
                return '';
            }
            if(rez.substr(-5) == '00:00') {
                rez = rez.substr(0,10);
            }

            return rez;
        }
        ,time: function(v){
            if(v && Ext.isPrimitive(v)) return v;
            t = '';
            if(!Ext.isEmpty(v.hours)){
                t = v.hours;
                switch(v.hours){
                    case 1: t = t + ' '+L.hour; break;
                    case 2:
                    case 3:
                    case 4: t = t + ' '+L.ofHour; break;
                    case 5: t = t + ' '+L.ofHours; break;
                }
            }
            if(!Ext.isEmpty(v.minutes)){
                t = t + ' ' + v.minutes;
                switch(v.minutes){
                    case 1: t = t + ' ' + L.minute; break;
                    case 2:
                    case 3:
                    case 4: t = t + ' ' + L.ofMinute; break;
                    case 5: t = t + ' ' + L.ofMinutes; break;
                }
            }
            return t;
        }
        ,filesize: function(v){
            if(isNaN(v) || Ext.isEmpty(v) || (v == '0') || (v <= 0)) {
                return '';
            }

            if(v <= 0) {
                return  '0 KB';
            }
            else if(v < 1024) return '1 KB';
            else if(v < 1024 * 1024) return (Math.round(v / 1024) + ' KB');
            else{
                n = v / (1024 * 1024);
                return (n.toFixed(2) + ' MB');
            }
        }
        ,tags: function(v, m, r, ri, ci, s){
            if(Ext.isEmpty(v)) return '';
            rez = [];
            Ext.each(v, function(i){rez.push(i.name);}, this);
            rez = rez.join(', ');
            m.attr = 'name="' + rez.replace(/"/g, '&quot;') + '"';
            return rez;
        }
        ,tagIds: function(v){
            if(Ext.isEmpty(v)) return '';
            rez = [];
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
            if(Ext.isEmpty(v)) return '';
            return CB.DB.importance.getName(v);
        }
        ,timeUnits: function(v){
            if(Ext.isEmpty(v)) return '';
            return CB.DB.timeUnits.getName(v);
        }
        ,taskStatus: function(v, m, r, ri, ci, s){
            if(Ext.isEmpty(v)) return '';
            return '<span class="taskStatus'+v+'">'+L['taskStatus'+parseInt(v, 10)]+'</span>';
        }
        ,text: function(v, m, r, ri, ci, s){
            if(Ext.isEmpty(v)) {
                return '';
            }
            return '<pre style="white-space: pre-wrap">' + v + '</pre>';
        }
        ,titleAttribute: function(v, m, r, ri, ci, s){
            m.attr = Ext.isEmpty(v) ? '' : 'title="'+Ext.util.Format.stripTags(v).replace(/"/g,"&quot;")+'"';
            return v;
        }
        ,userName: function(v){ return CB.DB.usersStore.getName(v);}
        ,iconcombo: function(v){
            if(Ext.isEmpty(v)) {
                return '';
            }
            return '<img src="css/i/s.gif" class="icon '+v+'" /> '+v;
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

        idx = CB.DB.templates.findExact('id', template_id);
        if(idx >= 0){
            r = CB.DB.templates.getAt(idx);
            it = r.get('info_template');
            if(!Ext.isEmpty(it)){
                App.templatesXTemplate[template_id] = new Ext.XTemplate(it);
                App.templatesXTemplate[template_id].compile();
                return App.templatesXTemplate[template_id];
            }
        }

        return App.xtemplates.object;
    };

    App.findTab = function(tabPanel, id, xtype){
        tabIdx = -1;
        if(Ext.isEmpty(id)) return tabIdx;
        i= 0;
        while((tabIdx == -1) && (i < tabPanel.items.getCount())){
            o = tabPanel.items.get(i);
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
        return tabIdx;
    };

    App.findTabByType = function(tabPanel, type){
        tabIdx = -1;
        if(Ext.isEmpty(type)) {
            return tabIdx;
        }

        i= 0;
        while((tabIdx == -1) && (i < tabPanel.items.getCount())){
            o = tabPanel.items.get(i);
            if(Ext.isDefined(o.isXType) && o.isXType(type)) {
                tabIdx = i;
            }
            i++;
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

        c = tabPanel.add(o);
        o.show();
        tabPanel.setActiveTab(c);

        return c;
    };

    App.getTextEditWindow = function(config){
        if(!App.textEditWindow) App.textEditWindow = new CB.TextEditWindow();
        App.textEditWindow = Ext.apply(App.textEditWindow, config);
        return App.textEditWindow;
    };

    App.getHtmlEditWindow = function(config){
        if(!App.htmlEditWindow) App.htmlEditWindow = new CB.HtmlEditWindow();
        App.htmlEditWindow = Ext.apply(App.htmlEditWindow, config);
        return App.htmlEditWindow;
    };

    App.openObjectWindow = function(config) {
        //at least template should be defined in config
        if(Ext.isEmpty(config)) {
            return;
        }

        var wndCfg = {
            xtype: 'CBObjectEditWindow'
            ,data: config
        };

        wndCfg.id = 'oew-' +
            (Ext.isEmpty(config.id)
                ? Ext.id()
                : config.id
            );

        w = App.openWindow(wndCfg);

        if(!w.existing) {
            App.alignWindowNext(w);
        }

        delete w.existing;
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
        // pos[1] -= 30; //above toolbar
        pos[1] -= 5;

        //position to the left of an active window if any
        var x = pos[0];
        App.mainStatusBar.windowBar.items.each(
            function(btn) {
                if(btn.win && (btn.win != w) && btn.win.isVisible() && !btn.win.maximized) {
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
        if(Ext.isEmpty(App.config.webdav_url)) {
            return;
        }
        var url = App.config.webdav_url;
        url = url.replace('{node_id}', Ext.valueFrom(data.id, data.nid));
        url = url.replace('{name}', data.name);
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
        }
    };

    /**
    * open path on active explorer tabsheet or in default eplorer tabsheet
    *
    * this function will not reset explorer navigation params (filters, search query, descendants)
    */
    App.openPath = function(path, params){
        if(Ext.isEmpty(path)) {
            path = '/';
        }
        params = Ext.valueFrom(params, {});
        params.path = path;
        params.query = null;
        params.start = 0;
        params.page = 1;

        App.activateBrowserTab().setParams(params);
    };

    App.activateBrowserTab = function(){
        var tab = App.mainTabPanel.getActiveTab();

        if(tab.isXType('CBBrowserViewContainer')) {
            return tab;
        }
        App.mainTabPanel.setActiveTab(App.explorer);
        return App.explorer;
    };

    App.locateObject = function(object_id, path){
        if(path === undefined){
            CB_Path.getPidPath(object_id, function(r, e){
                if(r.success !== true) return ;
                App.locateObject(r.id, r.path);
            });
            return;
        }

        App.locateObjectId = parseInt(object_id, 10);

        params = {
            descendants: false
            ,query: ''
            ,filters: {}
        };
        App.openPath(path, params);
    };

    App.downloadFile = function(fileId, zipped, versionId){
        if(Ext.isElement(fileId)){
            //retreive id from html element
            fileId = fileId.id;
            zipped = false;
        }
        url = '/' + App.config.coreName + '/download.php?id='+fileId;
        if(!Ext.isEmpty(versionId)) url += '&v='+versionId;
        if(zipped) {
            url += '&z=1';
        }
        window.open(url, '_blank');
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
            ,grid: e.grid
            ,pidValue: e.pidValue
            ,objectId: e.objectId
            ,path: e.path
        };
        var w, th, ed;
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
        switch(type){
            case '_auto_title':
                return new Ext.ux.TitleField(editorCfg);
            case '_objects':
                //e should contain all necessary info
                switch(cfg.editor){
                    case 'form':
                        if(e && e.grid){
                            e.cancel = true;
                            e.value = e.record.get('value');

                            var formEditor = new CB.objects.field.editor.Form({
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
                                            Ext.each(
                                                editor.selectedRecordsData
                                                ,function(d){
                                                    objStore.checkRecordExistance(d);
                                                }
                                                ,this
                                            );
                                        }

                                        this.originalValue = this.value;
                                        this.value = editor.getValue().join(',');

                                        this.record.set('value', this.value);

                                        if(this.grid.onAfterEditProperty) {
                                            this.grid.onAfterEditProperty(editor, this);
                                        } else {
                                            this.grid.fireEvent('change', this);
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
                            return new CB.ObjectsTriggerField({
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

                            this._setValue(v.join(' '));
                        };

                        ed.getValue = function() {
                            var value = this._getValue();
                            value = Ext.util.Format.trim(String(value).replace(/[\n\r]/g, ' '));

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
                        ed = new CB.objects.field.editor.Tag({
                            // enableKeyEvents: true
                            data: objData
                            ,valueField: 'id'
                            ,displayField: 'name'
                            ,forceSelection: true
                            ,queryMode: 'remote'
                            ,autoLoadOnValue: true
                            ,multiSelect: true
                            // ,hideTrigger: true
                            ,stacked: true
                            // ,checkChange: Ext.emptyFn
                            ,pinList: false
                            ,filterPickList: true
                        });

                        return ed;
                        // break;
                    default:
                        return new CB.ObjectsComboField({
                            enableKeyEvents: true
                            ,data: objData
                        });
                }

                break;
            case 'checkbox': return new Ext.form.ComboBox({
                        enableKeyEvents: true
                        // ,forceSelection: true
                        ,triggerAction: 'all'
                        // ,lazyRender: true
                        ,queryMode: 'local'
                        ,editable: false
                        ,store: CB.DB.yesno
                        ,displayField: 'name'
                        ,valueField: 'id'
                    });
            case 'timeunits': return new Ext.form.ComboBox({
                        enableKeyEvents: true
                        ,forceSelection: true
                        ,triggerAction: 'all'
                        ,lazyRender: true
                        ,queryMode: 'local'
                        ,editable: false
                        ,store: CB.DB.timeUnits
                        ,displayField: 'name'
                        ,valueField: 'id'
                    });
            case 'importance': return new Ext.form.ComboBox({
                        enableKeyEvents: true
                        ,forceSelection: true
                        ,triggerAction: 'all'
                        ,lazyRender: true
                        ,queryMode: 'local'
                        ,editable: false
                        ,store: CB.DB.importance
                        ,displayField: 'name'
                        ,valueField: 'id'
                    });
            case 'date':
                return new Ext.form.DateField({
                    enableKeyEvents: true
                    ,format: App.dateFormat
                    ,width: 100
                });
            case 'datetime':
                return new Ext.form.DateField({
                    enableKeyEvents: true
                    ,format: App.dateFormat+' ' + App.timeFormat
                    ,width: 130
                });
            case 'time':
                return new Ext.form.TimeField({
                    enableKeyEvents: true
                    ,format: App.timeFormat
                });
            case 'int':
                return new Ext.form.NumberField({
                    enableKeyEvents: true
                    ,allowDecimals: false
                    ,width: 90
                });
            case 'float':
                return new Ext.form.NumberField({
                    enableKeyEvents: true
                    ,allowDecimals: true
                    ,width: 90
                });
            case 'combo':
                th = cfg.thesauriId;
                if(th == 'dependent'){
                    th = e.pidValue;
                }
                return new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: getThesauriStore(th)
                    ,displayField: 'name'
                    ,valueField: 'id'
                });
            case 'iconcombo':
                th = cfg.thesauriId;
                if(th == 'dependent'){
                    th = e.pidValue;
                }
                return new Ext.form.ComboBox({
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
                    // ,plugins: [new Ext.ux.plugins.IconCombo()]
                });
            case '_language':
                return new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.languages
                    ,displayField: 'name'
                    ,valueField: 'id'
                });
            case '_sex':
                return new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.sex
                    ,displayField: 'name'
                    ,valueField: 'id'
                });
            case '_templateTypesCombo':
                return new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.templateTypes
                    ,displayField: 'name'
                    ,valueField: 'id'
                });
            case '_fieldTypesCombo':
                return new Ext.form.ComboBox({
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
                });
            case '_short_date_format':
                return new Ext.form.ComboBox({
                    enableKeyEvents: true
                    ,forceSelection: true
                    ,typeAhead: true
                    ,triggerAction: 'all'
                    ,lazyRender: true
                    ,queryMode: 'local'
                    ,store: CB.DB.shortDateFormats
                    ,displayField: 'name'
                    ,valueField: 'id'
                });
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
                return new Ext.form.TextArea({
                    enableKeyEvents: true
                    ,height: height
                });
            case 'text':
                e.cancel = true;
                w = App.getTextEditWindow({
                    title: tr.get('title')
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
                            // this.grid.fireEvent('change');
                        }
                    }
                });
                w.on('hide', e.grid.gainFocus, e.grid);
                w.show();
                // return w;
                break;
            case 'html':
                e.cancel = true;
                w = App.getHtmlEditWindow({
                    title: tr.get('title')
                    ,data: {
                        value: e.record.get('value')
                        ,scope: e
                        ,callback: function(w, v){
                            this.originalValue = this.record.get('value');
                            this.value = v;
                            this.record.set('value', v);
                            if(this.grid.onAfterEditProperty) this.grid.onAfterEditProperty(this);
                            this.grid.fireEvent('change');
                        }
                    }
                });
                if(!Ext.isEmpty(e.grid)) w.on('hide', e.grid.gainFocus, e.grid);
                w.show();
                return w;
                break;
            default:
                return new Ext.form.TextField({
                    enableKeyEvents: true
                });
        }
        // return false;
    };

    App.focusFirstField = function(scope){
        return;
        scope = Ext.valueFrom(scope, this);
        f = function(){
            var a = [];

            if(scope.find) {
                a = scope.find('isFormField', true);
            }

            if(a.length < 1) {
                return;
            }

            var found = false;
            var i = 0;
            while( !found && (i<a.length) ){
                found = ( !Ext.isEmpty(a[i]) && !Ext.isEmpty(a[i].isXType) && !a[i].isXType('radiogroup') && !a[i].isXType('displayfield') && (a[i].hidden !== true) );
                i++;
            }
            if(!found) return;
            c = a[i-1];
            if(c.isXType('fieldcontainer'))  c = c.items.first();
            c.focus();
        };

        Ext.Function.defer(f, 500, scope);
    };

    App.successResponse = function(r){
        if(r.success === true) {
            return true;
        }
        Ext.Msg.alert(L.Error, Ext.valueFrom(r.msg, L.ErrorOccured));
        return false;
    };

    App.showTestingWindow =function(){
        if(!App.testWindow) App.testWindow = new CB.TestingWindow({ closeAction: 'hide' });
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

        Ext.Msg.alert(L.Error, msg);

        dhf = function(){
            delete App.hideFailureAlerts;
        };
        Ext.Function.defer(dhf, 1500);
    };

    App.openObject = function(template_id, id, e){
        switch( CB.DB.templates.getType(template_id) ){
            case 'case':
            case 'object':
            case 'template':
            case 'field':
            case 'email':
            case 'task':
                App.mainViewPort.fireEvent('openobject', {id: id, template_id: template_id}, e);
                break;
            case 'file':
                App.mainViewPort.fireEvent('fileopen', {id: id}, e);
                break;
            default:
                return false;
        }
        return true;
    };

    App.clipboard = new CB.Clipboard();

    /* disable back button */
    var o = Ext.isIE ? document : window;
    o.onkeydown = function(e, t) {
        if(Ext.isEmpty(t)) {
            t = e.target;
        }
        if ((e.keyCode == Ext.event.Event.BACKSPACE) &&
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
            Ext.Msg.alert(L.Info, 'This browser does not support file uploading from desktop');
        }
    };

    App.onComponentActivated = function(component){
        plog('component activated', arguments, this);
    };

}

window.onbeforeunload = function() {
    if(App.confirmLeave === false) {
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
    if(!window.dragFromWindow && ( (e.pageX == '0') && (e.pageY == '0') ) ){
        App.fireEvent('dragfilesleave', e);
    }
    return false;
};
window.ondragend = function(e){
    delete window.dragFromWindow;
};
