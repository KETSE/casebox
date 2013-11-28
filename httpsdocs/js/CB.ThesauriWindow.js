Ext.namespace('CB');

CB.ThesauriWindow = Ext.extend(Ext.Window, {
    bodyBorder: false
    ,closable: true
    ,closeAction: 'hide'
    ,hideCollapseTool: true
    ,layout: 'fit'
    ,maximizable: false
    ,minimizable: false
    ,modal: true
    ,plain: true
    ,stateful: true
    ,data: { getStoreFunction: Ext.emptyFn, callback: Ext.emptyFn }
    ,showLocateButton: false
    ,filters: false
    ,selectedValues: []
    ,dateColumn: 'date'
    ,title: L.ChooseValues
    ,store: new Ext.data.ArrayStore({autoDestory: true, idIndex: 0, fields: [{name:'id', mapping: 0}, {name: 'name', mapping: 1}] ,data: []})
    ,minWidth: 350
    ,minHeight: 250
    ,height: 350
    ,initComponent: function(){
        this.cm = [{
                header:' '
                ,dataIndex: 'id'
                ,width: 15
                ,fixed: true
                ,resizable: false
                ,scope: this
                ,renderer: function(value, metaData, record, rowIndex, colIndex, store){
                    if(record.get('header_row') == 1) return;
                    metaData.css = (this.selectedValues.indexOf(value+'') >= 0) ? 'icon-element': 'icon-element-off'
                }
            },{
                header: L.Value
                ,dataIndex: 'name'
                ,width: 270
                ,renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    metaData.css = 'wsn '+ (record.get('icon') ? record.get('icon') + ' icon-padding' : '');
                    return value;
                }
            }
        ];
        this.trigger = new Ext.form.TriggerField({
                triggerClass: 'x-form-search-trigger'
                ,border: false
                ,emptyText: L.Filter
                ,enableKeyEvents: true
                ,onTriggerClick: function(e){this.doFilter(e)}.createDelegate(this)
                ,tabIndex: 1
                ,listeners: {
                    scope: this
                    ,keyup: function(f,e){
                        this.doFilter(e);
                    }
                    ,specialkey: function(f,e){
                        switch(e.getKey()){
                            case e.DOWN:
                            case e.TAB: this.focusGrid(); break;
                            case e.ENTER:  f.onTriggerClick()
                        }
                    }
                }
            })

        this.grid = new Ext.grid.GridPanel({
            border: false
            ,style: 'background-color: white'
            ,stripeRows: true
            ,store: this.store
            ,minColumnWidth: 5
            ,columns: this.cm
            ,tbar: [this.trigger]
            ,viewConfig: {
                forceFit: true
                ,enableRowBody: true
                ,getRowClass: function(r, rowIndex, rp, ds){
                    rp.body = (r.get('header_row') == 1) ? r.get('name') : '';
                    return (rp.body ? 'x-grid3-row-with-body' : '');
                }
            }
            ,hideHeaders: true
            ,sm: new Ext.grid.RowSelectionModel({singleSelect: true})
            ,tabIndex: 2
            ,listeners:{
                scope: this
                ,rowclick: this.toggleElementSelection
                ,keypress: function(e){
                    if( (e.getKey() == e.SPACE) && (!e.hasModifier())){
                        e.stopPropagation();
                        this.toggleElementSelection();
                    }
                }
            }
         });

        Ext.apply(this, {
            buttonAlign: 'left'
            ,items: this.grid
            ,keys:[{
                    key: "\r\n"
                    ,fn: this.doSubmit
                    ,scope: this
                },{
                    key: Ext.EventObject.ESC
                    ,fn: this.doClose
                    ,scope: this
                }
            ]
            ,buttons: [
                {   xtype: 'combo'
                    ,hidden: true
                    ,editable: false
                    ,name: 'filter'
                    ,width: 150
                    ,store: new Ext.data.JsonStore({
                        autoDestroy: true
                        ,proxy: new  Ext.data.MemoryProxy()
                        ,fields: [  'id', 'name' ]
                    })
                    ,valueField: 'id'
                    ,displayField: 'name'
                    ,triggerAction: 'all'
                    ,mode: 'local'
                    ,listeners: {
                        select: {scope: this, fn: this.doFilter}
                    }
                },{text: L.Locate, name: 'btLocate', handler: this.doLocateFirstSelected, scope: this, hidden: true, tabIndex: 5}
                ,{text: L.ClearSelection, handler: this.doClearSelection, scope: this, tabIndex: 6}
                ,'->'
                ,{text: Ext.MessageBox.buttonText.ok, handler: this.doSubmit, scope: this, tabIndex: 3}
                ,{text: Ext.MessageBox.buttonText.cancel, handler: this.doClose, scope: this, tabIndex: 4}
            ]
        });
        CB.ThesauriWindow.superclass.initComponent.apply(this, arguments);
        this.addEvents('setvalue');

        this.on('beforeshow', this.onBeforeShowEvent, this);
        this.on('hide', this.onHideEvent, this);
        this.on('show', this.onAfterLayoutEvent, this);
        this.on('resize', function(win, w, h){this.trigger.setWidth(w - 17)});
    }
    ,focusGrid: function(){
        this.grid.focus();
        if(this.grid.getStore().getCount() > 0){
            r = this.grid.getSelectionModel().getSelected();
            if(!r) r = this.grid.getStore().getAt(0);
            this.grid.getSelectionModel().selectRecords([r]);
            this.grid.getView().focusRow(this.grid.getStore().indexOf(r));
        }
    }
    ,toggleElementSelection: function(g, ri, e){
        r = this.grid.getSelectionModel().getSelected();
        if(!r || (r.get('header_row') == 1)) return;
        id = r.get('id') + '';
        if(this.selectedValues.indexOf(id) < 0 ) this.selectedValues.push(id);
        else this.selectedValues.remove(id);
        this.grid.getView().refresh(false);
        this.grid.getView().focusRow(this.grid.getStore().indexOf(r));
    }
    ,onBeforeShowEvent: function(){
        c = this.buttons[0];
        if(Ext.isArray(this.filters)){
            c.setVisible(true);
            value = null;
            for(i = 0; i < this.filters.length; i++){
                this.filters[i].id = i;
                if(this.filters[i].selected) value = i;
            }
            c.store.loadData(this.filters, false);
            c.setValue(value);
        } else c.setVisible(false);
        this.buttons[1].setVisible(this.showLocateButton === true);
        this.trigger.setValue('');
        this.trigger.focus(true, 350);

        this.submited = false;
        if(Ext.isArray(this.data.value)) this.data.value = this.data.value.join(',');
        this.selectedValues = this.data.value ? this.data.value.split(',') : [];
        if(this.store != this.grid.store) this.grid.reconfigure(this.store, this.grid.getColumnModel());
        if(this.store.fields.findIndex('name', this.dateColumn) >= 0){
            this.grid.reconfigure(this.store,
                new Ext.grid.ColumnModel([{
                    header:' '
                    ,dataIndex: 'id'
                    ,width: 15
                    ,fixed: true
                    ,resizable: false
                    ,scope: this
                    ,renderer: function(value, metaData, record, rowIndex, colIndex, store){
                        if(record.get('header_row') == 1) return;
                        metaData.css = (this.selectedValues.indexOf(value+'') >= 0) ? 'icon-element': 'icon-element-off'
                    }
                },{
                    header: L.Value
                    ,dataIndex: 'name'
                    ,width: 270
                    ,renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                        metaData.css = 'wsn '+ (record.get('icon') ? record.get('icon') + ' icon-padding' : '');
                        return value;
                    }
                },{
                    header: L.Date
                    ,width: 60
                    ,dataIndex: this.dateColumn
                    ,format: App.dateFormat
                    ,renderer: App.customRenderers.date
                }
            ])
            );
        }


        this.doFilter();
        this.setTitle(this.title);
        if(this.iconCls)  this.setIconClass(this.iconCls);
        this.width = 350 + (this.grid.getColumnModel().getColumnCount() - 2) * 100;
        this.setWidth(this.width);
    },onHideEvent: function(){
        this.grid.store.clearFilter();
        this.header.removeClass(this.iconCls);
        this.header.removeClass('x-panel-icon');
        delete this.iconCls;
        if(this.data.scope && this.data.scope.grid && this.data.scope.grid.gainFocus) this.data.scope.grid.gainFocus.createDelegate(this.data.scope.grid)();
    },onAfterLayoutEvent: function(){
        if(Ext.isArray(this.data.value)) this.data.value = this.data.value.join(',');
        this.selectedValues = this.data.value ? this.data.value.split(',') : [];
        this.grid.getView().refresh(false);
        this.grid.getSelectionModel().selectFirstRow();
        this.grid.syncSize();
    },doFilter: function(e){
        criterias = [{fn: function(rec){return !Ext.isEmpty(rec.get('id'))}, scope: this}];
        v = this.trigger.getValue();
        if(!Ext.isEmpty(v)) criterias.push({ property: 'name', value: v, anyMatch: true, caseSensitive: false });

        if(Ext.isArray(this.filters)){
            c = this.buttons[0];
            cr = this.filters[c.getValue()];
            if(cr) criterias.push({scope: this, fn: cr.fn});
        }
        if(Ext.isEmpty(criterias)) this.grid.store.clearFilter(); else this.grid.store.filter(criterias);
    },doClearSelection: function(){
        this.selectedValues = [];
        this.grid.getView().refresh(false);
    },doSubmit: function(){
        this.grid.store.clearFilter();
        this.value = this.selectedValues.join(',');
        this.submited = true;
        this.fireEvent('setvalue', v, this);
        this.data.callback.createDelegate(Ext.value(this.data.scope, this), [this, this.value])();
        this.doClose();
    },doClose: function(){
        this.filters = false;
        this.showLocateButton = false;
        this.dateColumn = 'date';
        this.hide();
    }
})

Ext.reg('CBThesauriWindow', CB.ThesauriWindow); // register xtype
