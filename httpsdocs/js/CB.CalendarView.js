Ext.namespace('CB'); 

CB.Calendar = Ext.extend(Ext.calendar.CalendarPanel, {
	activeItem: 2 // month view
	,border: false
	// CalendarPanel supports view-specific configs that are passed through to the 
	// underlying views to make configuration possible without explicitly having to 
	// create those views at this level:
	,monthViewCfg: {
		showHeader: true
		,showWeekLinks: true
		,showWeekNumbers: false
	}
	// Some optional CalendarPanel configs to experiment with:
	//showDayView: false,
	//showWeekView: false,
	//showMonthView: false,
	,showNavBar: false
	//showTodayText: false,
	//showTime: false,
	//title: 'My Calendar', // the header of the calendar, could be a subtitle for the app

	// Once this component inits it will set a reference to itself as an application
	// member property for easy reference in other functions within App.
	,initComponent: function(){
		App.calendarPanel = this;

		// This is an example calendar store that enables the events to have
            	// different colors based on CalendarId. This is not a fully-implmented
            	// multi-calendar implementation, which is beyond the scope of this sample app
		this.calendarStore = new Ext.data.JsonStore({
			root: 'calendars'
			,idProperty: 'id'
			,data: { "calendars":[{ id:1, title:"CaseBox" }] } // defined in calendar-list.js
			,proxy: new Ext.data.MemoryProxy()
			,autoLoad: true
			,fields: [
				{name:'CalendarId', mapping: 'id', type: 'int'}
				,{name:'Title', mapping: 'title', type: 'string'}
			]
			,sortInfo: {
				field: 'CalendarId'
				,direction: 'ASC'
			}
		});
		
		fields = Ext.calendar.EventRecord.prototype.fields.getRange();
		fields.push({ name: 'template_id', type: 'int' });
		fields.push('type');
		fields.push('iconCls');
		fields.push('cls');
		fields.push('category_id');
		this.eventsReloadTask = new Ext.util.DelayedTask( this.doReloadEventsStore, this);
		// A sample event store that loads static JSON from a local file. Obviously a real
		// implementation would likely be loading remote data via an HttpProxy, but the
		// underlying store functionality is the same.  Note that if you would like to 
		// provide custom data mappings for events, see EventRecord.js.
		this.eventStore = new Ext.data.DirectStore({
			autoLoad: false
			,autoDestroy: true
			,proxy: new  Ext.data.DirectProxy({
				paramsAsHash: true
				,directFn: CB_Calendar.getEvents
				,listeners:{
					scope: this
					,load: function(proxy, obj, opt){
						for (var i = 0; i < obj.result.data.length; i++) {
							obj.result.data[i].start = date_ISO_to_date(obj.result.data[i].start);
							obj.result.data[i].end = Ext.value(date_ISO_to_date(obj.result.data[i].end), obj.result.data[i].start);
						};
					}
				}
			})
			,reader: new Ext.data.JsonReader({
				successProperty: 'success'
				,idProperty: 'id'
				,root: 'data'
				,messageProperty: 'msg'
				,fields: fields
			}
			)
			,listeners: {
				scope: this
				,beforeload: function(st, r, o){
					el = this.getEl();
					if( Ext.isEmpty(el) || !el.isVisible(true) ) return false;
					// if(!Ext.isDate(r.params.start)) r.params.start = new Date()
					//  || !Ext.isDate(r.params.end)) return false;
					if(!this.allowedReload){
						this.eventsReloadTask.delay(500)
						return false;
					}
					this.allowedReload = false;
				}
				,load: function(st, recs, opt){
					Ext.each(recs, function(r){
						cls = 'cal-evt-bg-t'+r.get('type')+ ' cal-cat-'+CB.DB.thesauri.getIcon(r.get('category_id'));
						r.set('iconCls', getItemIcon(r.data) )
						if(!Ext.isEmpty(r.get('iconCls'))) cls = cls + ' icon-padding '+ r.get('iconCls');
						r.set('cls', cls);
					}, this)
					this.getLayout().activeItem.syncSize();
				}
			}
		});
		
		Ext.apply(this, {
			listeners: {
				scope: this
				,eventclick: function(vw, rec, el){
					this.showEditWindow(rec, el);
					this.clearMsg();
				}
				,eventover: function(vw, rec, el){
					//console.log('Entered evt rec='+rec.data.Title+', view='+ vw.id +', el='+el.id);
				}
				,eventout: function(vw, rec, el){
					//console.log('Leaving evt rec='+rec.data.Title+', view='+ vw.id +', el='+el.id);
				}
				,eventadd: function(cp, rec){
					this.showMsg('Event '+ rec.data.Title +' was added');
				}
				,eventupdate: function(cp, rec){
					this.showMsg('Event '+ rec.data.Title +' was updated');
				}
				,eventdelete: function(cp, rec){
					this.showMsg('Event '+ rec.data.Title +' was deleted');
				}
				,eventcancel: function(cp, rec){
					// edit canceled
				}
				,viewchange: function(p, vw, dateInfo){
					if(this.editWin) this.editWin.hide();
					if(dateInfo !== null){
						// will be null when switching to the event edit form so ignore
						//Ext.getCmp('app-nav-picker').setValue(dateInfo.activeDate);
						this.updateTitle(dateInfo, vw);
						this.eventsReloadTask.delay(500)
					}
				}
				,dayclick: function(vw, dt, ad, el){
					this.showEditWindow({ StartDate: dt, IsAllDay: ad }, el);
					this.clearMsg();
				}
				//,rangeselect: this.onRangeSelect
				,eventmove: function(vw, rec){
					rec.reject();
					return;
					rec.commit();
					var time = rec.data.IsAllDay ? '' : ' \\a\\t g:i a';
					this.showMsg('Event '+ rec.data.Title +' was moved to '+rec.data.StartDate.format('F jS'+time));
				}
				,eventresize: function(vw, rec){
					rec.commit();
					this.showMsg('Event '+ rec.data.Title +' was updated');
				}
				,eventdelete: function(win, rec){
					this.eventStore.remove(rec);
					this.showMsg('Event '+ rec.data.Title +' was deleted');
				}
				,initdrag: function(vw){
					return false;
					if(this.editWin && this.editWin.isVisible()) this.editWin.hide();
				}
			}
		});
		CB.Calendar.superclass.initComponent.apply(this, arguments);
	}
	,doReloadEventsStore: function(){
		this.allowedReload = true;
		if(Ext.isEmpty(this.getLayout().activeItem)) return;
		if(Ext.isDefined(this.eventStore.requestedParams)) Ext.apply(this.eventStore.baseParams, this.eventStore.requestedParams)
		delete this.eventStore.requestedParams;
		params = Ext.apply({}, this.eventStore.baseParams);
		Ext.apply(params, this.getLayout().activeItem.getViewBounds() )
		
		params.end.setHours(23);
		params.end.setMinutes(59);
		params.end.setSeconds(59);
		params.end.setMilliseconds(999);
		params.start = params.start.toISOString()
		params.end = params.end.toISOString()

		params.facets = 'calendar'

		if(Ext.isEmpty(params.path)) params.path = '/';

		this.eventStore.load({ params: params })
	}
	// The edit popup window is not part of the CalendarPanel itself -- it is a separate component.
        // This makes it very easy to swap it out with a different type of window or custom view, or omit
        // it altogether. Because of this, it's up to the application code to tie the pieces together.
        // Note that this function is called from various event handlers in the CalendarPanel above.
	,showEditWindow : function(rec, animateTarget){
	        if(Ext.isEmpty(rec.data)) rec = new Ext.calendar.EventRecord(rec);
	        App.openObject( rec.data.template_id, rec.data.EventId );
	}
        
        // This is an application-specific way to communicate CalendarPanel event messages back to the user.
        // This could be replaced with a function to do "toast" style messages, growl messages, etc. This will
        // vary based on application requirements, which is why it's not baked into the CalendarPanel.
        ,showMsg: function(msg){
            //Ext.fly('app-msg').update(msg).removeClass('x-hidden');
        }
        
        ,clearMsg: function(){
            //Ext.fly('app-msg').update('').addClass('x-hidden');
        }
        // The CalendarPanel itself supports the standard Panel title config, but that title
        // only spans the calendar views.  For a title that spans the entire width of the app
        // we added a title to the layout's outer center region that is app-specific. This code
        // updates that outer title based on the currently-selected view range anytime the view changes.
        ,updateTitle: function(dateInfo, view){
        	if(Ext.isEmpty(this.titleItem)) return ;
            	sd = dateInfo.viewStart;
            	ed = dateInfo.viewEnd;
            	ad = dateInfo.activeDate;
            	switch(view.xtype){
            		case 'dayview':  this.titleItem.setText(ad.format('F j, Y')); break;
            		case 'weekview': 
				if(sd.getFullYear() == ed.getFullYear()){
		                	if(sd.getMonth() == ed.getMonth()) this.titleItem.setText(sd.format('F j') + ' - ' + ed.format('j, Y'));
		                	else this.titleItem.setText( sd.format('F j') + ' - ' + ed.format('F j, Y') );
		            	}else this.titleItem.setText( sd.format('F j, Y') + ' - ' + ed.format('F j, Y') );

            			break;
            		case 'monthview': this.titleItem.setText(ad.format('F Y')); break;
            	}
        }
})
Ext.reg('CBCalendar', CB.Calendar);

CB.CalendarView = Ext.extend(Ext.Panel, {
	iconCls: 'icon-calendar'
	,layout: 'border'
	,closable: true
	,hideBorders: true
	,tbarCssClass: 'x-panel-white'
	,folderProperties: {}
	,initComponent: function(){
		this.titleItem = new Ext.Toolbar.TextItem({cls: 'calendar-title', text: '<span style="font-size: 16px; font-weight: bold; color: #333">December 2012</span>'});
		this.calendar = new CB.Calendar({
			titleItem: this.titleItem
			,region: 'center'
			,listeners:{
				scope: this
				,rangeselect: this.onRangeSelect
				,dayclick: this.onDayClick
			}
		});
		this.calendar.eventStore.baseParams.facets = 'calendar'
		this.calendar.eventStore.proxy.on('load', this.onProxyLoaded, this);
		
		this.filterButton = new Ext.Button({
             		text: L.Filter
             		,enableToggle: true
             		,iconCls: 'icon32-filter'
             		,activeIconCls: 'icon32-filter-on'
             		,iconAlign:'top'
             		,scale: 'large'
             		,toggleGroup: 'rightBtn'
             		,itemIndex: 1
             		,scope: this
             		,toggleHandler: this.onEastPanelButtonClick
             	})

                this.filtersPanel = new CB.FilterPanel({
                	bindButton: this.filterButton
                	,listeners:{
                		scope: this
                		,change: this.onFiltersChange
                	}
                });
		
		this.eastPanel = new Ext.Panel({
			region: 'east'
			,width: 300
			,split: true
			,hidden: true
			,animCollapse: false
			,border: false
			,layout: 'card'
			,activeItem: 0
			,hideBorders: true
			,statefull: true
			,stateId: 'cvEP' //calendarview east panel
			,items: [ this.filtersPanel ]
                })

		Ext.apply(this, {
			items: [ this.calendar, this.eastPanel ]
			,tbar: [
	                       {	text: L.NewTask
	                       		, iconCls: 'icon32-task-new'
	                       		, iconAlign:'top'
	                       		, scale: 'large'
	                       		,scope: this
	                       		,handler: this.onTaskCreateClick
	                       	},{	text: L.NewEvent
	                       		,iconCls: 'icon32-event-new'
	                       		,iconAlign:'top'
	                       		,scale: 'large'
	                       		,scope: this
	                       		,handler: this.onEventCreateClick
	                       	}
	                       ,'-'
	                       ,{	text: L.Today
	                       		,iconCls: 'icon32-cal-today'
	                       		,iconAlign:'top'
	                       		,scale: 'large'
	                       		,scope: this
	                       		,handler: function(){
	                       			this.calendar.setStartDate(new Date())
	                       		}
	                       	},{	
	                       		text: L.Day
	               			,iconCls: 'icon32-cal-day'
	               			,iconAlign:'top'
	               			,scale: 'large'
	               			,viewId: 0
	               			,toggleGroup: 'calView'
			    		,allowDepress: false
			    		,scope: this.calendar
	               			,handler: this.calendar.onDayClick /*onCalendarViewClick/**/
	               		},{	text: L.Week
	                       		,iconCls: 'icon32-cal-week'
	                       		,iconAlign:'top'
	                       		,scale: 'large'
	                       		,viewId: 1
	                       		,toggleGroup: 'calView'
			    		,allowDepress: false
			    		,scope: this.calendar
	               			,handler: this.calendar.onWeekClick /*onCalendarViewClick/**/
	               		},{	text: L.Month
	                       		,iconCls: 'icon32-cal-month'
	                       		,iconAlign:'top'
	                       		,scale: 'large'
	                       		,viewId: 2
	                       		,toggleGroup: 'calView'
	                       		,pressed: true
			    		,allowDepress: false
			    		,scope: this.calendar
	               			,handler: this.calendar.onMonthClick /*onCalendarViewClick/**/
	               		}
				,'-'
				,{	text: ''
					,iconCls: 'icon32-cal-prev'
					,iconAlign:'top'
					,scale: 'large'
					,tooltip: L.PreviousPeriod
					,scope: this.calendar
					,handler: this.calendar.onPrevClick
				},{	text: ''
					,iconCls: 'icon32-cal-next'
					,iconAlign:'top'
					,scale: 'large'
					,tooltip: L.NextPeriod
					,scope: this.calendar
					,handler: this.calendar.onNextClick
				},this.titleItem
	                       ,'->'
	                       ,this.filterButton
  	        	]
		})
		CB.CalendarView.superclass.initComponent.apply(this, arguments);
		
		App.mainViewPort.on('taskcreated', this.onReloadClick, this);
		App.mainViewPort.on('taskupdated', this.onReloadClick, this);
		App.mainViewPort.on('tasksdeleted', this.onReloadClick, this);

		this.addEvents('taskcreate', 'viewloaded');
		this.enableBubble(['taskcreate', 'viewloaded']);
	}
        ,onRangeSelect: function(c, range, callback){
        	allday = ((range.StartDate.format('H:i:s') == '00:00:00') && (range.EndDate.format('H:i:s') == '23:59:59') ) ? 1 : 0;
        	App.mainViewPort.fireEvent('taskcreate', { data: {
        		pid: this.folderProperties.id
        		,date_start: range.StartDate
        		,date_end: range.EndDate
        		,allday: allday
        		,path: this.folderProperties.path
        		,pathtext: this.folderProperties.pathtext
        		}
        	}
        	);
        	callback()
        }
        ,onDayClick: function(c, date, ad, el){
        	allday = (date.format('H:i:s') == '00:00:00') ? 1 : 0;
        	App.mainViewPort.fireEvent('taskcreate', { data: {
        		pid: this.folderProperties.id
        		,date_start: date
        		,allday: allday
        		,path: this.folderProperties.path
        		,pathtext: this.folderProperties.pathtext
        		}
        	}
        	);
        }
	,onProxyLoaded: function(proxy, o, options){
		this.folderProperties = o.result.folderProperties
		this.folderProperties.id = parseInt(this.folderProperties.id);
		this.folderProperties.system = parseInt(this.folderProperties.system);
		this.folderProperties.type = parseInt(this.folderProperties.type);
		this.folderProperties.subtype = parseInt(this.folderProperties.subtype);
		this.folderProperties.pathtext = o.result.pathtext;

		this.fireEvent('viewloaded', proxy, o, options)
		this.filtersPanel.updateFacets(o.result.facets, options);
	}
	,setParams: function(params){
		this.calendar.eventStore.requestedParams = Ext.apply({}, params, this.calendar.eventStore.baseParams);
		this.onReloadClick();
	}
	,onSearchQuery: function(query, e){
		this.query = query;
		this.onReloadClick();
	}
	,onReloadClick: function(b, e){
		this.calendar.doReloadEventsStore()
	}
	,onTaskCreateClick: function(b, e){
		this.fireEvent('taskcreate', {
			data: {
				type: 6
				,template_id: App.config.default_task_template
				,pid: this.folderProperties.id
				,path: this.folderProperties.path
				,pathtext: this.folderProperties.pathtext
			}
		})
	}
	,onEventCreateClick: function(b, e){
		this.fireEvent('taskcreate', {
			data: {
				type: 7
				,template_id: App.config.default_event_template
				,pid: this.folderProperties.id
				,path: this.folderProperties.path
				,pathtext: this.folderProperties.pathtext
			}
		})
	}
	,onEastPanelButtonClick: function(b, e){
		if(b.pressed){
			this.eastPanel.getLayout().setActiveItem(b.itemIndex);
			this.eastPanel.show();
			if(b.itemIndex == 0){
				r = this.grid.getSelectionModel().getSelected();
				if(r) this.previewPanel.loadPreview(r.get('nid'));
			}
            	}else{
            		this.eastPanel.hide();
            	}
		this.syncSize()
        }
	,onFiltersChange: function(filters){
		this.setParams({filters: filters});
	}

});

Ext.reg('CBCalendarView', CB.CalendarView);


CB.CalendarViewPanel = Ext.extend(Ext.Panel, {
	hideBorders: true
	,borders: false
	,closable: true
	,layout: 'fit'
	,iconCls: 'icon-calendarView'
	,initComponent: function(){
		this.view = new CB.CalendarView()
		Ext.apply(this,{
			iconCls: Ext.value(this.iconCls, 'icon-calendarView')
			,items: this.view
		})
		CB.CalendarViewPanel.superclass.initComponent.apply(this, arguments);
		this.view.setParams({path:'/', descendants: true, filters: {"status":[{"mode":"OR","values":["1","2"]}],"assigned":[{"mode":"OR","values":[App.loginData.id]}]} });
	}
})
Ext.reg('CBCalendarViewPanel', CB.CalendarViewPanel);