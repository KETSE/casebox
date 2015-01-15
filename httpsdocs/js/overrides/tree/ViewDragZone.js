/*
    Overrides for preventing nodes selection when start dragging node
*/

Ext.override(Ext.tree.ViewDragZone, {
    lastClickAt: null,
    b4MouseDown : function(e){
        var view = this.view
            ,sm = view.getSelectionModel();
        this.lastClickAt = e.getXY();
        if(sm) {
            view.suspendEvents(true);
            sm.suspendEvents(true);
        }
        this.callParent(arguments);
    }
});

Ext.override(Ext.tree.ViewDragZone, {
    onMouseUp : function(e){
        var view = this.view
            ,sm = view.getSelectionModel();
        var loc = e.getXY();
        if(sm && (Ext.isEmpty(this.lastClickAt) || (this.lastClickAt[0] == loc[0] && this.lastClickAt[1] == loc[1]))) {
            sm.resumeEvents();
            view.resumeEvents();
        } else{
            sm.resumeEvents(true);
            view.resumeEvents(true);
        }
        this.callParent(arguments);
    }
});
