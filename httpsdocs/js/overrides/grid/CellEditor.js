/*
    Overrides
*/

Ext.override(Ext.grid.CellEditor, {

    //overriding onEditComplete method
    //used for CB.plugin.field.DropDownList to avoid canceling edit when popup list visible

    onEditComplete: function(remainVisible) {
        if(this.field) {
            if(this.field.preventEditComplete) {
                // we dont delete the flag here
                // it will be deleted by CellEditing plugin onEditComplete overriden method
                // delete this.field.preventEditComplete;

                return;
            }
        }

        this.callParent(arguments);
    }
});
