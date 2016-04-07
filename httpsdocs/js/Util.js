// JavaScript Document
function isEmptyObject(ob){
    for(var i in ob){ if(ob.hasOwnProperty(i)){return false;}}
    return true;
}

/**
 * create date object from iso string
 * @param  varchar date_string [description]
 * @return Date | null
 */
function date_ISO_to_date(date_string){
    if(Ext.isEmpty(date_string)) {
        return null;
    }

    if(Ext.isDate(date_string)) {
        return date_string;
    }

    var d = Date.parse(date_string);
    if(Ext.isEmpty(d)) {
        return null;
    }

    return new Date(d);
}

function date_ISO_to_local_date(date_string){
    var d = date_ISO_to_date(date_string);

    if(Ext.isEmpty(d)) {
        return null;
    }

    if(!isNaN(App.loginData.cfg.gmt_offset)) {
        var localOffset = -d.getTimezoneOffset();
        var userOffset = App.loginData.cfg.gmt_offset;

        if(localOffset != userOffset) {
            // decrease date with local offset and encrease with user offset
            d = Ext.Date.add(d, Ext.Date.MINUTE, -localOffset + userOffset);
        }
    }

    return d;
}

function date_local_to_ISO_string(date) {
    if(!Ext.isDate(date)) {
        return null;
    }

    if(!isNaN(App.loginData.cfg.gmt_offset)) {
        var localOffset = - date.getTimezoneOffset();
        var userOffset = Ext.Number.from(App.loginData.cfg.gmt_offset, 0);

        if(localOffset != userOffset) {
            // decrease date with user offset and encrease with local offset
            date = Ext.Date.add(date, Ext.Date.MINUTE, localOffset - userOffset);
        }
    }

    return date.toISOString();
}

function getUserDisplayName(withEmail) {
    var rez = App.loginData.first_name + ' ' + App.loginData.last_name;

    rez = rez.trim();

    if (Ext.isEmpty(rez)) {
        rez = App.loginData.name;
    }

    if ((withEmail === true) && (!Ext.isEmpty(App.loginData.email))) {
        rez += "\n(" + App.loginData.email + ")";
    }

    return rez;
}

function displayDateTime(date, format){
    var d = date_ISO_to_local_date(date);
    if(Ext.isDate(d)) {
        format = Ext.valueFrom(format, App.longDateFormat + ' ' + App.timeFormat);
        return Ext.Date.format(d, format);
    }
    return '';
}
/**
 * Convert a date to a date string with time filled with 0
 * // 2014-02-17T00:00:00Z
 * @param  date
 * @return varchar
 */
function dateToDateString(date) {
    var rez = null;
    if(Ext.isPrimitive(date)) {
        rez = date;
    } else if(Ext.isDate(date)) {
        rez = Ext.Date.format(date, 'Y-m-d') + 'T00:00:00Z';
    }
    return rez;
}

function getItemIcon(d){
    var rez = Ext.valueFrom(d.iconCls, '');

    if(!rez && Ext.isEmpty(d.template_id) && d['type'] == 2){
        rez = 'icon-shortcut';
    }

    if (Ext.isEmpty(rez)) {
        rez = CB.DB.templates.getIcon(d.template_id);

        var type = CB.DB.templates.getType(d.template_id);

        switch(type){
            case 'file':
                rez = getFileIcon(d['name']);
                break;

            case 'task':
                if(d['task_status'] == 3) {
                    rez = 'icon-task-completed';
                }
        }
    }

    return rez;
}

/**
 * detect the editor group used for given filename from App.config['files.edit']
 * @param  varchar filename
 * @return varchar | false
 */
function detectFileEditor(filename) {
    var rez = false;

    if(Ext.isEmpty(App.config['files.edit'])) {
        return rez;
    }

    var extension = getFileExtension(filename);

    Ext.iterate(
        App.config['files.edit']
        ,function(k, v, o) {
            if(v.indexOf(extension) > -1) {
                rez = k;
                return false;
            }
        }
        ,this
    );

    return rez;
}

function getFileExtension(filename)
{
    var ext = String(filename).split('.');
    if (ext.length < 2) {
        return '';
    }
    ext = ext.pop();
    ext = ext.trim();

    return ext.toLowerCase();
}

function getFileIcon(filename){
    if(Ext.isEmpty(filename)) {
        return 'file-';
    }

    var a = String(filename).split('.');

    if(a.length <2 ) {
        return 'file-';
    }

    return 'file- file-'+ Ext.util.Format.lowercase(a.pop());
}

function getFileIcon32(filename){
    if(Ext.isEmpty(filename)) {
        return 'file-unknown32';
    }

    var a = String(filename).split('.');

    if(a.length <2 ) {
        return 'file-unknown32';
    }

    return 'file-unknown32 file-'+ Ext.util.Format.lowercase(a.pop())+'32';
}

function getStoreTitles(v){
    if(Ext.isEmpty(v)) {
        return '';
    }
    var ids = String(v).split(',')
        ,texts = [];

    Ext.each(
        ids
        ,function(id){
            var r = this.findRecord('id', parseInt(id, 10), 0, false, false, true);
            if(r) {
                texts.push(Ext.valueFrom(r.data.title, r.data.name));
            }
        }
        ,this
    );

    return texts.join(',');
}

function getStoreNames(v){
    if(Ext.isEmpty(v)) {
        return '';
    }

    var ids = String(v).split(',')
        ,texts = [];

    Ext.each(
        ids
        ,function(id){
            var idx = this.findExact('id', parseInt(id, 10));

            if(idx < 0) {
                idx = this.findExact('id', String(id));
            }

            if(idx >= 0) {
                var d = this.getAt(idx).data;
                texts.push(d.name);
            }
        }
        ,this
    );

    return texts.join(',');
}

function toNumericArray(v, delimiter){
    if (Ext.isEmpty(v)) {
        return [];
    }

    if(Ext.isEmpty(delimiter)) {
        delimiter = ',';
    }

    if (!Ext.isArray(v)) {
        v = String(v).split(delimiter);
    }

    var rez = [];

    for (var i = 0; i < v.length; i++) {
        var w = String(v[i]).trim()
            ,iw = parseInt(w, 10);

        if (iw == w) {
            rez.push(iw);
        } else if(!isNaN(iw)){
            rez.push(parseFloat(w));
        }
    }

    return rez;
}

setsGetIntersection = function(set1, set2){
    var i, rez = [];
    if(Ext.isEmpty(set1) || Ext.isEmpty(set2)) {
        return rez;
    }

    if(!Ext.isArray(set1)) {
        set1 = String(set1).split(',');
    }

    if(!Ext.isArray(set2)) {
        set2 = String(set2).split(',');
    }

    for (i = 0; i < set1.length; i++) {
        set1[i] = String(set1[i]);
    }

    for (i = 0; i < set2.length; i++) {
        set2[i] = String(set2[i]);
    }

    for (i = 0; i < set1.length; i++) {
        if( (set2.indexOf(set1[i]) >= 0) && (rez.indexOf(set1[i]) < 0 )) {
            rez.push(set1[i]);
        }
    }

    for (i = 0; i < set2.length; i++) {
        if( (set1.indexOf(set2[i]) >= 0) && (rez.indexOf(set2[i]) < 0 )) {
            rez.push(set2[i]);
        }
    }

    return rez;
};

setsHaveIntersection = function(set1, set2){
    return !Ext.isEmpty(setsGetIntersection(set1, set2));
};

function getMenuUserItems(handler, scope, excludeId){
    var rez = [];
    excludeId = parseInt(excludeId, 10);

    CB.DB.usersStore.each(
        function(u) {
            var d = u.data;
            if(d.id !== excludeId) {
                rez.push({
                    text: d.name
                    ,iconCls: d.iconCls
                    ,userId: d.id
                    ,handler: handler
                    ,scope: scope
                });
            }
        }
        ,this
    );

    return rez;
}

function updateMenu(menuButton, menuConfig, handler, scope){
    if(Ext.isEmpty(menuButton)) {
        return;
    }

    menuButton.menu.removeAll();
    menuConfig = String(menuConfig).split(',');

    var menu = [];

    for (var i = 0; i < menuConfig.length; i++) {
        switch (menuConfig[i]) {
            case 'case': break;
            case 'task': break;
            case 'event': break;
            case 'folder': break;

            case '-': //obsolete for upgraded menu model
                menu.push('-');
                break;

            default:
                var idx = CB.DB.templates.findExact('id', parseInt(menuConfig[i], 10));
                if(idx >= 0){
                    var tr = CB.DB.templates.getAt(idx)
                        ,title = Ext.valueFrom(tr.get('title'), tr.get('name'));

                    if(['-', '- Menu separator -'].indexOf(title) >= 0) {
                        menu.push('-');
                    } else {
                        var data = {
                                template_id: tr.get('id')
                                // ,type: tr.get('type')
                                ,title: title
                        };

                        if(!Ext.isEmpty(tr.get('cfg').data)) {
                            Ext.apply(data, tr.get('cfg').data);
                        }

                        menu.push({
                            text: title
                            ,iconCls: tr.get('iconCls')
                            ,scope: scope
                            ,handler: handler
                            ,data: data
                        });
                    }
                }
            break;

        }
    }

    for(i = 0; i < menu.length; i++) {
        menuButton.menu.add(menu[i]);
    }
}

/**
 * equivalent function to php html_entity_decode
 * @param  varchar str
 * @return varchar
 */
function htmlEntityDecode(str){
    if(Ext.isEmpty(str)) {
        return '';
    }

    if(!document.hedTA) {
        document.hedTA = document.createElement("textarea");
    }

    var ta = document.hedTA;

    ta.innerHTML = str.replace(/</g,"&lt;").replace(/>/g,"&gt;");

    return ta.value;
}
