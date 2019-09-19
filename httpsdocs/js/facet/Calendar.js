Ext.namespace('CB.facet');

Ext.define('CB.facet.Calendar', {
    extend: 'CB.facet.Base'

    ,xtype: 'CBFacetCalendar'
    ,alias: 'CB.Facet.Calendar'

    ,autoHeight: true
    ,layout: 'fit'
    ,bodyStyle: 'padding: 0'

    ,initComponent: function(){
        this.editor = new Ext.picker.Date({
            anchor: '100%'
            ,border: false
            ,listeners: {
                scope: this
                ,select: this.onDateSelect
            }
        });

        Ext.apply(this, {
            items: this.editor
        });

        this.callParent(arguments);

        this.enableBubble('dateselect');
    }

    ,getToolButtons: function() {
        return [];
    }

    ,setValue: function(value){
        this.editor.setValue(value);
    }

    ,getValue: function(){
        return this.editor.getValue();
    }

    ,updateVisibility: function() {
        this.setVisible(true);
    }

    ,onDateSelect: function(ed, date, eOpts) {
        this.fireEvent('dateselect', date);
    }
});
