Ext.namespace('CB');
/* loader class for custom case card class (if defined) or generic case card*/
CB.Case = Ext.extend(Ext.Panel, {
	title: L.LoadingData + ' ...'
	,closable: true
	,iconCls: 'icon-node-case'
	,border: false
	,autoScroll: true
	,params: {}
	,name: 'caseWindow'
  	,initComponent: function(){
  		Ext.apply(this, {
  			items: []
  			,listeners: {
  				render: this.onAfterShow
  			}
  		})
  		CB.Case.superclass.initComponent.apply(this, arguments)
	}
	,onAfterShow: function(c){
		if(Ext.isEmpty(this.params.id)) return;
		this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
		Objects.load(this.params, this.processLoad, this);
	}
	,processLoad: function(r, e){
		this.getEl().unmask();
		if(r.success !== true) return;
		this.data = r.data;
		this.setTitle(Ext.value(this.data.name, this.data.nr))

		/* adding corresponding view: custom if defined or generic otherwise*/
		view = null;
		if(!isNaN(this.data.type_id) && Ext.isDefined(CB['CaseCard'+this.data.type_id]))
			view = new CB['CaseCard'+this.data.type_id]({params: this.params});
		else view = new CB.CaseCard( {params: this.params} );
		this.add(view);
		this.doLayout();
	}
});
Ext.reg('Case', CB.Case)

/*generic case card class */
CB.CaseCard = Ext.extend(Ext.Panel, {
	hideBorders: true
	,border: false
	,autoScroll: true
	,bodyCssClass: 'casecard'
	,tbarCssClass: 'x-panel-gray'
	,padding: 5
	,params: {}
  	,initComponent: function(){
		this.titleLabel = new Ext.form.Label({colspan: 2, cls: 'casetitle', text: 'Case title'});
		this.searchField = new Ext.ux.SearchField({width: 250, minListWidth: 250, listeners: {scope: this, 'search': this.onSearchQuery} } )
		
		Ext.apply(this, {
			tbar: [ 
				{iconCls: 'icon-refresh', text: L.Refresh, scope: this, handler: this.reload} 
				,'-'
				,{iconCls: 'icon-browse', text: L.BrowseCase}
				,{iconCls: 'icon-calendarView', text: L.Calendar}
				,{iconCls: 'icon-taskView', text: L.Tasks}
				,{iconCls: 'icon-actionView', text: L.Actions}
				,{iconCls: 'icon-files', text: L.Files}
				,{iconCls: 'icon-permissions', text: L.Permissions}
				,'->'
				,this.searchField
			]
			,defaults: { border: false }
			,items:[
				this.titleLabel
								,{
					layout: 'hbox'
					,align: 'stretchmax'
					,renderHidden: true
					,autoHeight: true
					,items: [new CB.CaseCardProperties({ width: 450, params: this.params, listeners: {scope: this, load: this.onPropertiesLoad} })
						,new CB.CaseCardMilestones({ flex: 1, params: this.params, bodyStyle: 'padding-left: 30px' })
					]
				}
				,{ xtype: 'CaseCardDivider'}
				,{
					layout: 'hbox'
					,align: 'stretchmax'
					,renderHidden: true
					,autoHeight: true
					,items: [new CB.CaseCardActions({ width: 450, params: this.params })
						,new CB.CaseCardTasks({ flex: 1, params: this.params, bodyStyle: 'padding-left: 30px' })
					]
				}
			]
		})
  		CB.CaseCard.superclass.initComponent.apply(this, arguments)
		this.reload();
	}
	,onPropertiesLoad: function(block){
		this.data = block.data;
		this.titleLabel.setText(Ext.value(this.data.name, this.data.nr));
	}
	,onSearchQuery: function(query, e) {
		// if(query == this.params.query) return;
		// params = Ext.apply({}, this.params);
		// params.query = query;
		// this.setParams(params);
	}
	,reload: function(){
		c = this.findByType(CB.CaseCardBlock)
		for (var i = 0; i < c.length; i++) {
		 	if(c[i].reload) c[i].reload();
		 }
	}

});
Ext.reg('CaseCard', CB.CaseCard)

CB.CaseCardDivider = Ext.extend(Ext.form.Label, {
	xtype: 'label'
	,cls: 'divider'
	,html: '&nbsp;'
})
Ext.reg('CaseCardDivider', CB.CaseCardDivider)

/*generic block class for case properties */
CB.CaseCardBlock = Ext.extend(Ext.Panel, {
	hideBorders: true
	,border: false
	,layout: 'fit'
	,autoHeight: true
	//,autoWidth: true
	,unstyled: true
	,params: {}
	
	,cls: 'block'
	,serverRoot: 'serverBlockIdentifier'
	
	,fields: [{name: 'id', type: 'int'}, 'name', 'iconCls']
	,tpl: ['<ul>'
		,'<tpl for=".">'
		,'<li class="icon-padding16 {iconCls}">{name}</li>'
		,'</tpl>'
		,'</ul>'
	]
	,itemSelector: 'li'
  	,overClass: 'over'
  	,initComponent: function(){
  		this.store = new Ext.data.JsonStore({ fields: this.fields });
  		this.view = new Ext.DataView({
  			store: this.store
  			,tpl: new Ext.XTemplate( this.tpl, {compiled: true})
  			,autoHeight:true
  			,itemSelector: this.itemSelector
  			,overClass: this.overClass
  			,emptyText: L.noData
  		})
  		Ext.apply(this, {
  			defaults: { border: false }
  			,items: this.view
  			,listeners: {
  				scope: this
  				,click: this.onItemClick
  			}
  		})
  		this.relayEvents(this.view, ['click'])
  		CB.CaseCardBlock.superclass.initComponent.apply(this, arguments)
	}
	,reload: function(){
		params = {}
		params[this.serverRoot] = this.params;
		Objects.queryCaseData( params, this.processReload, this)
	}
	,processReload: function(r, e){
		if(r.success !== true) return;
		this.data = Ext.value(Ext.value(r[this.serverRoot], {}).data, {});
		data = this.prepareLoadedData(this.data);
		this.store.loadData(data);
		this.fireEvent('load', this);
		this.ownerCt.syncSize();
	}
	/* prepareLoadedData - this function should be overwriten for custom processing of server loaded data */
	,prepareLoadedData: function(data){return data}
	/* onItemClick - this function should be overwriten for custom processing of items click */
	,onItemClick: function(view, itemIndex, el, ev){}
});
Ext.reg('CaseCardBlock', CB.CaseCardBlock)

/* case properties block */
CB.CaseCardProperties = Ext.extend(CB.CaseCardBlock, {
	cls: 'block-props'
	,serverRoot: 'properties'
	,itemSelector: 'a'
	,fields: ['id', 'name', 'value', 'type', 'cfg']
	,tpl: ['<span class="fr"><a name="edit" href="#">'+L.edit+' ...</a></span>'
		,'<table>'
		,'<tpl for=".">'
		,'<tr><th style="padding-top: 3px">{name}</th><td>{value}</td></tr>'
		,'</tpl>'
		,'</table>'
	]
	,prepareLoadedData: function(data){
		rez = [{}];
		if(Ext.isEmpty(data['properties'])) return rez;
		for (var i = 0; i < data['properties'].length; i++) {
			f = data['properties'][i];
			// cfg = Ext.value(f.cfg, {});
			// r = App.getCustomRenderer(f.type);
			// if(r){
			// 	rec = new this.store.recordType(f);
			// 	f.value = r(f.value, {}, rec);
			// }
			rez.push({id: Ext.id(), name: f.title, value: f.value})
		};
		return rez;
	}
	,onItemClick: function(dv, itemIndex, el, ev){
		a = ev.getTarget('a')
		if(Ext.isEmpty(a)) return;
		switch(a.name){
			case 'edit': 
				App.openObject(this.data.template_id, this.data.id);
				break;
			default:
				r = dv.store.getAt(itemIndex -1);
				if(r) App.locateObject(r.get('id'), r.get('pid') );
		}
	}
});
Ext.reg('CaseCardProperties', CB.CaseCardProperties)

/*generic block class for actions */
CB.CaseCardActions = Ext.extend(CB.CaseCardBlock, {
	cls: 'block-actions'
	,serverRoot: 'actions'
	,fields: [{name: 'id', type: 'int'}, {name: 'pid', type: 'int'}, 'name', 'iconCls', {name: 'date', type: 'date'}, {name: 'cid', type: 'int'}, 'path' ]
	,tpl: ['<ul>'
        ,'<li><b>' + L.Actions + '</b> <a name="addObject" class="click">' + L.addNew + ' ...</a></li>'
		,'<tpl for=".">'
		,'<li><a class="i {iconCls}" href="#{id}">{name}</a>'
			,'<br />'
			,'<span class="info"><a name="cid" href="#{cid}">{[ CB.DB.usersStore.getName(values.cid)]}</a>, {[values.date.format("F j")]}</span>'
		,'</li>'
		,'</tpl>'
		,'</ul>'
	]
	,prepareLoadedData: function(data){
		for (var i = 0; i < data.length; i++) {
			data[i].date = date_ISO_to_date(data[i].date);
			data[i].iconCls = getItemIcon(data[i])
		};
		return data;
	}
	,onItemClick: function(dv, itemIndex, el, ev){
		a = ev.getTarget('a')
		if(Ext.isEmpty(a)) return;
        switch(a.name){
            case 'cid': Ext.Msg.alert('Click', 'Open user window'); break;
            case 'addObject':
                data = Ext.apply({}, {
                    pid: this.params.caseId
                    ,path: this.params.path
                    ,pathtext: this.params.pathtext
                }, b.data);
                App.mainViewPort.createObject(data, ev);
                break;
            default:
                r = dv.store.getAt(itemIndex);
                if(r) App.mainViewPort.openObject({id: r.get('id') });
		}
	}
});
Ext.reg('CaseCardActions', CB.CaseCardActions)

/*generic block class for tasks */
CB.CaseCardTasks = Ext.extend(CB.CaseCardBlock, {
	cls: 'block-tasks'
	,serverRoot: 'tasks'
	,itemSelector: 'tr'
	,fields: [{name: 'id', type: 'int'}, 'name', 'iconCls', {name: 'date', type: 'date'}, {name: 'date_end', type: 'date'}, {name: 'status', type: 'int'}, {name: 'cid', type: 'int'}, 'users' ]
	,tpl: ['<table width="100%">'
		,'<tr><th width="50%">' + L.Tasks + ', ' + L.Events+' <a name="addTask" class="click">'+L.addNew+' ...</a></th><th>'+L.Owner+'</th><th>'+L.TaskAssigned+'</th><th>'+L.Deadline+'</th><th>'+L.Completed+'</th></tr>'
		,'<tpl for=".">'
		,'<tr{[ (values.status == 3) ? \' class="done"\' :""]}><td><a class="i {iconCls}" href="#{id}">{name}</a></td>'
			,'<td><a name="uid" href="#{cid}"><img src="photo/{cid}.jpg" class="photo32" title="{[ CB.DB.usersStore.getName(values.cid) ]}"/></a></td>'
			,'<td>'
				,'<tpl for="users">'
				,' <a name="uid" href="#{id}"><img src="photo/{id}.jpg" class="photo32" title="{name}"/></a> '
				,'</tpl>'
			,'</td>'
			,'<td>{[ Ext.isEmpty(values.date_end) ? "" : values.date_end.format("F j") ]}</td>'
			,'<td>{[ Ext.isEmpty(values.completed) ? "" : values.completed.format("F j") ]}</td>'
		,'</tr>'
		,'</tpl>'
		,'</table>'
	]
	,prepareLoadedData: function(data){
		for (var i = 0; i < data.length; i++) {
			data[i].date = date_ISO_to_date(data[i].date);
			data[i].date_end = date_ISO_to_date(data[i].date_end);
			data[i].iconCls = getItemIcon(data[i])
			userIds = Ext.isEmpty(data[i].user_ids) ? [] : String(data[i].user_ids).split(',');
			data[i].users = [];
			for (var j = 0; j < userIds.length; j++) {
				data[i].users.push({
					id: userIds[j]
					,name: CB.DB.usersStore.getName(userIds[j])
				})
			};
		};
		return data;
	}
	,onItemClick: function(dv, itemIndex, el, ev){
		a = ev.getTarget('a')
		if(Ext.isEmpty(a)) return;
		switch(a.name){
			case 'uid': Ext.Msg.alert('Click', 'Open user window'); break;
			case 'addTask':
                App.mainViewPort.fireEvent('taskcreate', {data: {pid: this.params.caseId,path: this.params.path,pathtext: this.params.pathtext}})
				break;
			default:
				r = dv.store.getAt(itemIndex -1);
				if(r) App.openObject(86, r.get('id'), ev);
		}
	}
});
Ext.reg('CaseCardTasks', CB.CaseCardTasks)

/*generic block class for milestones */
CB.CaseCardMilestones = Ext.extend(CB.CaseCardBlock, {
	cls: 'block-milestones'
	,serverRoot: 'milestones'
  	,initComponent: function(){
  		Ext.apply(this, {
  			items:[{html:'some html'}]
  		})
  		CB.CaseCardMilestones.superclass.initComponent.apply(this, arguments)
	}
});
Ext.reg('CaseCardMilestones', CB.CaseCardMilestones)
