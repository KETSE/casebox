/*
    Overrides
*/

Ext.override(Ext.grid.plugin.CellEditing, {

    //overriding onEditComplete method
    //used for CB.plugin.field.DropDownList to avoid canceling edit when popup list visible

    onEditComplete : function(ed, value, startValue) {
        if(ed.field) {
            if(ed.field.preventEditComplete) {
                delete ed.field.preventEditComplete;
                return;
            }
        }

        this.callParent(arguments);
    }
});
