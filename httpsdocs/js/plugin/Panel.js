Ext.namespace('CB');

/**
 * Basic plugins panel for an item, that requires an api to be set
 * It contains main functionality for loading plugins data from given api,
 * instanciate plugin classes and add them tho the panel body
 * Also display object title at the top if not a task
 */

Ext.define('CB.plugin.Panel', {
    extend: 'Ext.Panel'
    ,alias: 'CBPluginPanel'

    ,scrollable: true
    ,cls: 'plugins-panel'
    ,padding:0

    ,initComponent: function(){

        Ext.apply(this, {
            layout: {
                type: 'vbox'
                ,align: 'stretch'
            }
        });

        this.callParent(arguments);

        App.on('filesuploaded', this.onFilesUploaded, this);
        App.on('objectsaction', this.onObjectsAction, this);

        this.delayLoadTask = new Ext.util.DelayedTask(this.doLoad, this);
    }

    ,load: function (params) {
        if(!isNaN(params)) {
            params = {id: params};
        }

        var el = this.getEl();
        if(Ext.isEmpty(el) || !el.isVisible(true)) {
            return;
        }

        this.delayLoadTask.cancel();

        /* check if not the same as current params */
        if(Ext.encode(params) == Ext.encode(this.loadedParams)) {
            return;
        }

        /* delay task */
        this.delayLoadTask.delay(60, this.doLoad, this, arguments);

    }

    ,doLoad: function(params) {
        if(Ext.isEmpty(this.api)) {
            return;
        }

        if(!isNaN(params)) {
            params = {id: params};
        }

        this.clear();

        if(Ext.isEmpty(params) || (Ext.isEmpty(params.id) && Ext.isEmpty(params.template_id))) {
            this.fireEvent('loaded', this);
            return;
        }

        this.loadedParams = params; //Ext.apply({}, params);
        this.api(params, this.onLoadData, this);
    }

    ,onLoadData: function(r, e) {
        var items = []
            ,params = Ext.valueFrom(this.loadedParams, {});

        //check if object was found (success = true)
        if(!r || (r.success !== true)) {
            this.update('<div class="x-preview-mask">' + L.RecordIdNotFound.replace('{id}', '#' + params.id) + '</div>');

        } else {
            var commonInfo = r.common
                ? r.common.data
                : Ext.valueFrom(r.data.systemProperties, {}).data;

            Ext.apply(params, commonInfo);

            this.removeAll(true);

            this.createMenu = r.menu;
            Ext.iterate(
                r.data
                ,function(k, v, o) {
                    var cls = Ext.valueFrom(v.class, k)
                        ,cl = Ext.util.Format.capitalize(cls.substr(0,1)) + cls.substr(1);
                    cl = 'CBObjectPlugin' + cl;

                    var c = Ext.create(
                        cl
                        ,{
                            params: params
                        }
                    );

                    c.createMenu = r.menu;

                    items.push(c);

                    if(!Ext.isDefined(v.data)) {
                        c.setVisible(false);
                    } else {
                        c.onLoadData(v);
                    }
                }
                ,this
            );

            if(!Ext.isEmpty(items)) {
                this.add(items);
            }

            /**
             * we make this check for title after all plugins have been added
             * because objectProperties plugin applies loaded data (including object name)
             * to the params
             */
            if(params &&
                // (CB.DB.templates.getType(params.template_id) !== 'task') &&
                (params.from !== 'window') &&
                !Ext.isEmpty(params.name)
            ){
                var data = Ext.copyTo(
                    {}
                    ,params
                    ,'id,pids,path,name,template_id,status,statusCls,cid,cdate_ago_text,uid,udate_ago_text'
                );

                var titleView = new CB.object.TitleView({
                    data: data
                    ,getContainerToolbarItems: function(){
                        return {};
                    }
                });

                this.insert(0, titleView);
            }

            this.doLayout(true, true);
        }

        this.fireEvent('loaded', this, params);
    }

    ,clear: function() {
        this.removeAll(true);
        delete this.loadedParams;
    }

    ,onFilesUploaded: function(pids) {
        if(!Ext.isEmpty(this.loadedParams)) {
            if(pids.indexOf(String(this.loadedParams.id)) >=0 ) {
                this.reload();
            }
        }
    }

    ,onObjectsAction: function(action, data, e) {
        if(this.loadedParams && (data.targetId == this.loadedParams.id)) {
            this.reload();
        }
    }

    ,reload: function() {
        this.doLoad(this.loadedParams);
    }

    /**
     * method to be overriden for returning needed buttons for container toolbar
     * @return object
     *         Example: rez = {
     *              tbar: {}
     *              ,menu: {
     *                   reload: {}
     *              }
     *          }
     */
    ,getContainerToolbarItems: function() {

    }
});
