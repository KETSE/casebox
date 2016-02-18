Ext.namespace('CB');

Ext.define('CB.VerticalSearchEditGrid', {
    extend: 'CB.VerticalEditGrid'

    ,alias: 'CBVerticalSearchEditGrid'

    ,xtype: 'CBVerticalSearchEditGrid'

    ,initComponent: function() {
        this.initRenderers = Ext.Function.createSequence(this.initRenderers, this.newInitRenderers, this);
        this.initColumns = Ext.Function.createSequence(this.initColumns, this.newInitColumns, this);

        this.oldOnBeforeEditProperty = this.onBeforeEditProperty;
        this.onBeforeEditProperty = this.newOnBeforeEditProperty;

        this.callParent(arguments);

        Ext.apply(this, {
            stateId: 'vseg'
        });
    }

    ,newInitRenderers: function () {
        this.renderers.condition = Ext.Function.bind(
            function(v, meta, record, row_idx, col_idx, store){
                var st = this.getConditionsStore(record.get('type'));
                var idx = st.findExact('id', v);

                if(idx >= 0) {
                    return st.getAt(idx).get('name');
                }
                return '';
            }
            ,this
        );
    }

    ,newInitColumns: function() {

        this.gridColumns.splice(
            1
            ,0
            ,{
                header: L.Condition
                ,width: 50
                ,dataIndex: 'cond'
                ,editor: new Ext.form.TextField()
                ,hidden: true
                ,editable: true
                ,scope: this
                ,renderer: this.renderers.condition
            }
        );
    }

    //grid, record, field, value, row, column, cancel
    ,newOnBeforeEditProperty: function(editor, context, eOpts){ //e
        if(context.field !== 'cond') {
            return this.oldOnBeforeEditProperty(editor, context, eOpts);
        }

        if(context.record.get('type') === 'H') {
            context.cancel = true;
            return;
        }

        var ed = new Ext.form.ComboBox({
            enableKeyEvents: true
            ,forceSelection: true
            ,triggerAction: 'all'
            ,lazyRender: true
            ,queryMode: 'local'
            ,displayField: 'name'
            ,valueField: 'id'
            ,store: this.getConditionsStore(context.record.get('type'))
            ,listConfig: {
                minWidth: 130
                // width: 'auto'
            }
        });
        this.attachKeyListeners(ed);

        context.column.setEditor(ed);
    }

    ,getConditionsStore: function(type) {
        var cond = [];
        switch(type) {
            case 'H':
                break;
            case 'int':
            case 'float':
            case 'date':
            case 'datetime':
                cond = [
                    {id: '=', name: L.condNumEq}
                    ,{id: '<=', name: L.condNumLt}
                    ,{id: '>=', name: L.condNumGt}
                    ,{id: '!=', name: L.condNumNe}
                ];
                // custom value formats (date1 .. date2, )
                break;

            case '_objects':
            case 'combo':
            case 'iconcombo':
            case 'timeunits':
            case '_sex':
                cond = [
                    {id: '<=', name: L.condSetLt}
                    ,{id: '>=', name: L.condSetGt}
                    ,{id: '=', name: L.condSetEq}
                    ,{id: '!=', name: L.condSetNe}
                ];
                //= (exact match), contains any, contains all, does not contain any, does not contain all
                break;

            case '_auto_title':
            case 'varchar':
            case 'text':
            case 'memo':
            case 'html':
                cond = [
                    {id: 'contain', name: L.condTxtContain}
                    ,{id: 'start', name: L.condTxtBegin}
                    ,{id: 'end', name: L.condTxtEnd}
                    ,{id: 'not', name: L.condTxtNc}
                    ,{id: '=', name: L.condTxtEq}
                    ,{id: '!=', name: L.condTxtNe}
                ];
                break;

            case 'checkbox':
                cond = [
                    {id: '=', name: L.condCbEq}
                    ,{id: '!=', name: l.condCbNe}
                ];
                break;

        }

        return new Ext.data.JsonStore({
            autoLoad: true
            ,autoDestroy: true
            ,model: 'Generic2'
            ,data: cond
            ,proxy: {
                type: 'memory'
                ,reader: {
                    type: 'json'
                }
            }
        });
    }
});
