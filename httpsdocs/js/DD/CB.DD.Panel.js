Ext.namespace('CB.DD');

/**
 * Plugin for drag and drop casebox objects over a panel
 *
 */

Ext.define('CB.DD.Panel', {
    extend: 'Ext.dd.DropZone'
    ,alias: 'plugin.CBDDPanel'

    ,ddGroup: 'CBO'
    ,selector: '.files-drop'
    /**
     * pass another ddGroup if needed
     * @param  json config
     * @return void
     */
    ,constructor: function(el, config){
        var idProperty = Ext.valueFrom(config.idProperty, this.idProperty);
        var defaultConfig = {

            dragZone: {

            }
            ,dropZone: {
                onNodeEnter: this.onNodeEnter
                ,onNodeOver: this.onNodeOver
                ,onNodeOut: this.onNodeOut
                ,onNodeDrop: this.onNodeDrop
            }
        };

        if(config) {
            Ext.apply(defaultConfig, config);

            if(config.dropZoneConfig) {
                Ext.apply(defaultConfig.dropZone, config.dropZoneConfig);
            }
        }
        Ext.apply(this, defaultConfig);

        this.callParent([el, defaultConfig]);
    }
    /**
     * @param  Ext.Panel
     * @return void
     */
    ,init: function(owner) {
        this.owner = owner;

        var cfg = {};
        if(!Ext.isDefined(this.enableDragDrop) &&
            !Ext.isDefined(this.enableDrag) &&
            !Ext.isDefined(this.enableDrop)
        ) {
            this.enableDragDrop = true;
        }

        cfg.enableDrag = this.enableDragDrop || this.enableDrag;
        cfg.enableDrop = this.enableDragDrop || this.enableDrop;

        if(cfg.enableDrag && cfg.enableDrop) {
            cfg.enableDragDrop = true;
        }

        Ext.apply(this, cfg);

        this.callParent(arguments);
    }

    ,getTargetFromEvent: function(e) {
        return e.getTarget(this.selector);
    }

    ,onNodeEnter: function(el, source, ev, data){
        this.owner.getEl().addCls('drop-target');
        // Ext.get(el).addCls('drop-target');
    }

    ,onNodeOver: function (el, source, ev, data){
        /* deny drop on:
            - node itself
            - direct parent of dragged node
        */
       var targetData = this.owner.params || {}
            ,targetId = Ext.valueFrom(targetData.nid, targetData.id);

        var rez = this.dropAllowed;
        if(Ext.isEmpty(targetId) ||
            !data ||
            Ext.isEmpty(data.records)
        ) {
            return this.dropNotAllowed;
        }

        if(isNaN(Ext.valueFrom(data.records[0].data.nid, data.records[0].data.id)) ||
            isNaN(targetId)
        ) {
            return this.dropNotAllowed;
        }

        var sourceData = Ext.isArray(data.records)
            ? data.records
            : [data.records];
        var i = 0;
        while ((i < sourceData.length) && (rez == this.dropAllowed))  {
            var id = Ext.valueFrom(sourceData[i].data.nid, sourceData[i].data.id);
            if( (targetId == id)
                || (targetId == sourceData[i].pid)
            ) {
                rez = this.dropNotAllowed;
            }
            i++;
        }

        return rez;
    }

    ,onNodeOut: function(el, source, ev, data){
        this.owner.getEl().removeCls('drop-target');
        // Ext.get(el).removeCls('drop-target');
    }

    ,onNodeDrop: function(el, source, e, data){
        return clog('dropped', arguments);

        if(Ext.isElement(el)) {
            if(this.onNodeOver(el, source, e, data) == this.dropAllowed){
                var targetRecord = this.view.getRecord(el);
                if(targetRecord) {
                    var d, sourceData = [];
                    for (var i = 0; i < data.records.length; i++) {
                        d = data.records[i].data;
                        sourceData.push({
                            id: d[this.idProperty]
                            ,name: d['name']
                            ,path: d['path']
                            ,template_id: d['template_id']
                        });
                    }

                    d = targetRecord.data;
                    var targetData = {
                        id: d[this.idProperty]
                        ,name: d['name']
                        ,path: d['path']
                        ,template_id: d['template_id']
                    };
                    App.DD.execute({
                        action: e
                        ,targetData: targetData
                        ,sourceData: sourceData
                    });

                }
            }
        } else { //drop over scroller area of the grid
            callback = this.scope
                ? this.onScrollerDragDrop.bind(this.scope)
                : this.onScrollerDragDrop;
            callback(el, source, e, data);
        }
        return false;
    }
});
