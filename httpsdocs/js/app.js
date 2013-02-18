// JavaScript Document
Ext.namespace('App'); 

Ext.BLANK_IMAGE_URL = '/css/i/s.gif';

clog = function(){if(typeof(console) != 'undefined') console.log(arguments)}
// application main entry point
Ext.onReady(function(){
	Ext.state.Manager.setProvider( new Ext.state.CookieProvider({
		expires: new Date(new Date().getTime()+(1000*60*60*24*7)) //7 days from now
	}));
	
	initApp();

	Ext.Direct.addProvider(Ext.app.REMOTING_API); 
	
	Ext.Direct.on('login', function(r, e){ /*if(r.method == 'logout') /**/ window.location.reload(); /*else App.PromtLogin(); /**/});
	Ext.Direct.on('exception', App.showException);
	Ext.QuickTips.init();
	Ext.apply(Ext.QuickTips.getQuickTip(), {showDelay: 1500});

	setTimeout(function(){
		Ext.get('loading').remove();
		Ext.fly('loading-mask').fadeOut({remove:true});
	}, 250);
	
	Auth.getLoginInfo( function(r, e){
		if(r.success){
			App.config = r.config;
			App.loginData = r.user;
			App.loginData.iconCls = 'icon-user-' + Ext.value(r.user.sex, '');
			if(App.loginData.short_date_format) App.dateFormat = App.loginData.short_date_format;
			if(App.loginData.long_date_format) App.longDateFormat = App.loginData.long_date_format;
			App.mainViewPort = new CB.ViewPort();
			App.mainViewPort.doLayout();
			App.mainToolBar = App.mainViewPort.items.get(0);
			App.mainTabPanel = App.mainViewPort.items.get(1);
			App.mainViewPort.fireEvent('login', r, e);
		} 
	});
});  

//--------------------------------------------------------------------------- application initialization function
function initApp(){
	overrides(); 
	App.dateFormat = 'd.m.Y';
	App.longDateFormat = 'j F Y';
	App.timeFormat = 'H:i';
	
	App.shortenString = function (st, maxLen) {
		if(Ext.isEmpty(st)) return '';
		st = Ext.util.Format.stripTags(st);
		return Ext.util.Format.ellipsis(st, maxLen);
		//return st.length > maxLen ? st.substr(0, maxLen) + '&hellip;' : st;
	}

	App.PromtLogin = function (e){
		if(!this.loginWindow || this.loginWindow.isDestroyed)
			this.loginWindow = new CB.Login({});
		this.loginWindow.show();
	}

	App.formSubmitFailure = function(form, action){
		if(App.hideFailureAlerts) return;
		switch (action.failureType) {
		    case Ext.form.Action.CLIENT_INVALID:
			Ext.Msg.alert(L.Error, 'Form fields may not be submitted with invalid values'); break;
		    case Ext.form.Action.CONNECT_FAILURE:
			Ext.Msg.alert(L.Error, 'Ajax communication failed'); break;
		    case Ext.form.Action.SERVER_INVALID:
		       msg = Ext.value(action.msg, action.result.msg);
		       msg = Ext.value(msg, L.ErrorOccured);
		       Ext.Msg.alert(L.Error, msg);
	       }
	}
	
	App.includeJS = function(file){
	   if (document.createElement && document.getElementsByTagName) {
		 var head = document.getElementsByTagName('head')[0];
	
		 var script = document.createElement('script');
		 script.setAttribute('type', 'text/javascript');
		 script.setAttribute('src', file);
	
		 head.appendChild(script);
	   } else {
			alert('Your browser can\'t deal with the DOM standard. That means it\'s old. Go fix it!');
	   }
 	}

	App.xtemplates = {
		cell: new Ext.XTemplate( '<ul class="thesauri_set"><tpl for="."><li>{.}</li></tpl></ul>' )
		,object: new Ext.XTemplate( '<ul><tpl for="."><li class="case_object" object_id="{id}">{[Ext.isEmpty(values.title) ? \'&lt;'+L.noName+'&gt; (id: \'+values.id+\')\' : values.title]}</li></tpl></ul>' )
	}
	App.xtemplates.cell.compile();
	App.xtemplates.object.compile();
	
	App.customRenderers = {
		thesauriCell: function(v, metaData, record, rowIndex, colIndex, store) {
			if(Ext.isEmpty(v)) return '';
			va = v.split(',');
			v = [];
			thesauri_id = record.get('cfg').thesauri_id;
			if(Ext.isEmpty(thesauri_id) && store.thesauri_ids) thesauri_id = store.thesauri_ids[record.id]
			if(!Ext.isEmpty(thesauri_id)){
				ts = getThesauriStore(thesauri_id);
				ts.each(function(r){if(va.indexOf(r.get('id')) >=0 ) v.push(r.get('name')); });
			}
			return App.xtemplates.cell.apply(v);
		}
		,relatedCell: function(v, metaData, record, rowIndex, colIndex, store) { }
		,combo: function(v, metaData, record, rowIndex, colIndex, store) { /* custom renderer for verticalEditGrid */
			if(Ext.isEmpty(v)) return '';
			ed = this.editor;
			ri = ed.store.findExact(ed.valueField, v);
			if(ri < 0) return '';
			return ed.store.getAt(ri).get(ed.displayField)
  		}
		/*,organizationCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) { 
			if(Ext.isEmpty(v)) return '';
			cw = grid.findParentByType(CB.Case);
			if(!cw || !cw.organizationsStore) return '';
			ri = cw.organizationsStore.findExact('id', parseInt(v));
			if(ri < 0) return '';
			return cw.organizationsStore.getAt(ri).get('name');
  		}/**/
		,objectCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) { /* custom renderer for verticalEditGrid */
			if(Ext.isEmpty(v)) return '';
			cw = grid.findParentByType(CB.Objects);
			if(!cw || !cw.objectsStore) return '';
			r = [];
			if(!Ext.isArray(v)) v = String(v).split(',');
			
			switch(record.data.cfg.renderer){
				case 'listGreenIcons':
					for(i=0; i < v.length; i++){
						ri = cw.objectsStore.findExact('id', parseInt(v[i]));
						row = cw.objectsStore.getAt(ri);
						if(ri >-1) r.push('<li class="icon-padding icon-element">'+row.get('title')+'</li>');
					}
					return '<ul>'+r.join('')+'</ul>';
					break;
				case 'listObjIcons': 
					for(i=0; i < v.length; i++){
						ri = cw.objectsStore.findExact('id', parseInt(v[i]));
						row = cw.objectsStore.getAt(ri);
						if(ri >-1) r.push('<li class="icon-padding '+row.get('iconCls')+'">'+row.get('title')+'</li>');
					}
					return '<ul>'+r.join('')+'</ul>';
					break;
				
				default: 
					for(i=0; i < v.length; i++){
						ri = cw.objectsStore.findExact('id', parseInt(v[i]));
						if(ri >-1) r.push(cw.objectsStore.getAt(ri).get('title'));
					}
					return r.join(', ');
			}
  		}
  		,objectsField: function(v, metaData, record, rowIndex, colIndex, store, grid) { /* custom renderer for verticalEditGrid */
			clog('incomming v', v);
			if(Ext.isEmpty(v)) return '';
			
			r = [];
			store = null;
			if(!Ext.isArray(v)) v = String(v).split(',');
			if( Ext.isEmpty(record.get('cfg').source) || (record.get('cfg').source == 'thesauri') ){
				store = getThesauriStore(record.get('cfg').thesauriId);
			}else{
				cw = grid.findParentByType(CB.Objects);
				if(!cw || !cw.objectsStore) return '';
				store = cw.objectsStore;
			}
			clog(v, store);
			switch(record.data.cfg.renderer){
				case 'listGreenIcons':
					for(i=0; i < v.length; i++){
						ri = store.findExact('id', parseInt(v[i]));
						row = store.getAt(ri);
						if(ri >-1) r.push('<li class="lh16 icon-padding icon-element">'+row.get('name')+'</li>');
					}
					return '<ul>'+r.join('')+'</ul>';
					break;
				case 'listObjIcons': 
					for(i=0; i < v.length; i++){
						ri = store.findExact('id', parseInt(v[i]));
						row = store.getAt(ri);
						if(ri >-1) r.push('<li class="lh16 icon-padding '+row.get('iconCls')+'">'+row.get('name')+'</li>');
					}
					return '<ul>'+r.join('')+'</ul>';
					break;
				
				default: 
					for(i=0; i < v.length; i++){
						ri = store.findExact('id', parseInt(v[i]));
						if(ri >-1) r.push(store.getAt(ri).get('name'));
					}
					return r.join(', ');
			}

  		}
		,languageCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) { /* custom renderer for verticalEditGrid */
			if(Ext.isEmpty(v)) return '';
			ri = CB.DB.languages.findExact('id', parseInt(v));
			if(ri < 0) return '';
			return CB.DB.languages.getAt(ri).get('name');
  		}
		,sexCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) { /* custom renderer for verticalEditGrid */
			if(Ext.isEmpty(v)) return '';
			ri = CB.DB.sex.findExact('id', v);
			if(ri < 0) return '';
			return CB.DB.sex.getAt(ri).get('name');
  		}
		,shortDateFormatCombo: function(v, metaData, record, rowIndex, colIndex, store, grid) { /* custom renderer for verticalEditGrid */
			if(Ext.isEmpty(v)) return '';
			ri = CB.DB.shortDateFormats.findExact('id', v);
			if(ri < 0) return '';
			return CB.DB.shortDateFormats.getAt(ri).get('name');
  		}
		,thesauriCombo: function(v, metaData, record, rowIndex, colIndex, store) { /* custom renderer for verticalEditGrid */
			if(Ext.isEmpty(v)) return '';
			th = record.get('cfg').thesauri_id;
			if(th == 'variable'){
				pri = store.findBy(function(r){return ( (r.get('field_id') == record.get('pid')) && (r.get('duplicate_id') == record.get('duplicate_id')) );}, this);
				if(pri > -1) th = store.getAt(pri).get('value');
			}
			ts = getThesauriStore(th);
			ri = ts.findExact('id', v);
			if(ri < 0) return '';
			return ts.getAt(ri).get('name')
  		}
		,checkbox: function(v){
		  	if(v == 1) return L.yes;
		  	if(v == -1) return L.no;
		  	return '';
		}
		,date: function(v){
		  	if(Ext.isEmpty(v)) return '';
		  	return ( v.format ? v.format(App.dateFormat) : Date.parseDate(v.substr(0,10), 'Y-m-d').format(App.dateFormat));
		}
		,datetime: function(v){
		  	if(Ext.isEmpty(v)) return '';
		  	if(Ext.isPrimitive(v)) v = date_ISO_to_date(v);
		  	s = v.toISOString();
		  	if(s.substr(-14) == 'T00:00:00.000Z') v = v.clearTime(true);
		  	d = v.format(App.dateFormat+' '+App.timeFormat);
	  		if(Ext.isEmpty(d)) return '';
	  		if(d.substr(-5) == '00:00') d = d.substr(0,10);
	  		return d;
		}
		,time: function(v){
		  	if(v && Ext.isPrimitive(v)) return v;
		  	t = '';
			if(!Ext.isEmpty(v.hours)){
				t = v.hours;
				switch(v.hours){
					case 1: t = t + ' '+L.hour; break; 
					case 2:
					case 3: 
					case 4: t = t + ' '+L.ofHour; break; 
					case 5: t = t + ' '+L.ofHours; break; 
				}
			}
			if(!Ext.isEmpty(v.minutes)){
				t = t + ' ' + v.minutes;
				switch(v.minutes){
					case 1: t = t + ' ' + L.minute; break; 
					case 2: 
					case 3: 
					case 4: t = t + ' ' + L.ofMinute; break; 
					case 5: t = t + ' ' + L.ofMinutes; break; 
				}
			}
			return t;
		}
		,filesize: function(v){
			if(isNaN(v) || Ext.isEmpty(v) || (v == 0)) return '';
			if(v <= 0) return  '0 KB'; 
			else if(v < 1024) return '1 KB';
			else if(v < 1024 * 1024) return (Math.round(v / 1024) + ' KB');
			else{
				n = v / (1024 * 1024);
				return (n.toFixed(2) + ' MB');
			}
		}
		,tags: function(v, m, r, ri, ci, s){
			if(Ext.isEmpty(v)) return '';
			rez = [];
			Ext.each(v, function(i){rez.push(i.name);}, this);
			rez = rez.join(', ');
			m.attr = 'title="' + rez.replace('"', '&quot;') + '"';
			return rez;
		}
		,tagIds: function(v){
			if(Ext.isEmpty(v)) return '';
			rez = [];
			v = String(v).split(',')
			Ext.each(v, function(i){rez.push(CB.DB.thesauri.getName(i))}, this);
			rez = rez.join(', ');
			return rez;
		}
		,taskImportance: function(v){
			if(Ext.isEmpty(v)) return '';
			return CB.DB.tasksImportance.getName(v);
		}
		,taskStatus: function(v, m, r, ri, ci, s){
			if(Ext.isEmpty(v)) return '';
			//m.css = 'taskStatus'+v;
			return '<span class="taskStatus'+v+'">'+L['taskStatus'+parseInt(v)]+'</span>';
			/*switch(v){
				case 1:
				case '1':
					return L.Processing;
					break;
				case 2:
				case '2':
					return L.Closed
					break;
				default: return L.Pending
			}/**/
		}
	}
	App.getTemplatesXTemplate = function(template_id){
		template_id = String(template_id);
		if(!Ext.isDefined(App.templatesXTemplate)) App.templatesXTemplate = {};
		if(App.templatesXTemplate[template_id]) return App.templatesXTemplate[template_id];
		idx = CB.DB.templates.findExact('id', template_id);
		if(idx >= 0){
			r = CB.DB.templates.getAt(idx);
			it = r.get('info_template');
			if(!Ext.isEmpty(it)){
				App.templatesXTemplate[template_id] = new Ext.XTemplate(it);
				App.templatesXTemplate[template_id].compile();
				return App.templatesXTemplate[template_id];
			}
		}
		return App.xtemplates.object;
	}
	App.findTab = function(tabPanel, id){
		tabIdx = -1;
		if(Ext.isEmpty(id)) return tabIdx;
		i= 0;
		while((tabIdx == -1) && (i < tabPanel.items.getCount())){
			o = tabPanel.items.get(i);
			if(Ext.isDefined(o.data) && Ext.isDefined(o.data.id) && (o.data.id == id)) tabIdx = i;
			i++;
		}
		return tabIdx;
	}
	App.findTabByType = function(tabPanel, type){
		tabIdx = -1;
		if(Ext.isEmpty(type)) return tabIdx;
		i= 0;
		while((tabIdx == -1) && (i < tabPanel.items.getCount())){
			o = tabPanel.items.get(i);
			if(Ext.isDefined(o.isXType) && o.isXType(type)) tabIdx = i;
			i++;
		}
		return tabIdx;
	}
	App.activateTab = function(tabPanel, id){
		tabIdx = App.findTab(tabPanel, id);
		if(tabIdx < 0) return false;
		tabPanel.setActiveTab(tabIdx);
		return tabPanel.items.itemAt(tabIdx);
	}
	App.addTab = function(tabPanel, o){
		c = tabPanel.add(o);
		o.show();
		return c;
	}
	App.getFileUploadWindow = function(config){
		if(!App.thetFileUploadWindow) App.theFileUploadWindow = new CB.FileUploadWindow();
		App.theFileUploadWindow = Ext.apply(App.theFileUploadWindow, config);
		return App.theFileUploadWindow;
	}
	App.getThesauriWindow = function(config){
		if(!App.thesauriWindow) App.thesauriWindow = new CB.ThesauriWindow();
		App.thesauriWindow = Ext.apply(App.thesauriWindow, config);
		return App.thesauriWindow;
	}
	/*App.onHideThesauriWindow = function(w){
		if(w.focusHandler) w.focusHandler();
		w.un('hide', App.onHideThesauriWindow, this);
	}/**/
	App.getTextEditWindow = function(config){
		if(!App.textEditWindow) App.textEditWindow = new CB.TextEditWindow();
		App.textEditWindow = Ext.apply(App.textEditWindow, config);
		return App.textEditWindow;
	}
	App.getHtmlEditWindow = function(config){
		if(!App.htmlEditWindow) App.htmlEditWindow = new CB.HtmlEditWindow();
		App.htmlEditWindow = Ext.apply(App.htmlEditWindow, config);
		return App.htmlEditWindow;
	}
	App.openCase = function(id, options){ 
		if(Ext.isElement(options)){
			//click is catched from a html element
			return App.mainViewPort.openCase({iconCls: 'icon-briefcase', data: {id: options.id}});
		}
		options = Ext.apply({iconCls: 'icon-briefcase', data: {id: id}}, options)
		return App.mainViewPort.openCase(options);
	}; // shortcut
	App.openObject = function(o, el){ 
		if(Ext.isElement(el)) o = el.id; //retreive id from html element
		if(Ext.isPrimitive(o)){
			Cases.getCaseId({object_id: o}, function(r, e){ if(r.success !== true) return; App.openCase(r.data.id, {selectActionId: o})}, this);
		}else{
			el = Ext.get(o); 
			o = el.dom.attributes.getNamedItem('href').nodeValue.substr(1);
			Ext.getCmp(el.id).findParentByType(CB.Case).openObject(o);
		}
	}
	App.downloadFile = function(fileId, zipped, versionId){
		if(Ext.isElement(fileId)){ //retreive id from html element
			fileId = fileId.id;
			zipped = false;
		}
		url = 'download.php?id='+fileId;
		if(!Ext.isEmpty(versionId)) url += '&v='+versionId;
		if(zipped == true) url += '&z=1';
		window.open(url, '_blank') 
	}
	App.getTypeEditor = function(type, e){
		objData = {
			ownerCt: e.ownerCt
			,record: e.record
			,grid: e.grid
			,pidValue: e.pidValue

		}
		switch(type){
			case '_auto_title':
				return new Ext.ux.TitleField({minWidth: 100, anchor: '90%', boxMaxWidth: 800}); break;
			case '_objects':
				//e should contain all necessary info
				switch(e.record.get('cfg').editor){
					case 'form': 
						if(e && e.grid){//clog('form field in grid');
							e.cancel = true;
							/* prepeare data to set to popup windows */
							store = false;
							if( Ext.isEmpty(e.record.get('cfg').source) || (e.record.get('cfg').source == 'thesauri') ){
								store = getThesauriStore(e.record.get('cfg').thesauriId);
							}else{
								cw = e.grid.findParentByType(CB.Objects);
								if(cw && cw.objectsStore)  store = cw.objectsStore;
							}
							data = []
							if(store){
								value= e.record.get('value')
								value = Ext.isEmpty(value) ? [] : String(value).split(',');
								for(i=0; i < value.length; i++){
									ri = store.findExact('id', parseInt(value[i]));
									if(ri >-1) data.push(store.getAt(ri).data);
								}
							}

							if( Ext.isEmpty(e.record.get('cfg').source) || (e.record.get('cfg').source == 'thesauri') ) w = new CB.ObjectsSelectionPopupList({data: objData, value: e.record.get('value')});
							else{
								w = new CB.ObjectsSelectionForm({data: objData, value: e.record.get('value')});
							}
							w.on('setvalue', function(data){
								value = []
								if(Ext.isArray(data)) Ext.each(data, function(d){ value.push( d.id ? d.id : d)}, this)
								value = value.join(',');
								e.record.set('value', value);
								this.grid.fireEvent('change');
							}, e)
							if(w.setData) w.setData(data);
							w.show();
							return w;
						}else return new CB.ObjectsTriggerField({data: objData, width: 500}); //clog('form field out of grid -> objects trigger field');
						// if(Ext.isEmpty(e.grid)) return new Ext.form.ObjectsFormField({ownerCt: e.ownerCt, width: 500}); //when it is in top fieldset
						// return new Ext.form.ObjectsTriggerField({});
						break;
					// case 'popuplist':
					// 	if(e && e.grid){ clog('popuplist in grid');
					// 		e.cancel = true;
					// 		w = new CB.ObjectsSelectionForm({data: objData});
					// 		w.show();
					// 		return w;
					// 	}else return new CB.ObjectsTriggerField({data: objData, width: 500}); //clog('popuplist out of grid -> objects trigger field');
					// 	break;
					default:
						return new CB.ObjectsComboField({data: objData, width: 500});
						// if(e && e.grid) clog('objects combo in grid');
						// else clog('objects combo out of grid');
						break;
				}
				// if(e.record.get('cfg').editor == 'form'){
					
				// }else{
				// 	params = Ext.apply({}, e.record.get('cfg'));
				// 	if(!Ext.isEmpty(e.pidValue)) params.pidValue = e.pidValue;
				// 	return new Ext.form.ObjectsField({ownerCt: e.ownerCt, params: params, width: 500})
				// // }
				break;
			case '_case':
				if(e.record.get('cfg').editor == 'form'){
					if(!Ext.isEmpty(e.ownerCt)) return new Ext.ux.AssociateCasesField({ownerCt: e.ownerCt, width: 500}); //when it is in top fieldset
				}else{
					params = Ext.apply({}, e.record.get('cfg'));
					if(!Ext.isEmpty(e.pidValue)) params.pidValue = e.pidValue;
					return new Ext.ux.CasesCombo({ownerCt: e.ownerCt, params: params, width: 500})
				}
				break;
			case '_case_object':
				if(e.record.get('cfg').editor == 'form'){
					if(!Ext.isEmpty(e.ownerCt)) return new Ext.ux.AssociateObjectsField({ownerCt: e.ownerCt, width: 500}); //when it is in top fieldset
				}else{
					params = Ext.apply({}, e.record.get('cfg'));
					if(!Ext.isEmpty(e.pidValue)) params.pidValue = e.pidValue;
					return new Ext.ux.ObjectsCombo({ownerCt: e.ownerCt, params: params, width: 500})
				}
				break;
			case 'boolean': //depricated
			case 'checkbox': return new Ext.form.ComboBox({
						xtype: 'combo'
						,forceSelection: true
						,triggerAction: 'all'
						,lazyRender: true
						,mode: 'local'
						,editable: false
						,store: CB.DB.yesno
						,displayField: 'name'
						,valueField: 'id'
					});
			case 'object_violation': return new Ext.form.Checkbox({inputValue: 1}); break;
			case 'date':  return new Ext.form.DateField({format: App.dateFormat, width: 100}); break;
			case 'datetime': return new Ext.form.DateField({format: App.dateFormat+' '+App.timeFormat, width: 130}); break;
			case 'time':  return new Ext.form.TimeField({format: App.timeFormat}); break;
			case 'int':  return new Ext.form.NumberField({allowDecimals: false, width: 90}); break;
			case 'float':  return new Ext.form.NumberField({allowDecimals: true, width: 90}); break;
			//case 'object_author': //depricated
			case 'combo':
				th = e.record.get('cfg').thesauri_id;
				if(th == 'variable'){
					pri = e.record.store.findBy(function(r){return ( (r.get('field_id') == e.record.get('pid')) && (r.get('duplicate_id') == e.record.get('duplicate_id')) );}, this);
					if(pri > -1) th = e.record.store.getAt(pri).get('value');
				}
				return new Ext.form.ComboBox({
					forceSelection: true
					,triggerAction: 'all'
					,lazyRender: true
					,mode: 'local'
					//,editable: false
					,store: getThesauriStore(th)
					,displayField: 'name'
					,typeAhead: true
					,valueField: 'id'
				})
				break;
			case 'iconcombo':
				th = e.record.get('cfg').thesauri_id;
				if(th == 'variable'){
					pri = e.record.store.findBy(function(r){return ( (r.get('field_id') == e.record.get('pid')) && (r.get('duplicate_id') == e.record.get('duplicate_id')) );}, this);
					if(pri > -1) th = e.record.store.getAt(pri).get('value');
				}
				return new Ext.form.ComboBox({
					forceSelection: true
					,triggerAction: 'all'
					,lazyRender: true
					,mode: 'local'
					,store: getThesauriStore(th)
					,displayField: 'name'
					,typeAhead: true
					,valueField: 'id'
					,iconClsField: 'name'
					,plugins: [new Ext.ux.plugins.IconCombo()]
				})
				break;
			case '_language':
				return new Ext.form.ComboBox({
					forceSelection: true
					,triggerAction: 'all'
					,lazyRender: true
					,mode: 'local'
					,editable: false
					,store: CB.DB.languages
					,displayField: 'name'
					,valueField: 'id'
				})
				break;
			case '_sex':
				return new Ext.form.ComboBox({
					forceSelection: true
					,triggerAction: 'all'
					,lazyRender: true
					,mode: 'local'
					,editable: false
					,store: CB.DB.sex
					,displayField: 'name'
					,valueField: 'id'
				})
				break;
			case '_short_date_format':
				return new Ext.form.ComboBox({
					forceSelection: true
					,triggerAction: 'all'
					,lazyRender: true
					,mode: 'local'
					,editable: false
					,store: CB.DB.shortDateFormats
					,displayField: 'name'
					,valueField: 'id'
				})
				break;
			case 'memo':  
				height = Ext.value(e.record.get('cfg').height, 50);
				height = parseInt(height) + 7;
				return new Ext.form.TextArea({ height: height })
				//e.cancel = true;
				break;
			case 'popuplist':
				e.cancel = true;
				w = App.getThesauriWindow({
					title: e.record.get('title')
					,store: getThesauriStore( e.record.get('cfg').thesauri_id )
					,data: {
						value: e.record.get('value')
						,scope: e
						,callback: function(w, v){ 
							this.record.set('value', v); 
							this.value = v;
							if(this.grid.onAfterEditProperty) this.grid.onAfterEditProperty(this);
							this.grid.fireEvent('change'); 
						}
					}
				});
				w.focusHandler = Ext.value(this.gainFocus, e.grid.gainFocus);
				//w.on('hide', App.onHideThesauriWindow, e.grid);
				w.show();
				break;
			case 'text':
				e.cancel = true;
				w = App.getTextEditWindow( { title: e.record.get('title'), data: { value: e.record.get('value'), scope: e, callback: function(w, v){this.record.set('value', v); this.grid.fireEvent('change'); } } } );
				w.on('hide', e.grid.gainFocus, e.grid);
				w.show();
				break;
			case 'html':
				e.cancel = true;
				w = App.getHtmlEditWindow( { title: e.record.get('title'), data: { value: e.record.get('value'), scope: e, callback: function(w, v){ this.record.set('value', v); this.grid.fireEvent('change');} } } );
				if(!Ext.isEmpty(e.grid)) w.on('hide', e.grid.gainFocus, e.grid);
				w.show();
				break;
			default:  return new Ext.form.TextField({width: 500}); break;
		}
		return false;
	}
	App.focusFirstField = function(scope){
		scope = Ext.value(scope, this);
		f = function(){ 
			a = [];
			if(scope.find) a = scope.find('isFormField', true); 
			if(a.length == 0) return; 
			found = false;
			i = 0;
			while( !found && (i<a.length) ){
				found = ( !Ext.isEmpty(a[i]) && !Ext.isEmpty(a[i].isXType) && !a[i].isXType('radiogroup') && (a[i].hidden !== true) );
				i++;
			}
			if(!found) return;
			c = a[i-1];
			if(c.isXType('compositefield'))  c = c.items.first(); 
			c.focus();
		}
		f.defer(500, scope);
	}
	App.successResponse = function(r){
		if(r.success == true) return true;
		Ext.Msg.alert(L.Error, Ext.value(r.msg, L.ErrorOccured));
		return false;
	}
	App.showTestingWindow =function(){
		if(!App.testWindow) App.testWindow = new CB.TestingWindow({ closeAction: 'hide' });
		App.testWindow.show();
	}
	App.openUniqueTabbedWidget = function(type, tabPanel, options){//experimenting
		if(Ext.isEmpty(tabPanel)) tabPanel = App.mainTabPanel;
		tabIdx = App.findTabByType(tabPanel, type);
		if(Ext.isEmpty(options)) options = {}
		if(tabIdx < 0) {
			w = Ext.create(options, type);
			App.addTab(tabPanel, w);
			tabPanel.setActiveTab(w);
		}else tabPanel.setActiveTab(tabIdx);
	}
	App.showException = function(e){ 
		msg = '';
		if(e) msg = e.msg;
		if(!msg && e.result) msg = e.result.msg;
		if(!msg && e.result) msg = L.ErrorOccured;
		Ext.Msg.alert(L.Error, msg); 
	}

	App.openObject = function(type, id, e){
		switch(type){
			// case 2:  //link
			// 	break;
			case 4:
			case 8:
				App.mainViewPort.fireEvent('openobject', {id: id}, e);
				break;
			case 5:
				App.mainViewPort.fireEvent('fileopen', {id: id}, e);
				break;
			case 6: 
			case 7: 
				App.mainViewPort.fireEvent('taskedit', { data:{id: id} }, e);
				break;
			default: return false; break;
		}
		return true;
	}
	App.setsGetIntersection = function(set1, set2){
		if(Ext.isEmpty(set1) || Ext.isEmpty(set2)) return false;
		if(Ext.isPrimitive(set1)) set1 = String(set1).split(',');
		if(Ext.isPrimitive(set2)) set2 = String(set2).split(',');
		rez = [];
		for (var i = 0; i < set1.length; i++) if( (set2.indexOf(set1[i]) >= 0) && (rez.indexOf(set1[i]) < 0 )) rez.push(set1[i]);
		for (var i = 0; i < set2.length; i++) if( (set1.indexOf(set2[i]) >= 0) && (rez.indexOf(set2[i]) < 0 )) rez.push(set2[i]);
		return rez;
	}
	App.setsHaveIntersection = function(set1, set2){
		return !Ext.isEmpty(App.setsGetIntersection(set1, set2));
	}
	App.clipboard = new CB.Clipboard();
	/* disable back button */
	Ext.EventManager.on(Ext.isIE ? document : window, 'keydown', function(e, t) {
	    if (e.getKey() == e.BACKSPACE && ((!/^input$/i.test(t.tagName) && !/^textarea$/i.test(t.tagName)) || t.disabled || t.readOnly)) {
	        e.stopEvent();
	    }
	});
	/* disable back button */

}

function overrides(){
	Ext.override(Ext.Window, {
		setIconCls: function(i){
			Ext.fly(this.ownerCt.getTabEl(this)).child('.x-tab-strip-text').replaceClass(this.iconCls, i);
			this.setIconClass(i);
		}
	});

	/* Overrides for preventing nodes selection when start dragging node  */
	Ext.override(Ext.tree.TreeDragZone, {
	    lastClickAt: null,
	    b4MouseDown : function(e){
	        var sm = this.tree.getSelectionModel();
	        this.lastClickAt = e.getXY();
	        if(sm != null)
	            sm.suspendEvents(true);
	        Ext.tree.TreeDragZone.superclass.b4MouseDown.apply(this, arguments);
	    }
	});
	 
	Ext.override(Ext.tree.TreeDragZone, {
	    onMouseUp : function(e){
	        var sm = this.tree.getSelectionModel();
	        var loc = e.getXY();
	        if(sm != null && (this.lastClickAt == null || (this.lastClickAt[0] == loc[0] && this.lastClickAt[1] == loc[1])) )
	            sm.resumeEvents();
	        else{
	            sm.clearEventQueue();
	            sm.resumeEvents();        
	        }
	        Ext.tree.TreeDragZone.superclass.onMouseUp.apply(this, arguments);
	    }
	}); 

	Ext.override(Ext.tree.DefaultSelectionModel, {
	    clearEventQueue : function() {
	        var me = this;
	        delete me.eventQueue;
	    }
	});
	
	/* prevend deselecting of selected when rightClicking in a RowSelectionModel*/
	Ext.grid.RowSelectionModel.prototype.selectRow = function(index, keepExisting, preventViewNotify){
	        if(this.isLocked() || (index < 0 || index >= this.grid.store.getCount()) || (keepExisting && this.isSelected(index))){
	            return;
	        }
	        var r = this.grid.store.getAt(index);
	        if(r && this.fireEvent('beforerowselect', this, index, keepExisting, r) !== false){
	            if(!keepExisting || this.singleSelect){
	                this.clearSelections();
	            }
	            this.selections.add(r);
	            this.last = this.lastActive = index;
	            if(!preventViewNotify){
	                this.grid.getView().onRowSelect(index);
	            }
	            if(!this.silent){
	                this.fireEvent('rowselect', this, index, r);
	                this.fireEvent('selectionchange', this);
	            }
	        }
	    }

	Ext.grid.RowSelectionModel.prototype.handleMouseDown = function(g, rowIndex, e){
		if(e.button !== 0 || this.isLocked()) return;
		var view = this.grid.getView();
		if(e.shiftKey && !this.singleSelect && this.last !== false){
		    var last = this.last;
		    this.selectRange(last, rowIndex, e.ctrlKey);
		    this.last = last; // reset the last
		    view.focusRow(rowIndex);
		}else{
		    var isSelected = this.isSelected(rowIndex);
		    if(e.ctrlKey && isSelected){
		        this.deselectRow(rowIndex);
		    }else if(!isSelected || this.getCount() > 1){
		        this.selectRow(rowIndex, e.ctrlKey || e.shiftKey);
		        view.focusRow(rowIndex);
		    }
		}
	}

	Ext.calendar.CalendarPanel.prototype.todayText = L.Today;
	Ext.calendar.CalendarPanel.prototype.dayText = L.Day;
	Ext.calendar.CalendarPanel.prototype.weekText = L.Week;
	Ext.calendar.CalendarPanel.prototype.monthText = L.Month;
	Ext.calendar.MonthView.prototype.todayText = L.Today;
	Ext.calendar.DayView.prototype.todayText = L.Today;
	Ext.calendar.DateRangeField.prototype.toText = L.to;
	Ext.calendar.DateRangeField.prototype.allDayText = L.AllDay;


}