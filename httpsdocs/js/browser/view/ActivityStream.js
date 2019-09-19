Ext.namespace('CB.browser.view');

Ext.define('CB.browser.view.ActivityStream',{
    extend: 'CB.browser.view.Interface'

    ,xtype: 'CBBrowserViewActivityStream'

    ,border: false
    ,tbarCssClass: 'x-panel-white'

    ,initComponent: function(){

        var tpl = new Ext.XTemplate(
            '<div class="taC"><table class="activity-stream">'
            ,'<tpl for=".">'
            ,'<tpl if="lastAction">'
            ,'<tr class="as-record">'
            ,'    <td>'
            ,'      <div class="as-item">'
            ,'        <table class="action">'
            ,'          <tr>'
            ,'            <td class="action-icon">{[this.getTitleIcon(values)]}</td>'
            ,'            <td class="action-title">{[this.getTitle(values)]}</td>'
            ,'          </tr>'
            ,'          <tr>'
            ,'            <td class="action-text" colspan="2">'
            ,'               <table>'
            ,'                  <tr>'
            ,'                    <td>{[this.getContent(values)]}</td>'
            ,'                 </tr>'
            ,'               </table>'
            ,'            </td>'
            ,'          </tr>'
            ,'        </table>'
            ,'      </div>'
            ,'      <div class="action-comments" id="as-record-{nid}">'
            ,'      </div>'
            ,'    </td>'
            ,'</tr>'
            ,'</tpl>'
            ,'</tpl>'
            ,'</table>{[this.getNextButton(this, values)]}</div>'
            ,{
                getTitleIcon: function(r){
                    var uid = r.lastAction.uids[0]
                        ,us = CB.DB.usersStore
                        ,rez = '<img class="i40" src="' +
                            App.config.photoPath + uid + '.jpg?32=' +
                            us.getPhotoParam(uid)  + '" title="' +
                            us.getName(uid)
                            + '">';
                   return rez;
                }

                ,getTitle: function(r){
                    var rez = '<div class="action-title-text">'
                        ,la = r.lastAction
                        ,us = CB.DB.usersStore
                        ,users = [];
                    for (var i = 0; i < la.uids.length; i++) {
                        users.push(' <b>' + us.getName(la.uids[i]) + '</b> ');
                    }

                    switch(users.length) {
                        case 0:
                            break;
                        case 1:
                            rez += users[0];

                            break;
                        case 2:
                            rez += users[0] + L.and + users[1];

                            break;

                        case 3:
                            rez += users[0] + ', ' + users[1] + L.and + users[2];

                            break;

                        default:
                            rez += users[0] + ', ' + users[1] + L.and + ' ' + Ext.valueFrom(L.NNOthers, '{count} others').replace('{count}', users.length -1);
                    }

                    switch(la.type) {
                        case 'copy':
                            rez += ' ' + Ext.valueFrom(L['copied'], la.type);
                            break;

                        case 'comment':
                            rez += ' ' + Ext.valueFrom(L[la.type + 'ed'], la.type);
                            break;

                        default:
                            rez += ' ' + Ext.valueFrom(L[la.type + 'd'], la.type);
                    }

                    rez += ' <a class="click open-obj" nid="' + r.nid + '">' + r.name + '</a></div>';

                    rez += ' <div class="as-ago-time">' + la.agoText + '</div>';

                    return rez;
                }

                ,getContent: function(r){
                   return r['diff'];
                }

                ,getNextButton: Ext.bind(
                    function() {
                        var rez = '<div class="asNext click" style="display:none"><span>' +
                            L.Next +
                            ' </span><span class="dIB i16 i-arrow-right"></span></div>';

                        return rez;
                    }
                    ,this
                )

            }
        );

        this.dataView = new Ext.DataView({
            tpl: tpl
            ,store: this.store
            ,deferInitialRefresh: false
            ,itemSelector: 'tr.as-record' // 'div.as-item'
            // ,overItemCls:'as-record-over'
            ,focusCls: ''
            ,scrollable: true
            ,listeners: {
                scope: this
                ,selectionchange: this.onSelectionChange
                ,beforecontainermousedown: this.onBeforeContainerMouseDown
            }
        });

        Ext.apply(this, {
            title: L.ActivityStream
            ,viewName: 'activityStream'
            ,header: false
            ,layout: 'fit'
            ,style: 'background-color: #e9eaed'
            ,items: [
                this.dataView
            ]
            ,listeners: {
                scope: this
                ,activate: this.onActivate
            }
        });

        this.store.on(
            'load'
            ,this.onStoreLoad
            ,this
            ,{
                defer: 300
            }
        );

        this.callParent(arguments);
    }

    ,updateToolbarButtons: function() {
        this.refOwner.fireEvent(
            'settoolbaritems'
            ,[
                'create'
                ,'upload'
                ,'download'
                ,'-'
                ,'edit'
                ,'delete'
                ,'->'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }

    ,onSelectionChange: function(view, selected, eOpts) {
        var recs = [];

        for (var i = 0; i < selected.length; i++) {
            recs.push(selected[i].data);
        }

        if(!Ext.isEmpty(recs)) {
            this.fireEvent('selectionchange', recs);
        }
    }

    ,onStoreLoad: function(store, records, successful, eOpts) {
        var visible = this.getEl().isVisible(true);

        if (visible) {
            this.addCommentPlugins();
        }
    }

    ,addCommentPlugins: function() {
       var ready = (this.store.getCount() === 0);

        this.store.each(
            function(r) {
                var id = r.get('nid')
                    ,recEl = Ext.get('as-record-' + id);

                ready = ready || !Ext.isEmpty(recEl);

                if(r.data.lastAction && !Ext.isEmpty(recEl)) {
                    var c = Ext.create(
                        'CBObjectPluginComments'
                        ,{
                            params: {id: id}
                            ,header: false
                            ,renderTo: 'as-record-' + id
                            ,showAddLabel: 'label'
                            ,commentFieldConfig: {
                                xtype: 'CBFieldCommentLight'
                            }
                        }
                    );
                    c.onLoadData(r.data.comments);
                }
            }
            ,this
        );

        if(ready) {

            var el = this.dataView.getEl().down('.asNext')
                ,s = this.store
                ,p = s.proxy
                ,start = Ext.valueFrom(p.extraParams.start, 0)
                ,total = p.reader.rawData.total
                ,rez = '';

            if (el && (total > 0) && (start + s.getCount() < total)) {
                el.setStyle('display', 'inherit');
            }

            this.dataView.scrollTo(0, 0, false);
        } else {
            Ext.defer(this.addCommentPlugins, 200, this);
        }
    }

    ,onBeforeContainerMouseDown: function(dv, e, eOpts) {
        var el = e.getTarget('.asNext');

        if(el) {
            e.stopEvent();
            this.fireEvent(
                'changeparams'
                ,{
                    start: Ext.valueFrom(this.store.proxy.extraParams.start, 0) + this.store.getCount()
                }
            );
        }
    }

    /**
     * called from view container when reload is clicked
     * @return void
     */
    ,onContainerReloadClick: function(params) {
        delete params.start;
        delete params.page;
    }

    ,onActivate: function() {
        this.fireEvent(
            'settoolbaritems'
            ,[
                ,'->'
                ,'reload'
                ,'apps'
                ,'-'
                ,'more'
            ]
        );
    }

});
