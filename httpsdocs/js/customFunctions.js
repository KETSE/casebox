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
			return Ext.value( CB.DB.templates.getIcon(d['template_id']), 'icon-none' );
			break;
		case 5: //file
			ext = String(d['name']).split('.');
			ext = ext.pop().toLowerCase();
			if([ 'pdf','doc', 'docx', 'rtf','ppt','pptx','txt','xls','xlsx','htm', 'html','mp3', 'rm','avi','bmp','gif', 'jpg','jpeg','jp2','j2k','pcx','png','ppm','tga','tif','tiff','flv'].indexOf(ext) >= 0) 
				return 'file-'+ ext;
				else return 'file-unknown';
			break;
		case 6:
			//clog('get task icon for "'+d.name+'", status:'+d.status)
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
	return 'file-'+ a.pop();
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
	return 'file-unknown32 file-'+ a.pop()+'32';
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