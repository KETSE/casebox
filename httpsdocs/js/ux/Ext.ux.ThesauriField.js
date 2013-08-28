Ext.namespace('Ext.ux');

Ext.ux.ThesauriField = Ext.extend(Ext.Panel, {
    iconCls: 'icon-element'
    ,bodyStyle: 'border: 1px solid #b5b8c8'
    ,cls: 'x-form-field'
    ,isFormField: true
    ,initComponent: function() {
        listeners = Ext.value(this.listeners, {});
        Ext.apply(listeners, {scope: this, change: this.onTagsChange});
        this.button = new Ext.Button({
            iconCls: this.iconCls
            ,cls:'fr '
            ,style: 'margin:-1px -2px '
            ,scope: this
            ,handler: this.onMenuButtonClick
        });
        this.dataView = new Ext.DataView({
            emptyText: L.NoTags
            ,overCls: 'field-over'
            ,itemSelector: 'li.item'
            ,style: 'margin: 3px; white-space: normal'
            ,tpl: '<ul><tpl for="."><li class="icon-padding16 icon-element {iconCls}" style="display: inline-block">{title}</li></tpl></ul>'
            ,data: []
        });
            
        Ext.apply(this, {
            items: [this.button, this.dataView]
        })
        Ext.ux.ThesauriField.superclass.initComponent.apply(this, arguments);
        this.addEvents('change');
    }
    ,setValue: function(v){
        this.value = [];
        if(!Ext.isEmpty(v)){
            if(!Ext.isArray(v)) v = v.split(',');
            for(i = 0; i < v.length; i++) this.value.push(parseInt(v[i])) ;
        }
        data = [];
        this.store.each(function(r){idx = this.value.indexOf(r.get('id')); if(idx >=0) data.push({id: r.get('id'), title: r.get('name'), iconCls: r.get('iconCls')})}, this)
        if(this.dataView.rendered) this.dataView.update(data); else this.dataView.data = data;
    }
    ,getValue: function(){ return this.value}
    ,onMenuButtonClick: function(b, e){
        w = App.getThesauriWindow({
            title: this.fieldLabel
            ,iconCls: this.iconCls
            ,store: this.store
            ,data: {
                value: this.value
                ,scope: this
                ,callback: this.onValueChange
            }
        }
        );
        w.on('hide', this.focus, this);
        w.show();       
    }
    ,focus: function(){
        this.button.focus();
        w.un('hide', this.focus, this);
    }
    ,onValueChange: function(w, newValue){
        oldValue = this.getValue();
        if(oldValue == newValue) return;
        this.setValue(newValue);
        this.fireEvent('change', oldValue, newValue);
    }
})
Ext.reg('CBThesauriField', Ext.ux.ThesauriField);