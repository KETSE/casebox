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
            d = d.add(Date.MINUTE, -localOffset + userOffset);
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
        var userOffset = Ext.num(App.loginData.cfg.gmt_offset, 0);

        if(localOffset != userOffset) {
            // decrease date with user offset and encrease with local offset
            date = date.add(Date.MINUTE, localOffset - userOffset);
        }
    }

    return date.toISOString();
}

function getUserDisplayName(withEmail) {
    var rez = App.loginData.first_name + ' ' + App.loginData.last_name;
    rez = rez.trim();
    if (Ext.isEmpty(rez)) {
        rez = App.loginData.rez;
    }
    if ((withEmail === true) && (!Ext.isEmpty(App.loginData.email))) {
        rez += "\n(" + App.loginData.email + ")";
    }
    return rez;
}

function displayDateTime(date){
    var d = date_ISO_to_local_date(date);
    if(Ext.isDate(d)) {
        return d.format(App.longDateFormat + ' ' + App.timeFormat);
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
    rez = null;
    if(Ext.isPrimitive(date)) {
        rez = date;
    } else if(Ext.isDate(date)) {
        rez = date.format('Y-m-d') + 'T00:00:00Z';
    }
    return rez;
}

function getItemIcon(d){

    if(!Ext.isEmpty(d.iconCls)) {
        return d.iconCls;
    }
    if(Ext.isEmpty(d.template_id)){
        if(d['type'] == 2) return 'icon-shortcut';
        return d.iconCls;
    }
    switch( CB.DB.templates.getType(d.template_id) ){
        case 'file':
            return getFileIcon(d['name']);
        case 'task':
            if(d['task_status'] == 3) {
                return 'icon-task-completed';
            }

        default:
            tr = CB.DB.templates.getById(d.template_id);
            if(tr) return tr.get('iconCls');
            return d.iconCls;
    }

}
function getFileIcon(filename){
    if(Ext.isEmpty(filename)) return 'file-';
    a = String(filename).split('.');
    if(a.length <2 ) return 'file-';
    return 'file- file-'+ Ext.util.Format.lowercase(a.pop());
}
function getVersionsIcon(versionsCount){
    if(isNaN(versionsCount)) return '';
    if(versionsCount > 20) return 'vc21';
    return 'vc'+versionsCount;
}
function getFileIcon32(filename){
    if(Ext.isEmpty(filename)) return 'file-unknown32';
    a = String(filename).split('.');
    if(a.length <2 ) return 'file-unknown32';
    return 'file-unknown32 file-'+ Ext.util.Format.lowercase(a.pop())+'32';
}

function getStoreTitles(v){
    if(Ext.isEmpty(v)) return '';
    ids = String(v).split(',');
    texts = [];
    Ext.each(ids, function(id){
         idx = this.findExact('id', parseInt(id, 10));
        if(idx >= 0) texts.push(this.getAt(idx).get('title'));
    }, this);
    return texts.join(',');
}
function getStoreNames(v){
    if(Ext.isEmpty(v)) {
        return '';
    }
    var ids = String(v).split(',');
    var texts = [];
    Ext.each(
        ids
        ,function(id){
            var idx = this.findExact('id', parseInt(id, 10));
            if(idx >= 0) {
                var d = this.getAt(idx).data;
                texts.push(d.name);
            }
        }
        ,this
    );

    return texts.join(',');
}

function toNumericArray(v){
    if (Ext.isEmpty(v)) {
        return [];
    }
    if (!Ext.isArray(v)) {
        v = String(v).split(',');
    }

    for (var i = v.length - 1; i >= 0; i--) {
        w = String(v[i]).trim();
        iw = parseInt(w, 10);
        if (iw == w) {
            v[i] = iw;
        } else {
            v[i] = parseFloat(w);
        }
    }
    return v;
}

setsGetIntersection = function(set1, set2){
    var i, rez = [];
    if(Ext.isEmpty(set1) || Ext.isEmpty(set2)) return rez;
    if(Ext.isPrimitive(set1)) set1 = String(set1).split(',');
    if(Ext.isPrimitive(set2)) set2 = String(set2).split(',');
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

function updateMenu(menuButton, menuConfig, handler, scope){
    if(Ext.isEmpty(menuButton) || Ext.isEmpty(menuConfig)) return;
    menuButton.menu.removeAll();
    menuConfig = String(menuConfig).split(',');
    menu = [];
    for (var i = 0; i < menuConfig.length; i++)
        switch(menuConfig[i]){
            case 'case': break;
            case 'task': break;
            case 'event': break;
            case 'folder': break;
            case '-':
                menu.push('-');
                break;
            default:
                idx = CB.DB.templates.findExact('id', parseInt(menuConfig[i], 10));
                if(idx >= 0){
                    tr = CB.DB.templates.getAt(idx);
                    data = {
                            template_id: tr.get('id')
                            // ,type: tr.get('type')
                            ,title: tr.get('title')
                    };
                    if(!Ext.isEmpty(tr.get('cfg').data)) Ext.apply(data, tr.get('cfg').data);
                    menu.push({
                        text: tr.get('title')
                        ,iconCls: tr.get('iconCls')
                        ,scope: scope
                        ,handler: handler
                        ,data: data
                    });

                }
            break;

        }

    for(i = 0; i < menu.length; i++) menuButton.menu.add(menu[i]);
}

/**
 * get the menu config with highest weight for given params
 *
 * weight is higher if more specified (non empty) conditions are satisfied
 *
 * Example: node_id = 4, ids_path = /1/4, node_template_id = 42
 *     this params could satisfy more than one rule:
 *         node_ids     node_template_ids   user_group_ids
 *         null         42                  null
 *         4            40,41,42            1
 *     Second rule has higher weight because it satisfies input params
 *     by 3 non empty criteryas: node_ids, node_template_ids and user_group_ids
 *     The first rule
 * @param  int node_id          selected node id
 * @param  varchar ids_path
 * @param  int node_template_id
 * @return varchar
 */
function getMenuConfig(node_id, ids_path, node_template_id){
    var lastWeight = 0;
    var menuConfig = '';
    CB.DB.menu.each( function(r){
        var weight = 0;

        /*check user_group ids
          firstly select only rules that are available for current user id (user_group_ids containt current user id or group id)
        */
        var ug_ids = ',' + String(Ext.value(r.get('user_group_ids'), '') ).replace(' ','') + ',';

        if (ug_ids.indexOf(','+App.loginData.id+',') >=0) {
            weight += 100;
        } else {
            if( ( ug_ids != ',,' ) && ( !setsHaveIntersection(ug_ids, App.loginData.groups ) ) ) return;
            weight += 50;
        }
        /*end of check user_group ids */

        /* check template_ids
           select only rules inth empty node_template_ids or that contain current selected node_template_id
        /**/
        if(!Ext.isEmpty(node_template_id)){
            nt_ids = ',' + String( Ext.value(r.get('node_template_ids'), '') ).replace(' ','') + ',';
            if(nt_ids.indexOf(','+node_template_id+',') >=0) weight += 100;
            else{
                if( nt_ids != ',,') return;
                weight += 50;
            }
        }else {
            if(!Ext.isEmpty(r.get('node_template_ids'))){
                return;
            }
        }

        var n_ids = ',' + String( Ext.value(r.get('node_ids'), '') ).replace(' ','') + ',';
        if( n_ids.indexOf(','+node_id+',') >= 0 ) {
            weight += 100;
        }else{
            if( n_ids == ',,') {
                weight += 50;
            }else{ /*check the nearest parents from path */
                ids = String(ids_path).split('/');
                for (var i = ids.length -1; i > 0; i--) {
                    if(n_ids.indexOf(','+ids[i]+',') >=0){
                        weight += 51 + i;
                        if(weight >= lastWeight){
                            lastWeight = weight;
                            menuConfig = r.get('menu');
                            return;
                        }
                        i = -1;
                    }
                }
                return;
            }
        }

        if(weight >= lastWeight){
            lastWeight = weight;
            menuConfig = r.get('menu');
        }
    }, this);

    return menuConfig;
}
