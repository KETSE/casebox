// JavaScript Document
function isEmptyObject(ob){
   for(var i in ob){ if(ob.hasOwnProperty(i)){return false;}}
  return true;
}

function date_ISO_to_date(date_string){
	if(Ext.isEmpty(date_string)) return null;
	d = Date.parse(date_string);
	if(Ext.isEmpty(d)) return null;
	return new Date(d);
}
function getItemIcon(d){
	
	if(Ext.isEmpty(d.template_id)){
		if(d['type'] == 2) return 'icon-shortcut';
		return d.iconCls;
	}
	switch( CB.DB.templates.getType(d.template_id) ){
		case 'file': return getFileIcon(d['name']); break;
		case 'task':
			if(d['status'] == 3) return 'icon-task-completed';
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
					data = {
							template_id: tr.get('id')
							// ,type: tr.get('type')
							,title: tr.get('title')
					}
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

function getMenuConfig(node_id, ids_path, node_template_id){
	lastWeight = 0
	menuConfig = '';
	CB.DB.menu.each( function(r){
		weight = 0;
		/*check user_group ids */
		ug_ids = ',' + String(Ext.value(r.get('user_group_ids'), '') ).replace(' ','') + ',';
		if(ug_ids.indexOf(','+App.loginData.id+',') >=0) weight += 100; 
		else{
			if( ug_ids != ',,') return;//TODO: check if user is member of a specied group
			weight += 50;
		}
		/*end of check user_group ids */
		
		/* check template_ids /**/ 
		if(!Ext.isEmpty(node_template_id)){
			nt_ids = ',' + String( Ext.value(r.get('node_template_ids'), '') ).replace(' ','') + ',';
		
			if(nt_ids.indexOf(','+node_template_id+',') >=0) weight += 100;
			else{
				if( nt_ids != ',,') return;
				weight += 50;
			}
		}else if(!Ext.isEmpty(r.get('node_template_ids'))) return;
		
		n_ids = ',' + String( Ext.value(r.get('node_ids'), '') ).replace(' ','') + ',';
		if( n_ids.indexOf(','+node_id+',') >=0 ) weight += 100;
		else{
			if( n_ids == ',,') weight += 50;
			else{ /*check the nearest parents from path */
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
			}
		}

		if(weight >= lastWeight){
			lastWeight = weight;
			menuConfig = r.get('menu');
		}
	}, this)
	return menuConfig;
}