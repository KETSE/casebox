Ext.namespace('CB.browser.view');


Ext.define('CB.browser.view.CalendarPanel', {
    extend: 'CB.browser.view.Interface'
    ,border: false
    ,closable: true
    ,layout: 'fit'
    ,iconCls: 'icon-calendarView'

    ,initComponent: function(){
        this.view = new CB.browser.view.Calendar();

        Ext.apply(this,{
            iconCls: Ext.valueFrom(this.iconCls, 'icon-calendarView')
            ,items: this.view
        });

        CB.browser.view.CalendarPanel.superclass.initComponent.apply(this, arguments);
        this.view.setParams({
            path:'/'
            ,descendants: true
            ,filters: {
                "task_status":[{
                    "mode":"OR"
                    ,"values":["1","2"]
                }]
                ,"assigned":[{
                    "mode":"OR"
                    ,"values":[App.loginData.id]
                }]
            }
        });
    }
});


Ext.define('CB.Calendar', {
    extend: 'Ext.calendar.CalendarPanel'
    ,activeItem: 2 // month view
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
    ,showNavBar: true
    //showTodayText: false,
    //showTime: false,
    //title: 'My Calendar', // the header of the calendar, could be a subtitle for the app

    ,params: {
    }
    // Once this component inits it will set a reference to itself as an application
    // member property for easy reference in other functions within App.
    ,initComponent: function(){
        // App.calendarPanel = this;

/*        // This is an example calendar store that enables the events to have
                // different colors based on CalendarId. This is not a fully-implmented
                // multi-calendar implementation, which is beyond the scope of this sample app
        this.calendarStore = new Ext.data.JsonStore({
            rootProperty: 'calendars'
            ,idProperty: 'id'
            ,data: { "calendars":[{ id:1, title:"CaseBox" }] } // defined in calendar-list.js
            ,proxy: {
                type: 'memory'
                ,reader: {
                    type: 'json'
                }
            }
            ,autoLoad: true
            ,fields: [
                {name:'CalendarId', mapping: 'id', type: 'int'}
                ,{name:'Title', mapping: 'title', type: 'string'}
            ]
            ,sortInfo: {
                field: 'CalendarId'
                ,direction: 'ASC'
            }
        });/**/

        // This is an example calendar store that enables event color-coding
        this.calendarStore = Ext.create('Ext.calendar.data.MemoryCalendarStore', {
            data: { "calendars":[{ id:1, title:"CaseBox" }] } // defined in calendar-list.js
        });

        // A sample event store that loads static JSON from a local file. Obviously a real
        // implementation would likely be loading remote data via an HttpProxy, but the
        // underlying store functionality is the same.
        this.eventStore = Ext.create('Ext.calendar.data.MemoryEventStore', {
            autoLoad: false
            ,autoDestroy: true
            ,listeners: {
                scope: this
                ,load: function(st, recs, opt){
                    Ext.each(
                        recs
                        , function(r){
                            cls = 'cal-cat-'+ Ext.valueFrom(r.get('cls'), 'default') +
                                ( (r.get('task_status') == 3) ? ' cal-status-c' : '');
                            if(r.get('template_id') == App.config.default_task_template) {
                                r.set('iconCls', '');
                            } else {
                                r.set('iconCls', getItemIcon(r.data));
                            }
                            if(!Ext.isEmpty(r.get('iconCls'))) {
                                cls = cls + ' icon-padding '+ r.get('iconCls');
                            }
                            r.set('cls', cls);
                            r.commit();
                        }
                        ,this
                    );
                    // this.getLayout().activeItem.syncSize();
                }
            }
        });

        this.eventsReloadTask = new Ext.util.DelayedTask( this.doReloadEventsStore, this);

/*        // fields = Ext.calendar.EventRecord.prototype.fields.getRange();
        // fields.push({ name: 'template_id', type: 'int' });
        // fields.push('task_status');
        // fields.push('iconCls');
        // fields.push('cls');
        // fields.push('category_id');
        // A sample event store that loads static JSON from a local file. Obviously a real
        // implementation would likely be loading remote data via an HttpProxy, but the
        // underlying store functionality is the same.  Note that if you would like to
        // provide custom data mappings for events, see EventRecord.js.
        this.eventStore = new Ext.calendar.data.MemoryCalendarStore({
            autoLoad: false
            ,autoDestroy: true
            // ,fields: fields
            ,listeners: {
                scope: this
                ,l_oad: function(st, recs, opt){
                    Ext.each(
                        recs
                        , function(r){
                            cls = 'cal-cat-'+ Ext.valueFrom(r.get('cls'), 'default') +
                                ( (r.get('task_status') == 3) ? ' cal-status-c' : '');
                            if(r.get('template_id') == App.config.default_task_template) {
                                r.set('iconCls', '');
                            } else {
                                r.set('iconCls', getItemIcon(r.data));
                            }
                            if(!Ext.isEmpty(r.get('iconCls'))) {
                                cls = cls + ' icon-padding '+ r.get('iconCls');
                            }
                            r.set('cls', cls);
                            r.commit();
                        }
                        ,this
                    );
                    // this.getLayout().activeItem.syncSize();
                }
            }
        });/**/

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
                    this.eventStore.remove(rec);
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
                        this.eventsReloadTask.delay(200);
                    }
                }
                ,dayclick: function(vw, dt, ad, el){
                    this.showEditWindow({ StartDate: dt, IsAllDay: ad }, el);
                    this.clearMsg();
                }
                //,rangeselect: this.onRangeSelect
                ,eventmove: function(vw, rec){
                    this.updateRecordDatesRemotely(rec);
                }
                ,eventresize: function(vw, rec){
                    this.updateRecordDatesRemotely(rec);
                }
                ,initdrag: function(vw){
                    // return false;
                    // if(this.editWin && this.editWin.isVisible()) this.editWin.hide();
                }
            }
        });
        CB.Calendar.superclass.initComponent.apply(this, arguments);

        this.enableBubble(['objectopen', 'changeparams', 'reload']);
    }
    ,updateRecordDatesRemotely: function(record){
        CB_Tasks.updateDates(
            {
                id: record.get('EventId')
                ,date_start: date_local_to_ISO_string(record.get('StartDate'))
                ,date_end: date_local_to_ISO_string(record.get('EndDate'))
            }
            ,function(r, e){
                if(r.success === true) {
                    this.commit();
                } else {
                    this.reject();
                }
            }
            ,record
        );
    }
    ,doReloadEventsStore: function(){
        this.allowedReload = true;
        if(Ext.isEmpty(this.getLayout().activeItem)) return;
        var bounds =  this.getLayout().activeItem.getViewBounds();
        var p = {};

        bounds.end.setHours(23);
        bounds.end.setMinutes(59);
        bounds.end.setSeconds(59);
        bounds.end.setMilliseconds(999);
        // p.dateStart = date_local_to_ISO_string(bounds.start);
        // p.dateEnd = date_local_to_ISO_string(bounds.end);
        p.dateStart = Ext.Date.format(bounds.start, 'Y-m-d') + 'T00:00:00.000Z';
        p.dateEnd = Ext.Date.format(bounds.end, 'Y-m-d') + 'T23:59:59.999Z';
        Ext.apply(this.params, p);

        this.fireEvent('reload', this);
    }

    // The edit popup window is not part of the CalendarPanel itself -- it is a separate component.
        // This makes it very easy to swap it out with a different type of window or custom view, or omit
        // it altogether. Because of this, it's up to the application code to tie the pieces together.
        // Note that this function is called from various event handlers in the CalendarPanel above.
    ,showEditWindow : function(rec, animateTarget){
            if(Ext.isEmpty(rec.data)) {
                rec = new Ext.calendar.EventRecord(rec);
            }

            var s = [
                {
                    nid: rec.data.EventId
                    ,template_id: rec.data.template_id
                    ,name: rec.data.Title
                }
            ];

            this.fireEvent('selectionchange', s);
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
                case 'dayview':  this.titleItem.setText(Ext.Date.format(ad, 'F j, Y')); break;
                case 'weekview':
            if(sd.getFullYear() == ed.getFullYear()){
                        if(sd.getMonth() == ed.getMonth()) this.titleItem.setText(Ext.Date.format(sd, 'F j') + ' - ' + Ext.Date.format(ed, 'j, Y'));
                        else this.titleItem.setText( Ext.Date.format(sd, 'F j') + ' - ' + Ext.Date.format(ed, 'F j, Y') );
                    }else this.titleItem.setText( Ext.Date.format(sd, 'F j, Y') + ' - ' + Ext.Date.format(ed, 'F j, Y') );

                    break;
                case 'monthview': this.titleItem.setText(Ext.Date.format(ad, 'F Y')); break;
            }
    }
});

Ext.define('CB.browser.view.Calendar', {
    extend: 'CB.browser.view.Interface'
    ,xtype: 'CBBrowserViewCalendar'

    ,iconCls: 'icon-calendar'
    ,layout: 'border'
    ,closable: true
    ,border: false
    ,tbarCssClass: 'x-panel-white'
    ,folderProperties: {}
    ,initComponent: function(){

        this.titleItem = new Ext.toolbar.TextItem({
            id: 'caltitle'
            ,cls: 'calendar-title'
            ,text: '<span style="font-size: 16px; font-weight: bold; color: #333">December 2012</span>'
        });
        var viewGroup = Ext.id();

        this.calendar = new CB.Calendar({
            titleItem: this.titleItem
            ,region: 'center'
            ,showNavBar: false
            ,listeners:{
                scope: this
                ,rangeselect: this.onRangeSelect
                ,dayclick: this.onDayClick
                ,selectionchange: this.onSelectionChange
            }
        });

        if(this.store) {
            this.store.on('load', this.onMainStoreLoad, this);
        }

        var instanceId = this.refOwner.instanceId;

        this.refOwner.buttonCollection.addAll(
            new Ext.Button({
                text: L.Day
                ,id: 'dayview' + instanceId
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-cal-day'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this.calendar
                ,handler: this.calendar.onDayClick
            })

            ,new Ext.Button({
                text: L.Week
                ,id: 'weekview' + instanceId
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-cal-week'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this.calendar
                ,handler: this.calendar.onWeekClick
            })

            ,new Ext.Button({
                text: L.Month
                ,id: 'monthview' + instanceId
                ,enableToggle: true
                ,allowDepress: false
                ,iconCls: 'ib-cal-month'
                ,iconAlign:'top'
                ,scale: 'large'
                ,toggleGroup: 'cv' + viewGroup
                ,pressed: true
                ,scope: this.calendar
                ,handler: this.calendar.onMonthClick
            })

            ,new Ext.Button({
                id: 'calprev' + instanceId
                ,iconCls: 'ib-arr-l'
                ,iconAlign:'top'
                ,scale: 'large'
                ,scope: this.calendar
                ,handler: this.calendar.onPrevClick
            })

            ,new Ext.Button({
                id: 'calnext' + instanceId
                ,iconCls: 'ib-arr-r'
                ,iconAlign:'top'
                ,scale: 'large'
                ,scope: this.calendar
                ,handler: this.calendar.onNextClick
            })

            ,this.titleItem
        );

        Ext.apply(this, {
            title: L.Calendar
            ,items: this.calendar
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });
        CB.browser.view.Calendar.superclass.initComponent.apply(this, arguments);

        this.enableBubble(['createobject']);
    }

    ,getViewParams: function() {
        var p = {
            from: 'calendar'
        };
        Ext.apply(p, this.calendar.params);
        return p;
    }

    ,onMainStoreLoad: function(store, records, options) {
        var el = this.getEl();

        if(Ext.isEmpty(el) || !el.isVisible(true)) {
            return;
        }

        var data = [];
        store.each(
            function(r) {
                var d = r.data;
                var sd = App.customRenderers.datetime(d.date);
                var ed = App.customRenderers.datetime(d.date_end);
                var ad = ((sd.length < 11) && (ed.length < 11));
                if(!Ext.isEmpty(d.date)) {
                    data.push({
                        EventId: d.nid //id
                        ,IsAllDay: ad //ad
                        ,category_id: d.category_id
                        ,CalendarId: 1 //that's calendar id (cid)
                        ,StartDate: d.date //start
                        ,EndDate: Ext.valueFrom(d.date_end, d.date) //end
                        ,task_status: d.task_status
                        ,template_id: d.template_id
                        ,Title: d.name //title
                        ,cls: d.cls
                    });
                }
            }
            ,this
        );

        this.calendar.eventStore.loadData(data);
    }

    ,onChangeViewClick: function() {

    }

    ,onRangeSelect: function(c, range, callback){
        var allday = ((Ext.Date.format(range.StartDate, 'H:i:s') == '00:00:00') && (Ext.Date.format(range.EndDate, 'H:i:s') == '23:59:59') ) ? 1 : -1;
        var prefix = (allday == 1) ? 'date' : 'datetime';
        var data = {
            template_id: App.config.default_task_template
            ,data: {
                allday: {
                    value: allday
                    ,childs: {}
                }
            }
        };
        data.data.allday.childs[prefix + '_start'] = range.StartDate;
        data.data.allday.childs[prefix + '_end'] = range.EndDate;

        this.fireEvent('createobject', data);
        callback();
    }

    ,onDayClick: function(c, date, ad, el){
        var allday = (Ext.Date.format(date, 'H:i:s') == '00:00:00') ? 1 : -1;
        var prefix = (allday == 1) ? 'date' : 'datetime';
        var data = {
            template_id: App.config.default_task_template
            ,data: {
                allday: {
                    value: allday
                    ,childs: {
                        date_start: date
                    }
                }
            }
        };
        data.data.allday.childs[prefix + '_start'] = date;

        this.fireEvent('createobject', data);
    }

    ,onActivate: function() {
        this.fireEvent(
            'settoolbaritems'
            ,[
                'apps'
                ,'create'
                ,'-'
                ,'dayview'
                ,'weekview'
                ,'monthview'
                ,'calprev'
                ,'calnext'
                ,'caltitle'
            ]
        );
    }

    ,onSelectionChange: function(selection) {
        this.fireEvent('selectionchange', selection);
    }
});


/* calendar component overrides */
// Ext.calendar.CalendarView.prototype.setStartDate = function(start, refresh) {
//     this.startDate = start.clearTime();
//     this.setViewBounds(start);

//     if (refresh === true) {
//         this.refresh();
//     }
//     this.fireEvent('datechange', this, this.startDate, this.viewStart, this.viewEnd);
// };
