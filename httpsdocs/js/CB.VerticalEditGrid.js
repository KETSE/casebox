Ext.namespace('CB'); 

CB.VerticalEditGrid = Ext.extend(Ext.grid.EditorGridPanel, {
	border: false
	,root: 'gridData'
	,cls: 'spacy-rows'
	,initComponent: function() {
		/*this.privacyCombo = new Ext.form.ComboBox({
			store: new Ext.data.JsonStore({
				autoDestroy: true
				,autoLoad: true
				,fields: ['id', 'name']
				,proxy: new Ext.data.MemoryProxy([{id: 0, name: L.forAll}, {id: 1, name: L.forOffice}, {id: 2, name: L.forMe}])
				})
			,typeAhead: true
			,triggerAction: 'all'
			,lazyRender:true
			,mode: 'local'
			,valueField: 'id'
			,displayField: 'name'
			,disabled: true
			,editable: false
			,width: 100
			,listeners: {scope: this, select: this.updateFieldPrivacy}
		});/**/
		tbar = [
			{iconCls: 'icon-table-insert-row', name: 'duplicateField', text: L.Add, disabled: true, handler: this.onDuplicateFieldClick, scope: this}
			,{iconCls: 'icon-table-delete-row', name: 'deleteDuplicatedField', text: L.Delete, disabled: true, handler: this.onDeleteDuplicatedFieldClick, scope: this}
			//,'-',{xtype: 'label', text: L.visible, style:'padding-right: 5px'}, this.privacyCombo
		]
		if(this.toolbarItems) Ext.each(this.toolbarItems, function(i){
				if(Ext.isDefined(i.position)) tbar.splice(i.position, 0, i);
				else tbar.push(i);
		});
		
		//fields = [{name:'id', type: 'int'}, {name: 'field_id', type: 'int'}, 'title', 'value', 'info', 'files', {name: 'pfu', type: 'int'}, {name: 'duplicate_id', type: 'int'}, {name: 'duplicate_pid', type: 'int'}, {name: 'pid', type: 'int'}, 'tag', {name: 'type', type: 'int'} , {name: 'thesauriId', type: 'int'}, {name: 'level', type: 'int'}, {name: 'visible', type: 'boolean'}, {name: 'maxInstances', type: 'int'}, 'showIn']
		fields = ['id', {name: 'field_id', type: 'int'}, 'title', 'value', 'info', 'files', {name: 'pfu', type: 'int'}, 'duplicate_id', 'duplicate_pid', {name: 'pid', type: 'int'}, 'tag', 'type', {name: 'level', type: 'int'}, {name: 'visible', type: 'int'}, 'cfg']
		this.fullStore = new Ext.data.JsonStore({
			fields: fields
			,reader: new Ext.data.JsonReader({ idProperty: 'id', messageProperty: 'msg' })
			,proxy: new Ext.data.MemoryProxy([])
			,listeners:{
				add: function(st, recs, idx){ 
					Ext.each(recs, function(r){
						v = r.get('cfg');
						if(Ext.isEmpty(v)) v = {}; 
						v.maxInstances = Ext.value(v.maxInstances, 1);
						v.showIn = Ext.value(v.showIn, 'grid');
						r.set('cfg', v);
					}, this);
				
				}
			}
		});
		
		gridColumns = [{
			header: L.Property
			,width: 250
			,dataIndex: 'title'
			,editable: false
			,scope: this
			,renderer : function(v, meta, record, row_idx, col_idx, store){
				if(record.get('tag') == 'H') meta.css ='fwB';
				else{
					meta.css = 'bgcLG vaT';
					meta.attr = 'style="margin-left: '+record.get('level')+'0px"';
				}
				if(!Ext.isEmpty(record.get('cfg').hint)) meta.attr += ' title="'+record.get('cfg').hint+'"';
				/* setting icon for duplicate fields /**/
				if(this.isDuplicateField(record)){
					//show duplicate index
					// inf last (and not exsceeded) then show + icon
					if(this.canDuplicateField(record) && this.isLastDuplicateField(record)) v = '<img name="add_duplicate" title="'+L.addDuplicateField+'" class="fr duplicate-plus" src="'+Ext.BLANK_IMAGE_URL + '" / >' + v;
					else{
						idx = this.getDuplicateFieldIndex(record) +1;
						v = '<img title="'+L.duplicate+' '+idx+'" class="fr duplicate'+idx+'" src="'+Ext.BLANK_IMAGE_URL + '" / >' + v;
					}
				}

				// if(!Ext.isEmpty(this.data)){
				// 	if(!Ext.isEmpty(this.data.duplicateFields)){
				// 		if((record.get('duplicate_id') != 0) 
				// 			&& !Ext.isEmpty(this.data.duplicateFields[record.get('field_id')]) 
				// 			&& !Ext.isEmpty(this.data.duplicateFields[record.get('field_id')][record.get('duplicate_id')])){
				// 			i = 1;
				// 			Ext.iterate(this.data.duplicateFields[record.get('field_id')], function(k, v, o){
				// 				i++;
				// 				return (k != record.get('duplicate_id'));
				// 			}, this);
				// 			meta.css = meta.css + ' duplicate' + i;
				// 			v += String(i)
				// 		}else if(!Ext.isEmpty(this.data.duplicateFields[record.get('field_id')])){
				// 			meta.css = meta.css + ' duplicate1';
				// 			v += ' d1';
				// 		}else if(record.get('cfg').maxInstances > 1){
				// 			meta.css = meta.css + ' duplicate-plus';
				// 			v += ' ...';
				// 		}
				// 	}else if(record.get('cfg').maxInstances > 1 ){
				// 		meta.css = meta.css + ' duplicate-plus';
				// 		v += ' ...2'
				// 	}
				// }
				/* end of setting icon for duplicate fields /**/
				return v;
			}
		},{	header: L.Value
			,width: 300
			,dataIndex: 'value'
			,editor: new Ext.form.TextField()
			,scope: this
			,renderer: function(v, meta, record, row_idx, col_idx, store){
				if(this.renderers && this.renderers[record.get('type')]) return this.renderers[record.get('type')](v, this);
				if(!Ext.isEmpty(record.get('cfg').height)) meta.attr = ' style="height:' + record.get('cfg').height + 'px"';

				if(Ext.isEmpty(v)) return '';
				switch(record.get('type')){
				case 'date': 
					return App.customRenderers.date(v, meta, record, row_idx, col_idx, store);
					break;
				case '_objects': 
					return App.customRenderers.objectsField(v, meta, record, row_idx, col_idx, store, this);
				case 'combo': 
				case 'object_author': 
					return App.customRenderers.thesauriCombo(v, meta, record, row_idx, col_idx, store);
					break;
				case '_language': 
					return App.customRenderers.languageCombo(v, meta, record, row_idx, col_idx, store);
				case '_sex': 
					return App.customRenderers.sexCombo(v, meta, record, row_idx, col_idx, store);
					break;
				case '_short_date_format': 
					return App.customRenderers.shortDateFormatCombo(v, meta, record, row_idx, col_idx, store);
					break;
				/*case '_organization': 
					return App.customRenderers.organizationCombo(v, meta, record, row_idx, col_idx, store, this);
					break/**/
				case '_contact': 
					return App.customRenderers.contactCombo(v, meta, record, row_idx, col_idx, store, this);
					break
				case '_case': 
					return App.customRenderers.caseCombo(v, meta, record, row_idx, col_idx, store, this);
					break
				case '_case_object': 
					return App.customRenderers.objectCombo(v, meta, record, row_idx, col_idx, store, this);
					break
				case 'checkbox': 
					return App.customRenderers.checkbox(v, meta, record, row_idx, col_idx, store);
					break;
				case 'popuplist': 
					return App.customRenderers.thesauriCell(v, meta, record, row_idx, col_idx, store);
					break;
				case 'text': //if(!Ext.isEmpty(record.get('value'))) meta.css = 'icon-document-text-centered'; return ''; break;
				case 'html': 
					return App.shortenString(v, 200);
					//if(!Ext.isEmpty(record.get('value'))) meta.css = 'icon-document-text-centered'; return ''; 
					break;
				case 'memo': 
					//meta.attr = ' style="height:' + Ext.value(record.get('cfg').height, 150) + 'px"';
					return '<pre>'+App.shortenString(v, 500)+'</pre>';
					break;
				default: return v;
				}
			}
		},{
			header: L.Additionally
			,width: 200
			,dataIndex: 'info'
			,editor: new Ext.form.TextField()
		}
		/*,{ header: 'duplicate_id', dataIndex: 'duplicate_id' },{ header: 'duplicate_pid', dataIndex: 'duplicate_pid' }
		,{ header: 'id', dataIndex: 'id' }
		,{ header: 'pid', dataIndex: 'pid' }
		,{ header: 'level', dataIndex: 'level' }
		,{ header: 'visible', dataIndex: 'visible' }/**/
		];
		
		Ext.apply(this, {
			//tbar: tbar
			store:  new Ext.data.JsonStore({ 
				fields: fields
				,reader: new Ext.data.JsonReader({ idProperty: 'id', messageProperty: 'msg' })
				,proxy: new Ext.data.MemoryProxy([])
			})
			,columns: gridColumns
			,sm: new Ext.grid.CellSelectionModel({ listeners: { selectionchange: {scope: this, fn: this.onSelectionChange} } })
			,stripeRows: true
			,header: false
			,clicksToEdit: 1
			,listeners: {
				scope: this
				,beforeedit: this.onBeforeEditProperty
				,afteredit: this.onAfterEditProperty
				,keypress:  function(e){ if( (e.getKey() == e.ENTER) && (!e.hasModifier())) this.onFieldTitleDblClick()}
				,celldblclick:  this.onFieldTitleDblClick
				,cellcontextmenu: this.onCellContextMenu
				,cellclick:  this.onCellClick
				,cellcontextmenu: this.onPopupMenu
				,fileuploaded: this.onFileUploaded
				,filesdeleted: this.onFilesDeleted
				,beforedestroy: function(){
					cw = this.refOwner.getBubbleTarget();
					if(cw){
						cw.un('fileuploaded', this.onFileUploaded, this);
						cw.un('filesdeleted', this.onFilesDeleted, this);
					}
				}
			}
			,viewConfig:{
				autoFill: true
				,forceFit: true
				,getRowClass: function( record, index, rowParams, store ){
					rez = ''
					if(record.get('tag') == 'H') rez = 'group-titles-colbg';
					if(record.get('pfu') > 0) rez += ' bgcLR';
					return rez;
				}
			}
		});
		cw = this.refOwner.getBubbleTarget();
		if(cw){
			cw.on('fileuploaded', this.onFileUploaded, this);
			cw.on('filesdeleted', this.onFilesDeleted, this);
		}
		this.addEvents('change', 'fileupload', 'filedownload', 'filesdelete', 'showcasefiles', 'loaded');
		this.enableBubble(['change', 'fileupload', 'filedownload', 'filesdelete', 'showcasefiles', 'loaded']);
  		CB.VerticalEditGrid.superclass.initComponent.apply(this, arguments);
	}
	,isDuplicateField: function(record){
		if(this.canDuplicateField(record)) return true;
		//clog('here', !Ext.isEmpty(this.data.duplicateFields[record.get('field_id')]))
		if( !Ext.isEmpty(this.data) 
			&& !Ext.isEmpty(this.data.duplicateFields)
			&& !Ext.isEmpty(this.data.duplicateFields[record.get('field_id')]) 
		) return true;
		return false;
	}
	,canDuplicateField: function(record){
		return (record.get('cfg').maxInstances > 1);
	}
	,getDuplicateFieldIndex: function(record){
		index = -1;
		if(!this.isDuplicateField(record)) return index;
		if(this.isFirstDuplicateField(record)) return 0;
		index = 0;
		if(	!Ext.isEmpty(this.data) 
			&& !Ext.isEmpty(this.data.duplicateFields)
			&& !Ext.isEmpty(this.data.duplicateFields[record.get('field_id')]) 
			&& !Ext.isEmpty(this.data.duplicateFields[record.get('field_id')][record.get('duplicate_id')])
		){
			Ext.iterate(this.data.duplicateFields[record.get('field_id')], function(k, v, o){
				index++;
				return (k != record.get('duplicate_id'));
			}, this);
		}
		return index;
	}
	,isFirstDuplicateField: function(record){
		if(!this.isDuplicateField(record)) return false;
		return (record.get('duplicate_id') == 0);
	}
	,isLastDuplicateField: function(record){
		
		idx = this.store.indexOf(record);
		clog(this.store.getCount(), idx, this.store.getCount() == (idx+1));
		if(this.store.getCount() == (idx+1) ) return true;
		return (this.store.getAt(idx +1).get('field_id') != record.get('field_id') );
		// if(	Ext.isEmpty(this.data) 
		// 	|| Ext.isEmpty(this.data.duplicateFields) 
		// 	|| Ext.isEmpty(this.data.duplicateFields[record.get('field_id')]) 
		// 	|| Ext.isEmpty(this.data.duplicateFields[record.get('field_id')][record.get('duplicate_id')])
		// ){ return true;
		// }else{
		// 	return (this.getDuplicateFieldIndex(record) == this.data.duplicateFields.length);
		// }
	}
	,onCellClick: function(g, r, c, e){
		if(g.getColumnModel().getDataIndex(c) == 'files') 
			return this.onPopupMenu(g, r, c, e);
		el = e.getTarget();
		if(el)
			switch(el.name){
				case 'add_duplicate': this.onDuplicateFieldClick();
					break;
			}
		clog(e.getTarget());
	}
	,updateFieldPrivacy: function(cb, r, idx){
		rec = this.getStore().getAt(this.popupForRow);
		if(!rec) return;
		rec.set('pfu', Ext.isEmpty(rec.get('pfu')) ? App.loginData.id : null);
		this.fireEvent('change'); //this.refOwner.setDirty(true);
		delete this.popupForRow;
	}
	,getFilesPopupMenu: function(){
		if(!this.filesPopupMenu) this.filesPopupMenu = new Ext.menu.Menu({
			items: [
				{text: L.upload, iconCls: 'icon-upload', scope: this, handler: function(){ this.fireEvent('fileupload', this.refOwner.data.id) } }
				,{text: L.download, iconCls: 'icon-download', scope: this, handler: function(b){ if(Ext.isDefined(this.popupForRow)) this.fireEvent('filedownload', this.store.getAt(this.popupForRow).get('files')) } }
				,'-'
				,{text: L.erase, iconCls: 'icon-clear', scope: this, handler: function(b){
						//c = this.getSelectionModel().getSelectedCell();
						//if(Ext.isEmpty(c)) return;
						r = this.getStore().getAt(this.popupForRow);
						if(!r) return;
						r.set('files', null);
						this.fireEvent('change'); //this.refOwner.setDirty(true);
						delete this.popupForRow;
					}
				}
				,{text: L.Delete, iconCls: 'icon-delete', scope: this, handler: function(b){
						if(!Ext.isDefined(this.popupForRow)) return;
						r = this.store.getAt(this.popupForRow);
						f = this.refOwner.getFileProperties(r.get('files'));
						this.fireEvent('filesdelete', f ? f : r.data);
					}
				}
				,'-'
				,{text: L.uploadAnother, iconCls: 'icon-upload-other', scope: this, handler: function(){ this.fireEvent('fileupload', this.refOwner.data.id) } }
				,'-'
				,{text: L.files, iconCls: 'icon-files', hideOnClick: false, menu: []}
				//,{text: L.allCaseFiles + ' ...', iconCls: 'icon-case-all-files', scope: this, handler: function(){ this.fireEvent('showcasefiles') }}
			]
			,listeners: {
				scope: this
				,itemclick: function(){this.keepPopupRef = true}
				,hide: function(){if(!this.keepPopupRef) delete this.popupForRow; delete this.keepPopupRef;}
			}
		})
		this.filesPopupMenu.items.each(function(i){i.setVisible(true)})
		return this.filesPopupMenu;
	}
	,onFileUploaded: function(data){
		if(!Ext.isDefined(this.popupForRow)) return;
		this.store.getAt(this.popupForRow).set('files', data.id);
		delete this.popupForRow;
		this.fireEvent('change'); //this.refOwner.setDirty(true);
	}
	,onFilesDeleted: function(fileId){
	}
	,onPopupMenu: function(g, r, c, e){
		e.preventDefault();
		switch(g.getColumnModel().getDataIndex(c)){
			case 'files': this.showFilesPopupMenu(g, r, c, e); break;
			case 'title': this.showTitlePopupMenu(g, r, c, e); break;
		}
	}
	,showFilesPopupMenu: function(grid, rowIndex, cellIndex, e){
		if(rowIndex <0) return;
		pm = this.getFilesPopupMenu();
		this.popupForRow = rowIndex;
		r = grid.getStore().getAt(rowIndex);
		if(!r) return;
		if(r.get('files')){
			pm.items.itemAt(pm.items.findIndex('iconCls', 'icon-upload')).hide();
			//download already visible
			//delete already visible
		}else{
			//upload already visible
			pm.items.itemAt(pm.items.findIndex('iconCls', 'icon-download')).hide();

			idx = pm.items.findIndex('iconCls', 'icon-clear');
			ci = pm.items.itemAt(idx).hide();
			pm.items.itemAt(idx - 1).hide();

			pm.items.itemAt(pm.items.findIndex('iconCls', 'icon-delete')).hide();

			idx = pm.items.findIndex('iconCls', 'icon-upload-other');
			pm.items.itemAt(idx-1).hide();
			pm.items.itemAt(idx).hide();
		}
		fm = pm.items.itemAt(pm.items.findIndex('iconCls', 'icon-files'));
		fm.menu.removeAll(true);
		if(this.refOwner.filesGrid)
			this.refOwner.filesGrid.getStore().each(function(i){
				fm.menu.add( new Ext.menu.Item({text: i.get('name'), iconCls: 'file file-unknown '+i.get('iconCls'), data:{id: i.get('id')}, scope: this, handler: this.setPropertyFile}) )
			}, this)
		if(fm.menu.items.getCount() < 1) fm.setVisible(false);
		pm.showAt(e.getXY());
	}
	,showTitlePopupMenu: function(grid, rowIndex, cellIndex, e){
		r = grid.getStore().getAt(rowIndex);
		this.popupForRow = rowIndex;
		if(!this.titlePopupMenu) this.titlePopupMenu = new Ext.menu.Menu({
			items: [
				{text: L.addDuplicateField, scope: this, handler: this.onDuplicateFieldClick }
				,{text: L.delDuplicateField, scope: this, handler: this.onDeleteDuplicatedFieldClick }
				//{xtype: 'menucheckitem', text: L.Confidential, scope: this, handler: this.updateFieldPrivacy}
			]
		});
		this.titlePopupMenu.items.itemAt(0).setDisabled(!this.canDuplicateField(r));
		this.titlePopupMenu.items.itemAt(1).setDisabled(this.isFirstDuplicateField(r));
		this.titlePopupMenu.showAt(e.getXY());
	}
	,onCellContextMenu: function(grid, rowIndex, cellIndex, e){
		switch(grid.getColumnModel().getDataIndex(cellIndex)){
			case 'files':  this.showFilesPopupMenu(grid, rowIndex, cellIndex, e);
			break;
			//...
		}
	}
	,setPropertyFile: function(b){
		if(!Ext.isDefined(this.popupForRow)) return;
		this.store.getAt(this.popupForRow).set('files', b.data.id);
		delete this.popupForRow;
		this.fireEvent('change');  //this.refOwner.setDirty(true);
	}
	,onFieldTitleDblClick: function(){
		sm = this.getSelectionModel();
		cm = this.getColumnModel();
		s = sm.getSelectedCell();
		gv = this.getView();
		
		if(Ext.isEmpty(s)) return;
		fieldName = cm.getDataIndex(s[1]);
	
		if(fieldName == 'title'){
			c = gv.getCell(s[0], s[1]);
			c.className = c.className.replace( (c.className.indexOf(' x-grid3-cell-selected') >= 0 ? ' x-grid3-cell-selected' : 'x-grid3-cell-selected'), '')
			s[1] = cm.findColumnIndex('value');
			this.getView().focusCell(s[0], s[1], false, false);
			this.startEditing(s[0], s[1]);//begin field edit
		}
	}
	,getBubbleTarget: function(){
		if(!this.parentWindow){
			this.parentWindow = this.findParentByType('CBGenericForm') || this.refOwner;
			//if(Ext.isEmpty(this.parentWindow))
		}
		return this.parentWindow;
	}
	,reload: function(){
		// initialization 
		this.data = {};
		w = this.refOwner;
		if(Ext.isDefined(w.data) && Ext.isDefined(w.data[this.root])) this.data = w.data[this.root];
		this.template_id = Ext.value(this.template_id, w.data.template_id); //if not specified directly to grid then try to look in owners data
		if(isNaN(this.template_id)) return Ext.Msg.alert('Error', 'No template id specified in data for "' + w.title + '" window.');
		this.templateStore = CB.DB['template' + this.template_id];
		this.fullStore.removeAll(false);
		this.templateStore.each(function(r){
			v = this.data.values ? this.data.values['f'+r.get('id')+'_0'] : (Ext.isDefined(r.get('cfg').value) ? {value: r.get('cfg').value} : {});
			if(!v) v = {};
			d = {
				id: v.id
				,field_id: r.get('id')
				,title: r.get('title')
				,value: Ext.value(v.value, '')
				,info: Ext.value(v.info, '')
				,files: v.files
				,pfu: v.pfu
				,duplicate_id: 0
				,duplicate_pid: null
				,pid: r.get('pid')
				,tag: r.get('tag')
				,type: r.get('type')
				,cfg: Ext.apply({}, r.get('cfg'))/*{
					showIn: r.get('cfg').showIn
					,edit_in: r.get('cfg').edit_in
					,thesauriId: r.get('cfg').thesauriId
					,maxInstances: r.get('cfg').maxInstances
					,tags: r.get('cfg').tags
				}/**/
				//,cfg: r.get('cfg')
				,level: r.get('level')
				,visible: r.get('visible')
			}
			//if(it's object_date_start field and it is a new object then we are setting it's value to today)
			if((r.get('name') == '_date_start') && isNaN(this.refOwner.data.id))
				d.value = this.refOwner.data.date_start ? this.refOwner.data.date_start : new Date();
			//if there is a date set for the date field, we are parsing it to a date value
			if( (d.type == 'date') && Ext.isString(d.value) && !Ext.isEmpty(d.value))
				d.value = Date.parseDate(d.value.substr(0,10), 'Y-m-d');
			//if(d.type == 'case_title') d.value = this.refOwner.getBubbleTarget().data.title;
			/* adding record to the store */	
			record = new this.fullStore.recordType(d, Ext.id());
			this.fullStore.add( record );
		}, this);

		// adding duplicate fields
		if(this.data.duplicateFields)
			Ext.iterate(this.data.duplicateFields, function(fieldId, v , o){
				Ext.iterate(v, function(key, value, object){
					this.duplicateField(fieldId, key, value);
				}, this)
			}, this)
		this.refilled = 0;
		this.updateVisibility();
		this.fireEvent('loaded', this);
	}
	,readValues: function(){
		if(!Ext.isDefined(this.data)) this.data = {};
		this.data.values = {};
		/* reading values from the store */
		this.store.each(function(r){
			if(!Ext.isEmpty(r.get('value')) || !Ext.isEmpty(r.get('info')) || !Ext.isEmpty(r.get('files')) || !Ext.isEmpty(r.get('pfu')))
				this.data.values['f'+r.get('field_id')+'_'+r.get('duplicate_id')] = { value: r.get('value'), info: r.get('info'), files: r.get('files'), pfu:  r.get('pfu')};
		}, this);
		w = this.getBubbleTarget();
		
		if(Ext.isDefined(w.data)) w.data[this.root] = this.data;
	}
	,onBeforeEditProperty: function(e){//grid, record, field, value, row, column, cancel
		if(e.record.get('tag') == 'H'){
			e.cancel = true;
			return;
		}
		if(e.field != 'value') return;

		pw = this.findParentByType(CB.Objects);
		t = e.record.get('type');
		if(pw && (t == '_case_object') ) e.pidValue = pw.data.id; /* setting by default parent case id for case_objects fields, this value will be overwriten if it is dependent on another field */
		
		if( (Ext.isDefined(e.record.data.cfg.dependency) ) && !Ext.isEmpty(e.record.get('pid')) )/* get and set pidValue id dependent */
			e.pidValue = Ext.isDefined(this.getBubbleTarget().getCurrentFieldValue) ? this.getBubbleTarget().getCurrentFieldValue(e.record.get('pid'), e.record.get('duplicate_id')) : this.getFieldValue(e.record.get('pid'), e.record.get('duplicate_id'));
		col = e.grid.colModel.getColumnAt(e.column);
		ed = col.getEditor();
		if(ed) ed.destroy();
		if(this.editors && this.editors[t]) {
			col.setEditor(new Ext.grid.GridEditor(this.editors[t](this)))
		}else{
			te = App.getTypeEditor(t, e);
			if(e.cancel) return ;
			col.setEditor(new Ext.grid.GridEditor(te));
		}
	}
	,gainFocus: function(){
		this.focus(false); 
		s = this.getSelectionModel().getSelectedCell();
		if(s) this.getView().focusCell(s[0], s[1]);
	}
	,onAfterEditProperty: function(e){
		if(e.field != 'value') return;
		//clog('afteredit');
		sm = this.getSelectionModel();
		s = this.getSelectionModel().getSelectedCell();
		this.updateVisibility();
		if(s) sm.select(s[0], s[1]);
		if(e.value != e.originalValue){
			this.fullStore.each( function(record){ //update dependent child field to null
				if( ( ( record.get('cfg').thesauriId == 'variable') || ( Ext.isDefined(record.get('cfg').dependency) )  )
					&& (e.record.get('field_id') == record.get('pid'))
					&& (e.record.get('duplicate_id') == record.get('duplicate_id')) 
				) record.set('value', null);
			}, this);
			this.fireEvent('change'); //this.refOwner.setDirty(true);
		}
	}
	,getFieldValue: function(field_id, duplication_id){
		result = null
		this.store.each(function(r){ if((r.get('field_id') == field_id) && (r.get('duplicate_id') == duplication_id)){ result = r.get('value'); return false; } }, this);
		return result;
	}
	,updateVisibility: function(){
		result = false;
		modified = true;
		while(modified){
			modified = false;
			this.fullStore.each( function(record){
				pid = record.get('pid');// //5
				if(!Ext.isEmpty(pid)){ // //5
					field_id = record.get('field_id');// //6
					duplicate_id = record.get('duplicate_id'); // //ext-gen363
					pid_duplicate_id = Ext.value(record.get('duplicate_pid'), 0); // //0
					if(pid_duplicate_id){ //iterating to top of the possible tree duplication
						pri = this.fullStore.findBy(function(r){return ( (r.get('field_id') == field_id) && (r.get('duplicate_id') === pid_duplicate_id) );}, this);
						while( (pri >= 0) && (pid_duplicate_id)){
							pr = this.fullStore.getAt(pri);
							duplicate_id = pr.get('duplicate_id');
							pid_duplicate_id = Ext.value(pr.get('duplicate_pid'), 0);
							pri = this.fullStore.findBy(function(r){return ( (r.get('field_id') == field_id) && (r.get('duplicate_id') == pid_duplicate_id) );}, this);
						}
					}
					pri = this.fullStore.findBy(function(r){return ( (r.get('field_id') == pid) && (r.get('duplicate_id') == duplicate_id) );}, this);
					if(pri < 0) pri = this.fullStore.findBy(function(r){return ( (r.get('field_id') == pid) && (r.get('duplicate_id') == pid_duplicate_id) );}, this);
					pr = this.fullStore.getAt(pri);
					if(pr.get('visible') == 1){ // if parent row is visible
						va = [];
						if(Ext.isDefined(record.get('cfg').dependency) && !Ext.isEmpty(record.get('cfg').dependency.pidValues)){
							v = record.get('cfg').dependency.pidValues;
							clog('!va', va, pr.get('value'));
							va = Ext.isArray(v) ? v : String(v).split(',');
						}
						if( record.get('visible') == 1 ){
							if( ( !Ext.isEmpty(v) && ( va.indexOf( pr.get('value') ) < 0 ) ) //if not empty pidValues specified and parent value out of pidValues then hide the field
							    || ( (record.get('cfg').thesauriId == 'variable') && Ext.isEmpty(pr.get('value')) ) // OR if the field is dinamic and parent has no selected value
							    || ( Ext.isDefined(record.get('cfg').dependency) && Ext.isEmpty(pr.get('value')) ) // OR if the field is dinamic and parent has no selected value
							)
							{
								clog('recird considered visible, setting it to hidden')
								record.set('visible', 0);
								modified = true;
							}
						}else{ //when record is not visible
							if( (Ext.isEmpty(v) || ( va.indexOf( pr.get('value') ) >= 0 ))
								&& ( (record.get('cfg').thesauriId !== 'variable') ||  !Ext.isEmpty(pr.get('value'))) 
								&& ( Ext.isDefined(record.get('cfg').dependency) ||  !Ext.isEmpty(pr.get('value'))) 
							) { //if no pidValues specified or pidValues contains the parent selected value then show the field
								clog('recird considered hidden, setting it to true')
								record.set('visible', 1);
								modified = true;
							}
						}
					}else{ // if parent row is not visible then we just hide the child
						if(record.get('visible') == 1){
							clog('parend is hiddem and child is visible, setting it to hidden')
								
							record.set('visible', 0);
							modified = true;
						}
					}
				}else record.set('visible', 1);
			}, this);
			if(modified) result = true;
		}
		result = (result || !this.refilled);
		if(result) this.refillGridStore();
		return result;
	}
	,refillGridStore: function(){
		if(!this.store) return;
		rc = this.fullStore.queryBy(this.storeFilter, this);
		if(this.store && this.store.suspendEvents) this.store.suspendEvents(true);
		this.store.removeAll(false);
		ra = [];
		rc.each(function(i, idx){ ra.push(i); }, this)
		this.store.resumeEvents();
		this.store.add(ra);
		this.refilled = 1;
		if(this.toFocus){
			ridx = this.store.find('duplicate_id', this.toFocus.duplicateId);
			if((ridx >= 0) && !Ext.isEmpty(this.toFocus.lastSelectedCell))
				this.getSelectionModel().select(ridx, this.toFocus.lastSelectedCell[1]);
			delete this.toFocus;
		}
	}
	,storeFilter: function(r, id){ 
		//return true;
		return (	(r.get('cfg').showIn != 'top') && (r.get('cfg').showIn != 'tabsheet') && 
				(r.get('visible') == 1) && ((r.get('tag') == 'f') || (r.get('tag') == 'H')) &&
				(Ext.isEmpty(this.data.hideFields) || (this.data.hideFields.indexOf(r.get('tag')+r.get('field_id') + '_' + r.get('duplicate_id')) < 0) )
			); 
	}
	,onSelectionChange: function(sm, o){
		/* duplication */
		// is = sm.grid.getTopToolbar().items;
		// b1 = is.find(function(i){return (i.name == 'duplicateField');});
		// b2 = is.find(function(i){return (i.name == 'deleteDuplicatedField');});
		// if(sm.hasSelection()){
		// 	b1.setDisabled(o.record.get('cfg').maxInstances < 2);
		// 	b2.setDisabled( (o.record.get('duplicate_id') === 0) || (Ext.isEmpty(this.data.duplicateFields[o.record.get('field_id')]) || Ext.isEmpty(this.data.duplicateFields[o.record.get('field_id')][o.record.get('duplicate_id')]) ) );
		// }else{
		// 	b1.setDisabled(true);
		// 	b2.setDisabled(true);
		// }
		/* end of duplication */
		/* privacy*/
		/*if(sm.hasSelection()){
			this.privacyCombo.setValue(o.record.get('privacy'));
			this.privacyCombo.setDisabled(false);
		}else{
			this.privacyCombo.setValue(0);
			this.privacyCombo.setDisabled(true);
		}
		/* end of privacy*/
	}
	,duplicateField: function(fieldId, duplicateId, duplicatePid){
		// 3 26 0
		if(Ext.isEmpty(duplicateId)) duplicateId = Ext.id();
		pidIndex = this.fullStore.findBy(function(r){ return ((r.get('field_id') == fieldId) && (r.get('duplicate_id') == duplicatePid))}, this);
		pidRow = this.fullStore.getAt(pidIndex);
		level = pidRow.get('level');
		insertBeforeIndex = this.fullStore.findBy(function(r){ 
				return ((r.get('cfg').showIn != 'top') && (r.get('cfg').showIn != 'tabsheet') && (r.get('level') <= level) && (r.get('field_id') != fieldId))
			}, this, pidIndex +1 
		);
		/* collecting the rows set that should be duplicated */
		pids = [pidRow.get('field_id')]
		qc = this.templateStore.queryBy(function(r){
			if(pids.indexOf(r.get('id')) >=0 ) return true;
			if(pids.indexOf(r.get('pid')) >=0 ){
				pids.push(r.get('id'));
				return true;
			}
			return false;
		}, this);
		dra = [];
		qc.each(function(r, idx, a){
			v = this.data.values ? this.data.values['f'+r.get('id')+'_'+duplicateId] : {};
			if(!v) v = {};
			if( (r.get('type') == 'date') && !Ext.isEmpty(v.value)) if( v.value.substr ) v.value = Date.parseDate(v.value.substr(0,10), 'Y-m-d');
			d = {
				id: null
				,field_id: r.get('id')
				,title: r.get('title')
				,value: Ext.value(v.value, '')
				,info: Ext.value(v.info, '')
				,files: Ext.value(v.files, '')
				,pfu: Ext.value(v.pfu, 0)
				,duplicate_id: duplicateId
				,duplicate_pid: duplicatePid
				,pid: r.get('pid')
				,tag: r.get('tag')
				,type: r.get('type')
				,cfg: {
					thesauriId: r.get('cfg').thesauriId
					,maxInstances: (r.get('id') == fieldId) ? pidRow.get('cfg').maxInstances : r.get('cfg').maxInstances
					,multiValued: r.get('cfg').multiValued
					,templates: r.get('cfg').templates
					,tags: r.get('cfg').tags
					,editor: r.get('cfg').editor
					,dependency: r.get('cfg').dependency
				}
				,level: r.get('level')
				,visible: r.get('visible')
			}
			r = new this.fullStore.recordType(d, Ext.id());
			if((d.field_id == fieldId) && (d.duplicate_id == duplicateId))
				this.toFocus = {fieldId: fieldId, duplicateId: duplicateId, lastSelectedCell: this.getSelectionModel().getSelectedCell()};
			dra.push(r);
		}, this)
		if(insertBeforeIndex >=0 )this.fullStore.insert(insertBeforeIndex, dra); else this.fullStore.add(dra);
		this.refilled = 0;
		/* end of collecting the rows set that should be duplicated */
		
		if(!this.data.duplicateFields) this.data.duplicateFields = {};
		if(!this.data.duplicateFields[fieldId]) this.data.duplicateFields[fieldId] = {};
		this.data.duplicateFields[fieldId][duplicateId] = duplicatePid;
		
		this.changeMaxInstances(fieldId, duplicatePid, -1);
	}
	,changeMaxInstances: function(fieldId, duplicatePid, count){
		/* going up until reaching the begining of the duplication set(block) */
		parentDuplicateId = String(duplicatePid);
		while(Ext.isDefined(this.data.duplicateFields[fieldId][parentDuplicateId])) parentDuplicateId = String(this.data.duplicateFields[fieldId][parentDuplicateId]);
		/* eof */
		dids = [parentDuplicateId];
		this.fullStore.each(function(r, idx, s){
			if( r.get('field_id') == fieldId ){
				did = String(r.get('duplicate_id'));
				if( dids.indexOf(did) >= 0) r.get('cfg').maxInstances = Number(r.get('cfg').maxInstances) + Number(count);
				else{
					dpid = String(this.data.duplicateFields[fieldId][r.get('duplicate_id')]);
					if( ( dids.indexOf(dpid) >=0 ) ){
						dids.push(did);
						r.get('cfg').maxInstances = Number(r.get('cfg').maxInstances) + Number(count);
					}
				}
			}
		}, this)
		/* eof iterating store to change maxInstances for same fields at the same block level */
	}
	,deleteDuplicatedField: function(fieldId, duplicateId, duplicatePid){
		if(!duplicateId) return;
		delDuplicates = [duplicateId]; //ext-gen-423
		s = this.getSelectionModel().getSelectedCell();
		delRecords = [];
		this.fullStore.each(function(r){
			if(delDuplicates.indexOf(r.get('duplicate_id')) >=0) delRecords.push(r);
			else if(delDuplicates.indexOf(r.get('duplicate_pid')) >=0){
				//if(r.get('field_id') == fieldId){
					r.set('duplicate_pid', duplicatePid);
					this.data.duplicateFields[fieldId][r.get('duplicate_id')] = duplicatePid;
				//}
				//else delRecords.push(r);
			}
		}, this);
		this.changeMaxInstances(fieldId, duplicateId, 1);
		this.fullStore.remove(delRecords);
		this.store.remove(delRecords);
		delete this.data.duplicateFields[fieldId][duplicateId];
		if(isEmptyObject(this.data.duplicateFields[fieldId])) delete this.data.duplicateFields[fieldId];
		this.getView().refresh(false);
		s[0] = Math.min(s[0], this.store.getCount()-1);
		this.getSelectionModel().select(s[0], s[1]);
		this.getView().focusCell(s[0], s[1], false, false);
	}
	,onDuplicateFieldClick: function(b){
		s = this.getSelectionModel().getSelectedCell();
		if(Ext.isEmpty(s)) return;
		r = this.store.getAt(s[0]);
		this.duplicateField(r.get('field_id'), null, r.get('duplicate_id'));
		this.updateVisibility()
		this.fireEvent('change'); //this.refOwner.setDirty(true);
	}
	,onDeleteDuplicatedFieldClick: function(b){
		s = this.getSelectionModel().getSelectedCell();
		if(Ext.isEmpty(s)) return;
		r = this.store.getAt(s[0]);
		this.deleteDuplicatedField( r.get('field_id'), r.get('duplicate_id'), r.get('duplicate_pid') );
		this.fireEvent('change'); //this.refOwner.setDirty(true);
	}
})

Ext.reg('CBVerticalEditGrid', CB.VerticalEditGrid);