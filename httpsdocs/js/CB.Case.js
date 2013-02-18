Ext.namespace('CB');
// ----------------------------------------------------------- add case form
CB.AddCaseForm = Ext.extend(Ext.Window, { //added to tests
	data: {}
	,layout: 'fit'
	,width: 400
	,title: L.NewCase
	,iconCls: 'icon-briefcase'
	,initComponent: function(){
		this.buttonSave = this.hideTitle ?  new Ext.Button({text: L.Transfer, iconCls: 'icon-folder-export', disabled: true, handler: this.saveData, scope: this}) :
			new Ext.Button({text: L.Save, iconCls: 'icon-save', disabled: true, handler: this.saveData, scope: this});
		this.buttonCancel = new Ext.Button({text: Ext.MessageBox.buttonText.cancel, iconCls: 'icon-cancel', handler: function(b, e){e.stopPropagation();this.destroy();}, scope: this});
		reader = new Ext.data.JsonReader({
			successProperty: 'success'
			,idProperty: 'id'
			,root: 'data'
			,messageProperty: 'msg'
			,fields: [ {name: 'id', type: 'int', mapping: 'id'}, 'name' ]
		}
		);
		Ext.apply(this, {
			buttons:[ this.buttonSave, this.buttonCancel ]
			,items: [{
				xtype: 'form'
				,bodyStyle: 'padding:10px'
				,border: false
				,autoHeight: true
				,defaults: {width: 250, bubbleEvents: ['change']}
				,items: [{	xtype: 'combo'
					,fieldLabel: L.CaseType
					,editable: false
					,name: 'case_type'
					,hiddenName: 'case_type'
					,valueField: 'id'
					,displayField: 'name'
					,triggerAction: 'all'
					,mode: 'local'
					,store: CB.DB.caseTypes
					,value: CB.DB.caseTypes.getAt(0).get('id')
					,hidden: (CB.DB.caseTypes.getCount() < 2)
				},{
					xtype: 'textfield'
					,fieldLabel: L.CaseNumber
					,name: 'nr'
					,value: Ext.value(this.data.nr, '')
				},{
					xtype: 'textfield'
					,fieldLabel: L.CaseName
					,name: 'name'
					,hidden: this.hideTitle
					,value: Ext.value(this.data.name, '')
				},{
					xtype: 'datefield'
					,fieldLabel: L.Date
					,name: 'date'
					,format: App.dateFormat
					,value: new Date()
				}/*,{	xtype: 'combo'
					,fieldLabel: L.Office
					,editable: false
					,name: 'office'
					,hiddenName: 'office_id'
					,valueField: 'id'
					,displayField: 'name'
					,triggerAction: 'all'
					,mode: 'local'
					,store: new Ext.data.DirectStore({
						autoLoad: true
						,autoDestroy: true
						,proxy: new  Ext.data.DirectProxy({ paramsAsHash: true, directFn: Security.getManagedOffices })
						,reader: reader
						,listeners: {
							scope: this
							,load: function(st, r, o){
								cbr = this.find('hiddenName', 'office_id')[0];
								v = Ext.value(this.data.office_id, null);
								cbr.setValue(v);
								cbr.fireEvent('select', cbr);
							}
						}
					})
					,listeners: {
						scope: this
						,select : function(cb, r, idx){
							this.find('name', 'user_id')[0].store.reload({params : {office_id: cb.getValue()}});
							this.setDirty(true);
						}
					}
				},{
					xtype: 'combo'
					,fieldLabel: L.ResponsiblePerson
					,disabled: true
					,editable: false
					,name: 'user_id'
					,hiddenName: 'user_id'
					,valueField: 'id'
					,displayField: 'name'
					,triggerAction: 'all'
					,mode: 'local'
					,value: Ext.value(this.data.user_id, '')
					,store: new Ext.data.DirectStore({
						autoLoad: false
						,autoDestroy: true
						,proxy: new  Ext.data.DirectProxy({ paramsAsHash: true, directFn: Security.getOfficeUsers })
						,reader: reader
						,listeners: {
							scope: this
							,load: function(st, r, o){
								cbu = this.find('name', 'user_id')[0];
								v = cbu.getValue();
								idx = st.findExact('id', v);
								v = (idx < 0) ? null : v;
								cbu.setValue(v);
								cbu.setDisabled(Ext.isEmpty(v) && (st.getCount()==0) );
							}
						}
					})
				}/**/
				]
			}	
			]
			,listeners: { change: { scope: this, fn: function(field, newValue, oldValue){ this.setDirty(true); } } }
		});
		CB.AddCaseForm.superclass.initComponent.apply(this, arguments);
		this.on('show', App.focusFirstField, this)
	}
	,setDirty: function(value){
		this._isDirty = value;
		u = this.find('name', 'name')[0];
		//emptyOffice = Ext.isEmpty(this.find('hiddenName', 'office_id')[0].getValue());
		this.buttonSave.setDisabled(!value || ( u.isVisible() && Ext.isEmpty(u.getValue())));// || emptyOffice
		//this.find('name', 'user_id')[0].setDisabled(emptyOffice);
	}
	,saveData: function(){
		params = this.items.itemAt(0).getForm().getValues();
		params.pid = this.data.pid;
		if(this.data.callback) this.data.callback(params, this.ownerCt);
		this.destroy();
	}
})
Ext.reg('CBAddCaseForm', CB.AddCaseForm);
// ----------------------------------------------------------- end of add case form

CB.Case = Ext.extend(Ext.Panel, {
	title: L.LoadingData + ' ...'
	,closable: true
	,disabled: true
	,iconCls: 'icon-node-case'
	,border: false
	,data: {}
	,name: 'caseWindow'
  	,initComponent: function(){
	}
});
Ext.reg('Case', CB.Case)
