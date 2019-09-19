Ext.ns('CB');

Ext.define('CB.search.Field', {

    extend: 'Ext.form.field.Text'

    ,xtype: 'CBSearchField'
    ,alias: 'widget.CBSearchField'

    ,emptyText: L.Search
    ,enableKeyEvents: true
    ,style: 'background-color: #fff'

    ,triggers: {
        clear: {
            cls: 'x-form-clear-trigger'
            ,hidden: true
            ,scope: 'this'
            ,handler: 'onTrigger1Click'
        }
        ,search: {
            cls: 'x-form-search-trigger'
            ,scope: 'this'
            ,handler: 'onTrigger2Click'
        }
    }

    ,initComponent : function(){
        Ext.apply(this, {
            listeners: {
                scope: this
                ,keyup: function(ed, e){
                    if(Ext.isEmpty(this.getValue())) {
                        this.triggers.clear.hide();

                    } else {
                        this.triggers.clear.show();
                    }
                }
                ,specialkey: function(ed, e){
                    switch(e.getKey()){
                        case e.ESC:
                            this.onTrigger1Click(e);
                            break;
                        case e.ENTER:
                            this.onTrigger2Click(e);
                            break;
                    }
                }
            }
        });

        this.callParent(arguments);
    }

    ,afterRender: function() {
        this.callParent(arguments);
    }

    ,setValue: function(value) {
        this.callParent(arguments);

        if (Ext.isEmpty(value)){
            this.triggers.clear.hide();
        } else {
            this.triggers.clear.show();
        }
    }

    ,onTrigger1Click : function(e){
        if(Ext.isEmpty(this.getValue())) {
            return;
        }

        this.setValue('');
        this.triggers.clear.hide();
        this.fireEvent('search', '', e);
    }

    ,onTrigger2Click : function(e){
        this.fireEvent('search', this.getValue(), this, e);
    }

    ,clear: function(){
        this.setValue('');
        this.triggers.clear.hide();
    }
});
