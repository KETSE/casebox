Ext.namespace('CB.browser.view');

CB.browser.view.ActionsGrid = Ext.extend(CB.browser.view.Grid,{
    initComponent: function(){

        Ext.apply(this, {
            gridStateId: 'avg'
        })
        CB.browser.view.ActionsGrid.superclass.initComponent.apply(this, arguments);
        this.grid.store.baseParams = {template_types: 'object', facets: 'actions'}
        tb =this.getTopToolbar();
        idx = tb.items.findIndex('iconCls', 'ib-upload');
        if(idx > -1) tb.remove(idx);
        idx = tb.items.findIndex('iconCls', 'ib-download');
        if(idx > -1) tb.remove(idx);
        idx = tb.items.findIndex('iconCls', 'ib-task-new');
        if(idx > -1){
            tb.remove(idx);
            tb.remove(idx-1);
        }
        this.actions.createTask.setHidden(true);
        this.actions.createEvent.setHidden(true);
        this.actions.createFolder.setHidden(true);
        this.actions.createCase.setHidden(true);
    }
})

Ext.reg('CBBrowserViewActionsGrid', CB.browser.view.ActionsGrid);

CB.browser.view.ActionsGridPanel = Ext.extend(Ext.Panel, {
    hideBorders: true
    ,borders: false
    ,closable: true
    ,layout: 'fit'
    ,initComponent: function(){

        this.view = new CB.browser.view.ActionsGrid({
            hideArrows: true
            ,params: { descendants: true }
            ,listeners: {
                scope: this
                ,changeparams: this.onChangeParams
            }
        })
        Ext.apply(this,{
            items: this.view
            ,listeners:{
                scope: this
                ,afterrender: this.onAfterRender
            }
        })
        CB.browser.view.ActionsGridPanel.superclass.initComponent.apply(this, arguments);
    }
    ,onAfterRender: function(){
        this.view.onFiltersChange();
    }
    ,onChangeParams: function(params){
        this.view.onChangeParams(params)
    }
})
Ext.reg('CBBrowserViewActionsGridPanel', CB.browser.view.ActionsGridPanel);
