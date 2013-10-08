Ext.namespace('CB'); 
/*
    Search model form with VerticalEditGrid 
 */

CB.Search = Ext.extend(Ext.Panel, {
    title: L.Search
    ,padding: 0
    ,tbarCssClass: 'x-panel-white'
    ,closable: true
    ,layout: 'fit'
    ,initComponent: function(){
        /* objectsStore used to keep selected values from the grid for rendering after edit*/
        this.objectsStore = new Ext.data.JsonStore({
            fields:[ 
                {name: 'id', type: 'int'}
                ,'name'
                ,{name: 'date', type: 'date'}
                ,{name: 'type', type: 'int'}
                ,{name: 'subtype', type: 'int'}
                ,{name: 'template_id', type: 'int'}
                ,{name: 'status', type: 'int'}
                , 'iconCls'
            ]
            ,getTexts: getStoreNames
            ,getData: function(v){
                if(Ext.isEmpty(v)) return [];
                ids = String(v).split(',');
                data = [];
                Ext.each(ids, function(id){
                     idx = this.findExact('id', parseInt(id));
                    if(idx >= 0) data.push(this.getAt(idx).data);           
                }, this)
                return data;
            }
            ,checkRecordExistance: function(data){
                if(Ext.isEmpty(data)) return false;
                idx = this.findExact('id', parseInt(data.id));
                if(idx< 0){
                    r = new this.recordType(data);
                    r.set('iconCls', getItemIcon(data));
                    this.add(r);
                }
            }
        });
        
        /* define all actions needed for this form */
        this.actions = {
            search: new Ext.Action({
                text: L.Search
                ,iconAlign:'top'
                ,iconCls: 'icon32-next'
                ,scale: 'large'
                ,scope: this
                ,handler: this.onSearchClick
            })
        }

        /* the gird actially */
        this.grid = Ext.create({ 
            title: L.Params
            ,refOwner: this
        }, 'CBVerticalEditGrid');

        /* aplly configuration to our panel */
        Ext.apply(this, {
            // initial data used by this panel and grid
            data: {
                template_id: 4
            }
            ,tbar: [this.actions.search]
            ,items: this.grid
            ,listeners:{
                afterrender: this.onAfterRender
                ,beforedestroy: {
                    scope: this
                    ,fn: function(){ 
                        // destroy the grid
                        if(this.grid){
                            this.grid.destroy();
                            delete this.grid;
                        }
                    }
                }
            }
        });
        
        CB.Search.superclass.initComponent.apply(this, arguments);
    }
    // initialize and read data into grid
    ,onAfterRender: function(){
        if(Ext.isEmpty(this.data.gridData)) {
            this.data.gridData = {};
        }
        
        this.grid.reload();
        
        App.focusFirstField(this);
    }
    //processing click on search button
    ,onSearchClick: function(){
        this.data.gridData.values = {};
        this.grid.readValues();
        CB_Search.query(this.data, this.processSearchQueryResponse, this)
    }
    // process server responce to our last search query
    ,processSearchQueryResponse: function (r, e){
        clog('processing search query result');
    }
})

Ext.reg('CBSearch', CB.Search); // register xtype                                                 
