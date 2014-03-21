Ext.namespace('CB.browser.view');

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
    ,showNavBar: true
    //showTodayText: false,
    //showTime: false,
    //title: 'My Calendar', // the header of the calendar, could be a subtitle for the app

    ,params: {
    }
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
        fields.push('status');
        fields.push('iconCls');
        fields.push('cls');
        fields.push('category_id');
        this.eventsReloadTask = new Ext.util.DelayedTask( this.doReloadEventsStore, this);
        // A sample event store that loads static JSON from a local file. Obviously a real
        // implementation would likely be loading remote data via an HttpProxy, but the
        // underlying store functionality is the same.  Note that if you would like to
        // provide custom data mappings for events, see EventRecord.js.
        this.eventStore = new Ext.data.JsonStore({
            autoLoad: false
            ,autoDestroy: true
            ,fields: fields
            ,listeners: {
                scope: this
                ,load: function(st, recs, opt){
                    Ext.each(
                        recs
                        , function(r){
                            cls = 'cal-cat-'+ Ext.value(r.get('cls'), 'default') +
                                ( (r.get('status') == 3) ? ' cal-status-c' : '');
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

        this.addEvents('objectopen', 'changeparams', 'reload');
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
        p.dateStart = date_local_to_ISO_string(bounds.start);
        p.dateEnd = date_local_to_ISO_string(bounds.end);
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
});
Ext.reg('CBCalendar', CB.Calendar);

CB.browser.view.Calendar = Ext.extend(CB.browser.view.Interface, {
    iconCls: 'icon-calendar'
    ,layout: 'border'
    ,closable: true
    ,hideBorders: true
    ,tbarCssClass: 'x-panel-white'
    ,folderProperties: {}
    ,initComponent: function(){

        this.titleItem = new Ext.Toolbar.TextItem({
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

        this.refOwner.buttonCollection.addAll(
            new Ext.Button({
                text: L.Day
                ,id: 'dayview'
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
                ,id: 'weekview'
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
                ,id: 'monthview'
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
                id: 'calprev'
                ,iconCls: 'ib-arr-l'
                ,iconAlign:'top'
                ,scale: 'large'
                ,scope: this.calendar
                ,handler: this.calendar.onPrevClick
            })

            ,new Ext.Button({
                id: 'calnext'
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

        this.addEvents('createobject');
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
            return false;
        }

        var data = [];
        store.each(
            function(r) {
                var d = r.data;
                var sd = App.customRenderers.datetime(d.date);
                var ed = App.customRenderers.datetime(d.date_end);
                var ad = ((sd.length < 11) && (sd.length < 11));
                if(!Ext.isEmpty(d.date)) {
                    data.push({
                        id: d.nid
                        ,ad: ad
                        ,category_id: d.category_id
                        ,cid: 1 //that's calendar id
                        ,start: d.date
                        ,end: Ext.value(d.date_end, d.date)
                        ,status: d.status
                        ,template_id: d.template_id
                        ,title: d.name
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
        var allday = ((range.StartDate.format('H:i:s') == '00:00:00') && (range.EndDate.format('H:i:s') == '23:59:59') ) ? 1 : -1;
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
        var allday = (date.format('H:i:s') == '00:00:00') ? 1 : -1;
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

Ext.reg('CBBrowserViewCalendar', CB.browser.view.Calendar);


CB.browser.view.CalendarPanel = Ext.extend(Ext.Panel, {
    hideBorders: true
    ,borders: false
    ,closable: true
    ,layout: 'fit'
    ,iconCls: 'icon-calendarView'

    ,initComponent: function(){
        this.view = new CB.browser.view.Calendar();

        Ext.apply(this,{
            iconCls: Ext.value(this.iconCls, 'icon-calendarView')
            ,items: this.view
        });

        CB.browser.view.CalendarPanel.superclass.initComponent.apply(this, arguments);
        this.view.setParams({
            path:'/'
            ,descendants: true
            ,filters: {
                "status":[{
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
Ext.reg('CBBrowserViewCalendarPanel', CB.browser.view.CalendarPanel);


/* calendar component overrides */
Ext.calendar.CalendarView.prototype.setStartDate = function(start, refresh) {
    this.startDate = start.clearTime();
    this.setViewBounds(start);

    if (refresh === true) {
        this.refresh();
    }
    this.fireEvent('datechange', this, this.startDate, this.viewStart, this.viewEnd);
};
