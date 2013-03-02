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
	switch(parseInt(d['type'])){
		case 0: return Ext.value(d['iconCls'], 'icon-folder'); //offices, year, month folders
			break; 
		case 1: 
			switch(parseInt(d['subtype'])){
				case 1:	break;
				case 2:	return 'icon-star'; break;
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

	}
}
//function getItemCls()
function getFileIcon(filename){
	if(Ext.isEmpty(filename)) return 'file-unknown';
	a = String(filename).split('.');
	if(a.length <2 ) return 'file-unknown';
	return 'file-'+ Ext.util.Format.lowercase(a.pop());
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
	a = []; //objects, in actions, out actions, applicants, subjects
	CB.DB.templates_per_tags.each(function(r){ 
		if(Ext.isEmpty(templatesFilter) || setsHaveIntersection(templatesFilter, r.get('template_id')) ){
			idx = CB.DB.templates.findExact('id', r.get('template_id'));
			tr = CB.DB.templates.getAt(idx);
			a.push({
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
}