<?php
namespace CB;

use CB\CONFIG as CONFIG;

header('content-type: text/javascript; charset=utf-8');
require_once '../init.php';
DB\connect();

?>
Ext.namespace('CB.DB');

    CB.DB.yesno = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [[0, ' '], [-1, L.no], [1, L.yes]]
    });
    CB.DB.sex = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: ['id', 'name']
        ,data:  [[null, '-'], ['m', L.male], ['f', L.female]]
    });
    CB.DB.templateTypes = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: ['id', 'name']
        ,data:  [[null, '-'], ['case', 'case'], ['object', 'object'], ['file', 'file'], ['task', 'task'], ['email', 'email'], ['user', 'user']]
        ,getName: function(id){ idx = this.findExact('id', parseInt(id)); return (idx >=0 ) ? this.getAt(idx).get('name') : ''; }
    });
    CB.DB.reminderTypes = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name', 'iconCls']
        ,data:  [[1, L.byMail, 'icon-mail'], [2, L.bySystem, 'icon-bell']]
    });
    CB.DB.reminderUnits = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [[1, L.ofMinutes], [2, L.ofHours], [3, L.ofDays], [4, L.ofWeeks]]
        ,getName: function(id){ idx = this.findExact('id', parseInt(id)); return (idx >=0 ) ? this.getAt(idx).get('name') : ''; }
    });
    CB.DB.shortDateFormats = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: ['id', 'name']
        ,data:  [['%m/%d/%Y', 'm/d/Y'], ['%d/%m/%Y', 'd/m/Y'], ['%d.%m.%Y', 'd.m.Y'], ['%d-%m-%Y', 'd-m-Y']]
    });
    CB.DB.roles = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [<?php echo '[1, "'.L\Administrator.'"], [2, "'.L\Manager.'"], [3, "'.L\Lawyer.'"], [4, "'.L\User.'"]'; ?>]
    });
    CB.DB.objectTypes = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name', 'iconCls']
        ,data:  [[1, L.Folder, 'icon-folder'], [2, L.Link, 'icon-link'], [3, L.Case, 'icon-briefcase'], [4, L.Action, 'icon-action'], [5, L.File, 'icon-file-unknown'], [6, L.Task, 'icon-calendar-task'], [7, L.Event, 'icon-event'], [8, L.Email, 'icon-letter']]
        ,getName: function(id){ idx = this.findExact('id', parseInt(id)); return (idx >=0 ) ? this.getAt(idx).get('name') : ''; }
    });
    CB.DB.tasksImportance = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [ [1, L.Low], [2, L.Medium], [3, L.High] ]
        ,getName: function(id){ idx = this.findExact('id', parseInt(id)); return (idx >=0 ) ? this.getAt(idx).get('name') : ''; }
    })
    CB.DB.phone_codes = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [ 'code', 'name']
        ,data:  []
        ,
    });

<?php

$data = array();
if (defined('CB\\CONFIG\\TEMPLATEICONS')) {
    $data = explode(',', CONFIG\TEMPLATEICONS);
    $data = implode("\n", $data);
    $data = str_replace("\r\n", "\n", $data);
    $data = explode("\n", $data);
    for ($i = 0; $i < sizeof($data); $i++) {
        $data[$i] = array($data[$i], $data[$i]);
    }
}
echo 'CB.DB.templatesIconSet = new Ext.data.ArrayStore({ idIndex: 0,fields: ["id","name"], data: '. json_encode($data).'});';

/* languages */
$arr = array();
for ($i=0; $i < sizeof($GLOBALS['languages']); $i++) {
    $lang = &$GLOBALS['language_settings'][$GLOBALS['languages'][$i]];
    $lp = array($i+1, $GLOBALS['languages'][$i], $lang['name'], $lang['long_date_format'], $lang['short_date_format'], $lang['time_format'] );
    for ($j=0; $j < sizeof($lp); $j++) {
        $lp[$j] = str_replace(array('%', '\/'), array('', '/'), $lp[$j]);
    }
    $arr[] = $lp;
}

echo "\n".'CB.DB.languages = new Ext.data.ArrayStore({'.
    'fields: [{name: "id", type: "int"}, "abreviation", "name", "long_date_format", "short_date_format", "time_format"]'.
    ',data: '.(empty($arr) ? '[]' : json_encode($arr)).
    '});'."\n";
/* end of languages */

/* Security questions */
$arr = array();
for ($i=0; $i < 10; $i++) {
    if (defined('CB\\L\\SecurityQuestion'.$i)) {
        $arr[] = array($i, constant('CB\\L\\'.'SecurityQuestion'.$i));
    }
}
if (defined('CB\\L\\OwnSecurityQuestion')) {
    $arr[] = array( -1 , constant('CB\\L\\'.'OwnSecurityQuestion') );
}
echo "\n".'CB.DB.securityQuestions = new Ext.data.ArrayStore({'.
    'fields: [{name: "id", type: "int"}, "text"]'.
    ',data: '.(empty($arr) ? '[]' : json_encode($arr)).
    '});'."\n";
/* end of Security questions */

/* menu */
$arr = array();
$res = DB\dbQuery('select * from menu') or die( DB\dbQueryError() );
while ($r = $res->fetch_row()) {
    $intersection = array_intersect(
        explode(',', $r[4]),
        array_merge(
            $_SESSION['user']['groups'],
            array($_SESSION['user']['id'])
        )
    );
    if (empty($r[4]) || !empty( $intersection )) {
        $arr[] = $r;
    }
}
$res->close();

echo "\n".'CB.DB.menu = new Ext.data.ArrayStore({'.
    'fields: [{name: "id", type: "int"}, "node_ids", "node_template_ids", "menu", "user_group_ids"]'.
    ',data: '.(empty($arr) ? '[]' : json_encode($arr)).
    '});'."\n";
/* end of menu */

/* templates */
$sql = 'SELECT ts.id, ts.pid, t.id template_id, ts.tag, ts.`level`, ts.`name`, ts.l'.USER_LANGUAGE_INDEX.' `title`, ts.`type`, ts.`order`, ts.cfg'.
        ', (coalesce(t.title_template, \'\') <> \'\' ) `has_title_template`'.
        ' FROM templates t left join templates_structure ts on t.id = ts.template_id ORDER BY template_id, level, `order`';
$res = DB\dbQuery($sql, $_SESSION['user']['language_id']) or die( DB\dbQueryError() );

$templates = array();
while ($r = $res->fetch_assoc()) {
    $t = $r['template_id'];
    unset($r['template_id']);
    if (($r['type'] == '_auto_title') && ($r['has_title_template'] == 0)) {
        $r['type'] = 'varchar';
    }
    unset($r['has_title_template']);
    if (!empty($r['cfg'])) {
        $r['cfg'] = json_decode($r['cfg']);
    }
    if (empty($r['id'])) {
        $templates[$t] = '';
    } else {
        $templates[$t][$r['pid']][] = $r;
    }
}
function sortTemplateRows(&$array, $pid, &$result)
{
    if (empty($pid)) {
        $pid = null;
    }
    if (!empty($array[$pid])) {
        foreach ($array[$pid] as $r) {
            array_push($result, $r);
            sortTemplateRows($array, $r['id'], $result);
        }
    }
}
$res->close();
foreach ($templates as $t => $f) {
    $sf = array();
    sortTemplateRows($f, null, $sf);
    echo 'CB.DB.template'.$t.' = new Ext.data.JsonStore({'.
    'autoLoad: true'.
    ',baseParams: {template_id: '.$t.'}'.
    ',fields: ["id", "pid","tag","level","name", "title", "type", "order", {name: "cfg", convert: function(v, r){ return Ext.isEmpty(v) ? {} : v}}'.
    ']'.
    ',proxy: new Ext.data.MemoryProxy('.(empty($sf) ? '' : json_encode($sf)).')'.
    '});';
}

?>
    reloadTemplates = function(){
        CB.DB.templates.reload({
            callback: function(){
                CB_Templates.getTemplatesStructure(function(r, e){
                    Ext.iterate(CB.DB, function(k, st){
                        if (k.substr(0, 8) == 'template') {
                            tid = k.substr(8);
                            if (!isNaN(tid)) {
                                st.removeAll();
                                if(r.data[tid]) st.loadData(r.data[tid]);
                            }
                        }
                    })
                })
            }
        })
    }
    reloadThesauri = function(){
        CB.DB.thesauri.reload({callback: function(){
            Ext.iterate(CB.DB, function(k, st){
                if (k.substr(0, 13) == 'ThesauriStore') {
                    thesauriId = k.substr(13);
                    if (!isNaN(thesauriId)) {
                        st.removeAll();
                        data = CB.DB.thesauri.queryBy(function(record, id){ return (record.get('pid') == thesauriId); });
                        st.add(data.items);
                    }
                }
            })
        }
        })
    }
createDirectStores = function(){
    if (typeof(CB_Security) == 'undefined') {
        createDirectStores.defer(500);

        return;
    }
    CB.DB.thesauri = new Ext.data.DirectStore({
        autoLoad: true
        ,restful: false
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,api: {
                create:   CB_Thesauri.create
                ,read:    CB_Thesauri.read
                ,update:  CB_Thesauri.update
                ,destroy: CB_Thesauri.destroy
            }
        })
        ,reader: new Ext.data.JsonReader({
            successProperty: 'success'
            ,idProperty: 'id'
            ,root: 'data'
            ,messageProperty: 'msg'
        },[ {name: 'id',    type: 'int'}
            ,{name: 'pid',  type: 'int'}
            ,'name'
            ,{name: 'order', type: 'int'}
            ,'iconCls'
        ]
        )
        ,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: true})
        ,getName: getStoreNames
        ,getIcon: function(id){
            idx = this.findExact('id', parseInt(id))

            return (idx >=0 ) ? this.getAt(idx).get('iconCls') : '';
        }
    });

    CB.DB.templates = new Ext.data.DirectStore({
        autoLoad: true
        ,restful: false
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,api: {
                create:   CB_Templates.create
                ,read:    CB_Templates.readAll
                ,update:  CB_Templates.update
                ,destroy: CB_Templates.destroy
            }
        })
        ,reader: new Ext.data.JsonReader({
            successProperty: 'success'
            ,idProperty: 'id'
            ,root: 'data'
            ,messageProperty: 'msg'
        },[ {name: 'id', type: 'int'}
            ,{name: 'pid', type: 'int'}
            ,'type'
            ,'title'
            ,'iconCls'
            ,{name: "cfg", convert: function(v, r){ return Ext.isEmpty(v) ? {} : v}}
            ,'info_template'
            ,{name: 'visible', type: 'int'}
            ]
        )
        ,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: true})
        ,getName: getStoreTitles
        ,getIcon: function(id){
            idx = this.findExact('id', parseInt(id))

            return (idx >=0 ) ? this.getAt(idx).get('iconCls') : '';
        }
        ,getType: function(id){
            idx = this.findExact('id', parseInt(id))

            return (idx >=0 ) ? this.getAt(idx).get('type') : '';
        }

    });

    CB.DB.usersStore =  new Ext.data.DirectStore({
        autoLoad: true
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,directFn: CB_Security.getActiveUsers
        })
        ,reader: new Ext.data.JsonReader({
                successProperty: 'success'
                ,idProperty: 'id'
                ,root: 'data'
                ,messageProperty: 'msg'
            },[ {name: 'id', type: 'int'}, 'name', 'iconCls' ]
        )
        ,getName: getStoreNames
    });
    App.on('userprofileupdated', function(userData, event){ CB.DB.usersStore.reload();});

    CB.DB.groupsStore =  new Ext.data.DirectStore({
        autoLoad: true
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,api: {
                read: CB_Security.getUserGroups
                ,create: CB_Security.createUserGroup
                ,update: CB_Security.updateUserGroup
                ,destory: CB_Security.destroyUserGroup

            }
        })
        ,reader: new Ext.data.JsonReader({
                successProperty: 'success'
                ,idProperty: 'id'
                ,root: 'data'
                ,messageProperty: 'msg'
            },[ {name: 'id', type: 'int'}, 'name', 'title', {name: 'system', type: 'int'}, {name: 'enabled', type: 'int'} ]
        )
        ,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: true})
        ,sortInfo: {
            field: 'title'
            ,direction: 'ASC'
        }
        ,getName: getStoreTitles
    });

    CB.DB.usersGroupsSearchStore = new Ext.data.DirectStore({
        autoLoad: false
        ,autoDestroy: false
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,directFn: CB_Security.searchUserGroups
        })
        ,reader: new Ext.data.JsonReader({
                successProperty: 'success'
                ,idProperty: 'id'
                ,root: 'data'
                ,messageProperty: 'msg'
            },[ {name: 'id', type: 'int'}, 'name', {name: 'system', type: 'int'}, {name: 'enabled', type: 'int'}, 'iconCls' ]
        )
        ,sortInfo: {
            field: 'name'
            ,direction: 'ASC'
        }
        ,getName: getStoreTitles
    });

    CB.DB.countries = new Ext.data.DirectStore({
        autoLoad: false
        ,autoDestroy: false
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,directFn: CB_System.getCountries
        })
        ,reader: new Ext.data.ArrayReader({
                successProperty: 'success'
                ,idProperty: 'id'
                ,root: 'data'
                ,messageProperty: 'msg'
            },[ {name: 'id', type: 'int'}, 'name', 'phone_codes' ]
        )
        ,listeners: {
            load: function(st, recs, opts){
                pc = []
                for (i = 0; i < recs.length; i++) {
                    codes = String(recs[i].get('phone_codes')).split('|');
                    for(j = 0; j < codes.length; j++)
                    pc.push([codes[j], recs[i].get('name')+ ' ' + codes[j]]);
                }
                CB.DB.phone_codes.loadData(pc, false);
            }
        }
        ,getName: getStoreNames
         /*idx = CB.DB.countries.findExact('id', this.data.country_id);
        if (idx >= 0) {
            codes = CB.DB.countries.getAt(idx).get('phone_codes');
            codes = String(codes).split('|');
            if(!Ext.isEmpty(codes)) data.country_code = codes[0];
        }/**/
    });
    CB.DB.timezones = new Ext.data.DirectStore({
        autoLoad: false
        ,autoDestroy: false
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,directFn: CB_System.getTimezones
        })
        ,reader: new Ext.data.ArrayReader({
                successProperty: 'success'
                ,idProperty: 'id'
                ,root: 'data'
                ,messageProperty: 'msg'
            },[ 'id', 'gmt_offset', 'caption' ]
        )
        ,listeners:{
            load: function( st, recs, opts){
                for (i=0; i < recs.length; i++) {
                    recs[i].set('caption', '(GMT'+ recs[i].get('gmt_offset') +') '+recs[i].get('id'));
                }
            }
        }
    });

};
createDirectStores.defer(500);

function getThesauriStore(thesauriId)
{
    storeName = 'ThesauriStore'+thesauriId;
    if (!Ext.isDefined(CB.DB[storeName])) {
        data = CB.DB.thesauri.queryBy(function(record, id){ return (record.get('pid') == thesauriId); });
        CB.DB[storeName] = new Ext.data.ArrayStore({
            idIndex: 0
            ,fields: [{name:'id', type: 'int'}, {name:'pid', type: 'int'}, 'name', {name:'order', type: 'int'}, 'iconCls']
            ,data:  []
            ,getNames: getStoreNames
        });
        CB.DB[storeName].add([new CB.DB[storeName].recordType({id: null, name: ''}, Ext.id())]);
        CB.DB[storeName].add(data.items);/**/
    }

    return CB.DB[storeName];
}
<?php
/*
CB.DB.tasksStoreConfig = {
    autoLoad: true
    ,paramsAsHash: true
    ,sortInfo: { field: 'cdate', direction: 'ASC' }
    ,reader: new Ext.data.JsonReader({
            idProperty: 'id'
            ,root: 'data'
            ,fields: [
                {name:'id', type: 'int'}
                ,{name: 'case_id', type: 'int'}
                ,{name: 'object_id', type: 'int'}
                ,'title',
                ,{name: 'date_end', type: 'date', dateFormat: 'Y-m-d'}
                ,{name: 'missed', type: 'int'}
                ,{name: 'type', type: 'int'}
                ,{name: 'privacy', type: 'int'}
                ,'responsible_user_ids'
                ,'description'
                ,'parent_ids'
                ,'child_ids'
                ,'reminds'
                ,{name: 'status', type: 'int'}
                ,{name: 'cid', type: 'int'}
                ,{name: 'completed', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                , {name: 'cdate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                ,'case'
                ,'object'
                ,'days'
                ,'completed_text'
                ,{name: 'expired', type: 'bool'}
                ,'hot'
                ,'cls'
                ,'iconCls'
            ]
        }
    )
};
/**/
