Ext.namespace('CB.browser.view');


Ext.define('CB.browser.view.CalendarPanel', {
    extend: 'CB.browser.view.Interface'
    ,border: false
    ,closable: true
    ,layout: 'fit'

    ,initComponent: function(){
        this.view = new CB.browser.view.Calendar();

        Ext.apply(this,{
            items: this.view
        });

        this.callParent(arguments);
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
    ,showNavBar: true
    ,params: {
    }

    ,initComponent: function(){
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
        });

        this.eventsReloadTask = new Ext.util.DelayedTask( this.doReloadEventsStore, this);

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
                    if(this.getEl().isVisible(true) !== true) {
                        return;
                    }

                    if(this.editWin) {
                        this.editWin.hide();
                    }

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

                ,initdrag: function(vw){
                    // return false;
                    // if(this.editWin && this.editWin.isVisible()) this.editWin.hide();
                }
            }
        });

        this.callParent(arguments);

        this.enableBubble(['objectopen', 'changeparams', 'reload']);
    }

    ,doReloadEventsStore: function(){
        this.allowedReload = true;

        if(Ext.isEmpty(this.getLayout().activeItem)) {
            return;
        }

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
            return;
            // rec = new Ext.calendar.EventRecord(rec);
        }

        var s = [{
            nid: rec.data.EventId
            ,template_id: rec.data.template_id
            ,name: rec.data.Title
        }];

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
        if(Ext.isEmpty(this.titleItem)) {
            return;
        }

        if(Ext.isEmpty(view)) {
            view = this.getLayout().activeItem;
            dateInfo = view.getViewBounds();
        } else if(Ext.isEmpty(dateInfo)) {
            dateInfo = view.getViewBounds();
        }

        var sd = dateInfo.viewStart
            ,ed = dateInfo.viewEnd
            ,ad = dateInfo.activeDate
            ,text = '';

        switch(view.xtype){
            case 'dayview':
                text = Ext.Date.format(ad, 'F j, Y');
                break;

            case 'weekview':
                if(sd.getFullYear() == ed.getFullYear()) {
                    if(sd.getMonth() == ed.getMonth()) {
                        text = Ext.Date.format(sd, 'F j') + ' - ' + Ext.Date.format(ed, 'j, Y');
                    } else {
                        text = Ext.Date.format(sd, 'F j') + ' - ' + Ext.Date.format(ed, 'F j, Y');
                    }
                } else {
                    text = Ext.Date.format(sd, 'F j, Y') + ' - ' + Ext.Date.format(ed, 'F j, Y');
                }

                break;

            case 'monthview':
                text = Ext.Date.format(ad, 'F Y');
                break;
        }

        this.titleItem.text = text;
        this.titleItem.setText(text);
    }
});

Ext.define('CB.browser.view.Calendar', {
    extend: 'CB.browser.view.Interface'
    ,xtype: 'CBBrowserViewCalendar'

    ,layout: 'border'
    ,closable: true
    ,border: false
    ,tbarCssClass: 'x-panel-white'
    ,folderProperties: {}

    ,initComponent: function(){

        this.titleItem = new Ext.toolbar.TextItem({
            id: 'caltitle'
            ,cls: 'calendar-title'
            ,text: '<span style="font-size: 16px; font-weight: bold; color: #333"> &nbsp; </span>'
        });
        var viewGroup = Ext.id();

        this.coloringCombo = new Ext.form.ComboBox({
            xtype: 'combo'
            ,itemId: 'coloringCombo'
            ,selectedFacetIndex: 0
            ,forceSelection: true
            ,editable: false
            ,triggerAction: 'all'
            ,lazyRender: true
            ,queryMode: 'local'
            ,fieldLabel: L.ColoringBy
            ,labelWidth: 'auto'
            ,style: 'margin-right: 10px'
            ,store: new Ext.data.JsonStore({
                model: 'Generic2'
            })
            ,displayField: 'name'
            ,valueField: 'id'
            ,listeners: {
                scope: this
                ,select: this.onColoringComboChange
            }
        });

        this.calendar = new CB.Calendar({
            titleItem: this.titleItem
            ,region: 'center'
            ,border: false
            ,showNavBar: false
            ,listeners:{
                scope: this
                ,rangeselect: this.onRangeSelect
                ,dayclick: this.onDayClick
                ,selectionchange: this.onSelectionChange

                //,rangeselect: this.onRangeSelect
                ,eventmove: function(vw, rec){
                    this.updateRecordDatesRemotely(rec);
                }
                ,eventresize: function(vw, rec){
                    this.updateRecordDatesRemotely(rec);
                }
                ,viewchange: function(cmp, view) {
                    var BC = this.refOwner.buttonCollection;

                    BC.get(view.xtype).setPressed(true);
                }
            }
        });

        if(this.store) {
            this.store.on('load', this.onMainStoreLoad, this);
        }

        this.refOwner.buttonCollection.addAll(
            new Ext.Button({
                text: L.Day
                ,itemId: 'dayview'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-cal-day'
                ,scale: 'medium'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this.calendar
                ,handler: this.calendar.onDayClick
            })

            ,new Ext.Button({
                text: L.Week
                ,itemId: 'weekview'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-cal-week'
                ,scale: 'medium'
                ,toggleGroup: 'cv' + viewGroup
                ,scope: this.calendar
                ,handler: this.calendar.onWeekClick
            })

            ,new Ext.Button({
                text: L.Month
                ,itemId: 'monthview'
                ,enableToggle: true
                ,allowDepress: false
                // ,iconCls: 'ib-cal-month'
                ,scale: 'medium'
                ,toggleGroup: 'cv' + viewGroup
                ,pressed: true
                ,scope: this.calendar
                ,handler: this.calendar.onMonthClick
            })

            ,new Ext.Button({
                itemId: 'calprev'
                ,iconCls: 'im-arr-l'
                ,scale: 'medium'
                ,scope: this.calendar
                ,handler: this.calendar.onPrevClick
            })

            ,new Ext.Button({
                itemId: 'calnext'
                ,iconCls: 'im-arr-r'
                ,scale: 'medium'
                ,scope: this.calendar
                ,handler: this.calendar.onNextClick
            })

            ,this.titleItem
            ,this.coloringCombo
        );

        Ext.apply(this, {
            title: L.Calendar
            ,items: this.calendar
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });

        this.callParent(arguments);

        this.enableBubble(['createobject', 'reload']);
    }

    ,getViewParams: function() {
        var p = {
            from: 'calendar'
            ,selectedColoring: this.selectedColoring
        };
        Ext.apply(p, this.calendar.params);

        return p;
    }

    ,onMainStoreLoad: function(store, records, options) {
        var el = this.getEl();

        if(Ext.isEmpty(el) || !el.isVisible(true)) {
            return;
        }

        this.loadColoringFacets();

        var data = [];

        store.each(
            function(r) {
                var d = r.data;

                d.date = Ext.valueFrom(d.date, d.date_end);

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
                        ,style: d.style
                    });
                }
            }
            ,this
        );

        this.calendar.eventStore.loadData(data);

    }

    ,loadColoringFacets: function() {
        var rawData = this.store.proxy.reader.rawData
            ,vp = Ext.valueFrom(rawData.view, {})
            ,coloring = Ext.valueFrom(vp.coloring, [])
            ,data = [];

        if(Ext.isEmpty(this.selectedColoring)) {
            this.selectedColoring = vp.defaultColoring;
        }

        Ext.iterate(
            rawData.facets
            ,function(key, val, o) {
                if(coloring.indexOf(val.f) > -1) {
                    data.push({
                        id: val.f
                        ,name: val.title
                    });

                    if(Ext.isEmpty(this.selectedColoring)) {
                        this.selectedColoring = val.f;
                    }
                }
            }
            ,this
        );

        this.coloringCombo.store.loadData(data);
        this.coloringCombo.setValue(this.selectedColoring);
        this.coloringCombo.setHidden(this.coloringCombo.store.getCount() < 2);
    }

    ,onColoringComboChange: function(c, rec, eOpts) {
        this.selectedColoring = c.getValue();

        this.fireEvent('reload', this);
    }

    ,onChangeViewClick: function() {

    }

    ,onRangeSelect: function(c, range, callback){
        var allday = ((Ext.Date.format(range.StartDate, 'H:i:s') === '00:00:00') && (Ext.Date.format(range.EndDate, 'H:i:s') === '23:59:59') ) ? 1 : -1;
        var prefix = (allday == 1) ? 'date' : 'datetime';
        var data = {
            pid: this.refOwner.folderProperties.id
            ,template_id: App.config.default_task_template
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
        var allday = (Ext.Date.format(date, 'H:i:s') === '00:00:00') ? 1 : -1;
        var prefix = (allday == 1) ? 'date' : 'datetime';
        var data = {
            pid: this.refOwner.folderProperties.id
            ,template_id: App.config.default_task_template
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
                ,'calprev'
                ,'calnext'
                ,'caltitle'
                ,'->'
                ,'coloringCombo'
                ,'dayview'
                ,'weekview'
                ,'monthview'
                ,'-'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );

        this.calendar.fireViewChange();
    }

    ,onSelectionChange: function(selection) {
        // this.fireEvent('selectionchange', selection);
        if(selection) {
            var data = Ext.isArray(selection)
                ? data = selection[0]
                : selection;

            this.fireEvent('openobject', data);
        }
    }

    ,updateRecordDatesRemotely: function(record){
        var dateEnd = date_local_to_ISO_string(record.get('EndDate'));

        if(this.store) {
            var r = this.store.findRecord('nid', record.get('EventId'), 0, false, true, true);
            if(r) {
                if(Ext.isEmpty(r.get('date_end'))) {
                    dateEnd = null;
                }
            }
        }

        CB_Tasks.updateDates(
            {
                id: record.get('EventId')
                ,date_start: date_local_to_ISO_string(record.get('StartDate'))
                ,date_end: date_local_to_ISO_string(dateEnd)
            }
            ,function(r, e){
                if(!r || (r.success !== true)) {
                    this.reject();
                } else {
                    this.commit();
                }
            }
            ,record
        );
    }
});
