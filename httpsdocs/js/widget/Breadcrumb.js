Ext.namespace('CB');
Ext.define('CB.widget.Breadcrumb', {
    extend: 'Ext.view.View'

    ,alias: 'CB.Breadcrumb'

    ,xtype: 'CBBreadcrumb'

    ,border: false

    ,autoWidth: true

    ,initComponent: function(){
        var store = new Ext.data.JsonStore({
                proxy: {
                    type: 'memory'
                    ,reader: {
                        type: 'json'
                    }
                }
                ,fields: ['id', 'name', 'iconCls']
            })

            ,tpl = new Ext.XTemplate(
                '<div class="breadcrumb" role="navigation" style="right: auto">'
                    ,'<tpl for=".">'
                        ,'<div class="item" role="listitem">{[ (xindex < xcount) ? \'<span class="im-arr-r fr"></span>\': \'\']}{name}</div>'
                    ,'</tpl>'
                ,'</div>'
            );

        Ext.apply(this, {
            tpl: tpl
            ,itemSelector: '.item'
            ,store: store
            ,listeners: {
                scope: this
                ,resize: this.onViewResize
                ,itemclick: this.onItemClick
            }
        });

        this.callParent(arguments);
    }

    /**
     * update breadcrumb value
     * @param array pathArray
     */
    ,setValue: function(pathArray) {
        this.store.loadData(pathArray);
    }

    /**
     * handler for resize event
     * @param  object view
     * @param  int width
     * @param  int height
     * @param  int oldWidth
     * @param  int oldHeight
     * @param  object eOpts
     * @return void
     */
    ,onViewResize: function(view, width, height, oldWidth, oldHeight, eOpts) {
        var node
            ,nodesWidth = 0
            ,minNodeWidth = 50
            ,parentNodesWidth
            ,parentNodesMaxWidth
            ,widthDelta
            ,smallerNodesDelta = 0
            ,store = this.store
            ,lastRecord = store.last()
            ,lastNode = view.getNode(lastRecord);


        // get common items width
        store.each(
            function(r) {
                var node = view.getNode(r);
                nodesWidth += node.scrollWidth;
            }
            ,this
        );

        if(nodesWidth > width) {
            // exclude last item width and we get target width of parent nodes
            parentNodesMaxWidth = width - lastNode.scrollWidth;

            //get the limit width
            parentNodesWidthLimit = parentNodesMaxWidth / (store.getCount() -1);

            //real width of parnet nodes
            parentNodesWidth = nodesWidth - lastNode.scrollWidth;

            //iterate parent nodes an calculate delta for smaller nodes width than parentNodesWidthLimit
            if(parentNodesWidthLimit >= minNodeWidth) {
                store.each(
                    function(r) {
                        if(r != lastRecord) {
                            node = view.getNode(r);
                            if(node.scrollWidth < parentNodesWidthLimit) {
                                smallerNodesDelta += node.scrollWidth;
                            }
                        }
                    }
                    ,this
                );
            }

            //increase parentNodesWidthLimit with smallerNodesDelta
            parentNodesWidthLimit += smallerNodesDelta;

            //now iterate parent nodes an set style width
            store.each(
                function(r) {
                    if(r != lastRecord) {
                        node = view.getNode(r);
                        if(parentNodesWidthLimit < minNodeWidth) {
                            node.setAttribute('style', 'width: 0px; padding: 0');
                        } else {
                            if(node.scrollWidth > parentNodesWidthLimit) {
                                node.setAttribute('style', 'width: ' + parentNodesWidthLimit + 'px');
                            } else {
                                node.removeAttribute('style');
                            }
                        }
                    }
                }
                ,this
            );


        } else {//clear items widths
            store.each(
                function(r) {
                    node = view.getNode(r);
                    node.removeAttribute('style');
                }
                ,this
            );
        }
    }
}
);
