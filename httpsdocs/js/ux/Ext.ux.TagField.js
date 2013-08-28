Ext.namespace('Ext.ux');

Ext.ux.TagField = Ext.extend(Ext.Panel, {
    iconCls: 'icon-tag-label'
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
            ,itemSelector: 'li.case_tag'
            ,style: 'margin: 3px; white-space: normal'
            ,tpl: '<ul><tpl for="."><li class="icon-padding16 icon-tag-small {iconCls}" style="display: inline-block">{title}</li></tpl></ul>'
            ,data: []
        });
            
        Ext.apply(this, {
            items: [this.button, this.dataView]
            ,listeners:{
                scope: this
                ,afterrender: this.afterrender
            }
        })
        Ext.ux.TagField.superclass.initComponent.apply(this, arguments);
        this.addEvents('change');
    }
    ,afterrender: function(){ 
        this.setValue(this.value);
    }
    ,setValue: function(v){
        this.value = [];
        if(!Ext.isEmpty(v)) for(i = 0; i < v.length; i++) this.value.push(parseInt(v[i])) ;
        data = [];
        for (var i = 0; i < this.value.length; i++) {
            idx = this.store.findExact('id', this.value[i]);
            if(idx >=0){
                r = this.store.getAt(idx);
                data.push({id: r.get('id'), title: r.get('name'), iconCls: r.get('iconCls')})
            }
        };
        if(this.dataView.rendered) this.dataView.update(data); else this.dataView.data = data;
    }
    ,getValue: function(){ return this.value}
    ,onMenuButtonClick: function(b, e){
        if(!this.tagsMenu){
            listeners = Ext.value(this.listeners, {});
            Ext.apply(listeners, {scope: this, change: this.onTagsChange})
            this.tagsMenu = new Ext.menu.Menu({items: new Ext.ux.TagEditor({
                store: this.store
                ,groupField: this.groupField
                ,filter: this.filter
                ,value: this.value
                ,api: this.api
                ,listeners: listeners
            })
            });
        }
        v = Ext.value(this.getValue(), []);
        
        this.tagsMenu.items.itemAt(0).setValue(v);
        this.tagsMenu.showAt(e.getXY());
    }
    ,onTagsChange: function(ed, newValue){
        this.lastValue = this.getValue();
        if(Ext.isEmpty(this.lastValue)) this.lastValue = {};
        this.lastValue = ed.getValue();
        if(!this.TagsChangeTask) this.TagsChangeTask = new Ext.util.DelayedTask(this.setTags, this);
        this.TagsChangeTask.delay(500);
    }
    ,setTags: function(){
        this.setValue(this.lastValue);
        this.fireEvent('change', this, this.lastValue);
    }
})
Ext.reg('CBTagField', Ext.ux.TagField);