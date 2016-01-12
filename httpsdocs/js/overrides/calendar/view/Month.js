Ext.namespace('Ext.calendar.view');

Ext.override(Ext.calendar.view.Month, {
    //Override translations
    todayText: L.Today

    // override methods that use time fromat to use user time format (App.timeFormat)

    //private
    ,initClock: function() {
        if (Ext.fly(this.id + '-clock') !== null) {
            this.prevClockDay = new Date().getDay();
            if (this.clockTask) {
                Ext.TaskManager.stop(this.clockTask);
            }
            this.clockTask = Ext.TaskManager.start({
                run: function() {
                    var el = Ext.fly(this.id + '-clock'),
                    t = new Date();

                    if (t.getDay() == this.prevClockDay) {
                        if (el) {
                            el.update(Ext.Date.format(t, App.timeFormat));
                        }
                    }
                    else {
                        this.prevClockDay = t.getDay();
                        this.moveTo(t);
                    }
                },
                scope: this,
                interval: 1000
            });
        }
    }

    // private
    ,getTemplateEventData: function(evt) {
        var M = Ext.calendar.data.EventMappings,
        selector = this.getEventSelectorCls(evt[M.EventId.name]),
        title = evt[M.Title.name];

        return Ext.applyIf({
            _selectorCls: selector,
            _colorCls: 'ext-color-' + (evt[M.CalendarId.name] ?
            evt[M.CalendarId.name] : 'default') + (evt._renderAsAllDay ? '-ad': ''),
            _elId: selector + '-' + evt._weekIndex,
            _isRecurring: evt.Recurrence && !Ext.isEmpty(evt.Recurrence),
            _isReminder: evt[M.Reminder.name] && !Ext.isEmpty(evt[M.Reminder.name]),
            Title: (evt[M.IsAllDay.name]
                ? ''
                : Ext.Date.format(evt[M.StartDate.name], App.timeFormat + ' ')
            ) + Ext.valueFrom(title, '(No title)'),
            _operaLT11: this.operaLT11 ? 'ext-operaLT11' : ''
        },
        evt);
    }

});
