Ext.namespace('CB'); 
// ----------------------------------------------------------- add user form
CB.AddUserForm = Ext.extend(Ext.Window, {
	data: {}
	,layout: 'fit'
	,autoWidth: true
	,title: L.AddUser 
	,iconCls: 'icon-user-gray'
	,initComponent: function(){
		recs = CB.DB.roles.queryBy(function(r){ return ( (r.get('id') !=3) && (r.get('id') !=1) && (App.loginData.manage || (r.get('id') !=2)) );});//&& (App.loginData.admin || (r.get('id') !=1))
		data = [];
		recs.each(function(r){data.push(r.data)}, this);
		this.rolesStore = new Ext.data.JsonStore({
			autoLoad: true
			,autoDestroy: true
			,fields: [{name: "id", dataIndex: "id"} ,{name: "name", dataIndex: "name"}]
			,proxy: new Ext.data.MemoryProxy()
			,data: data
		});
		
		items = [{
			xtype: 'textfield'
			,allowBlank: false
			,fieldLabel: L.Username
			,name: 'name'
		}];
		CB.DB.languages.each(function(r){
			items.push({xtype: 'textfield'
				,allowBlank: false
				,fieldLabel: L.FullName + ' ('+r.get('abreviation')+')'
				,name: 'l'+r.get('id')
			});
		}, this);
		
		items.push({
				xtype: 'textfield'
				,allowBlank: false
				,fieldLabel: L.Email
				,name: 'email'
				,vtype: 'email'
			},{
				xtype: 'textfield'
				,allowBlank: false
				,fieldLabel: L.Password
				,inputType: 'password'
				,name: 'password'
			},{	xtype: 'textfield'
				,allowBlank: false
				,fieldLabel: L.PasswordConfirmation
				,inputType: 'password'
				,name: 'confirm_password'
			},{
				xtype: 'combo'
				,fieldLabel: L.Office
				,editable: false
				,name: 'office_id'
				,hiddenName: 'office_id'
				,store: new Ext.data.DirectStore({
					autoLoad: true
					,autoDestroy: true
					,proxy: new  Ext.data.DirectProxy({ paramsAsHash: true, directFn: Security.getManagedOffices })
					,reader: new Ext.data.JsonReader({
						successProperty: 'success'
						,idProperty: 'id'
						,root: 'data'
						,messageProperty: 'msg'
						,fields: [ {name: 'id', type: 'int', mapping: 'id'}, 'name' ]
					}
					)
					,listeners: {
						scope: this
						,beforeload: function(st, p){st.baseParams.withNoOffice = true}
						,load: function(st, r, o){
							cbr = this.find('hiddenName', 'office_id')[0];
							v = Ext.value(this.data.office_id, 0);
							cbr.setValue(v);
							cbr.fireEvent('select', cbr);
						}
					}
				})
				,valueField: 'id'
				,displayField: 'name'
				,triggerAction: 'all'
				,value: 0
				,mode: 'local'
				,listeners: {
					scope: this
					,select : function(cb, r, idx){
						this.find('name', 'role_id')[0].setDisabled(cb.getValue() == 0);
					}
				}
			},{
				xtype: 'combo'
				,disabled: true
				,editable: false
				,name: 'role_id'
				,hiddenName: 'role_id'
				,store: this.rolesStore
				,valueField: 'id'
				,displayField: 'name'
				,fieldLabel: L.Access
				,triggerAction: 'all'
				,mode: 'local'
				,value: 4
			},{xtype: 'label', name: 'E', hideLabel: true, cls:'cR', text: ''});
			
		Ext.apply(this, {
			buttons:[
				{text: L.Save, iconCls: 'icon-save', disabled: true, handler: this.saveData, scope: this}
				,{text: Ext.MessageBox.buttonText.cancel, iconCls: 'icon-cancel', handler: function(b, e){this.destroy();}, scope: this}
			]
			,items: [{
				xtype: 'fieldset'
				,border: false
				,autoHeight: true
				,autoWidth: true
				,labelWidth: 150
				,defaults: {width: 250, bubbleEvents: ['change']}
				,items: items
			}	
			]
			,listeners: { 
				scope: this
				,change: function(e){this.setDirty(true);}
				,show: function(){this.syncSize(); this.center()}
			}
		});
		CB.AddUserForm.superclass.initComponent.apply(this, arguments);
		this.on('show', App.focusFirstField, this)
		this.on('close', function(){CB.DB.roles.clearFilter();}, this)
	}
	,setDirty: function(value){
		this._isDirty = value;
		required = true;
		a = this.find('isFormField', true);
		Ext.each(a, function(i){if(!i.allowBlank) required = required && !Ext.isEmpty(i.getValue()); return required; }, this);

		p = this.find('name', 'password')[0];
		pc = this.find('name', 'confirm_password')[0];
		pm = (p.getValue() != pc.getValue());
		msg = required ? '' : L.EmptyRequiredFields;
		this.find('name', 'E')[0].setText( pm ? L.PasswordMissmatch : msg);
		this.fbar.items.get(0).setDisabled(!value || !required || pm);
	}
	,saveData: function(){
		params = {}
		a = this.find('isFormField', true);
		Ext.each(a, function(i){params[i.name] = i.getValue()}, this);
		if(this.data.callback) this.data.callback(params, this.ownerCt);
		this.destroy();
	}			
})
// ----------------------------------------------------------- end of add user form
CB.UsersGroupsTree = Ext.extend(Ext.tree.TreePanel, {
	autoScroll: true
	,containerScroll: true
	,stateId: 'CB_UsersGroupsState'
	,stateful: true
	,rootVisible: false
	,animate: false
	,border: false
	,enableDD: true
	,initComponent: function(){
	
		treeMenuItems = [];
		treeMenuItems.push({text: L.Add, iconCls: 'icon-plus', handler: function(){
				w = new CB.AddUserForm({modal: true, ownerCt: this, data: {callback: this.addUser}});
				w.show();
			}, scope: this}
		);

		treeMenuItems.push([
			{text: L.Delete, iconCls: 'icon-minus', disabled: true, handler: this.delNode, scope: this}
			,{text: L.Remove, iconCls: 'icon-user-arrow', disabled: true, handler: this.deassociateNode, scope: this}
			,'->'
			,{iconCls: 'icon-reload', qtip: L.Reload, scope:this, handler: function(){this.getRootNode().reload();}}
		]);
		
		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({
				directFn: UsersGroups.getChildren
				,paramsAsHash: true
				,listeners:{
					// Add NodePath to the params
					beforeload: { fn: function(treeLoader, node) { treeLoader.baseParams.path = node.getPath('nid'); } }
					,load: { scope: this, fn: function(o, n, r) { if(n.attributes.kind > 1) n.sort(this.sortTree)} }
					,loadexception: {fn : function(loader, node, response) {
							node.leaf = false; //force it to folder?
							node.loaded = false;
						}
					}
				}
			})
			,root: {
				nodeType: 'async'
				,expanded: false
				,expandable: true
				,iconCls: 'icon-home'
				,leaf: false
				,nid: 'root'
				,text: 'root'
			}
			,tbar: treeMenuItems
			,listeners:{
				afterlayout: function(){this.getRootNode().expand()}
				,nodedragover: function(o){
					if( (o.point != 'append')
						|| (o.target == o.dropNode.parentNode)
						|| (o.target.attributes.kind != 2) 
						|| ((o.target.attributes.role_id < 1) || (o.target.attributes.role_id > 2))
						){
						o.cancel = true;
						return;
					}
				}
				,beforenodedrop: function(o){
					o.cancel = true;
					o.dropStatus = true;
				}
				,dragdrop: {scope: this, fn: function( tree, node, dd, e ){
						this.sourceNode = dd.dragOverData.dropNode;
						this.targetNode = dd.dragOverData.target;
						UsersGroups.associate(this.sourceNode.attributes.id, this.targetNode.attributes.id, this.processAssociate, this);
					}
				}
				,beforeappend: { scope: this, fn: function(t, p, n){ 
					n.id = Ext.id();
					if(n.attributes.kind == 1){
						if(n.attributes.role_id > 0 && n.attributes.active != 1){n.attributes.text = n.attributes.text + ' <span class="cG">' + L.inactive + '</span>';}; 
						n.attributes.iconCls = 'icon-user-role' + n.attributes.role_id + '-'+ Ext.value(n.attributes.sex, '');
					}
					if(n.attributes.role_id > 0 && n.attributes.role_id < 3){n.attributes.cls = n.attributes.cls + ' fwB'; n.getUI().addClass('fwB')}; 
					if(n.attributes.pid == App.loginData.id){n.attributes.cls = n.attributes.cls + ' cDB'; n.getUI().addClass('cDB')};
					if(n.attributes.users > 0) n.attributes.text += ' <span class="cG">(' + n.attributes.users + ')</span>';
					n.setText(n.attributes.text);
				} }
				,remove: this.updateChildrenCount
				,append: this.updateChildrenCount
			}
			,selModel: new Ext.tree.DefaultSelectionModel({
				listeners: {
					selectionchange: {scope: this, fn: function(sm, node){ 
							tb = this.getTopToolbar();
							if(Ext.isEmpty(node)){
								tb.items.get(1).setDisabled(true); 
								tb.items.get(2).setDisabled(true); 
							}else{
								p = node.parentNode;
								isGroupAdmin = false;
								if(p) isGroupAdmin = ( (p.attributes.role_id > 0) && (p.attributes.role_id <= 2) );
								if(App.loginData.admin){
									tb.items.get(1).setDisabled(node.attributes.kind > 2);
									tb.items.get(2).setDisabled(!isGroupAdmin);
								}else{
									tb.items.get(1).setDisabled((node.attributes.kind != '1') || ((node.attributes.pid != App.loginData.id) && (!App.loginData.admin))); 
									tb.items.get(2).setDisabled((node.attributes.kind != '1') || (!isGroupAdmin)  || (p.attributes.role_id >= node.attributes.role_id) ); 
								}
							}
						} 
					}
				}
			})
		});
		CB.UsersGroupsTree.superclass.initComponent.apply(this, arguments);
	}
	,afterRender: function() {
        CB.UsersGroupsTree.superclass.afterRender.apply(this, arguments);
    }
	,addUser: function(params, t){
		//params: name, office_id, role_id
		UsersGroups.addUser(params, t.processAddUser, t);
	}
	,processAddUser: function(r, e){
		if(r.success !== true) return false;
		path = '/root/o-'+r.data.office_id+'/u-'+r.data.tag_id;
		this.getRootNode().reload(function(){this.selectPath(path, 'nid')}, this);
		App.mainViewPort.fireEvent('useradded', r.data);
	}
	,sortTree: function(n1, n2){ 
		return ( 	  (n1.attributes.role_id > n2.attributes.role_id) ? 1 : 
					( (n1.attributes.role_id < n2.attributes.role_id) ? -1 : ( (n1.text < n2.text) ? -1 : 1 ) )   )
	}
	,deassociateNode: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		Ext.Msg.confirm(L.ExtractUser, L.ExtractUserMessage.replace('{user}', n.attributes.name).replace('{office}', n.parentNode.attributes.text),
			function(b){if(b == 'yes') UsersGroups.deassociate(n.attributes.id, n.parentNode.attributes.id, this.processDeassociate, this); }, this
		)
	}
	,processDeassociate: function(r, e){
		if(r.success !== true) return false;
		n = this.getSelectionModel().getSelectedNode();
		attr = n.attributes;
		attr.iconCls = 'icon-user-gray';
		n.remove(true);
		if(r.outOfOffice){
			p = this.getRootNode().findChild( 'nid', 'o-0');
			if(p.loaded){
				p.appendChild(attr);
				p.sort(this.sortTree);
			}else{
				p.attributes.users++;
				p.setText(p.attributes.text.split('<')[0] + ' <span class="cG">(' + p.attributes.users + ')</span>');
			}
		}
	}
	,processAssociate: function(r, e){
		if(r.success !== true) return false;
		attr = this.sourceNode.attributes;
		//attr.id = Ext.id()
		//attr.iconCls = 'icon-user-gray';
		if(this.targetNode.loaded){
			this.targetNode.appendChild(attr);
			this.targetNode.sort(this.sortTree);
		}else{
			this.targetNode.attributes.users++;
			this.targetNode.setText(this.targetNode.attributes.text.split('<')[0] + ' <span class="cG">(' + this.targetNode.attributes.users + ')</span>');
		}
		if(this.sourceNode.parentNode.attributes.nid == 'o-0') this.sourceNode.remove(true);
	}
	,delNode: function(){
		n = this.getSelectionModel().getSelectedNode();
		if(!n) return;
		switch(n.attributes.kind){
		case '1': 
			this.deletedUserData = n.attributes;
			Ext.MessageBox.confirm(L.Confirmation, L.DeleteUser + ' "'+n.attributes.text+'"?', 
			function(btn, text){
				if(btn == 'yes'){
					n = this.getSelectionModel().getSelectedNode();
					UsersGroups.deleteUser(n.attributes.id, this.processDelNode, this);
				}
			}
			, this);
			break;
		case '2': 
			Ext.MessageBox.confirm(L.Confirmation, L.DeleteOfficeConfirmationMessage + ' "'+n.attributes.text+'"?', 
			function(btn, text){
				if(btn == 'yes'){
					n = this.getSelectionModel().getSelectedNode();
					UsersGroups.deleteOffice(n.attributes.id, this.processDelNode, this);
				}
			}
			, this);
			break;
		}
	}
	,processDelNode: function(r, e){
		if(r.success !== true) return false;
		nid = this.getSelectionModel().getSelectedNode().attributes.nid;
		deleteNodes = [];
		this.getRootNode().cascade(function(n){if(n.attributes.nid == nid) deleteNodes.push(n)}, this);
		for(i = 0; i< deleteNodes.length; i++) deleteNodes[i].remove(true);
		if(this.deletedUserData){
			App.mainViewPort.fireEvent('userdeleted', this.deletedUserData);
			delete this.deletedUserData;
		}
	}
	,updateChildrenCount: function( t, p, n ){
		if(Ext.isEmpty(n)) return;
		if(Ext.isEmpty(p.childNodes)){
			p.setText(p.attributes.text.split('<')[0].trim());
			return;
		}
		p.attributes.users = p.childNodes.length;
		p.setText(p.attributes.text.split('<')[0] + ' <span class="cG">(' + p.attributes.users + ')</span>');
	}
})
// ----------------------------------------------------------- edit user form
CB.UserEditWindow = Ext.extend(Ext.Window, {
	iconCls: 'icon-user'
	,title: L.User
	,modal: true
	,closeAction: 'destroy'
	,y: 150
	,width: 780
	,autoHeight: true
	,layout: 'fit'
	,autoShow: true
	,initComponent: function() {
		Ext.apply(this, {
			items: new CB.GenericEditForm({
				header: false
				,data: this.data
				,api: {
					load: 	UsersGroups.getUserData
					,submit: 	UsersGroups.saveUserData
				}
				,listeners: {
					scope: this
					,savesuccess: function(f, a){ this.fireEvent('savesuccess', f, a); this.destroy() }
					,cancel: this.destroy
					,change: function(){ this.syncSize() }
				}
			})
			,listeners:{scope: this, loaded: this.syncSize}
		});
		CB.UserEditWindow.superclass.initComponent.apply(this, arguments);
	}
})
// ----------------------------------------------------------- form
CB.UsersGroupsForm = Ext.extend(Ext.form.FormPanel, {
	border: false
	,disabled: true
	,fileUpload: true
	,data: {}
	,initComponent: function(){
		bulletRenderer = function(v, m){
			m.css = 'taC';
			if(v == 1) return '<span class="icon-padding icon-tick"></span>';
			return '';
		}
		this.userInfo = new Ext.DataView({
			tpl: ['<img class="fl user-photo-field click icon-user32-{sex}" src="css/i/s.gif">'
				,'<span class="fwB click">{l'+App.loginData.language_id+'}</span><br />'
				,'<span class="cG">'+L.User+':</span> {name}, <span class="cG">'+L.lastAction+':</span> {[ Ext.isEmpty(values.last_action_time) ? "" : values.last_action_time ]}<br />'
				,'<span class="cG">'+L.addedByUser+':</span> {owner}, {cdate}'
			]
			,itemSelector:'.click'
			,autoHeight: true
			,listeners:{ scope: this, click: this.onEditUserDataClick }
		});

		Ext.apply(this, {
			layout: 'border'
			,api: {submit: User.uploadPhoto }
			,hideBorders: true
			,tbar:[{text: L.Save, iconCls: 'icon-save', disabled: true, handler: this.saveData, scope: this}
				,{text: Ext.MessageBox.buttonText.cancel, iconCls: 'icon-cancel', disabled: true, handler: function(b, e){e.stopPropagation();this.loadData();}, scope: this}
				,{xtype: 'tbseparator', hidden: true}
				,{text: L.Edit, iconCls: 'icon-pencil', handler: this.onEditUserDataClick, scope: this, hidden: true}
				,{xtype: 'tbseparator', hidden: true}
				,{text: L.Options, hidden: true, menu: [
					{text: L.ChangePassword, iconCls: 'icon-key', handler: this.onEditUserPasswordClick, scope: this}
					,'-'
					,{text: L.ChangeUsername, iconCls: 'icon-pencil', handler: this.onEditUsernameClick, scope: this}
				]}
			]
			,items: [{
					region: 'north'
					,height: 60
					,bodyStyle: 'padding: 10px'
					,items: [{
						xtype: 'textfield'
						,name: 'photo'
						,cls: 'fl'
						,style: 'position:absolute;width:1px;height:1px;opacity:0;top:-100px'
						,inputType: 'file'
						,allowBlank: false
						,listeners:{
							scope: this
							,afterrender: function(c){
								//clog('afterRender', arguments);
								c.getEl().on('change', this.onPhotoChanged, this);
							}
						}
					}, this.userInfo ]
				},{
				region: 'center'
				,hideBorders: true
				,autoScroll: true
				,bodyStyle: 'padding: 0 20px'
				,items: [{
						border: false
						,anchor: '100%'
						,minHeight: 100
						,autoHeight: true
						,xtype: 'grid'
						,style: 'margin-top: 15px'
						,stripeRows: true
						,sm: new Ext.grid.CellSelectionModel()
						,store: new Ext.data.JsonStore({
							autoDestroy: true
							,fields: ['id', 'office', 'manager', 'user']
						})
						,colModel: new Ext.grid.ColumnModel({
							defaults: {
								width: 120
								,sortable: true
							}
							,columns: [
								{header: L.Offices, width: 150, dataIndex: 'office'}
								,{header: L.Manager, dataIndex: 'manager', renderer: bulletRenderer}
								//,{header: 'Адвокат', dataIndex: 'lawyer', renderer: bulletRenderer}
								,{header: L.User, dataIndex: 'user', renderer: bulletRenderer}
							]
						})
						,viewConfig: {
							forceFit: true
							,markDirty: false
							,getRowClass: function(r, index) {
								return ((r.get('user') != 1)  && (r.get('manager') != 1)) ? '' : 'fwB';
							}
						}
						,listeners:{
							celldblclick: {scope: this, fn: function(g, ri, ci, e){
									r = g.getStore().getAt(ri);
									switch(g.getColumnModel().getDataIndex(ci)){
										case 'manager':
											r.set('manager', (r.get('manager') == 1) ? null : 1);
											r.set('user', null);
											break;
										case 'user': 
											r.set('manager', null);
											r.set('user', (r.get('user') == 1) ? null : 1);
											break;
										default: return;
									}
									this.fireEvent('change');
								}
							}
						}
					}
				]
			},{
				region: 'south'
				,height: 300
				,split: true
				,collapseMode: 'mini'
				,collapsed: true
				,tbar: [{xtype: 'label', cls:'fwB', text: L.ChangeLog + ': '}]
			}
			]
			,listeners: {
				change: { scope: this, fn: function(e){this.setDirty(true);} }
				,show: {scope: this, fn: function() {
						f = function(){ a = this.find('isFormField', true); a[0].focus();}
						f.defer(500);
					}
				}
			}
		});
		CB.UsersGroupsForm.superclass.initComponent.apply(this, arguments);
		this.addEvents('beforesave', 'save');
		this.grid = this.findByType('grid')[0];
	}
	,setDirty: function(value){
		this._isDirty = (value !== false);
		this.getTopToolbar().items.get(0).setDisabled(!this._isDirty);
		this.getTopToolbar().items.get(1).setDisabled(!this._isDirty);
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------
	,loadData: function(id){
		if(!Ext.isEmpty(id)) this.data.id = id;
		this.getEl().mask(L.LoadingData + ' ...');
		UsersGroups.getAccessData(this.data.id, this.processLoadedData, this);
	}
	,processLoadedData: function(response, e){
		//clog('process loading data')
		if(response.success === true){
			this.data.name = Ext.value(response.data.name);
			this.data.title = Ext.value(response.data['l'+App.loginData.language_id], response.data.name);
			this.data.template_id = response.data.template_id;
			this.userInfo.data = response.data;
			this.userInfo.update(response.data);
			this.grid.setDisabled(response.data.id == App.loginData.id);//disable editing access for self
			this.grid.getStore().loadData(response.data.offices, false);
			this.canEditUserData = ((App.loginData.admin) || (response.data.pid == App.loginData.id) || (response.data.id == App.loginData.id));
			eb = this.getTopToolbar().find('iconCls', 'icon-pencil')[0];
			eb.setVisible(this.canEditUserData); // edit button
			idx = this.getTopToolbar().items.indexOf(eb);
			this.getTopToolbar().items.itemAt(idx -1).setVisible(this.canEditUserData);// divider for edit button
			visible = (this.canEditUserData || (response.data.id == App.loginData.id));
			this.getTopToolbar().items.itemAt(idx + 1).setVisible(visible); //divider for options button
			this.getTopToolbar().items.itemAt(idx + 2).setVisible(visible); // options button
			/*this.getTopToolbar().items.itemAt(idx + 1).setVisible(this.canEditUserData || (response.data.id == App.loginData.id)); //divider for change password button
			this.getTopToolbar().items.itemAt(idx + 2).setVisible(this.canEditUserData || (response.data.id == App.loginData.id)); // change password button
			this.getTopToolbar().items.itemAt(idx + 3).setVisible(this.canEditUserData); //change username divider
			this.getTopToolbar().items.itemAt(idx + 4).setVisible(this.canEditUserData); //change username button/**/
			this.updatePhoto(response.data.photo);
			this.setDisabled(false);

			this.fireEvent('loaded', this.data);
		}
		this.getEl().unmask();
		this.setDirty(false);
	}
	,saveData: function(){
		this.fireEvent('beforesave');
		/*if(this.find('name', 'passwd')[0].getValue() != this.find('name', 'passwd2')[0].getValue()){
			Ext.Msg.alert(L.Error, L.PasswordMissmatch);
			return;
		}/**/
		this.getEl().mask(L.SavingChanges + ' ...')
		//params = this.getForm().getFieldValues();
		params = { offices: [] };
		this.grid.getStore().each(function(r){params.offices.push(r.data)}, this);
		params.id = this.data.id;
		UsersGroups.saveAccessData(params, function(r, e){ this.setDirty(false); this.getEl().unmask(); this.fireEvent('save');}, this);
	}			
	,onEditUserDataClick: function(w, idx, el, ev){
		if(ev && (ev.getTarget().localName == "img") ) {
			el = this.find('name', 'photo')[0].getEl();
			//clog(el);
			el.dom.click();
		}else this.fireEvent('edit');
	}
	,onPhotoChanged: function(ev, el, o){
		if(!this.getForm().isValid()) return;
		this.getForm().submit({
			clientValidation: true
		   	,params: {
		        	id: this.data.id	
		    	}
			,scope: this
			,success: function(form, action) { this.updatePhoto(action.result.photo); }
		    	,failure: App.formSubmitFailure
		});
	}
	,updatePhoto: function(name){
		if(Ext.isEmpty(name)) return;
	       	del = this.userInfo.getEl().query('img.user-photo-field')[0]
	       	del.src = '/photo/' + name;
	}
	,onEditUsernameClick: function(){
		Ext.Msg.prompt(L.ChangeUsername, L.ChangeUsernameMessage, function(btn, text){
			if (btn == 'ok'){
				if(Ext.isEmpty(text)) return Ext.Msg.alert(L.Error, L.UsernameCannotBeEmpty);
				r = /^[a-z0-9\._]+$/i;
				if(Ext.isEmpty(r.exec(text))) return Ext.Msg.alert(L.Error, L.UsernameInvalid);
				UsersGroups.changeUsername(
					{id: this.data.id, name: text}, 
					function(r, e){ 
						if(r.success !== true) return Ext.Msg.alert(L.Error, L.ErrorOccured);
						this.data.name = r.name; 
						this.userInfo.data.name = r.name;
						this.userInfo.update(this.userInfo.data);
					}, this)
			}
		}, this, false, this.data.name);
	}
	,onEditUserPasswordClick: function(){
		w = new CB.ChangePasswordWindow({data: this.data});
		w.show();
	}
})
// ----------------------------------------------------------- end of form
// ---------------------------------------------- Main panel
CB.UsersGroups = Ext.extend(Ext.Panel, {
	layout: 'border'
	,border: false
	,closable: true
	,iconCls: 'icon-users'
	,title: L.UserManagement
	,initComponent: function(){
		this.tree = new CB.UsersGroupsTree({
			region: 'west'
			,width: 250
			,split: true
			,collapseMode: 'mini'
		});//west region
		this.tree.getSelectionModel().on( 'selectionchange', this.onTreeSelectionChange, this );
		this.tree.getSelectionModel().on( 'beforeselect', this.onTreeBeforeSelect, this );

		this.form = new CB.UsersGroupsForm( { 
			region: 'center'
			,api: {submit: User.uploadPhoto }
			,listeners:{
				scope: this
				,beforesave: this.onBeforeFormSave
				,save: this.onFormSave
				,loaded: this.onLoadFormData
				,edit: this.onEditUserData
			}
		} );//center region

		Ext.apply(this, { items: [this.tree, this.form] });

		CB.UsersGroups.superclass.initComponent.apply(this, arguments);
	}
	,onBeforeFormSave: function(){
		this.lastPath = '';
		n = this.tree.getSelectionModel().getSelectedNode();
		if(n) this.lastPath = n.getPath('nid');
	}
	,onFormSave: function(){
		this.tree.getRootNode().reload(function(){this.tree.selectPath(this.lastPath, 'nid', 
			function(success){
				if(!success){
					this.form.setDisabled(true);
					this.tree.getRootNode().cascade(function(n){
						if(n.attributes.id == this.form.data.id) {
							this.tree.getSelectionModel().select(n); 
							this.form.setDisabled(false);
							return false;
						}
					}, this);
				}
			}.createDelegate(this) 
		)}, this);
	}
	,onTreeSelectionChange: function(sm, node){
		n = this.tree.getSelectionModel().getSelectedNode();
		if((!n) || (n.attributes.kind != 1)){
			this.form.setDisabled(true);
			if(this.loadFormTask) this.loadFormTask.cancel();
			return ;
		}
		this.loadId = n.attributes.id;
		this.onLoadFormTask();
	}
//------------------------------------------------------------------------------------------------------------------------------------------------
	,onTreeBeforeSelect: function(sm, newNode, oldNode){
		if(Ext.value(this._forceSelection, 0)){ this._forceSelection = 0; return true; }
		if(oldNode && this.form._isDirty){
			this.newNode = newNode;
			Ext.Msg.show({
				buttons: Ext.Msg.YESNO
				,title: L.Confirmation
				,msg: L.SaveChangesConfirmationMessage
				,scope: this
				,fn: function(btn, text){
					if (btn == 'yes') this.form.saveData(); 
					else{
						this._forceSelection = 1;
						this.tree.getSelectionModel().select(this.newNode);
						this.form.setDirty(false);
					}
				}
			}); 
			return false;
		}
	}
	,onLoadFormTask: function(){
		if(!this.loadFormTask) this.loadFormTask = new Ext.util.DelayedTask(function(){this.form.loadData(this.loadId);}, this);
		this.loadFormTask.delay(500);
	}
	,onLoadFormData: function(data){
		this.tree.getRootNode().cascade(function(n){
			if(n.attributes.id == data.id) n.setText(data.title);
		}, this);
	}
	,onEditUserData: function(){
		if(!this.form.canEditUserData) return;
		data = Ext.apply({}, this.form.data);
		data.id = data.id.split('-').pop();
		n = this.tree.getSelectionModel().getSelectedNode();
		iconCls = n ? n.attributes.iconCls : 'icon-user';
		w = new CB.UserEditWindow({title: data.title, iconCls: iconCls, data: data, listeners: {scope: this, savesuccess: function(){this.form.loadData()}}});
		w.show();
	}
});
Ext.reg('CBUsersGroups', CB.UsersGroups); // register xtype
// ----------------------------------------------------------- change password window

CB.ChangePasswordWindow = Ext.extend(Ext.Window, {
	modal: true
	,title: L.ChangePassword
	,autoWidth: true
	,autoHeight: true
	,hideBorders: true
	,iconCls: 'icon-key'
	,initComponent: function() {
		items = [];
		if(this.data.id == App.loginData.id)
			items = [{
				xtype: 'textfield'
				,fieldLabel: L.CurrentPassword
				,inputType: 'password'
				,name: 'currentpassword'
				,allowBlank: (this.data.id != App.loginData.id)
			}];
		items.push({
				xtype: 'textfield'
				,fieldLabel: L.Password
				,inputType: 'password'
				,name: 'password'
				,allowBlank: false
				,shouldMatch: true
			},{
				xtype: 'textfield'
				,fieldLabel: L.ConfirmPassword
				,inputType: 'password'
				,name: 'confirmpassword'
				,allowBlank: false
				,shouldMatch: true
			},{
				xtype: 'displayfield'
				,hideLabel: true
				,cls: 'cR taC'
				,anchor: '100%'
				,id: 'msgTarget'
				,value: '&nbsp;'
			});
		
		Ext.apply(this, {
			items: {
				xtype: 'form'
				,autoWidth: true
				,autoHeight: true
				,hideBorders: true
				,monitorValid: true
				,baseParams: this.data
				,api: {
					submit: UsersGroups.changePassword
				}
				,items: {
					xtype: 'fieldset'
					,labelWidth: 150
					,autoWidth: true
					,autoHeight: true
					,hideBorders: true
					,style: 'margin:0'
					,defaults: {
						listeners: {
							scope: this
							,invalid: function(field, msg){
								if(field.getEl().hasClass('x-form-invalid')) this.hasInvalidFields = true;
							}
						}
					}
					,items: items
				}
				,listeners: {
					scope: this
					,clientvalidation: function(form, valid){
						label = this.find('id', 'msgTarget')[0];
						if(!valid && this.hasInvalidFields){
							label.setValue(L.EmptyRequiredFields);
							return;
						}
						a = this.find('shouldMatch', true);
						if(a[0].getValue() != a[1].getValue()){
							this.findByType('form')[0].buttons[0].setDisabled(true);
							label.setValue(L.PasswordMissmatch);
							return;
						}
						label.setValue('&nbsp;');
					}
				}
				,buttons: [{text: Ext.MessageBox.buttonText.ok, iconCls:'icon-tick', formBind: true, type: 'submit', scope: this, plugins: 'defaultButton'
						,handler: function(){
							f = this.findByType('form')[0];
							f.getForm().submit({
								clientValidation: true
								,params: this.data
								,scope: this
								,success: this.destroy
								//,failure: App.formSubmitFailure
							})
						}
					}
					,{text: Ext.MessageBox.buttonText.cancel, iconCls:'icon-cancel', handler: this.destroy, scope: this}
					]			
			}
			,listeners: {
				afterrender: function(){ f = this.findByType('form')[0]; f.syncSize(); App.focusFirstField(f) }
			}
		});
		CB.ChangePasswordWindow.superclass.initComponent.apply(this, arguments);
	}
}
)