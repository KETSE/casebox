Ext.namespace('CB');

Ext.define('CB.controller.Browsing', {
    extend: 'Ext.util.Observable'

    ,xtype: 'browsingcontroller'

    ,constructor: function() {
        this.callParent(arguments);

        App.on('cbinit', this.onAppInit, this);
    }

    /**
     * set main manipulated components and needed event listeners
     * on application initialization
     * @return void
     */
    ,onAppInit: function() {
        var vp = App.mainViewPort
            ,bc = vp.breadcrumb
            ,sf = vp.searchField
            ,tree = App.mainTree
            ,vc = App.explorer
            ,op = vc.objectPanel
            ,fp = App.mainFilterPanel;

        this.tree = tree;

        //viewport (VP)
        this.VP = vp;

        //search field (SF)
        this.SF = sf;

        //breadcumb
        this.breadcrumb = bc;

        //view container (VC)
        this.VC = vc;

        //object panel (OP), used for preview of selected items
        this.OP = op;

        //filter panel
        this.FP = fp;


        //add tree listeners
        tree.getSelectionModel().on(
            'selectionchange'
            ,this.onTreeSelectionChange
            ,this
        );

        tree.on('itemclick', this.onTreeItemClick, this);
        tree.on('afterrename', this.onTreeRenameItem, this);

        //search field listeners
        sf.on('search', this.onSFSearch, this);

        //breadcumb listeners
        bc.onItemClick = Ext.Function.bind(this.onBreadcrumbItemClick, this);


        //add view container listeners
        vc.on('viewloaded', this.onVCViewLoaded, this);
        vc.on('selectionchange', this.onVCSelectionChange, this);


        //add filter panel listeners
        fp.on('change', this.onFiltersChange, this);

        //add object panel listeners
        op.on('expand', this.onOPExpand, this);
    }

    // TREE methods

    /**
     * tree selection change listener
     * @param  object sm        [description]
     * @param  array selection [description]
     * @return void
     */
    ,onTreeSelectionChange: function(sm, selection){
        if(this.syncingTreePathWithViewContainer ||
            Ext.isEmpty(selection) ||
            Ext.isEmpty(selection[0].getPath)
        ) {
            return;
        }

        var node = selection[0];
        var params = {
            id: node.get('nid')
            ,from: 'tree'
            // ,view: Ext.valueFrom(node.get('view'), 'grid')
        };

        App.openPath(node.getPath('nid'), params);

        this.updatePreview(node.data);
    }

    /**
     * tree item click listener
     * @param  component tree   [description]
     * @param  Model record [description]
     * @param  Node item   [description]
     * @param  int index  [description]
     * @param  object e      [description]
     * @param  object eOpts  [description]
     * @return void
     */
    ,onTreeItemClick: function(tree, record, item, index, e, eOpts){
        if(Ext.isEmpty(item) || Ext.isEmpty(record.getPath)) {
            return;
        }

        if(tree.getSelectionModel().isSelected(record)) {
            this.onTreeSelectionChange(null, [record]);
        }

        this.updatePreview(record.data);
    }

    /**
     * reload the viewcontainer when a tree node is renamed
     * @return void
     */
    ,onTreeRenameItem: function(tree, r, e){
        var node = tree.getSelectionModel().getSelection()[0];

        if(Ext.isEmpty(node) || Ext.isEmpty(node.getPath)) {
            return;
        }

        this.VC.onReloadClick();

        this.updatePreview(node.data);
    }

    //View container methods

    /**
     * view loaded listener
     * @param  object proxy
     * @param  object action
     * @param  object options
     * @return void
     */
    ,onVCViewLoaded: function(proxy, action, options) {
        //change breadcrumb value for search template restults
        var bvalue = action.pathtext;

        if(options.search && !isNaN(options.search.template_id)) {
            bvalue = L.SearchResultsTitleTemplate;
            bvalue = bvalue.replace('{name}', CB.DB.templates.getName(options.search.template_id));
            bvalue = bvalue.replace('{count}', action.total);
        } else if(!Ext.isEmpty(options.query)) {
            bvalue = L.SearchResultsTitleTemplate;
            bvalue = bvalue.replace('{name}', options.query);
            bvalue = bvalue.replace('{count}', action.total);
        }

        this.breadcrumb.setValue(bvalue);

        this.VC.updateCreateMenuItems(this.VP.buttons.create);


        if(action &&
            action.folderProperties &&
            Ext.isEmpty(this.VC.params.query) // dont sync on search query
        ) {
            // add flag to avoid reloading viewport on tree node selection change
            this.syncingTreePathWithViewContainer = true;

            this.tree.updateCreateMenu(action.folderProperties.menu);

            //check if rootnode id is set at the beginning of the path
            //its id could be missing if it's a virtual root node
            var p = String(action.folderProperties.path).split('/');
            if(p.indexOf(App.config.rootNode.nid) < 0) {
                if(Ext.isEmpty(p[0])) {
                    p.splice(1, 0, App.config.rootNode.nid);
                } else {
                    p.unshift(App.config.rootNode.nid);
                }
            }
            //select the path in tree
            App.mainTree.selectPath(
                p.join('/')
                ,'nid'
                ,'/'
                ,function(){
                    delete this.syncingTreePathWithViewContainer;
                }
                ,this
            );
        }
    }

    ,onVCSelectionChange: function(objectsDataArray) {
        if(!Ext.isEmpty(this.VC.params.query) && Ext.isEmpty(objectsDataArray)) {
            this.updatePreview({});
            return;
        }

        if(!this.VC.params.locatingObject) {
            this.updatePreview();
        }
    }


    //Filter panel methods
    /**
     * filter panel change listener
     * @param  array filters
     * @return void
     */
    ,onFiltersChange: function(filters){
        this.VC.changeSomeParams({filters: filters});
    }


    //Search field methods
    ,onSFSearch: function(query, editor, event){
        editor.clear();
        query = String(query).trim();

        if(Ext.isEmpty(query)) {
            return;
        }

        if(query.substr(0,1) == '#') {
            query = query.substr(1).trim();
            if(!isNaN(query)) {
                // this.locateObject(query);
                this.openObjectWindowById(query);
                return;
            }
        }

        this.VC.setParams({
            query: query
            ,descendants: !Ext.isEmpty(query)
        });

        this.updatePreview({});
    }


    //Breadcrumb methods

    /**
     * dreadcrumb item click listemer
     * @param  button b
     * @param  event e
     * @return void
     */
    ,onBreadcrumbItemClick: function(b, e) {
        var idx = this.breadcrumb.menu.items.indexOf(b)
            ,p = String(this.VC.folderProperties.path).split('/');

        if(Ext.isEmpty(p[0])) {
            p.shift();
        }

        if((p.length > 0) && Ext.isEmpty(p[p.length-1])) {
            p.pop();
        }

        p = p.slice(0, p.length - idx - 1);
        p = p.join('/');
        if(p.substr(0, 1) !== '/') {
            p = '/' + p;
        }

        this.VC.changeSomeParams({'path': p});
    }

    //Object preview component methods

    ,onOPExpand: function() {
        this.updatePreview();
    }
    /**
     * update preview
     * @param  object customParams
     * @return void
     */
    ,updatePreview: function(customParams) {
        var vc = this.VC
            ,fp = vc.folderProperties;

        var data = customParams;

        //if custom params are empty then try to load current view selection
        //or the currently opened object
        if(Ext.isEmpty(data)) {
            var s = vc.cardContainer.getLayout().activeItem.currentSelection;
            data = Ext.isEmpty(s)
                ? {
                    id: fp.id
                    ,name: fp.name
                    ,template_id: fp.template_id
                }
                : {
                    id: Ext.valueFrom(Ext.valueFrom(s[0].target_id, s[0].nid), s[0].id)
                    ,name: s[0].name
                    ,template_id: s[0].template_id
                    ,can: s[0].can
                };
        } else {
            data = {
                id: Ext.valueFrom(Ext.valueFrom(data.target_id, data.nid), data.id)
                ,name: data.name
                ,template_id: data.template_id
                ,can: data.can
            };
        }

        this.OP.load(data);
    }

    //Genertal methods

    /**
     * locate object method that will retreive object path if not given in params
     * For backward compatibility params could also be specified as (id, path)
     * @param  object params
     * @return
     */
    ,locateObject: function(params){ //object_id, path
        if(!Ext.isObject(params)) {
            params = {id: params};
        }

        if(Ext.isEmpty(params.path) && !Ext.isEmpty(arguments[1])) {
            params.path = arguments[1];
        }

        if(Ext.isEmpty(params.path)){
            CB_Path.getPidPath(
                params.id
                ,function(r, e){
                    if(r.success !== true) {
                        return ;
                    }
                    this.locateObject(r);
                }
                ,this
            );
            return;
        }

        this.updatePreview(params);

        //check and remove object id from path property if present
        var path = params.path.split('/');
        path = Ext.Array.difference(path, [String(params.id)]);
        params.path = path.join('/');

        Ext.apply(
            params
            ,{
                locatingObject: params.id
                ,descendants: false
                ,query: ''
                ,filters: {}
            }
        );

        this.openPath(params);
    }

    /**
     * loads basic data for given object id and try to open its window if found
     * @param  int id
     * @return void
     */
    ,openObjectWindowById: function (id) {
        if(!Ext.isNumeric(id)) {
            return;
        }

        CB_Objects.getBasicInfoForId(
            id
            ,function(r, e) {
                if(r.success !== true) {
                    Ext.Msg.alert(
                        L.Error
                        ,L.RecordIdNotFound.replace('{id}', '#' + r.id)
                    );
                    return;
                }
                App.openObjectWindow(r.data);
            }
            ,this
        );
    }

    /**
    * open path on active explorer tabsheet or in default eplorer tabsheet
    *
    * this function will not reset explorer navigation params (filters, search query, descendants)
    *
    * for backward compatibility params could be specified as (path, params)
    */
    ,openPath: function(params){

        if(!Ext.isObject(params)) {
            var path = Ext.valueFrom(Ext.clone(arguments[0]), '/');
            params = Ext.valueFrom(Ext.clone(arguments[1]), {});
            params.path = path;
        } else {
            params = Ext.valueFrom(params, {});

        }

        params.locatingObject = Ext.valueFrom(params.locatingObject, false);
        params.query = null;
        params.start = 0;
        params.page = 1;

        this.VC.setParams(params);
    }
});
