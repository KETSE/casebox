Ext.namespace('CB.browser.view');

CB.browser.view.Summary = Ext.extend(Ext.Panel, {
    closable: false
    ,bodyCssClass: 'summary'
    ,autoScroll: true
    ,params: { descendants:false }
    ,initComponent: function(){
        this.dvActiveTasks = new CB.SummaryBlock({
            name: 'activeTasks'
            ,emptyText: 'No tasks'
            ,cellCls: 'cell'
            ,title: L.ActiveTasks
            ,tpl: ['<ul><tpl for=".">'
                ,'<li class="icon-padding {iconCls}"><a href="#">{name}</a> {[ (values.status == 1) ? \'<span class="taskStatus\'+values.status+\'">\'+L["taskStatus"+values.status]+\'</span>\' : "" ]}</li>'
                ,'</tpl></ul>'
            ]
            ,listeners: {scope: this, click: this.onItemClick }
            ,activeFilter: 0
            ,filters: [
                {   name: L.assignedToMe
                    ,value: {sort: ['status', 'date_end', 'date_start']
                        ,template_types: 'task'
                        ,filters: {
                            status: [ {mode: 'OR', values: [1, 2] } ]
                            ,user_ids: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{     name: L.ownedByMe
                    ,value: {sort: ['status', 'date_end', 'date_start']
                        ,template_types: 'task'
                        ,filters: {
                            status: [ {mode: 'OR', values: [1, 2] } ]
                            ,cid: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{ name: L.all
                    ,value: {sort: ['status', 'date_end', 'date_start']
                        ,template_types: 'task'
                        ,filters: {
                            status: [ {mode: 'OR', values: [1, 2] } ]
                        }
                    }
                }
            ]
        });

        this.dvCompleteTasks = new CB.SummaryBlock({
            name: 'completeTasks'
            ,emptyText: 'No tasks'
            ,cellCls: 'cell'
            ,title: L.CompletedTasks
            ,tpl: ['<ul><tpl for=".">'
                ,'<li class="icon-padding {iconCls}"><a href="#"><span class="n">{name}</span></a></li>'
                ,'</tpl></ul>'
            ]
            ,listeners: {scope: this, click: this.onItemClick }
            ,activeFilter: 0
            ,filters: [
                {   name: L.assignedToMe
                    ,value: {sort: ['udate desc', 'name']
                        ,template_types: 'task'
                        ,filters: {
                            status: [ {mode: 'OR', values: [3] } ]
                            ,user_ids: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{ name: L.ownedByMe
                    ,value: {sort: ['udate desc', 'name']
                        ,template_types: 'task'
                        ,filters: {
                            status: [ {mode: 'OR', values: [3] } ]
                            ,cid: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{ name: L.all
                    ,value: {sort: ['udate desc', 'name']
                        ,template_types: 'task'
                        ,filters: {
                            status: [ {mode: 'OR', values: [3] } ]
                        }
                    }
                }
            ]
        })
        this.dvActions = new CB.SummaryBlock({
            name: 'actions'
            ,emptyText: 'No actions'
            ,cellCls: 'cell'
            ,title: L.Actions
            ,tpl: ['<ul><tpl for=".">'
                ,'<li class="icon-padding {iconCls}"><a href="#">{name}</a></li>'
                ,'</tpl></ul>'
            ]
            ,listeners: {scope: this, click: this.onItemClick }
            ,activeFilter: 0
            ,filters: [
                {   name: L.modifiedByMe
                    ,value: {sort: ['udate desc', 'name']
                        ,template_types: 'object'
                        ,folders: false
                        ,filters: {
                            uid: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{ name: L.modifiedByAnybody
                    ,value: {sort: ['udate desc', 'name']
                        ,template_types: 'object'
                        ,folders: false
                    }
                }
            ]
        })
        this.dvFiles = new CB.SummaryBlock({
            name: 'files'
            ,emptyText: 'No files'
            ,cellCls: 'cell'
            ,title: L.Files
            ,tpl: ['<ul><tpl for=".">'
                ,'<li class="icon-padding file- {iconCls}"><a href="#">{name}</a></li>'
                ,'</tpl></ul>'
            ]
            ,listeners: {scope: this, click: this.onItemClick }
            ,activeFilter: 0
            ,filters: [
                {   name: L.modifiedByMe
                    ,value: {sort: ['udate desc', 'name']
                        ,template_types: 'file'
                        ,filters: {
                            uid: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{ name: L.modifiedByAnybody
                    ,value: {sort: ['udate desc', 'name']
                        ,template_types: 'file'
                    }
                }
            ]
        })
        this.dvTasksUsers = new CB.SummaryBlock({
            name: 'tasksUsers'
            ,emptyText: 'No users'
            ,cellCls: 'cell last'
            ,title: L.ActiveTasksPerUser
            ,bodyCssClass: 'block taskview'
            ,tpl: ['<table class="people">'
                ,'<tpl for=".">'
                ,'<tr><td class="user"><img class="photo32" src="photo/{id}.jpg" alt="{name}" title="{name}"></td>'
                ,'<td><b>&nbsp;{name}</b><p class="gr">{total} {[ (values.total > 1) ? L.tasks : L.task ]} {[ (values.total2 > 0) ? \'<span class="taskStatus1">\'+ values.total2 +\' \' + L.taskStatus1 + \'</span>\' : "" ]}</p></td></tr>'
                ,'</tpl>'
                ,'</table>'
            ]
            ,listeners: {scope: this, click: this.onItemClick }
            ,activeFilter: 0
            ,filters: [
                {   name: L.thatCreatedTasksForMe
                    ,value: { // group by cid, status
                        template_types: 'task'
                        ,facets: 'activeTasksPerUsers'
                        ,facetPivot: 'cid,status'
                        ,filters: {
                            status: [ {mode: 'OR', values: [1, 2] } ]
                            ,user_ids: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{     name: L.assignedToTasksCreatedByMe
                    ,value: { //group by user_ids, status
                        template_types: 'task'
                        ,facets: 'activeTasksPerUsers'
                        ,facetPivot: 'user_ids,status'
                        ,filters: {
                            status: [ {mode: 'OR', values: [1, 2] } ]
                            ,cid: [ {mode: 'OR', values: [App.loginData.id] } ]
                        }
                    }
                },{     name: L.createdTasks
                    ,value: {// group by cid, status (but without filter for me)
                        template_types: 'task'
                        ,facets: 'activeTasksPerUsers'
                        ,facetPivot: 'cid,status'
                        ,filters: {
                            status: [ {mode: 'OR', values: [1, 2] } ]
                        }
                    }
                },{     name: L.assignedToTasks
                    ,value: {
                        template_types: 'task'
                        ,facets: 'activeTasksPerUsers'
                        ,facetPivot: 'user_ids,status'
                        ,filters: {
                            status: [ {mode: 'OR', values: [1, 2] } ]
                        }
                    }
                }
            ]
            ,preprocessData: function(data){
                for (var i = 0; i < data.length; i++)
                    data[i][1] = CB.DB.usersStore.getName(data[i][0]);
            }
        })
        Ext.apply(this, {
            layout: 'table'
            ,hideBorders: true
            , layoutConfig: {
                   tableAttrs: {
                    style: {
                        width: '100%'
                        }
                    }
                    ,columns: 2
            }
            ,items: [
                this.dvActiveTasks
                ,this.dvCompleteTasks
                ,this.dvActions
                ,this.dvFiles
                ,this.dvTasksUsers
                ,{cellCls: 'cell last', border: false}
            ]
            ,listeners: {
                scope: this
                ,beforedestroy: function(){
                    App.mainViewPort.un('taskcreated', this.onTasksChange, this);
                    App.mainViewPort.un('taskupdated', this.onTasksChange, this);
                    App.mainViewPort.un('tasksdeleted', this.onTasksChange, this);
                }
            }
        })
        CB.browser.view.Summary.superclass.initComponent.apply(this, arguments);
        this.addEvents('viewloaded');
        this.enableBubble(['viewloaded']);
        App.mainViewPort.on('taskcreated', this.onTasksChange, this);
        App.mainViewPort.on('taskupdated', this.onTasksChange, this);
        App.mainViewPort.on('tasksdeleted', this.onTasksChange, this);
    }
    ,reload:function(){
        if(this.rendered) this.getEl().mask(L.LoadingData, 'icon-loading');
        this.lastParams = {}
        this.items.each( function(i){
            if(!Ext.isEmpty(i.getParams)) this.lastParams[i.name] = i.getParams();
        }, this)
        CB_BrowserView.getSummaryData(this.lastParams, this.processReload, this);
    }
    ,processReload: function(r, e){
        if(this.rendered) this.getEl().unmask()
        if(r.success !== true) return;

        this.params = this.requestedParams;
        this.folderProperties = r.folderProperties
        this.fireEvent('viewloaded', this, e, {params: this.lastParams});

        Ext.iterate(r.data, function(key, value, obj){
            for (var i = 0; i < value.length; i++)
                obj[key][i][5] = getItemIcon({
                    'name': value[i][1]
                    ,'type': value[i][2]
                    ,'status': value[i][3]
                    ,'template_id': value[i][4]
                });
        }, this)

        this.items.each( function(i){
            data = Ext.value(r.data[i.name], []);
            if(i.preprocessData) i.preprocessData(data)
            if(i.store) i.store.loadData(data)
        }, this);
        if(this.rendered) this.doLayout();
    }
    ,onItemClick: function(){
    }
    ,setParams: function(params){
        if(Ext.isEmpty(params.path)) params.path = '/';

        this.requestedParams = Ext.apply({}, params, this.params);
        this.items.each( function(i){
            Ext.apply(i, {requestedParams: this.requestedParams});
        }, this)
        this.onReloadClick();
    }
    ,onReloadClick: function(){
        this.reload()
    }
        ,onShowDescendantsClick: function(cb, e){
            this.fireEvent('showdescendants', !cb.checked, e);
        }
        ,onTasksChange: function(){
            this.dvActiveTasks.reload()
            this.dvCompleteTasks.reload()
        }
})
Ext.reg('CBBrowserViewSummary', CB.browser.view.Summary);

CB.SummaryBlock = Ext.extend( Ext.Panel, {
    autoHeight: true
    ,headerCfg: { cls: 'x-panel-header panel-header-nobg block-header' }
    ,bodyCssClass: 'block'
    ,padding: 0
    ,initComponent: function(){
        this.store = new Ext.data.ArrayStore({
            idIndex: 0
            ,autoDestroy: true
            ,fields: [
                {name: 'id', type: 'int'}
                ,'name'
                ,{name: 'type', type: 'int'}
                ,{name: 'status', type: 'int'}
                ,{name: 'template_id', type: 'int'}
                ,'iconCls'
                ,{name:'total', type: 'int'}
                ,{name:'total2', type: 'int'}
            ]
        });
        this.view = new Ext.DataView({
            autoHeight: true
            ,itemSelector: 'a'
            ,overClass:'item-over'
            ,emptyText: this.emptyText
            ,tpl: this.tpl
            ,store: this.store
            ,listeners: {
                scope: this
                ,click: this.onItemClick
            }
        })
        Ext.apply(this, {
            items: this.view
            ,listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        })
        CB.SummaryBlock.superclass.initComponent.apply(this, arguments);
    }
    ,onAfterRender: function(){
        this.updateTitle()
    }
    ,onItemClick: function(obj, idx, el, ev){
        row = this.store.getAt(idx);
        if(!row) return;
        if(!App.openObject(row.get('template_id'), row.get('id'), ev) ){
        }
    }
    ,updateTitle: function(){
        title = this.initialConfig.title;
        if(Ext.isDefined(this.activeFilter) && Ext.isDefined(this.filters) && !Ext.isEmpty(this.filters[this.activeFilter])) title += ' <a href="#">' + this.filters[this.activeFilter].name +'</a>';
        this.setTitle( title );
        a = this.header.query('a');
        if(!Ext.isEmpty(a)){
            Ext.get(a[0]).on('click', this.showMenu, this)
        }
    }
    ,showMenu: function(ev, el){
        if(Ext.isEmpty(this.menu) ){
            this.menu = new Ext.menu.Menu();
            for (var i = 0; i < this.filters.length; i++) {
                this.menu.add({ filterIndex: i, text: this.filters[i].name, scope: this, handler: this.onChangeFilterClick, checked: (i == this.activeFilter), group: 'af', xtype: 'menucheckitem' })
            };
        }
        this.menu.showAt(ev.getXY());
    }
    ,onChangeFilterClick: function(b, e){
        this.activeFilter = b.filterIndex;
        this.updateTitle();
        this.reload()
    }
    ,getParams: function(){
        this.lastParams = Ext.apply({}, this.requestedParams);
        Ext.apply(this.lastParams, this.filters[Ext.value(this.activeFilter, 0)].value);
        this.lastParams.rows = 15;
        return this.lastParams;
    }
    ,reload:function(){
        if(this.rendered) this.getEl().mask(L.LoadingData, 'icon-loading');
        this.params = {}
        this.params[this.name] = this.getParams()
        CB_BrowserView.getSummaryData( this.params, this.processReload, this);
    }
    ,processReload: function(r, e){
        if(this.rendered) this.getEl().unmask()
        if(r.success !== true) return;
        if(Ext.isEmpty(r.data[this.name])) r.data[this.name] = [];
        for (var i = 0; i < r.data[this.name].length; i++)
            r.data[this.name][i][5] = getItemIcon({
                'name': r.data[this.name][i][1]
                ,'type': r.data[this.name][i][2]
                ,'status': r.data[this.name][i][3]
                ,'template_id': r.data[this.name][i][4]
            });
        data = Ext.value(r.data[this.name], []);
        if(!Ext.isEmpty(this.preprocessData)) this.preprocessData(data);
        this.store.loadData(data);
    }
})
