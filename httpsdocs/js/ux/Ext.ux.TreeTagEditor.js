Ext.namespace('Ext.ux');

Ext.ux.TreeTagEditor = Ext.extend(Ext.Panel, {
	bodyStyle: 'margin: 0; padding: 5px'
	,autoWidth: true
	,autoHeight: true
	,border: false
	,lastSearchText: ''
	,initComponent: function() {
		this.itemsStore  =  new Ext.data.ArrayStore({
			fields: [{name: 'id', type: 'int'}, {name: 'pid', type: 'int'}, 'name', {name: 'checked', type: 'boolean'}, 'group', {name: 'addDivider', type: 'boolean'}, {name:'level', type: 'int'}, {name: 'expanded', type: 'boolean'}]
			
		});
		this.searchTask = new Ext.util.DelayedTask(this.doSearchTags, this);

		this.idField = Ext.value(this.idField, 'id');
		this.pidField = Ext.value(this.pidField, 'pid');
		this.nameField = Ext.value(this.nameField, 'name');
		this.groupField = Ext.value(this.groupField, 'group');
		this.fillStore();
		if(this.store) this.store.on('load', this.fillStore, this);
		tbarItems = [{
			tooltip: 'clear'
			,iconCls: 'icon-broom'
			,scope: this
			,handler: this.clearValue
		},'->',{
			tooltip: 'close'
			,iconCls: 'icon-close-light'
			,scope: this
			,handler: this.onCloseClick
		}
		]


		items = []
		items.push({
			xtype: 'dataview'
			,boxMaxHeight: 500
			,maxHeight: 500
			,autoWidth: true
			,autoScroll: true
			//,emptyText: L.NoTags
			,store: this.itemsStore
			,style: 'padding: 5px 0 5px 0'
			,tpl: ['<tpl for=".">'
				,'{[ values.addDivider ? \'<hr class="tag-groups-spacer" />\' : \'\']}'
				,'<div class="tag-item {[ values.checked ? \'icon-tristate-checked\' : \'icon-tristate-unchecked\']}" {[ (values.level > 0) ? \'style="margin-left:\'+(values.level*18)+\'px"\' : \'\']}>{name} </div>'
			,'</tpl>'
			,'<div class="x-clear"></div>']
			,itemSelector:'div.tag-item'
			,overClass:'tag-item-over'
			,listeners: {
				scope: this
				,click: this.onItemClick
			}
		})
		Ext.apply(this, {
			items: items
			,tbar: { items: tbarItems }
			,listeners: {
				scope: this
				,afterlayout: App.focusFirstField
				,afterrender: this.onAfterRender
			}
		
		})
		this.on('render', this.onRenderEvent, this);
		this.addEvents('change');
		this.enableBubble('change');
		Ext.ux.TreeTagEditor.superclass.initComponent.apply(this, arguments);
	}
	,onAfterRender: function(){
		this.ownerCt.addClass('tagsMenu');
		this.syncSize();
		if(this.getWidth() < 100){
			this.autoWidth = false;
			this.setWidth(150);
			this.syncSize();
		}
	}
	,fillStore: function(){
		/* 	1. collecting ids array 
			2. iterate pids and attach ids to existing parent ids, if some pids do not exist in ids then these ids are assigned to main root node (with id = 0)
		*/
		if(!this.value) this.value = [];
		tmp = [];
		for(i = 0; i < this.value.length; i++) tmp.push(parseInt(this.value[i]));
		this.value = tmp;
		this.ids = {0:[]}

		this.treeData = [];
		if(this.store){// when a store is given then retreiving items from it
			this.store.each(function(r){
				if(!this.filter || this.filter(r)) this.ids[r.get(this.idField)] = [];
			}, this);
			this.store.each(function(r){
				if(!this.filter || this.filter(r)){
					if(Ext.isDefined(this.ids[r.get(this.pidField)])) this.ids[r.get(this.pidField)].push(r.get(this.idField)); else this.ids[0].push(r.get(this.idField))
				}
			}, this);
		}else if(this.data){ //setting data from a given array of data
			Ext.each(this.data, function(row){ this.ids[r[this.idField]] = []; }, this);
			Ext.each(this.data, function(row){ if(Ext.isDefined(this.ids[r[this.pidField]])) this.ids[r[this.pidField]].push(r[this.idField]); else this.ids[0].push(r[this.idField]) }, this);
		}
		this.iterateChildren(0, 0);
		this.itemsStore.loadData(this.treeData);
		if(this.isVisible()) this.ownerCt.doLayout();
	}
	,iterateChildren: function(pid, level){
		level = parseInt(level)
		lastGroup = -1;
		lastParentOrder = -1;
		Ext.each(this.ids[pid], function(childId){
			if(this.store){
				r = this.store.getAt(this.store.findExact(this.idField, childId));
				addDivider = ( (level == 0) && ( ( (lastGroup != -1) && (lastGroup != r.get(this.groupField)) ) || ((lastParentOrder != -1) && (lastParentOrder != r.get(this.parentOrderField))) ) );
				checked = (this.value.indexOf(r.get(this.idField)) > -1);
				this.treeData.push([r.get(this.idField), pid, r.get(this.nameField), checked, r.get(this.groupField), addDivider, level, checked]);
				lastGroup = r.get(this.groupField);
				lastParentOrder = r.get(this.parentOrderField);
				if(checked) this.iterateChildren(r.get(this.idField), level+1);
			}else{
				idx = Ext.each(this.data, function(row){return (row[this.idField] != childId);}, this);
				r =  this.data[idx];
				addDivider = ( (level == 0) && ( ( (lastGroup != -1) && (lastGroup != r[this.groupField]) ) || ((lastParentOrder != -1) && (lastParentOrder != r[this.parentOrderField])) ) );
				checked = (this.value.indexOf(r[this.idField]) > -1);
				this.treeData.push([r[this.idField], pid, r[this.nameField], checked, r[this.groupField], addDivider, level, checked]);
				lastGroup = r[this.groupField];
				lastParentOrder = r[this.parentOrderField];
				if(checked) this.iterateChildren(r[this.idField], level+1);
			}
		}, this)
	}
	,setValue: function(v){
		this.value = Ext.value(v, []);
		this.fillStore();
		this.valueChanged();
		this.doLayout();
	}
	,getValue: function(){ return this.value}
	,clearValue: function(){
		if(Ext.isEmpty(this.value)) return;
		this.setValue(null);
		this.valueChanged()
	}
	,onRenderEvent: function(){
		b = this.findParentByType('button');
		if(b) b.on('menushow', function(){ f = this.findByType('textfield')[0]; if(f){f.setValue(''); this.fillStore();}}, this)
	}
	,onItemClick: function(dv, idx, n, e){
		r = this.itemsStore.getAt(idx);
		if((!r.get('checked')) && this.singleValuePerGroup){
			/* clear all checked values for this group if any /**/
			group  = r.get('group');
			this.itemsStore.each(function(r2){
				if((r2.get('group') == group) && (r2.get('checked'))){
					r2.set('checked', false);
					this.value.remove(r2.get('id'));
				}
			}, this)
		}
		r.set('checked', !r.get('checked'));
		/* check all parents or uncheck all children*/
		if(r.get('checked')){
			pid = r.get('pid');
			pids = [];
			while( (pid > 0) && Ext.isDefined(this.ids[pid])){
				pr = this.itemsStore.getAt(this.itemsStore.findExact('id', pid));
				if(!pr.get('checked')){
					pids.unshift(pr.get('id'));
					pr.set('checked', true);
				}
				pid = pr.get('pid');
			}
			for(i=0; i < pids.length; i++) this.value.push(pids[i]);
			this.value.push(r.get('id'));
		}else{
			this.value.remove(r.get('id'));
			this.uncheckAllChildren(r.get('id'));
		}
		this.valueChanged()
	}
	,uncheckAllChildren: function(id){
		for(i = 0; i < this.ids[id]; i++){
			this.uncheckAllChildren(this.ids[i]);
			this.itemsStore.getAt(this.itemsStore.findExact('id', this.ids[i])).set('checked', false);
		}
		this.itemsStore.getAt(this.itemsStore.findExact('id', id)).set('checked', false);
	}
	,valueChanged: function(){
		tb = this.getTopToolbar();
		i = tb.find('pressed', true);
		if(!Ext.isEmpty(i)) this.criteria = i[0].criteria;
		if(this.showFilterOptions){
			tb = this.getTopToolbar();
			tb.items.itemAt(0).setDisabled(this.value.length < 2);
			tb.items.itemAt(1).setDisabled(this.value.length < 1);
			tb.items.itemAt(2).setDisabled(this.value.length < 1);
		}
		this.fireEvent('change', this, this.value);
		this.fillStore()
		this.syncSize();
	}
	,onCreateNewTagClick: function(){
		Ext.Msg.prompt(L.NewTag, L.TagName, function(btn, text){
			if ((btn == 'ok') && (!Ext.isEmpty(text))){
				this.api.create({name: text}, this.onTagCreateProcess, this)
			 }
		}, this)
	}
	,onTagCreateProcess: function(r, e){
		if(r.success !== true) return;
		if(this.store) this.store.reload();
	}
	,doSearchTags: function(){
		f = this.findByType('textfield')[0];
		this.lastSearchText = f.getValue().trim();
		if(Ext.isEmpty(this.lastSearchText)) return this.fillStore();
		
		this.api.search({text: this.lastSearchText, group_id: this.api.searchGroup}, this.onProcessSearchTagsResult, this);
	}
	,onProcessSearchTagsResult: function(r, e){
		if(r.success != true) return;
		data = [];
		lastGroup = -1;
		Ext.each(r.data, function(row){
			addDivider = (lastGroup != -1) && (lastGroup != row[this.groupField]);
			data.push([row[this.idField], row[this.nameField], (this.value.indexOf(parseInt(row[this.idField])) > -1), row[this.groupField], addDivider]);
			lastGroup = row[this.groupField];
		}, this);
		this.itemsStore.loadData(data);
		if(this.isVisible()) this.ownerCt.doLayout();
	}
	,onCloseClick: function(){ this.ownerCt.hide() }
})
Ext.reg('CBTreeTagEditor', Ext.ux.TreeTagEditor);