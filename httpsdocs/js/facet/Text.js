Ext.namespace('CB.facet');

Ext.define('CB.facet.Text', {
    extend: 'CB.facet.Base'

    ,xtype: 'CBFacetText'
    ,alias: 'CB.Facet.Text'

    ,autoHeight: true
    ,layout: 'fit'
    ,bodyStyle: 'padding: 5px 5px 0px 5px'

    ,initComponent: function(){
        this.editor = new Ext.form.field.Text({
            emptyText: L.searchText
            ,triggerClass: 'x-form-search-trigger'
            ,name: 'queryText'
            ,enableKeyEvents: true
            ,scope: this
            ,anchor: '100%'
            ,onTriggerClick: function(ev){ this.scope.fireEvent('facetchange', this, ev); }
            ,listeners: {
                specialkey: {scope: this, fn: function(ed, ev){ if(ev.getKey() == ev.ENTER) ed.onTriggerClick(ev); } }
            }
        });

        Ext.apply(this, { items: this.editor });

        this.callParent(arguments);
    }

    ,setValue: function(value){
        this.editor.setValue(value);
    }

    ,getValue: function(){
        return this.editor.getValue();
    }
}
);
