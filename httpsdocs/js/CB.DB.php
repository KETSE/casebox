<?php
	header('content-type: text/javascript; charset=utf-8');
	include '../init.php'; 
	require_once('../lib/DB.php');
	connect2DB();
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
	CB.DB.templateTypes = new Ext.data.ArrayStore({
		idIndex: 0
		,fields: [{name: 'id', type: 'int'}, 'name']
		,data:  [[0, L.Folder], [1, L.CaseObject], [2, L.IncomingAction], [3, L.OutgoingAction], [4, L.Applicant], [5, L.Subject], [6, L.User], [7, L.Contact], [8, L.Email]]
		,getName: function(id){ idx = this.findExact('id', parseInt(id)); return (idx >=0 ) ? this.getAt(idx).get('name') : ''; }
	});
	CB.DB.tasksImportance = new Ext.data.ArrayStore({
		idIndex: 0
		,fields: [{name: 'id', type: 'int'}, 'name']
		,data:  [ [1, L.Low], [2, L.Medium], [3, L.High] ]
		,getName: function(id){ idx = this.findExact('id', parseInt(id)); return (idx >=0 ) ? this.getAt(idx).get('name') : ''; }
	})

<?php
	$data = array();
	if(!empty($_SESSION['config']['templateIcons'])){
		$data = explode(',', $_SESSION['config']['templateIcons'][0]);
		$data = implode("\n", $data);
		$data = str_replace("\r\n", "\n", $data);
		$data = explode("\n", $data);
		for($i = 0; $i < sizeof($data); $i++) $data[$i] = array($data[$i]);
	}
	echo 'CB.DB.templatesIconSet = new Ext.data.ArrayStore({ idIndex: 0,fields: ["name"], data: '. json_encode($data).'});';

	/* case types */
	$sql = 'SELECT DISTINCT t.id, t.l'.UL_ID().' `name`, t.iconCls 
		FROM tag_groups tg 
		JOIN tag_groups__tags_result tr  ON tr.tags_group_id = tg.id 
		JOIN tags t ON t.id = tr.tag_id
		WHERE tg.system =2 AND t.hidden IS NULL ORDER BY t.`order`';
	$res = mysqli_query_params($sql) or die(mysqli_query_error());
	$a = Array();
	while($r = $res->fetch_row()) $a[] = $r;
	echo 'CB.DB.caseTypes = new Ext.data.ArrayStore({fields: ["id", "name", "iconCls"], data: '.(empty($a) ? '[]' : json_encode($a)).'});';
	/* end of case types */
	/* tag groups  */
	$sql = 'SELECT id, l'.UL_ID().', `system`, `order` FROM tag_groups ORDER BY `system`, `order`, 2';
	$res = mysqli_query_params($sql) or die(mysqli_query_error());
	$a = Array();
	while($r = $res->fetch_row()) $a[] = $r;
	echo 'CB.DB.tagGroups = new Ext.data.ArrayStore({fields: [{name: "id", type: "int"}, "name", {name: "system", type: "int"}, {name: "order", type: "int"}], data: '.(empty($a) ? '[]' : json_encode($a)).'
		,getName: function(id){ idx = this.findExact(\'id\', parseInt(id)); return (idx >=0 ) ? this.getAt(idx).get(\'name\') : \'\'; }
	});';
	/* end tags per groups */	/* tags per groups  */
	$sql = 'SELECT DISTINCT t.id, t.pid, gt.pid_value, gt.template_id, t.l'.UL_ID().', t.iconCls, g.id, g.system, gt.parent_order'.
		' FROM tag_groups g'.
		' JOIN tag_groups__tags_result gt ON g.id = gt.tags_group_id'.
		' JOIN tags t ON gt.tag_id = t.id AND t.hidden IS NULL'.
		' ORDER BY `system`, g.`order`, g.l'.UL_ID().', gt.parent_order, t.order, 2';
	$res = mysqli_query_params($sql) or die(mysqli_query_error());
	$a = Array();
	while($r = $res->fetch_row()) $a[] = $r;
	echo 'CB.DB.groupedTags = new Ext.data.ArrayStore({fields: [{name: "id", type: "int"}, {name: "pid", type: "int"}, {name: "pid_value", type: "int"}, {name: "template_id", type: "int"}, "name", "iconCls", {name: "groupId", type: "int"}, {name: "system", type: "int"}, {name: "parent_order", type: "int"}], data: '.(empty($a) ? '[]' : json_encode($a)).'});';
	/* end tags per groups */
	/* languages */
	$sql = 'SELECT id, name, abreviation, long_date_format, short_date_format FROM languages order by name';
	$res = mysqli_query_params($sql) or die(mysqli_query_error());
	
	$a = Array();
	while($r = $res->fetch_row()) $a[] = $r;
	$res->close();
	echo 'CB.DB.languages = new Ext.data.ArrayStore({'.
		'fields: [{name: "id", type: "int"}, "name", "abreviation", "long_date_format", "short_date_format"]'.
		',data: '.(empty($a) ? '[]' : json_encode($a)).
		'});';
	/* end of languages */
	/* templates */
	$sql = 'SELECT ts.id, ts.pid, t.id template_id, ts.tag, ts.`level`, ts.`name`, ts.l'.UL_ID().' `title`, ts.`type`, ts.`order`, ts.cfg'.
			', (coalesce(t.title_template, \'\') <> \'\' ) `has_title_template`'.
			' FROM templates t left join templates_structure ts on t.id = ts.template_id ORDER BY template_id, level, `order`';
	$res = mysqli_query_params($sql, $_SESSION['user']['language_id']) or die(mysqli_query_error());
	
	$templates = Array();
	while($r = $res->fetch_assoc()){
		$t = $r['template_id'];
		unset($r['template_id']);
		if( ($r['type'] == '_auto_title') && ($r['has_title_template'] == 0) ) $r['type'] = 'varchar';
		unset($r['has_title_template']);
		if(!empty($r['cfg'])) $r['cfg'] = json_decode($r['cfg']);
		if(empty($r['id'])) $templates[$t] = ''; else $templates[$t][$r['pid']][] = $r;
	}
	function sort_template_rows(&$array, $pid = null, &$result){
		if(!empty($array[$pid])){
			foreach($array[$pid] as $r){
				array_push($result, $r);
				sort_template_rows($array, $r['id'], $result);
			}
		}
	}
	$res->close();
	foreach($templates as $t => $f){
		$sf = array();
		sort_template_rows($f, null, $sf);
		echo 'CB.DB.template'.$t.' = new Ext.data.JsonStore({'.
		'autoLoad: true'.
		',baseParams: {template_id: '.$t.'}'.
		',fields: ["id", "pid","tag","level","name", "title", "type", "order", {name: "cfg", convert: function(v, r){ return Ext.isEmpty(v) ? {} : v}}'.
		']'.
		',proxy: new Ext.data.MemoryProxy('.(empty($sf) ? '' : json_encode($sf)).')'.
		'});';
	}
	/* templates per tags */
	$res = mysqli_query_params('SELECT id from templates where `type` in (1, 2, 3, 4, 5, 7) and visible = 1 ORDER BY case when `type` = 1 then 3 when `type` = 3 then 1 else `type` end, `order`') or die(mysqli_query_error());
	$a = Array();
	while($r = $res->fetch_row()) $a[] = $r;
	$res->close();
	echo 'CB.DB.templates_per_tags = new Ext.data.ArrayStore({ fields: [{name: "template_id", type: "int"}, {name: "case_type_id", type: "int"}, {name: "tag_id", type: "int"}],data: '.(empty($a) ? '[]' : json_encode($a)).'});';
	/* end of templates per tags */
	
?>
	reloadTemplates = function(){
		CB.DB.templates.reload({
			callback: function(){
				Templates.getTemplatesStructure(function(r, e){
					Ext.iterate(CB.DB, function(k, st){ 
						if(k.substr(0, 8) == 'template'){
							tid = k.substr(8);
							if(!isNaN(tid)){
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
		CB.DB.thesauri .reload({callback: function(){
			Ext.iterate(CB.DB, function(k, st){ 
				if(k.substr(0, 13) == 'ThesauriStore'){
					thesauriId = k.substr(13);
					if(!isNaN(thesauriId)){
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
	if(typeof(Thesauri) == 'undefined'){
		createDirectStores.defer(500);
		return;
	}
	CB.DB.thesauri = new Ext.data.DirectStore({
		autoLoad: true
		,restful: false
		,proxy: new  Ext.data.DirectProxy({
			paramsAsHash: true
			,api: {
				create:   Thesauri.create
				,read:    Thesauri.read
				,update:  Thesauri.update
				,destroy: Thesauri.destroy
			}
		})
		,reader: new Ext.data.JsonReader({
			successProperty: 'success'
			,idProperty: 'id'
			,root: 'data'
			,messageProperty: 'msg'
		},[	{name: 'id',	type: 'int'}
			,{name: 'pid',	type: 'int'}
			,'name'
			,{name: 'order', type: 'int'}
			,'iconCls'
		]
		)
		,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: true})
		,getName: function(id){
			idx = this.findExact('id', parseInt(id))
			return (idx >=0 ) ? this.getAt(idx).get('name') : '';
		}
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
				create:   Templates.create
				,read:    Templates.readAll
				,update:  Templates.update
				,destroy: Templates.destroy
			}
		})
		,reader: new Ext.data.JsonReader({
			successProperty: 'success'
			,idProperty: 'id'
			,root: 'data'
			,messageProperty: 'msg'
		},[	{name: 'id', type: 'int'}
			,{name: 'pid', type: 'int'}
			,{name: 'type', type: 'int'}
			,'title'
			,'iconCls'
			,'cfg'
			,'info_template'
			]
		)
		,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: true})
		,getName: function(id){
			idx = this.findExact('id', parseInt(id))
			return (idx >=0 ) ? this.getAt(idx).get('title') : '';
		}
		,getIcon: function(id){
			idx = this.findExact('id', parseInt(id))
			return (idx >=0 ) ? this.getAt(idx).get('iconCls') : '';
		}
		
	});
	CB.DB.userTags = new Ext.data.DirectStore({
		autoLoad: true
		,restful: false
		,proxy: new  Ext.data.DirectProxy({
			paramsAsHash: true
			,api: {
				create:   UsersGroups.addUserTag
				,read:    UsersGroups.getUserTags
			}
		})
		,reader: new Ext.data.JsonReader({
			successProperty: 'success'
			,idProperty: 'id'
			,root: 'data'
			,messageProperty: 'msg'
		},[	{name: 'id', type: 'int'},'name' ]
		)
	});
	CB.DB.updateTagGroups = function(data){
		if(Ext.isEmpty(data)) return;
		CB.DB.groupedTags.removeAll();
		CB.DB.groupedTags.loadData(data);
	}
};
createDirectStores.defer(500);

function getThesauriStore(thesauriId){
	storeName = 'ThesauriStore'+thesauriId;
	if(!Ext.isDefined(CB.DB[storeName])){
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
				,{name: 'responsible_party_id', type: 'int'}
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
				,'responsible_party'
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
