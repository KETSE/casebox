<?php
namespace CB;

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
        ,data:  [[null, '-'], ['case', 'case'], ['comment', 'comment'], ['email', 'email'], ['field', 'field'], ['file', 'file'], ['object', 'object'], ['search', 'search'], ['task', 'task'], ['template', 'template'], ['user', 'user']]
        ,getName: getStoreNames
    });
<?php
$data = array(
    array(null, '-')
    ,array('_auto_title', L\get('ftAutoTitle')) //Auto title (uses title_template)
    ,array('checkbox', L\get('ftCheckbox')) //CheckBox
    ,array('combo', L\get('ftCombo')) //ComboBox
    ,array('date', L\get('ftDate')) //Date
    ,array('datetime', L\get('ftDatetime')) //Datetime
    ,array('float', L\get('ftFloat')) //Float
    ,array('G', L\get('ftGroup')) //Group
    ,array('H', L\get('ftHeader')) //Header
    ,array('html', L\get('ftHtml')) //Html
    ,array('iconcombo', L\get('ftIconcombo')) //IconCombo
    ,array('importance', L\get('Importance')) //Importance
    ,array('int', L\get('ftInt')) //Integer
    ,array('_language', L\get('ftLanguage')) //Language
    ,array('memo', L\get('ftMemo')) //Memo
    ,array('_objects', L\get('ftObjects')) //Objects
    ,array('_sex', L\get('ftSex')) //Sex
    ,array('_short_date_format', L\get('ftShortDateFormat')) //Short date format combo
    ,array('_fieldTypesCombo', L\get('ftFieldTypesCombo')) //Template field types combo
    ,array('_templateTypesCombo', L\get('ftTemplateTypesCombo')) //Template types combo
    ,array('text', L\get('ftText')) //Text
    ,array('time', L\get('ftTime')) //Time
    ,array('timeunits', L\get('ftTimeunits')) //Time units
    ,array('varchar', L\get('ftVarchar')) //Varchar
);
?>
    CB.DB.fieldTypes = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: ['id', 'name']
        ,data: <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?>
        ,getName: getStoreNames
    });
    CB.DB.reminderTypes = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name', 'iconCls']
        ,data:  [[1, L.byMail, 'icon-mail'], [2, L.bySystem, 'icon-bell']]
    });
    CB.DB.timeUnits = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [[1, L.ofMinutes], [2, L.ofHours], [3, L.ofDays], [4, L.ofWeeks]]
        ,getName: getStoreNames
    });
    CB.DB.shortDateFormats = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: ['id', 'name']
        ,data:  [['%m/%d/%Y', 'm/d/Y'], ['%d/%m/%Y', 'd/m/Y'], ['%d.%m.%Y', 'd.m.Y'], ['%d-%m-%Y', 'd-m-Y']]
    });
    CB.DB.roles = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [<?php echo '[1, "'.L\get('Administrator').'"], [2, "'.L\get('Manager').'"], [3, "'.L\get('Lawyer').'"], [4, "'.L\get('User').'"]'; ?>]
    });
    CB.DB.importance = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [ [1, L.Low], [2, L.Medium], [3, L.High] ]
        ,getName: getStoreNames
    })
    CB.DB.phone_codes = new Ext.data.ArrayStore({
        idIndex: 0
        ,fields: [ 'code', 'name']
        ,data:  []
    });

<?php

$data = array();
$templateIcons = Config::get('templateIcons');

if (!empty($templateIcons)) {
    $data = explode(',', $templateIcons);
    $data = implode("\n", $data);
    $data = str_replace("\r\n", "\n", $data);
    $data = explode("\n", $data);
    for ($i = 0; $i < sizeof($data); $i++) {
        $data[$i] = array($data[$i], $data[$i]);
    }
}
echo 'CB.DB.templatesIconSet = new Ext.data.ArrayStore({ idIndex: 0,fields: ["id","name"], data: '. json_encode($data, JSON_UNESCAPED_UNICODE).'});';

/* languages */
$coreLanguages = Config::get('languages');
$languageSettings = Config::get('language_settings');

$arr = array();
for ($i=0; $i < sizeof($coreLanguages); $i++) {
    $lang = $languageSettings[$coreLanguages[$i]];
    $lp = array($i+1, $coreLanguages[$i], $lang['name'], $lang['long_date_format'], $lang['short_date_format'], $lang['time_format'] );
    for ($j=0; $j < sizeof($lp); $j++) {
        $lp[$j] = str_replace(array('%', '\/'), array('', '/'), $lp[$j]);
    }
    $arr[] = $lp;
}

echo "\n".'CB.DB.languages = new Ext.data.ArrayStore({'.
    'fields: [{name: "id", type: "int"}, "abreviation", "name", "long_date_format", "short_date_format", "time_format"]'.
    ',data: '.(empty($arr) ? '[]' : json_encode($arr, JSON_UNESCAPED_UNICODE)).
    '});'."\n";
/* end of languages */

/* Security questions */
$arr = array();
for ($i=0; $i < 10; $i++) {
    $sq = L\get('SecurityQuestion' . $i);
    if (!empty($sq)) {
        $arr[] = array($i, $sq);
    }
}
$osq = L\get('OwnSecurityQuestion');
if (!empty($osq)) {
    $arr[] = array( -1 , $osq);
}
echo "\n".'CB.DB.securityQuestions = new Ext.data.ArrayStore({'.
    'fields: [{name: "id", type: "int"}, "text"]'.
    ',data: '.(empty($arr) ? '[]' : json_encode($arr, JSON_UNESCAPED_UNICODE)).
    '});'."\n";
/* end of Security questions */

/* menu */
$arr = array();
$res = DB\dbQuery('SELECT * FROM menu') or die( DB\dbQueryError() );
while ($r = $res->fetch_assoc()) {
    $intersection = array_intersect(
        explode(',', $r['user_group_ids']),
        array_merge(
            $_SESSION['user']['groups'],
            array($_SESSION['user']['id'])
        )
    );
    if (empty($r['user_group_ids']) || !empty( $intersection )) {
        $arr[] = array_values($r);
    }
}
$res->close();

echo "\n".'CB.DB.menu = new Ext.data.ArrayStore({'.
    'fields: [{name: "id", type: "int"}, "node_ids", "node_template_ids", "menu", "user_group_ids"]'.
    ',data: '.(empty($arr) ? '[]' : json_encode($arr, JSON_UNESCAPED_UNICODE)).
    '});'."\n";
/* end of menu */

/* templates */
$templatesClass = new Templates();
$data = $templatesClass->getTemplatesStructure();
$templates = array();

foreach ($data['data'] as $t => $fields) {
    $templates[$t] = array();
    foreach ($fields as $f) {
        $templates[$t][$f['pid']][] = $f;
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

foreach ($templates as $t => $f) {
    $sf = array();
    sortTemplateRows($f, null, $sf);
    echo 'CB.DB.template'.$t.' = new CB.DB.TemplateStore({data:'.json_encode($sf, JSON_UNESCAPED_UNICODE).'});';
}

?>
reloadTemplates = function(){
    CB.DB.templates.reload({
        callback: function(){
            CB_Templates.getTemplatesStructure(function(r, e){
                Ext.iterate(CB.DB, function(k, st){
                    if (k.substr(0, 8) == 'template') {
                        var tid = k.substr(8);
                        if (!isNaN(tid)) {
                            st.removeAll();
                            if (r.data[tid]) {
                                st.loadData(r.data[tid]);
                            }
                        }
                    }
                })
            })
        }
    })
}

createDirectStores = function(){
    if (typeof(CB_Security) == 'undefined') {
        createDirectStores.defer(500);

        return;
    }
    CB.DB.thesauri = new Ext.data.JsonStore({
        reader: new Ext.data.JsonReader({
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
        ,getName: getStoreNames
        ,getIcon: function(id){
            var idx = this.findExact('id', parseInt(id));

            return (idx >=0)
                ? this.getAt(idx).get('iconCls')
                : '';
        }
    });

    CB.DB.templates = new Ext.data.DirectStore({
        autoLoad: true
        ,restful: false
        ,proxy: new  Ext.data.DirectProxy({
            paramsAsHash: true
            ,api: {
                read:    CB_Templates.readAll
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
            var idx = this.findExact('id', parseInt(id))

            return (idx >= 0)
                ? this.getAt(idx).get('iconCls')
                : '';
        }
        ,getType: function(id){
            var idx = this.findExact('id', parseInt(id, 10))

            return (idx >=0)
                ? this.getAt(idx).get('type') : '';
        }
        ,getProperty: function(templateId, propertyName) {
            var idx = this.findExact('id', parseInt(templateId, 10))

            return (idx >= 0)
                ? this.getAt(idx).get(propertyName)
                : '';
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
            },[ {name: 'id', type: 'int'}, 'user', 'name', 'iconCls', 'photo' ]
        )

        ,getName: getStoreNames
        ,getPhotoParam: function(id) {
            var idx = this.findExact('id', parseInt(id));

            return (idx >= 0)
                ? this.getAt(idx).get('photo')
                : '';
        }
        ,getUserById: function(id) {
            var idx = this.findExact('id', parseInt(id));

            return (idx >= 0)
                ? this.getAt(idx).get('user')
                : '';
        }
        ,getIdByUser: function(user) {
            var idx = this.findExact('user', user);

            return (idx >= 0)
                ? this.getAt(idx).get('id')
                : null;
        }
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
    });

};
createDirectStores.defer(500);

CB.DB.convertJsonReaderDates = function (jsonData) {
    if (jsonData && Ext.isArray(jsonData.data)) {
        for (var f = 0; f < this.meta.fields.length; f++) {
            if (Ext.isObject(this.meta.fields[f]) && (this.meta.fields[f].type == 'date')) {
                var fn = this.meta.fields[f].name;
                for (var i = 0; i < jsonData.data.length; i++) {
                    if (!Ext.isEmpty(jsonData.data[i][fn])) {
                        var d = date_ISO_to_local_date(jsonData.data[i][fn]);
                        jsonData.data[i][fn] = d;
                    }
                }
            }
        }
    }
}

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
