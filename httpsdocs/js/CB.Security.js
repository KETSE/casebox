Ext.namespace('CB'); 

CB.SecurityPanel = Ext.extend(Ext.Panel, {
	title: L.Security
	,closable: true
	,iconCls: 'icon-key'
	,layout: 'border'
	,initComponent: function(){

  		this.actions = {
  			edit: new Ext.Action({
  				text: L.Edit
  				,scope: this
  				,handler: this.onEditClick
  			})
  			,add: new Ext.Action({
  				text: L.Add
  				,scope: this
  				,handler: this.onAddClick
  				,hidden: true
  			})
  			,del: new Ext.Action({
  				text: L.Delete
  				,scope: this
  				,handler: this.onDeleteClick
  				,hidden: true
  				,disabled: true
  			})
  			
  			,advanced: new Ext.Action({
  				text: L.Advanced
  				,scope: this
  				,disabled: true
  				,handler: this.onAdvancedClick
  			})
  			,save: new Ext.Action({
  				text: L.Save
  				,scope: this
  				,handler: this.onSavePermissionsClick
  				,hidden: true
  				,disabled: true
  			})
  			,apply: new Ext.Action({
  				text: L.Apply
  				,scope: this
  				,handler: this.onApplyPermissionsClick
  				,hidden: true
  				,disabled: true
  			})
  			,cancel: new Ext.Action({
  				text: Ext.MessageBox.buttonText.cancel
  				,scope: this
  				,handler: this.onCancelPermissionsChangeClick
  				,hidden: true
  				,disabled: true
  			})
  		}

  		this.objectLabel = new Ext.form.DisplayField({value: 'Object name: ', style:'padding: 10px; background-color: #fff', region: 'north'})
  		this.editLabel = new Ext.form.DisplayField( {value: 'To change permissions, click Edit'});
  		
  		this.aclStore = new Ext.data.DirectStore({
			baseParams:{ id: this.data.id }
			,autoSave: false
			,restful: true
			,root: 'data'
			,api:{
				read: Security.getObjectAcl
				,create: Security.addObjectAccess
				,update: Security.updateObjectAccess
				,destroy: Security.destroyObjectAccess
			}
			,fields: [
				{name:'id', type: 'int'}
				,'name'
				,'iconCls'
				,'allow'
				,'deny'
			]
			,writer: new Ext.data.JsonWriter({encode: false, writeAllFields: true})
			,listeners: {
				scope: this
				,load: this.onAclStoreLoad
			}
		});
  		this.aclStore.proxy.on('load', this.onAclProxyLoad, this)

  		this.aclList = new Ext.list.ListView({
			store: this.aclStore
			,singleSelect: true
			,emptyText: L.noData
			,reserveScrollOffset: true
			,hideHeaders: true
			,boxMinHeight: 100
			,height: 300
			,style: 'border: 1px solid #aeaeae'
			,columns: [{
				header: 'Group or user'
				,dataIndex: 'name'
				,tpl: '<span class="icon-padding dIB lh16 {iconCls}">{name}</span>'
			}]
			,listeners: {
				scope: this
				,selectionchange: this.onAclListSelectionChange
			}
		});

  		this.specialPermissionsLabel = new Ext.form.DisplayField({value: 'For special permissions or advanced settings,<br /> click Advanced.'});
  		
  		this.permissionsStore = new Ext.data.ArrayStore({
			accessGroups: {
				'FullControl': 	[ 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1 ]
				,'Modify': 	[ 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 1 ]
				,'Read': 	[ 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1 ]
				,'Write': 	[ 0, 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0 ]
			}
			,fields: [
				'id'
				,'name'
				,'allow'
				,'deny'
			]
		});
  		this.permissionsAllowTpl = new Ext.XTemplate(
			'<tpl for=".">'
			,'{[ (this.readOnly ? '
				,'( (values.allow > 0) ? \'<input type="checkbox" disabled="disabled" checked="checked" value="\'+values.allow+\'">\' : "") '
				,': \'<input type="checkbox" \' + ( (values.allow == 2) ? \'disabled="disabled" value="2" \': \'value="1" \')+ ( (values.allow > 0) ? \'checked="checked" \': "")+" />"'
			,')]}'
			,'</tpl>'
  			,{
  				compiled: true
  				,readOnly: true
  			}
  		)
  		this.permissionsDenyTpl = new Ext.XTemplate(
			'<tpl for=".">'
			,'{[ (this.readOnly ? '
				,'( (values.deny < 0) ? \'<input type="checkbox" disabled="disabled" checked="checked" value="\'+values.deny+\'">\' : "") '
				,': \'<input type="checkbox" \' + ( (values.deny == -2) ? \'disabled="disabled" value="-2" \': \'value="-1" \')+ ( (values.deny < 0) ? \'checked="checked" \': "")+" />"'
			,')]}'
			,'</tpl>'
  			,{
  				compiled: true
  				,readOnly: true
  			}
  		)

  		this.permissionsList = new Ext.list.ListView({
			store: this.permissionsStore
			,singleSelect: true
			,emptyText: L.noData
			,reserveScrollOffset: true
			,boxMinHeight: 100
			,height: 300
			,style: 'border: 1px solid #aeaeae'
			,columnSort: false
			,columns: [{
				header: 'Permission'
				,width: .5
				,dataIndex: 'name'
			},{
				header: 'Allow'
				,width: .25
				,dataIndex: 'allow'
				,tpl: this.permissionsAllowTpl
				,align: 'center'
			},{
				header: 'Deny'
				,dataIndex: 'deny'
				,tpl: this.permissionsDenyTpl
				,align: 'center'
			}]
			,listeners:{
				scope: this
				,click: this.onPermissionNodeClick
			}
		});

  		Ext.apply(this, {
  			items: [
  				this.objectLabel
  				,{
  					layout: 'hbox'
  					,region: 'center'
  					,border: false
  					,height: 300
					,autoScroll: true
  					,items: [{
						title: 'Group or user names:'
  						,layout: 'fit'
  						,items: this.aclList
						,unstyled: true
						//,flex: 1
						,width: 400
						,padding: 10
						,buttonAlign: 'left'
						,buttons: [this.editLabel
							,'->'
							,this.actions.edit
							,this.actions.add
							,this.actions.del
						]

  					},{
						title: 'Permissions for selected user/group:'
  						,layout: 'fit'
  						,items: this.permissionsList
						,unstyled: true
						//,flex: 1
						,width: 400
						,padding: 10
						,style: 'margin-left: 50px'
						,buttonAlign: 'left'
						,buttons: [this.specialPermissionsLabel
							,'->'
							,this.actions.save
							,this.actions.apply
							,this.actions.cancel
							,this.actions.advanced
						]

  					}
  					]
  				}
  				//,this.aclList
  			]
  			,listeners:{
  				scope: this
  				,afterrender: this.onAfterRender
  			}
  		})
  		CB.SecurityPanel.superclass.initComponent.apply(this, arguments);
	}
	,onAfterRender: function(){
		this.getEl().mask(L.loading, 'x-mask-loading');
		this.aclStore.load();
	}
	,onAclStoreLoad: function(store, records, options){
		//clog(arguments);
	}
	,onAclProxyLoad: function(proxy, object, options){
		this.getEl().unmask();
		this.objectLabel.setValue('Object name: ' + Ext.value(object.result.path, '') + object.result.name);
		this.setTitle(object.result.name);
	}
	,onEditClick: function(b, e){
		this.setReadOnly(false);
	}
	,setReadOnly: function(readOnly){
		this.editLabel.setVisible(readOnly);
		this.actions.edit.setHidden(!readOnly);
		this.actions.add.setHidden(readOnly);
		this.actions.del.setHidden(readOnly);
		
		this.updateDeleteAction()

		this.permissionsAllowTpl.readOnly = readOnly;
		this.permissionsDenyTpl.readOnly = readOnly;
		this.permissionsList.refresh();

		this.specialPermissionsLabel.setVisible(readOnly);
		this.actions.advanced.setHidden(!readOnly);
		this.actions.save.setHidden(readOnly);
		this.actions.apply.setHidden(readOnly);
		this.actions.cancel.setHidden(readOnly);		
	}
	,updateDeleteAction: function(){
		canDelete = true;
		sr = this.aclList.getSelectedRecords();
		if(!Ext.isEmpty(sr)){
			r = sr[0];
			canDelete = ( ( r.get('allow').indexOf('2') < 0 ) && ( r.get('deny').indexOf('-2') < 0 ));
		}
		this.actions.del.setDisabled(!canDelete);
	}
	,onAddClick: function(b, e){
		w = new CB.ObjectsSelectionForm({
			config: {
				autoLoad: true
				,source: 'usersgroups'
				,renderer: 'listObjIcons'
			}
			,data: {}
		});

		w.on('setvalue', function(data){
			if(Ext.isEmpty(data)) return;
			d = data[0];
			idx = this.aclStore.findExact('id', parseInt(d.id) );
			if(idx >= 0 ){ 
				this.aclList.select(idx);
				return;
			}
			rd = {	id: d.id
				,name: d.name
				,iconCls: d.iconCls 
				,allow: '0,0,0,0,0,0,0,0,0,0,0,0'
				,deny: '0,0,0,0,0,0,0,0,0,0,0,0'
			}
			this.aclStore.add([new this.aclStore.recordType(rd)]);
			this.aclStore.save();
			this.aclList.select(this.aclStore.getCount()-1);
		}, this);
		w.show();
	}
	,onDeleteClick: function(b, e){
		ra = this.aclList.getSelectedRecords();
		if(Ext.isEmpty(ra)) return;
		Ext.Msg.confirm(L.Delete, L.DeleteSelectedConfirmationMessage, function(b){
			if(b == 'yes'){
				this.aclStore.remove(ra);
				this.aclStore.save()
			}
		}, this)
	}
	,onAclListSelectionChange: function(listView, selections){
		this.permissionsStore.removeAll();
		if(!Ext.isEmpty(selections)) this.reloadPermissionsStore();
		this.updateDeleteAction()
	}
	,onPermissionNodeClick: function(dataView, index, node, e){
		r = dataView.getRecord(node);
		cb = e.getTarget('input');
		if(Ext.isEmpty(r) || Ext.isEmpty(cb) || cb.disabled ) return;
		this.changeAccesses(r, cb.checked ? cb.value: 0);
		
		this.actions.save.setDisabled(false);
		this.actions.apply.setDisabled(false);
		this.actions.cancel.setDisabled(false);
	}
	,accessToGroupsData: function(accessRecord, groups){
		rez = [];
		allow = accessRecord.get('allow');
		if(!Ext.isArray(allow)) allow = allow.split(',');
		deny = accessRecord.get('deny');
		if(!Ext.isArray(deny)) deny = deny.split(',');
		Ext.iterate(groups, function(g, gv, obj){
			rez.push( [g, L[g], this.accessToGroupValue(allow, gv), this.accessToGroupValue(deny, gv) ]); 
		}, this);
		return rez;
	}
	,accessToGroupValue: function(accessArray, groupBitsArray){
		lastBit = null;
		bitsMatch = true;
		bitsCombinedMatch = false;
		i = 0;
		while( (i < accessArray.length ) && bitsMatch){
			if(groupBitsArray[i] == 1){
				if(Ext.isEmpty(lastBit)){
					lastBit = accessArray[i];
				}else if( (accessArray[i] * lastBit) > 0 ){
					if(accessArray[i] != lastBit) bitsCombinedMatch = true;
				}else bitsMatch = false;
			}
			i++;
		}
		return bitsCombinedMatch ? ( (lastBit < 0) ? -1 : 1 ) : (bitsMatch ? lastBit : 0); 
	}
	,changeAccesses: function(groupRecord, newValue){
		r = this.aclList.getSelectedRecords()[0]; //user or group record
		if(Ext.isEmpty(r)) return;
		allow = r.get('allow').split(',');
		deny = r.get('deny').split(',');
		group = this.permissionsStore.accessGroups[groupRecord.get('id')];
		if(Ext.isEmpty(group)) return;
		newValue = parseInt(newValue);
		for (var i = 0; i < group.length; i++)
			if(group[i] == 1){
				if( newValue > -1 ){
					if( (allow[i] > -2) && (allow[i] < 2) ) allow[i] = newValue;
					if(deny[i] > -2) deny[i] = 0;
				}
				if( newValue < 1 ){
					if( (deny[i] > -2) && (deny[i] < 2) ) deny[i] = newValue;
					if(allow[i] < 2) allow[i] = 0;
				}
			}
		r.set('allow', allow.join(','));
		r.set('deny', deny.join(','));
		clog(r.get('allow'))
		clog(r.get('deny'))
		this.reloadPermissionsStore()
	}
	,reloadPermissionsStore: function(){
		data = [];
		sr = this.aclList.getSelectedRecords()
		if(!Ext.isEmpty(sr))
			data = this.accessToGroupsData(sr[0], this.permissionsStore.accessGroups)
		this.permissionsStore.loadData( data );
	}
	,onSavePermissionsClick: function(){
		this.aclStore.save();
		this.setReadOnly(true);
	},onApplyPermissionsClick: function(){
		this.aclStore.save();
		this.actions.save.setDisabled(true);
		this.actions.apply.setDisabled(true);
		this.actions.cancel.setDisabled(true);
	}
	,onCancelPermissionsChangeClick: function(b, e){
		this.aclStore.rejectChanges();
		this.reloadPermissionsStore();
		this.actions.save.setDisabled(true);
		this.actions.apply.setDisabled(true);
		this.actions.cancel.setDisabled(true);
	}
})

Ext.reg('CBSecurityPanel', CB.SecurityPanel); // register xtype													
