// JavaScript Document
function isEmptyObject(ob){
   for(var i in ob){ if(ob.hasOwnProperty(i)){return false;}}
  return true;
}

function date_ISO_to_date(date_string){
	if(Ext.isEmpty(date_string)) return null;
	//if(date_string.substr(-14) == 'T00:00:00.000Z') date_string = date_string.substr(0, 10);
	d = Date.parse(date_string);
	if(Ext.isEmpty(d)) return null;
	return new Date(d);
}
function getItemIcon(d){
	switch(parseInt(d['type'])){
		case 0: return Ext.value(d['iconCls'], 'icon-folder'); //offices, year, month folders
			break; 
		case 1: 
			switch(parseInt(d['subtype'])){
				case 1:	break;
				case 2:	return 'icon-home'; break;
				case 3:	return 'icon-blue-folder'; break;
				case 4:	return 'icon-briefcase'; break;
				case 5:	return 'icon-calendar-small'; break;
				case 6:	return 'icon-mail-medium'; break;
				case 7:	return 'icon-blue-folder-stamp'; break;
				case 8:	return 'icon-folder'; break;
				case 9: return 'icon-blue-folder'; break;
				case 10: return 'icon-blue-folder-share'; break;
				default: return Ext.value(d['iconCls'], 'icon-folder'); break;
			}
			break;
		case 2: return 'icon-shortcut';//case
			break;
		case 3: return 'icon-briefcase';//case
			break;
		case 4: //case object
			if(!Ext.isEmpty(d.cfg) && !Ext.isEmpty(d.cfg.iconCls)) return d.cfg.iconCls;
			return Ext.value( CB.DB.templates.getIcon(d['template_id']), 'icon-none' );
			break;
		case 5: //file
			return getFileIcon(d['name']);
			break;
		case 6:
			if(d['status'] == 3) return 'icon-task-completed';
			return 'icon-task';//task
			break;
		case 7: return 'icon-event';//Event
		case 8: return 'icon-mail';//Message (email)
			break;
		default: return d.iconCls;
	}
}
//function getItemCls()
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
		 idx = this.findExact('id', parseInt(id));
		if(idx >= 0) texts.push(this.getAt(idx).get('title'));			
	}, this)
	return texts.join(',');
}
function getStoreNames(v){
	if(Ext.isEmpty(v)) return '';
	ids = String(v).split(',');
	texts = [];
	Ext.each(ids, function(id){
		 idx = this.findExact('id', parseInt(id));
		if(idx >= 0) texts.push(this.getAt(idx).get('name'));			
	}, this)
	return texts.join(',');
}
setsGetIntersection = function(set1, set2){
	rez = [];
	if(Ext.isEmpty(set1) || Ext.isEmpty(set2)) return rez;
	if(Ext.isPrimitive(set1)) set1 = String(set1).split(',');
	if(Ext.isPrimitive(set2)) set2 = String(set2).split(',');
	for (var i = 0; i < set1.length; i++) set1[i] = String(set1[i]);
	for (var i = 0; i < set2.length; i++) set2[i] = String(set2[i]);
	for (var i = 0; i < set1.length; i++) if( (set2.indexOf(set1[i]) >= 0) && (rez.indexOf(set1[i]) < 0 )) rez.push(set1[i]);
	for (var i = 0; i < set2.length; i++) if( (set1.indexOf(set2[i]) >= 0) && (rez.indexOf(set2[i]) < 0 )) rez.push(set2[i]);
	return rez;
}
setsHaveIntersection = function(set1, set2){
	return !Ext.isEmpty(setsGetIntersection(set1, set2));
}
getGroupedTemplates = function(menuButton, handler, scope, templatesFilter){
	a = [];

	CB.DB.templates.each(function(r){ 
		if( (r.get('visible') == 1) && ([1, 2, 3, 7].indexOf(r.get('type')) >= 0) )
		if(Ext.isEmpty(templatesFilter) || setsHaveIntersection(templatesFilter, r.get('id')) ){
			a.push({
				text: r.get('title')
				,iconCls: r.get('iconCls')
				,scope: scope
				,handler: handler
				,data: {
					template_id: r.get('id')
					,type: r.get('type')
					,title: r.get('title')
					
				}
			});
		}
	}, this);
	menuButton.menu.removeAll();
	menuButton.lastGroup = null;
	for(i = 0; i < a.length; i++){
		if(menuButton.lastGroup != a[i].data.type){
			if(menuButton.menu.items.getCount() > 0) menuButton.menu.add('-');
			menuButton.lastGroup = a[i].data.type;
		}
		menuButton.menu.add(a[i]);
	}

	return;
	//we already do not have templates_per_tags, 
	
	// CB.DB.templates_per_tags.each(function(r){ 
	// 	if(Ext.isEmpty(templatesFilter) || setsHaveIntersection(templatesFilter, r.get('template_id')) ){
	// 		idx = CB.DB.templates.findExact('id', r.get('template_id'));
	// 		tr = CB.DB.templates.getAt(idx);
	// 		a.push({
	// 			text: tr.get('title')
	// 			,iconCls: tr.get('iconCls')
	// 			,scope: scope
	// 			,handler: handler
	// 			,data: {
	// 				template_id: tr.get('id')
	// 				,type: tr.get('type')
	// 				,title: tr.get('title')
					
	// 			}
	// 		});
	// 	}
	// }, this);
	// menuButton.menu.removeAll();
	// menuButton.lastGroup = null;
	// for(i = 0; i < a.length; i++){
	// 	if(menuButton.lastGroup != a[i].data.type){
	// 		if(menuButton.menu.items.getCount() > 0) menuButton.menu.add('-');
	// 		menuButton.lastGroup = a[i].data.type;
	// 	}
	// 	menuButton.menu.add(a[i]);
	// }
}

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
			case '-': menu.push('-'); break;
			default:
				idx = CB.DB.templates.findExact('id', parseInt(menuConfig[i]));
				if(idx >=0){
					tr = CB.DB.templates.getAt(idx);
					menu.push({
						text: tr.get('title')
						,iconCls: tr.get('iconCls')
						,scope: scope
						,handler: handler
						,data: {
							template_id: tr.get('id')
							,type: tr.get('type')
							,title: tr.get('title')
						}
					});

				}
			break;

		}

	for(i = 0; i < menu.length; i++) menuButton.menu.add(menu[i]);
}
function getMenuConfig(node_id, ids_path, node_template_id){
	lastWeight = 0
	menuConfig = '';
	CB.DB.menu.each( function(r){
		weight = 0;
		ug_ids = ',' + String(r.get('user_group_ids')).replace(' ','') + ',';
		if(ug_ids.indexOf(','+App.loginData.id+',') >=0) weight++;
		
		nt_ids = ',' + String(r.get('node_template_ids')).replace(' ','') + ',';
		if(nt_ids.indexOf(','+node_template_id+',') >=0) weight++;
		
		n_ids = ',' + String(r.get('node_ids')).replace(' ','') + ',';
		if(n_ids.indexOf(','+node_id+',') >=0) weight += 2;

		if(weight >= lastWeight){
			lastWeight = weight;
			menuConfig = r.get('menu');
		}else{
			if(!Ext.isEmpty(ids_path)){// find parents menu
				ids = String(ids_path).split('/');
				for (var i = ids.length -1; i > 0; i--) {
					if(n_ids.indexOf(','+ids[i]+',') >=0){
						weight ++;
						if(weight >= lastWeight){
							lastWeight = weight;
							menuConfig = r.get('menu');
						}
						i = -1;
					}
				};
			}
		}
	})
	return menuConfig;
}