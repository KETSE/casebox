<?php
namespace CB;

header('content-type: text/javascript; charset=utf-8');
require_once '../init.php';
DB\connect();

?>
Ext.namespace('CB.DB');

CB.DB.themes = new Ext.data.ArrayStore({
    model: 'Generic2'
    ,data: [
        //['aria', 'Aria'] // loads data from http
        ['classic', 'Classic']
        //,['classic-sandbox', 'Classic Sandbox'] //bad
        ,['crisp', 'Crisp']
        ,['crisp-touch', 'Crisp Touch']
        ,['gray', 'Gray']
        ,['neptune', 'Neptune']
        ,['neptune-touch', 'Neptune touch']
    ]
});

CB.DB.yesno = new Ext.data.ArrayStore({
    idIndex: 0
    ,model: 'Generic'
    //,fields: [{name: 'id', type: 'int'}, 'name']
    ,data:  [[0, ' '], [-1, L.no], [1, L.yes]]
});
CB.DB.sex = new Ext.data.ArrayStore({
    idIndex: 0
    ,model: 'Generic2'
    //,fields: ['id', 'name']
    ,data:  [[null, '-'], ['m', L.male], ['f', L.female]]
});
CB.DB.templateTypes = new Ext.data.ArrayStore({
    idIndex: 0
    ,model: 'Generic2'
    //,fields: ['id', 'name']
    ,data:  [[null, '-'], ['case', 'case'], ['comment', 'comment'], ['email', 'email'], ['field', 'field'], ['file', 'file'], ['object', 'object'], ['search', 'search'], ['shortcut', 'shortcut'], ['task', 'task'], ['template', 'template'], ['user', 'user']]
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
        ,model: 'Generic2'
        //,fields: ['id', 'name']
        ,data: <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?>
        ,getName: getStoreNames
    });
    CB.DB.reminderTypes = new Ext.data.ArrayStore({
        idIndex: 0
        ,model: 'Generic'
        //,fields: [{name: 'id', type: 'int'}, 'name', 'iconCls']
        ,data:  [[1, L.byMail, 'icon-mail'], [2, L.bySystem, 'icon-bell']]
    });
    CB.DB.timeUnits = new Ext.data.ArrayStore({
        idIndex: 0
        ,model: 'Generic'
        //,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [[1, L.ofMinutes], [2, L.ofHours], [3, L.ofDays], [4, L.ofWeeks]]
        ,getName: getStoreNames
    });
    CB.DB.shortDateFormats = new Ext.data.ArrayStore({
        idIndex: 0
        ,model: 'Generic2'
        //,fields: ['id', 'name']
        ,data:  [['m/d/Y', 'm/d/Y'], ['d/m/Y', 'd/m/Y'], ['d.m.Y', 'd.m.Y'], ['d-m-Y', 'd-m-Y']]
    });
    CB.DB.roles = new Ext.data.ArrayStore({
        idIndex: 0
        ,model: 'Generic'
        //,fields: [{name: 'id', type: 'int'}, 'name']
        ,data:  [<?php echo '[1, "'.L\get('Administrator').'"], [2, "'.L\get('Manager').'"], [3, "'.L\get('Lawyer').'"], [4, "'.L\get('User').'"]'; ?>]
    });

    CB.DB.phone_codes = new Ext.data.ArrayStore({
        idIndex: 0
        ,model: 'PhoneCode'
        //,fields: [ 'code', 'name']
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
echo 'CB.DB.templatesIconSet = new Ext.data.ArrayStore({
    idIndex: 0
    ,model: \'Generic2\'
    //,fields: ["id","name"]
    ,data: '. json_encode($data, JSON_UNESCAPED_UNICODE).'});';

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
    'model: \'Language\''.
    //'fields: [{name: "id", type: "int"}, "abreviation", "name", "long_date_format", "short_date_format", "time_format"]'.
    ', data: '.(empty($arr) ? '[]' : json_encode($arr, JSON_UNESCAPED_UNICODE)).
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
   'model: \'SecurityQuestion\''.
//    'fields: [{name: "id", type: "int"}, "text"]'.
    ',data: '.(empty($arr) ? '[]' : json_encode($arr, JSON_UNESCAPED_UNICODE)).
    '});'."\n";
/* end of Security questions */

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
        Ext.Function.defer(createDirectStores, 500);

        return;
    }
    /*CB.DB.thesauri = new Ext.data.JsonStore({
        reader: new Ext.data.JsonReader({
            successProperty: 'success'
            ,idProperty: 'id'
            ,rootProperty: 'data'
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
    });/**/

    CB.DB.templates = new Ext.data.DirectStore({
        autoLoad: true
        ,restful: false
        ,model: 'Template'
        ,proxy: {
            type: 'direct'
            ,paramsAsHash: true
            ,api: {
                read: CB_Templates.readAll
            }
            ,reader: {
                type: 'json'
                ,successProperty: 'success'
                ,idProperty: 'id'
                ,rootProperty: 'data'
                ,messageProperty: 'msg'
            }
        }

        ,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: true})

        ,getName: getStoreTitles

        ,getIcon: function(id){
            var idx = this.findExact('id', parseInt(id))

            var rez = (idx >= 0)
                ? this.getAt(idx).get('iconCls')
                : '';

            return rez;
        }

        ,getType: function(id){
            var rec = this.findRecord('id', parseInt(id, 10))

            return rec ? rec.get('type') : '';
        }

        ,getProperty: function(templateId, propertyName) {
            var idx = this.findExact('id', parseInt(templateId, 10))

            var rez = (idx >= 0)
                ? this.getAt(idx).get(propertyName)
                : '';

            return rez;
        }

        //check if children are accepted by config of the given template id
        //by default all templates accept children except for templates of type 'file'
        ,acceptChildren: function(templateId) {
            if (isNaN(templateId)) {
                return false;
            }

            var cfg = Ext.valueFrom(this.getProperty(templateId, 'cfg'), {})
                ,rez = (cfg.acceptChildren !== false);

            if (!Ext.isDefined(cfg.acceptChildren)) {
                rez = (this.getType(templateId) !== 'file');
            }

            return rez;
        }

    });

    CB.DB.usersStore =  new Ext.data.DirectStore({
        autoLoad: true
        ,model: 'User'
        ,proxy: {
            type: 'direct'
            ,paramsAsHash: true
            ,directFn: CB_Security.getActiveUsers
            ,reader: {
                type: 'json'
                ,successProperty: 'success'
                ,idProperty: 'id'
                ,rootProperty: 'data'
                ,messageProperty: 'msg'
            }
        }
        ,getName: getStoreNames
        ,getPhotoParam: function(id) {
            var idx = this.findExact('id', parseInt(id));

            var rez = (idx >= 0)
                ? this.getAt(idx).get('photo')
                : '';

            return rez;
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
        ,autoSync: true
        ,model: 'Group'
        ,proxy: {
            type: 'direct'
            ,paramsAsHash: true
            ,api: {
                read: CB_Security.getUserGroups
                ,create: CB_Security.createUserGroup
                ,update: CB_Security.updateUserGroup
                ,destory: CB_Security.destroyUserGroup
            }
            ,reader: {
                type: 'json'
                ,successProperty: 'success'
                ,idProperty: 'id'
                ,rootProperty: 'data'
                ,messageProperty: 'msg'
            }
            ,writer: {
                type: 'json'
                ,rootProperty: 'data'
                ,encode: false
                ,writeAllFields: true
            }
        }
        ,sortInfo: {
            field: 'title'
            ,direction: 'ASC'
        }
        ,getItemName: getStoreTitles
    });

    CB.DB.usersGroupsSearchStore = new Ext.data.DirectStore({
        autoLoad: false
        ,autoDestroy: false
        ,model: 'Group'
        ,proxy: {
            type: 'direct'
            ,paramsAsHash: true
            ,directFn: CB_Security.searchUserGroups
            ,reader: {
                type: 'json'
                ,successProperty: 'success'
                ,idProperty: 'id'
                ,rootProperty: 'data'
                ,messageProperty: 'msg'
            }
        }
        ,sortInfo: {
            field: 'name'
            ,direction: 'ASC'
        }
        ,getName: getStoreTitles
    });

    CB.DB.countries = new Ext.data.DirectStore({
        autoLoad: false
        ,autoDestroy: false
        ,model: 'Country'
        ,proxy: {
            type: 'direct'
            ,paramsAsHash: true
            ,directFn: CB_System.getCountries
            ,reader: {
                successProperty: 'success'
                ,idProperty: 'id'
                ,rootProperty: 'data'
                ,messageProperty: 'msg'
            }
        }
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
        ,model: 'Timezone'
        ,proxy: {
            type: 'direct'
            ,paramsAsHash: true
            ,directFn: CB_System.getTimezones
            ,reader: {
                type: 'json'
                ,successProperty: 'success'
                ,idProperty: 'id'
                ,rootProperty: 'data'
                ,messageProperty: 'msg'
            }
        }
    });

};

Ext.Function.defer(createDirectStores, 500);

CB.DB.convertJsonReaderDates = function (jsonData) {
    if (jsonData && Ext.isArray(jsonData.data)) {
        for (var f = 0; f < this._model.fields.length; f++) {
            if (Ext.isObject(this._model.fields[f]) && (this._model.fields[f].type == 'date')) {
                var fn = this._model.fields[f].name;
                for (var i = 0; i < jsonData.data.length; i++) {
                    //detect if its task object
                    var isTask = false;
                    if (Ext.isDefined(jsonData.data[i]['template_id'])) {
                        isTask = (CB.DB.templates.getType(jsonData.data[i]['template_id']) == 'task');
                    }

                    if (!Ext.isEmpty(jsonData.data[i][fn])) {
                        //if is task then dates with empty time should not be shifted
                        var d = (isTask && (String(jsonData.data[i][fn]).substr(11, 8) == '00:00:00'))
                            ? new Date(jsonData.data[i][fn])
                            : date_ISO_to_local_date(jsonData.data[i][fn]);
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
        CB.DB[storeName].add([
            Ext.create(
                CB.DB[storeName].getModel().getName()
                ,{id: null, name: ''}
            )
        ]);
        CB.DB[storeName].add(data.items);/**/
    }

    return CB.DB[storeName];
}
