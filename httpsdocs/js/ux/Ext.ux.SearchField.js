Ext.ns('CB');

Ext.define('Ext.ux.SearchField', {
    extend: 'Ext.form.field.Text'
    ,alias: ['widget.ExtuxSearchField']

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
                    if(Ext.isEmpty(this.getValue())) this.triggers.clear.hide();
                    else this.triggers.clear.show();
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
        // Ext.ux.SearchField.superclass.initComponent.apply(this);
        this._setValue = this.setValue;
        this.setValue = function(value){
            this._setValue(value);
            if(Ext.isEmpty(value)){
                this.triggers.clear.hide();
            }else{
                this.triggers.clear.show();
            }
        };
    }
    ,afterRender: function() {
        Ext.ux.SearchField.superclass.afterRender.apply(this, arguments);
    }

    ,onTrigger1Click : function(e){
        if(Ext.isEmpty(this.getValue())) return;
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
