Ext.namespace('Ext.calendar.view');

Ext.override(Ext.calendar.template.BoxLayout, {
    //Override translations

    // override methods that use time fromat to use user time format (App.timeFormat)
    getTodayText : function(){
        var dt = Ext.Date.format(new Date(), 'l, F j, Y'),
            fmt,
            todayText = this.showTodayText !== false ? this.todayText : '',
            timeText = this.showTime !== false ? ' <span id="'+this.id+'-clock" class="ext-cal-dtitle-time">' +
                    Ext.Date.format(new Date(), App.timeFormat) + '</span>' : '',
            separator = todayText.length > 0 || timeText.length > 0 ? ' &mdash; ' : '';

        if(this.dayCount == 1){
            return dt + separator + todayText + timeText;
        }
        fmt = this.weekCount == 1 ? 'D j' : 'j';
        return todayText.length > 0 ? todayText + timeText : Ext.Date.format(new Date(), fmt) + timeText;
    }
});
