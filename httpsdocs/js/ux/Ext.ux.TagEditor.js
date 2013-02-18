Ext.namespace('Ext.ux');

Ext.ux.TagEditor = Ext.extend(Ext.Panel, {
	bodyStyle: 'margin: 0; padding: 5px'
	,autoWidth: true
	,autoHeight: true
	,border: false
	,lastSearchText: ''
	,initComponent: function() {
		this.itemsStore  =  new Ext.data.ArrayStore({
			fields: [{name: 'id', type: 'int'}, 'name', {name: 'checked', type: 'boolean'}, 'group', {name: 'addDivider', type: 'boolean'}]
			
		});
		this.searchTask = new Ext.util.DelayedTask(this.doSearchTags, this);

		this.idField = Ext.value(this.idField, 'id');
		this.nameField = Ext.value(this.nameField, 'name');
		this.groupField = Ext.value(this.groupField, 'group');
		this.parentOrderField = Ext.value(this.parentOrderField, 'parent_order');
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
		if(this.showFilterOptions) 	tbarItems.unshift({
				tooltip: L.and
				,criteria: 'and'
				,iconCls: 'x-tool-chain'
				,toggleGroup: 'criteria'
				,allowDepress: false
				,scope: this
				,handler: this.valueChanged
				,disabled: true
			},{
				tooltip: L.or
				,criteria: 'or'
				,iconCls: 'x-tool-unchain'
				,toggleGroup: 'criteria'
				,allowDepress: false
				,pressed: true
				,scope: this
				,handler: this.valueChanged
				,disabled: true
			},{
				tooltip: L.exact
				,criteria: 'exact'
				,iconCls: 'icon-magnifier-zoom-actual-equal'
				,toggleGroup: 'criteria'
				,allowDepress: false
				,scope: this
				,handler: this.valueChanged
				,disabled: true
			},'-');

		items = [
		/*{	
			xtype: 'label'
				,cls: 'fwB'
				,style: 'display: block'
				,height: 15
				,width: '100%'
				,text: L.Tags + ':'
			}/**/]
		if(!Ext.isEmpty(this.api) && !Ext.isEmpty(this.api.search)) items.push({ 
			xtype: 'textfield'
			,cls: 'icon-locate-right'
			,width: '100%'
			,enableKeyEvents: true
			,listeners: {
				scope: this
				,keyup: function(field, event){
					if(this.lastSearchText == field.getValue()) return this.searchTask.cancel();
					this.searchTask.delay(500);
				}
			}
		});
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
				,'<div class="tag-item {[ values.checked ? \'icon-tristate-checked\' : \'icon-tristate-unchecked\']}">{name} </div>'
			,'</tpl>'
			,'<div class="x-clear"></div>']
			,itemSelector:'div.tag-item'
			,overClass:'tag-item-over'
			,listeners: {
				scope: this
				,click: this.onItemClick
			}
		})
		if(!Ext.isEmpty(this.api) && !Ext.isEmpty(this.api.create)) items.push({ xtype: 'dataview', height: 26, cls: 'taC pt10 btg', data: [], tpl: '<a href="#" class="cBl">'+L.CreateNewTag+'</a>', itemSelector: 'a', listeners: { click: {scope: this, fn: this.onCreateNewTagClick} } });
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
		Ext.ux.TagEditor.superclass.initComponent.apply(this, arguments);
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
		if(!this.value) this.value = [];
		tmp = [];
		for(i = 0; i < this.value.length; i++) tmp.push(parseInt(this.value[i]));
		this.value = tmp;
		data = [];
		lastGroup = -1;
		lastParentOrder = -1;
		if(this.store){// when a store is given then retreiving items from it
			this.store.each(function(r){
				if(!this.filter || this.filter(r)){ 
					addDivider = ( ( (lastGroup != -1) && (lastGroup != r.get(this.groupField)) ) || ((lastParentOrder != -1) && (lastParentOrder != r.get(this.parentOrderField))) );
					data.push([r.get(this.idField), r.get(this.nameField), (this.value.indexOf(r.get(this.idField)) > -1), r.get(this.groupField), addDivider]);
					lastGroup = r.get(this.groupField);
					lastParentOrder = r.get(this.parentOrderField);
				}
			}, this);
		}else if(this.data){ //setting data from a given array of data
			Ext.each(this.data, function(row){
				addDivider = (lastGroup != -1) && (lastGroup != row[this.groupField]);
				addDivider = ( ( (lastGroup != -1) && (lastGroup != row[this.groupField]) ) || ((lastParentOrder != -1) && (lastParentOrder != row[this.parentOrderField])) );
				data.push([row[this.idField], row[this.nameField], (this.value.indexOf(parseInt(row[this.idField])) > -1), row[this.groupField], addDivider]);
				lastGroup = row[this.groupField];
				lastParentOrder = row[this.parentOrderField];
			}, this);
		}
		this.itemsStore.loadData(data);
		if(this.isVisible()) this.ownerCt.doLayout();
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
			/* clear all checked values for this group if any/**/
			group  = r.get('group');
			this.itemsStore.each(function(r2){
				if((r2.get('group') == group) && (r2.get('checked'))){
					r2.set('checked', false);
					this.value.remove(r2.get('id'));
				}
			}, this)
		}
		r.set('checked', !r.get('checked'));
		if(r.get('checked')) this.value.push(r.get('id')); else this.value.remove(r.get('id'));
		this.valueChanged()
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
Ext.reg('CBTagEditor', Ext.ux.TagEditor);