Ext.define('CB.widget.DataSorter', {
    extend: 'Ext.window.Window'

    ,requires: [
        'Ext.button.Button'
        ,'Ext.grid.Panel'
    ]

    ,alias: 'widget.DataSorter'

    ,xtype: 'CBDataSorter'

    ,alwaysOnTop: true

    ,modal: true

    ,closable: true

    ,closeAction: 'destroy'

    ,minimizable: false

    ,minWidth: 250
    ,minHeight: 300
    ,width: 350
    ,height: 400

    ,initComponent: function () {
        var me = this;

        this.store = new Ext.data.JsonStore({
            model: 'Generic'
            ,proxy: {
                type: 'memory'
                ,reader: {
                    type: 'json'
                }
            }
        });

        this.grid = Ext.create('Ext.grid.Panel', {
            border: false
            ,store: this.store
            ,columns: [
                {
                    text: L.Name
                    ,dataIndex: 'name'
                    ,sortable: false
                    ,flex: true
                    ,renderer: function(v, m, r, ri, ci, s){
                        m.css = 'icon-grid-column-top '+ r.get('iconCls');
                        v = '<span class="order-arrows"><b class="click arrow-up">&nbsp;</b><b class="click arrow-down">&nbsp;</b></span><span class="n">' + v + '</span>';
                        return v;
                    }
                }
            ]
            ,viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragText: L.ReorderDDText
                }
                ,listeners: {
                    scope: this
                    ,itemclick: this.onGridItemClick
                }
            }
        });


        Ext.apply(me, {
            title: L.SortValue
            ,border: false
            ,layout: 'fit'
            ,cls: 'x-panel-white'
            ,tbar: Ext.create('Ext.panel.Panel', {
                autoHeight  : true
                ,border: false
                ,bodyPadding: 3
                ,html: L.ReorderItemsMsg
            })
            ,items: [
                this.grid
            ]
            ,buttons: [
                '->'
                ,{
                    text: Ext.MessageBox.buttonText.ok
                    ,scope: this
                    ,handler: this.onOkClick
                },{
                    text: L.Cancel
                    ,scope: this
                    ,handler: this.destroy
                }
            ]
            ,listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });

        me.callParent(arguments);
    }

    ,onAfterRender: function() {
        var map = new Ext.util.KeyMap({
            target: this.grid.getView().getEl()
            ,binding: [{
                key: [10,13]
                ,scope: this
                ,fn: this.onOkClick
            }, {
                key: Ext.event.Event.UP
                ,ctrl: true
                ,shift: false
                ,scope: this
                ,fn: this.onMoveUpClick
            }, {
                key: Ext.event.Event.DOWN
                ,ctrl: true
                ,shift: false
                ,scope: this
                ,fn: this.onMoveDownClick
            }]
        });

        var r = this.store.getAt(0)
            ,v = this.grid.getView();
        if(r) {
            //select
            v.getSelectionModel().select([r]);
            //focus
            v.getNavigationModel().setPosition(0, 0);
            v.focus(10);
            v.focusRow(r, 100);
        }
    }

    ,onMoveUpClick: function(key, e) {
        var v = this.grid.getView()
            ,s = v.getSelection()
            ,idx;

        if(!Ext.isEmpty(s)) {
            idx = this.store.indexOf(s[0]);
            if(idx > 0) {
                v.plugins[0].dropZone.handleNodeDrop(
                    {records: s, view: v}
                    ,this.store.getAt(idx - 1)
                    ,'before'
                );
            }
        }
    }

    ,onMoveDownClick: function(key, e) {
        var v = this.grid.getView()
            ,s = v.getSelection()
            ,idx;

        if(!Ext.isEmpty(s)) {
            idx = this.store.indexOf(s[0]);
            if((idx + 1) < this.store.getCount()) {
                v.plugins[0].dropZone.handleNodeDrop(
                    {records: s, view: v}
                    ,this.store.getAt(idx + 1)
                    ,'after'
                );
            }
        }
    }

    ,onGridItemClick: function(view, record, item, index, e, eOpts) {
        var el = e.getTarget('.arrow-down');
        if(el) {
            this.onMoveDownClick();
        } else {
            el = e.getTarget('.arrow-up');
            if(el) {
                this.onMoveUpClick();
            }
        }
    }

    ,onOkClick: function (b, e) {
        var st = this.store
            ,rez = st.collect('id');

        this.fireEvent('change', this, rez);
    }
});
