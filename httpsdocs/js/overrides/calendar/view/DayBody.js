Ext.namespace('Ext.calendar.view');

Ext.override(Ext.calendar.view.DayBody, {
    //Override translations

    // override methods that use time fromat to use user time format (App.timeFormat)

    // private
    getTemplateEventData: function(evt) {
        var selector = this.getEventSelectorCls(evt[Ext.calendar.data.EventMappings.EventId.name]),
            data = {},
            M = Ext.calendar.data.EventMappings;

        this.getTemplateEventBox(evt);

        data._selectorCls = selector;
        data._colorCls = 'ext-color-' + (evt[M.CalendarId.name] || '0') + (evt._renderAsAllDay ? '-ad': '');
        data._elId = selector + (evt._weekIndex ? '-' + evt._weekIndex: '');
        data._isRecurring = evt.Recurrence && !Ext.isEmpty(evt.Recurrence);
        data._isReminder = evt[M.Reminder.name] && !Ext.isEmpty(evt[M.Reminder.name]);
        var title = evt[M.Title.name];
        data.Title = (
            evt[M.IsAllDay.name]
            ? ''
            : Ext.Date.format(evt[M.StartDate.name], App.timeFormat + ' ')
        ) + Ext.valueFrom(title, '(No title)');

        return Ext.applyIf(data, evt);
    }
});
