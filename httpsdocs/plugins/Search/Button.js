Ext.namespace('CB.plugins.Search');

CB.plugins.Search.Button =  Ext.extend(CB.plugins.customInterface, {

    init: function(owner) {
        this.historyData = {};
        CB.plugins.Search.Button.superclass.init.call(this, arguments);
        this.owner = owner;

        // get filter button from the collection to detect its toggle group
        var fb = owner.buttonCollection.get('filter');
        if(Ext.isEmpty(fb)) {
            return;
        }

        this.button = new Ext.SplitButton({
            text: L.Search
            ,id: 'pluginsearchbutton'
            ,enableToggle: true
            ,iconCls: 'ib-search'
            ,iconAlign:'top'
            ,scale: 'large'
            ,toggleGroup: fb.toggleGroup
            ,allowDepress: false
            ,itemIndex: 2
            ,menu: []
            ,scope: owner
            ,toggleHandler: owner.onRightPanelViewChangeClick.createSequence(this.onButtonClick, this)
        });



        this.loadSearchTemplates();

        owner.buttonCollection.add(this.button);

        owner.containerToolbar.insert(0, this.button);

        this.searchForm = owner.rightPanel.add({xtype: 'CBSearchPanel'});
    }

    ,onButtonClick: function(b, e) {
        //load default search template if not already loaded
        if(this.defaultSearchLoaded) {
            return;
        } else {
            this.defaultSearchLoaded = true;
            this.searchForm.loadData(b.menu.items.itemAt(0).data);
        }
    }

    ,loadSearchTemplates: function(){
        var menu = this.button.menu;
        var templates = CB.DB.templates.query('type', 'search');
        templates.each(
            function(t){
                clog(t.data);
                menu.add({
                    iconCls: t.data.iconCls
                    ,data: {template_id: t.data.id}
                    ,text: t.data.title
                    ,scope: this
                    ,handler: this.onSearchTemplateClick
                });
            }
            ,this
        );
    }

    ,onSearchTemplateClick: function(b, e) {
        var tid, data, objectsStoreData = [];

        this.owner.onRightPanelViewChangeClick({itemIndex: 2});
        this.button.toggle(true);
        //save currently specified values from search form
        //we also need to save records from objectsStore (used for rendering in grid)
        if(!Ext.isEmpty(this.searchForm.data.template_id)) {
            tid = this.searchForm.data.template_id;
            data = this.searchForm.readValues();

            this.searchForm.objectsStore.each(
                function(r) {
                    objectsStoreData.push(Ext.apply({}, r.data));
                }
                ,this
            );

            this.historyData[tid] = {
                data: Ext.apply({}, data)
                ,storeRecords: objectsStoreData
            };
        }

        this.searchForm.clear();

        //loading data for newly selected template
        tid = b.data.template_id;
        data = Ext.value(this.historyData[tid], {template_id: tid});
        objectsStoreData = [];

        if(!Ext.isEmpty(this.historyData[tid])) {
            data = Ext.apply(this.historyData[tid].data, b.data);
            objectsStoreData = this.historyData[tid].storeRecords;
        }
        this.searchForm.loadData(data);

        //loading objectsStore records
        var storeRecords = [];
        for (var i = 0; i < objectsStoreData.length; i++) {
            storeRecords.push(new this.searchForm.objectsStore.recordType(objectsStoreData[i]));
        }
        this.searchForm.objectsStore.add(storeRecords);
    }
});

Ext.ComponentMgr.registerPlugin('CBPluginsSearchButton', CB.plugins.Search.Button);
