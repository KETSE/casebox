Ext.namespace('CB');

// ----------------------------------------------------------- add user form
Ext.define('CB.AddUserForm', {
    extend: 'Ext.Window'

    ,layout: 'fit'
    ,autoWidth: true
    ,title: L.AddUser
    ,iconCls: 'icon-user-gray'
    ,data: {}

    ,initComponent: function(){
        var recs = CB.DB.roles.queryBy(
            function(r){
                return ( (r.get('id') !=3) &&
                    (r.get('id') !=1) &&
                    (App.loginData.manage || (r.get('id') !=2))
                );
            }
        );

        var data = [];

        recs.each(function(r){data.push(r.data);}, this);

        this.rolesStore = new Ext.data.JsonStore({
            autoLoad: true
            ,autoDestroy: true
            ,model: 'Generic2'
            ,proxy: {
                type: 'memory'
            }
            ,data: data
        });

        data = [];

        CB.DB.groupsStore.each(
            function(r){
                if(parseInt(r.get('system'), 10) === 0) {
                    data.push(r.data);
                }
            }
            ,this
        );

        this.groupsStore = new Ext.data.JsonStore({
            autoLoad: true
            ,model: 'Group'
            ,proxy: {
                type: 'memory'
            }
            ,sortInfo: {
                field: 'title'
                ,direction: 'ASC'
            }
            ,data: data
        });

        items = [{
                xtype: 'textfield'
                ,allowBlank: false
                ,fieldLabel: L.Username
                ,name: 'name'
            },{
                xtype: 'textfield'
                ,allowBlank: true
                ,fieldLabel: L.FirstName
                ,name: 'first_name'
            },{
                xtype: 'textfield'
                ,allowBlank: true
                ,fieldLabel: L.LastName
                ,name: 'last_name'
            },{
                xtype: 'textfield'
                ,allowBlank: false
                ,fieldLabel: L.Email
                ,name: 'email'
                ,vtype: 'email'
            }

            ,{
                xtype: 'checkbox'
                ,fieldLabel: L.SendEmailInvite
                ,inputValue: 1
                ,name: 'send_invite'
            }
            /*,{
                xtype: 'textfield'
                ,allowBlank: false
                ,fieldLabel: L.Password
                ,inputType: 'password'
                ,name: 'password'
            },{ xtype: 'textfield'
                ,allowBlank: false
                ,fieldLabel: L.PasswordConfirmation
                ,inputType: 'password'
                ,name: 'confirm_password'
            },/**/

            ,{
                xtype: 'combo'
                ,fieldLabel: L.Group
                ,editable: false
                ,name: 'group_id'
                ,hiddenName: 'group_id'
                ,store: this.groupsStore
                ,valueField: 'id'
                ,displayField: 'title'
                ,triggerAction: 'all'
                ,value: null
                ,queryMode: 'local'
            },{
                xtype: 'label'
                ,name: 'E'
                ,hideLabel: true
                ,cls:'cR'
                ,text: ''
            }];

        Ext.apply(this, {
            buttons:[
                {   text: L.Save
                    ,iconCls: 'icon-save'
                    ,disabled: true
                    ,handler: this.saveData
                    ,scope: this
                },{
                    text: Ext.MessageBox.buttonText.cancel
                    ,iconCls: 'icon-cancel'
                    ,handler: function(b, e){
                        this.destroy();
                    }
                    ,scope: this
                }
            ]
            ,items: [{
                xtype: 'fieldset'
                ,border: false
                ,autoHeight: true
                ,autoWidth: true
                ,labelWidth: 150
                ,style: 'padding-top: 10px'
                ,defaults: {
                    width: 250
                    ,bubbleEvents: ['change']
                }
                ,items: items
            }
            ]
            ,listeners: {
                scope: this
                ,change: function(e){
                    this.setDirty(true);
                }
                ,show: function(){
                    this.center();
                }
            }
        });
        this.callParent(arguments);

        this.on('show', App.focusFirstField, this);
        this.on('close', function(){CB.DB.roles.clearFilter();}, this);
    }

    ,setDirty: function(value){
        this._isDirty = value;
        required = true;
        var a = this.query('[isFormField=true]');
        Ext.each(a, function(i){
            if(!i.allowBlank) {
                required = required && !Ext.isEmpty(i.getValue());
                return required;
            }
        }, this);

        msg = required ? '' : L.EmptyRequiredFields;
        this.down('[name="E"]').setText(msg);

        this.dockedItems.getAt(1).items.getAt(0).setDisabled(!value || !required);
    }

    ,saveData: function(){
        var params = {};
        var a = this.query('[isFormField=true]');

        Ext.each(a, function(i){params[i.name] = i.getValue();}, this);

        if(this.config.data.callback) {
            this.config.data.callback(params, this.config.ownerCt);
        }
    }
});

// ----------------------------------------------------------- end of add user form
Ext.define('CB.UsersGroupsTree', {
    extend: 'Ext.tree.TreePanel'
    ,scrollable: true
    ,containerScroll: true
    ,rootVisible: false
    ,animate: false
    ,border: false
    ,enableDD: true
    ,tbarCssClass: 'x-panel-white'

    ,initComponent: function(){
        this.actions = {
            addUser: new Ext.Action({
                text: L.User
                ,iconCls: 'icon-user'
                ,handler: this.onAddUserClick
                ,scope: this
            })
            ,addGroup: new Ext.Action({
                text: L.AddGroup
                ,iconCls: 'icon-users'
                ,handler: this.onAddGroupClick
                ,scope: this
            })
            ,del: new Ext.Action({
                text: L.Delete
                ,iconCls: 'im-trash'
                ,scale: 'medium'
                ,disabled: true
                ,handler: this.delNode
                ,scope: this
            })
            ,remove: new Ext.Action({
                text: L.Remove
                ,iconCls: 'im-cancel'
                ,scale: 'medium'
                ,disabled: true
                ,handler: this.deassociateNode
                ,scope: this
            })
            ,reload: new Ext.Action({
                iconCls: 'im-refresh'
                ,scale: 'medium'
                ,qtip: L.Reload
                ,scope:this
                ,handler: function(){
                    this.store.reload({node: this.getRootNode()});
                }
            })

        };

        this.editor = new Ext.Editor({
            field: {
                xtype: 'textfield'
            }
            ,allowBlank: false
            ,blankText: L.NameRequired
            ,selectOnFocus: true
            ,ignoreNoChange: true
        });

        this.editor.on('beforestartedit', this.onBeforeStartEdit, this);
        this.editor.on('startedit', this.onStartEdit, this);
        this.editor.on('beforecomplete', this.onBeforeEditComplete, this);

        Ext.apply(this, {
            cls: 'x-panel-white'
            ,store: Ext.create('Ext.data.TreeStore', {
                root:  {
                    expanded: false
                    ,expandable: true
                    ,iconCls: 'icon-home'
                    ,leaf: false
                    ,nid: 'root'
                    ,text: 'root'
                }
                ,proxy: {
                    type: 'direct'
                    ,directFn: CB_UsersGroups.getChildren
                    ,paramsAsHash: true
                    ,extraParams: {
                        path: '/'
                    }
                }
                ,listeners: {
                    scope: this
                    ,beforeload: function(store, record, eOpts) {
                        store.proxy.extraParams.path = record.config.node.getPath('nid');
                    }
                    ,load: function(o, n, r) {
                        if(!Ext.isEmpty(this.lastPath)) {
                            this.selectPath(this.lastPath, 'nid', '/');
                        }

                        return;
                        if(n.data.kind > 1) {
                            n.sort(this.sortTree);
                        }
                    }
                }
            })

            ,tbar: [
                {
                    text: L.Add
                    ,iconCls: 'im-create'
                    ,scale: 'medium'
                    ,menu: [
                        this.actions.addUser
                        ,this.actions.addGroup
                    ]
                }
                ,this.actions.del
                ,this.actions.remove
                ,'-'
                ,this.actions.reload
            ]
            ,listeners:{
                scope: this
                ,nodedragover: function(o){
                    if( (o.point != 'append')
                        || (o.target == o.dropNode.parentNode)
                        || (o.target.getDepth() != 1)
                        || (o.target.data.nid < 1)
                        ){
                        o.cancel = true;
                        return;
                    }
                }
                ,beforenodedrop: function(o){
                    o.cancel = true;
                    o.dropStatus = true;
                }

                ,dragdrop: {scope: this, fn: function( tree, node, dd, e ){
                        this.sourceNode = dd.dragOverData.dropNode;
                        this.targetNode = dd.dragOverData.target;
                        CB_UsersGroups.associate(
                            this.sourceNode.data.nid
                            ,this.targetNode.data.nid
                            ,this.processAssociate
                            ,this
                        );
                    }
                }
                ,beforeitemappend: function(parent, n){
                    var text = Ext.valueFrom(n.data.title, n.data.name);
                    n.data.title = text;

                    if( parent.getDepth() == 1 ){
                        text += ' <span class="cG">(id:' + n.data.nid + ')</span>';
                        if(n.data.enabled != 1){
                            text += ' <span class="cG">' + L.Disabled + '</span>';
                        }
                        n.data.iconCls = 'icon-user-' + Ext.valueFrom(n.data.sex, '');
                    }
                    if(n.data.type == 1){
                        n.data.cls = n.data.cls + ' fwB';
                        this.getView().addItemCls(n, 'fwB');
                    }

                    if(n.data.cid == App.loginData.id){
                        n.data.cls = n.data.cls + ' cDB';
                        this.getView().addItemCls(n, 'fwB');
                    }

                    n.set('text', text);
                }
                ,remove: this.updateChildrenCount
                ,append: this.updateChildrenCount
            }
            ,selType: 'treemodel'
            ,selModel: {
                listeners: {
                    scope: this
                    ,selectionchange: function(sm, selection){
                        if(Ext.isEmpty(selection)){
                            this.actions.del.setDisabled(true);
                            this.actions.remove.setDisabled(true);
                        }else{
                            this.actions.del.setDisabled(selection[0].data.system == 1);
                            this.actions.remove.setDisabled( (selection[0].getDepth() <2) || (selection[0].parentNode.data.nid <1 ) );
                        }
                    }
                }
            }
            ,keys: [{
                key: Ext.event.Event.F2
                ,alt: false
                ,ctrl: false
                ,stopEvent: true
                ,fn: this.onRenameClick
                ,scope: this
            }]
        });
        this.callParent(arguments);

        this.enableBubble(['verify']);
    }
    ,onRenameClick: function(b, e){
        var n = this.getSelectionModel().getSelection()[0];
        if(!n) return;
        this.editor.editNode = n;
        this.editor.startEdit(n.ui.textNode);
    }
    ,onBeforeStartEdit: function(editor, boundEl, value){
        var n = this.getSelectionModel().getSelection()[0];
        if( (n.data.type != 1) || (n.data.nid < 1) ) return false;
    }
    ,onStartEdit: function(boundEl, value){
        var n = this.getSelectionModel().getSelection()[0];
        if(n.data.type != 1) return false;
        value = n.data.title;
        this.editor.setValue(value);
    }
    ,onBeforeEditComplete: function(editor, newVal, oldVal) {
        var n = this.getSelectionModel().getSelection()[0];
        oldVal = n.data.title;

        if(newVal === oldVal) {
            return;
        }

        n = editor.editNode;
        editor.cancelEdit();
        this.getEl().mask(L.Processing, 'x-mask-loading');
        CB_UsersGroups.renameGroup({id: n.data.nid, title: newVal}, this.processRenameGroup, this);
        return false;
    }
    ,processRenameGroup: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) return;
        this.editor.editNode.data.title = r.title;
        this.updateChildrenCount(this, this.editor.editNode);
    }

    ,onAddUserClick: function(b, e){
        this.addUserForm = new CB.AddUserForm({
            modal: true
            ,ownerCt: this
            ,data: {
                callback: this.addUser
            }
        });

        this.addUserForm.show();
    }

    ,addUser: function(params, t){
        CB_UsersGroups.addUser(params, t.processAddUser, t);
    }

    ,processAddUser: function(r, e){
        if(r.success !== true) {
            if(!Ext.isEmpty(r.msg)) {
                Ext.Msg.alert(L.Error, r.msg);
            }

            return false;
        } else if(this.addUserForm) {
            this.addUserForm.destroy();
            delete this.addUserForm;
        }

        this.lastPath = '/root/'+r.data.group_id+'/'+r.data.nid;

        this.store.clearFilter();

        this.ownerCt.container.component.searchField.clear();
        this.store.reload({node: this.getRootNode()});

        App.mainViewPort.fireEvent('useradded', r.data);
    }

    ,onAddGroupClick: function(b, e){
        Ext.Msg.prompt(
            L.Group
            ,L.Name
            ,function(b, text){
                if((b == 'ok') && !Ext.isEmpty(text)){
                    var rec = {
                        name: text
                        ,title: text
                    };

                    CB.DB.groupsStore.beginUpdate();
                    CB.DB.groupsStore.add(rec);
                    CB.DB.groupsStore.endUpdate();
                    this.store.reload({node: this.getRootNode()});
                }
            }
            ,this
        );
    }

    ,sortTree: function(n1, n2){
        return (n1.text < n2.text) ? -1 : 1;
    }

    ,deassociateNode: function(){
        var n = this.getSelectionModel().getSelection()[0];
        if(!n) return;
        Ext.Msg.confirm(
            L.ExtractUser
            ,L.ExtractUserMessage.replace('{user}', n.data.name).replace('{group}', n.parentNode.data.text)
            ,function(b){
                if(b == 'yes') {
                    CB_UsersGroups.deassociate(
                        n.data.nid
                        ,n.parentNode.data.nid
                        ,this.processDeassociate
                        ,this
                    );
                }
            }
            ,this
        );
    }

    ,processDeassociate: function(r, e){
        if(r.success !== true) {
            if(r.verify) {
                this.fireEvent('verify', this);
            }
            return false;
        }
        var n = this.getSelectionModel().getSelection()[0];
        var attr = n.data;

        attr.iconCls = 'icon-user-gray';
        this.store.remove(n);

        if(r.outOfGroup){
            p = this.getRootNode().findChild( 'nid', '-1');
            if(p.loaded){
                p.appendChild(attr);
                p.sort(this.sortTree);
            } else {
                p.data.users++;
                p.set(
                    'text'
                    ,p.data.text.split('<')[0] + ' <span class="cG">(' + p.data.users + ')</span>'
                );
            }
        }
    }

    ,processAssociate: function(r, e){
        if(r.success !== true) {
            if(r.verify) {
                this.fireEvent('verify', this);
            }
            return false;
        }
        var attr = Ext.apply({}, this.sourceNode.data);

        if(this.targetNode.loaded){
            attr.id = Ext.id();
            this.targetNode.appendChild(attr);
            this.targetNode.sort(this.sortTree);
        }else{
            this.targetNode.data.users++;
            this.targetNode.set(
                'text'
                ,this.targetNode.data.text.split('<')[0] + ' <span class="cG">(' + this.targetNode.data.users + ')</span>'
            );
        }
        if(this.sourceNode.parentNode.data.nid == '-1') this.sourceNode.remove(true);
    }

    ,delNode: function(){
        n = this.getSelectionModel().getSelection()[0];
        if(!n) return;
        switch(n.getDepth()){
        case 2:
            this.deletedUserData = n.data;
            Ext.MessageBox.confirm(L.Confirmation, L.DeleteUser + ' "'+n.data.text+'"?',
            function(btn, text){
                if(btn == 'yes'){
                    n = this.getSelectionModel().getSelection()[0];
                    CB_UsersGroups.deleteUser(n.data.nid, this.processDelNode, this);
                }
            }
            , this);
            break;
        case 1:
            Ext.MessageBox.confirm(L.Confirmation, L.DeleteGroupConfirmationMessage + ' "'+n.data.text+'"?',
            function(btn, text){
                if(btn == 'yes') CB_Security.destroyUserGroup(n.data.nid, this.processDestroyUserGroup, this);
            }
            , this);
            break;
        }
    }

    ,processDestroyUserGroup: function(r, e){
        if(r.success !== true) {
            if(r.verify) {
                this.fireEvent('verify', this);
            }
            return false;
        }
        this.processDelNode(r, e);
        CB.DB.groupsStore.reload();
    }

    ,processDelNode: function(r, e){
        if(r.success !== true) {
            if(r.verify) {
                this.fireEvent('verify', this);
            }
            return false;
        }
        var nid = this.getSelectionModel().getSelection()[0].data.nid,
            deleteNodes = [];
        this.getRootNode().cascadeBy({
            before: function(n){
                if(n.data.nid == nid) {
                    deleteNodes.push(n);
                }
            }
            ,scope: this
        });

        for(i = 0; i< deleteNodes.length; i++) deleteNodes[i].remove(true);
        if(this.deletedUserData){
            App.mainViewPort.fireEvent('userdeleted', this.deletedUserData);
            delete this.deletedUserData;
        }
    }
    ,updateChildrenCount: function( t, p ){
        if(Ext.isEmpty(p)) return;
        if(Ext.isEmpty(p.childNodes)){
            if(!Ext.isEmpty(p.data)) {
                p.set(
                    'text'
                    ,p.data.title
                );
            }
            return;
        }
        p.data.users = p.childNodes.length;
        p.set(
            'text'
            ,p.data.title + ' <span class="cG">(' + p.data.users + ')</span>'
        );
    }
    ,filter: function(text, property){
        var store = this.store;

        store.clearFilter();
        if(Ext.isEmpty(text)){
            return;
        }

        text = text.toLowerCase();

        store.filterBy(
            function(record, id){
                return ( (record.data.depth < 2) ||
                    (record.data[property].toLowerCase().indexOf(text) >= 0)
                );
            }
            ,this
        );
    }

    ,clearFilter: function(){
        this.store.clearFilter();
    }
});
// ----------------------------------------------------------- edit user form
Ext.define('CB.UserEditWindow', {
    extend: 'Ext.Window'

    ,iconCls: 'icon-user'
    ,title: L.User
    ,modal: true
    ,closeAction: 'destroy'
    ,y: 150
    ,autoWidth: true
    ,autoHeight: true
    ,layout: 'fit'
    ,autoShow: true

    ,initComponent: function() {

        this.data = this.config.data;

        this.profileForm = new CB.ProfileForm({
            header: false
            ,data: this.data
            ,autoHeight: true
            ,autoWidth: true
            ,border: false
            ,listeners: {
                scope: this
                ,savesuccess: function(f, a){
                    this.fireEvent('savesuccess', f, a);
                    App.fireEvent('userprofileupdated', f.data, a);
                    this.destroy();
                }
                ,cancel: this.destroy
            }
        });

        Ext.apply(this, {
            items: this.profileForm
            ,listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });

        this.callParent(arguments);

        this.enableBubble(['verify']);
    }
    ,onAfterRender: function(){
        this.getEl().mask(L.LoadingData + ' ...');
        CB_User.getProfileData(this.data.id, this.onLoadProfileData, this);
    }
    ,onLoadProfileData: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) {
            if(r.verify) {
                this.fireEvent('verify', this);
            }
            this.destroy();
            return;
        }
        this.profileForm.loadData(r);
    }
});

// ----------------------------------------------------------- form
Ext.define('CB.UsersGroupsForm', {
    extend: 'Ext.form.FormPanel'

    ,border: false
    ,disabled: true
    ,fileUpload: true
    ,data: {}

    ,initComponent: function(){

        var bulletRenderer = function(v, m){
            m.css = 'taC';
            return (v == 1)
                ? '<span class="icon-padding icon-tick"></span>'
                : '';
        };

        this.actions = {
            disableTSV: new Ext.Action({
                text: L.Disable + ' ' + L.TSV
                ,scope: this
                ,disabled: true
                ,handler: this.onDisableTSVClick
            })
            ,enableUser: new Ext.Action({
                text: L.EnableUser
                ,scope: this
                ,handler: this.onUserToggleEnableClick
            })
            ,disableUser: new Ext.Action({
                text: L.DisableUser
                ,scope: this
                ,handler: this.onUserToggleEnableClick
            })
        };

        this.userInfo = new Ext.DataView({
            tpl: ['<img class="fl user-photo-field click icon-user32-{sex}" src="/' + App.config.coreName + '/photo/{id}.png?32={[ CB.DB.usersStore.getPhotoParam(values.id) ]}">'
                ,'<span class="fwB click">{title}</span>{[ (values.enabled != 1) ? \' - \' + L.Disabled : \'\' ]}<br />'
                ,'<span class="cG">'+L.User+':</span> {name}, <span class="cG">'+L.lastAction+':</span> '
                  ,'{[ Ext.isEmpty(values.last_action_time) ? "" : values.last_action_time ]}<br />'
                ,'<span class="cG">'+L.addedByUser+':</span> {owner}, {cdate}<br />'
                ,'<span class="cG">'+L.TSV+':</span> {tsv}<br />'
            ]
            ,itemSelector:'.none'
            ,autoHeight: true
            ,listeners:{
                scope: this
                ,containerclick: this.onUserInfoContainerCLick
            }
        });

        Ext.apply(this, {
            layout: 'border'
            ,api: {submit: CB_User.uploadPhoto }
            ,border: false
            ,cls: 'x-panel-white'
            ,tbar:[
                {
                    text: L.Save
                    ,iconCls: 'im-save'
                    ,scale: 'medium'
                    ,disabled: true
                    ,handler: this.saveData
                    ,scope: this
                },{
                    text: Ext.MessageBox.buttonText.cancel
                    ,iconCls: 'im-cancel'
                    ,scale: 'medium'
                    ,disabled: true
                    ,handler: function(b, e){
                        e.stopPropagation();
                        this.loadData();
                    }
                    ,scope: this
                },{xtype: 'tbseparator', hidden: true}
                ,{
                    text: L.Edit
                    ,iconCls: 'im-edit-obj'
                    ,scale: 'medium'
                    ,handler: this.onEditUserDataClick
                    ,scope: this
                    ,hidden: true
                },{xtype: 'tbseparator', hidden: true}
                ,{
                    text: L.Options
                    ,iconCls:'im-apps'
                    ,scale: 'medium'
                    ,hidden: true
                    ,menu: [
                        {text: L.SendResetPassMail, iconCls: 'icon-key', handler: this.onSendResetPassMailClick, scope: this}
                        ,'-'
                        ,{text: L.ChangeUsername, iconCls: 'icon-pencil', handler: this.onEditUsernameClick, scope: this}
                        ,'-'
                        ,this.actions.disableTSV
                        ,'-'
                        ,this.actions.enableUser
                        ,this.actions.disableUser
                    ]
                }
            ]
            ,items: [{
                    region: 'north'
                    ,height: 75
                    ,bodyStyle: 'padding: 10px'
                    ,border: false
                    ,items: [{
                        xtype: 'filefield'
                        ,name: 'photo'
                        ,cls: 'fl'
                        ,style: 'position:absolute;width:1px;height:1px;opacity:0;top:-100px'
                        ,buttonOnly: true
                        ,allowBlank: false
                        ,clearOnSubmit: false
                        ,listeners:{
                            scope: this
                            ,afterrender: function(c){
                                c.button.fileInputEl.on('change', this.onPhotoChanged, this);
                            }
                        }
                    }, this.userInfo ]
                },{
                region: 'center'
                ,border: false
                ,scrollable: true
                ,bodyStyle: 'padding: 0 20px'
                ,items: [{
                        border: false
                        ,anchor: '100%'
                        ,minHeight: 100
                        ,autoHeight: true
                        ,xtype: 'grid'
                        ,style: 'margin-top: 15px'
                        ,selType: 'cellmodel'
                        ,store: new Ext.data.JsonStore({
                            autoDestroy: true
                            ,model: 'Facet'
                        })

                        ,columns: [
                            {
                                header: L.Groups
                                ,width: 150
                                ,dataIndex: 'name'
                            }
                            ,{
                                header: L.Active
                                ,dataIndex: 'active'
                                ,renderer: bulletRenderer
                            }
                        ]
                        ,viewConfig: {
                            forceFit: true
                            ,stripeRows: false
                            ,markDirty: false
                            ,getRowClass: function(r, index) {
                                return (r.get('active') != 1) ? '' : 'fwB';
                            }
                        }
                        ,listeners:{
                            scope: this
                            ,celldblclick: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
                                switch(cmp.headerCt.columnManager.columns[cellIndex].dataIndex){
                                    case 'active':
                                        record.set('active', (record.get('active') == 1) ? null : 1);
                                        break;
                                    default: return;
                                }
                                this.fireEvent('change');
                            }
                        }
                    }
                ]
            },{
                region: 'south'
                ,height: 300
                ,split: true
                ,collapseMode: 'mini'
                ,collapsed: true
                ,tbar: [{xtype: 'label', cls:'fwB', text: L.ChangeLog + ': '}]
            }
            ]
            ,listeners: {
                scope: this
                ,change: function(e){
                    this.setDirty(true);
                }
                ,show: function() {
                    var f = function(){
                        a = this.down('[isFormField=true]');
                        a.focus();
                    };

                    Ext.Function.defer(f, 500, this);
                }
            }
        });

        this.callParent(arguments);

        this.grid = this.down('grid');
    }
    ,setDirty: function(value){
        this._isDirty = (value !== false);

        var ttb = this.dockedItems.getAt(0);
        ttb.items.getAt(0).setDisabled(!this._isDirty);
        ttb.items.getAt(1).setDisabled(!this._isDirty);
    }
    //------------------------------------------------------------------------------------------------------------------------------------------------
    ,loadData: function(id){
        if(!Ext.isEmpty(id)) {
            this.data = {id: id};
        }
        this.getEl().mask(L.LoadingData + ' ...');
        CB_UsersGroups.getAccessData(this.data.id, this.processLoadedData, this);
    }

    ,processLoadedData: function(response, e){
        if(response.success !== true) {
            if(response.verify) {
                this.fireEvent('verify', this);
            }
        } else {
            this.data.title = Ext.valueFrom(response.data.title, response.data.name);

            response.data.title = this.data.title;
            this.data.template_id = response.data.template_id;
            this.userInfo.data = response.data;
            this.userInfo.update(response.data);

            this.grid.setDisabled(response.data.id == App.loginData.id);//disable editing access for self

            accessData = [];
            CB.DB.groupsStore.each( function(r){
                if(parseInt(r.get('system'), 10) === 0) {
                    accessData.push({
                        id: r.get('id')
                        ,'name': r.get('title')
                        ,'active': (response.data.groups.indexOf(String(r.get('id') ) ) >=0 ) ? 1: 0
                    });
                }
            }, this);

            this.grid.getStore().loadData(accessData, false);

            this.canEditUserData = (
                App.loginData.admin ||
                (response.data.cid == App.loginData.id) ||
                (response.data.id == App.loginData.id)
            );
            var ttb = this.dockedItems.getAt(0)
                ,eb = ttb.down('[iconCls="im-edit-obj"]')
                ,idx = ttb.items.indexOf(eb)
                ,enabled = (response.data.enabled == 1);
            eb.setVisible(this.canEditUserData); // edit button
            ttb.items.getAt(idx -1).setVisible(this.canEditUserData);// divider for edit button

            var visible = (this.canEditUserData || (response.data.id == App.loginData.id));
            ttb.items.getAt(idx + 1).setVisible(visible); //divider for options button
            ttb.items.getAt(idx + 2).setVisible(visible); // options button
            this.updatePhoto(response.data.photo);
            this.setDisabled(false);

            this.actions.disableTSV.setDisabled(!this.canEditUserData || (response.data.tsv == 'none'));
            this.actions.enableUser.setHidden(enabled);
            this.actions.disableUser.setHidden(!enabled);

            this.fireEvent('loaded', this.data);
        }
        this.getEl().unmask();
        this.setDirty(false);
    }

    ,saveData: function(){
        this.fireEvent('beforesave');
        this.getEl().mask(L.SavingChanges + ' ...');
        params = { groups: [] };
        this.grid.getStore().each(
            function(r){
                if(r.get('active') == 1) {
                    params.groups.push(r.get('id'));
                }
            },
            this
        );
        params.id = this.data.id;
        CB_UsersGroups.saveAccessData(
            params,
            function(r, e){
                if(r.success !== true) {
                    if(r.verify) {
                        this.fireEvent('verify', this);
                    }
                    return false;
                }
                this.setDirty(false);
                this.getEl().unmask();
                this.fireEvent('save');
            },
            this
        );
    }

    ,onUserInfoContainerCLick: function(cmp, e, eOpts){
        if(e) {
            var target = e.getTarget();
            if(target.localName == "img") {
                var el = this.down('[name="photo"]').button.fileInputEl;
                el.dom.click();
            } else if(target.classList.contains('click')) {
                this.onEditUserDataClick();
            }
        }
    }

    ,onEditUserDataClick: function(cmp, e, eOpts){
        this.fireEvent('edit');
    }

    ,onPhotoChanged: function(ev, el, o){
        if(!this.getForm().isValid()) {
            return;
        }
        this.getForm().submit({
            clientValidation: true
            ,params: {
                    id: this.data.id
                }
            ,scope: this
            ,success: function(form, action) { this.updatePhoto(action.result.photo); }
                ,failure: App.formSubmitFailure
        });
    }

    ,updatePhoto: function(name){
        if(Ext.isEmpty(name)) return;
            del = this.userInfo.getEl().query('img.user-photo-field')[0];
            del.src = '/' + App.config.coreName + '/photo/' + name;
    }

    ,onEditUsernameClick: function(){
        Ext.Msg.prompt(L.ChangeUsername, L.ChangeUsernameMessage, function(btn, text){
            if (btn == 'ok'){
                if(Ext.isEmpty(text)) return Ext.Msg.alert(L.Error, L.UsernameCannotBeEmpty);
                r = /^[a-z0-9\._]+$/i;
                if(Ext.isEmpty(r.exec(text))) return Ext.Msg.alert(L.Error, L.UsernameInvalid);
                CB_UsersGroups.renameUser(
                    {id: this.data.id, name: text},
                    function(r, e){
                        if(r.success !== true) {
                            if(r.verify) {
                                this.fireEvent('verify', this);
                            } else {
                                return Ext.Msg.alert(L.Error, Ext.valueFrom(e.msg, L.ErrorOccured) );
                            }
                        }
                        this.data.name = r.name;
                        this.userInfo.data.name = r.name;
                        this.userInfo.update(this.userInfo.data);
                    }, this);
            }
        }, this, false, this.data.name);
    }

    ,onSendResetPassMailClick: function() {
        CB_UsersGroups.sendResetPassMail(
            this.data.id
            ,function(r, e) {
                Ext.Msg.alert(
                    L.Info
                    ,r.success ? L.EmailSent: L.ErrorOccured
                );
            }
            ,this
        );
    }

    // ,onEditUserPasswordClick: function(){
    //     w = new CB.ChangePasswordWindow({data: this.data});
    //     w.show();
    // }

    ,onDisableTSVClick: function(){
        Ext.Msg.confirm(
            L.Disable + ' ' + L.TSV
            ,L.DisableTSVConfirmation
            ,function(b){
                if(b == 'yes') {
                    CB_UsersGroups.disableTSV(
                        this.data.id
                        ,this.processDisableTSV
                        ,this
                    );
                }
            }
            ,this
        );
    }
    ,processDisableTSV: function(r, e) {
        if(r.success !== true) {
            return;
        }
        this.loadData(this.data.id);
    }

    ,onUserToggleEnableClick: function(b, e) {
        var enable = (b.baseAction == this.actions.enableUser);
        CB_UsersGroups.setUserEnabled(
            {
                id: this.data.id
                ,enabled: enable
            }
            ,this.processToggleUserEnable
            ,this
        );
    }

    ,processToggleUserEnable: function(r, e) {
        if(r.success !== true) {
            return;
        }

        this.actions.enableUser.setHidden(r.enabled);
        this.actions.disableUser.setHidden(!r.enabled);

        var d = Ext.apply(this.userInfo.data, {enabled: r.enabled});

        this.userInfo.update(d);
    }

});
// ----------------------------------------------------------- end of form

// ---------------------------------------------- Main panel
Ext.define('CB.UsersGroups', {
    extend: 'Ext.Window'

    ,alias: 'CBUsersGroups'
    ,xtype: 'CBUsersGroups'

    ,layout: 'border'
    ,border: false
    ,closable: true
    ,minimizable: true

    ,iconCls: 'icon-users'
    ,title: L.UserManagement
    ,width: 850
    ,height: 600

    ,initComponent: function(){
        this.tree = new CB.UsersGroupsTree({
            region: 'center'
            ,split: true
            ,collapseMode: 'mini'
        });

        this.tree.getSelectionModel().on( 'selectionchange', this.onTreeSelectionChange, this );
        this.tree.getSelectionModel().on( 'beforeselect', this.onTreeBeforeSelect, this );

        this.form = new CB.UsersGroupsForm( {
            region: 'center'
            ,api: {submit: CB_User.uploadPhoto }
            ,listeners:{
                scope: this
                ,beforesave: this.onBeforeFormSave
                ,save: this.onFormSave
                ,loaded: this.onLoadFormData
                ,edit: this.onEditUserData
                ,verify: this.onVerifyEvent
            }
        } );

        this.searchField = new CB.search.Field({
            region: 'south'
            ,listeners: {
                scope: this
                ,'search': this.onSearchQuery
            }
        });

        Ext.apply(this, {
            items: [
                {
                    layout: 'border'
                    ,region: 'west'
                    ,width: 265
                    ,border: false
                    ,split: true
                    ,items: [
                        this.tree
                        ,this.searchField
                    ]
                }
                ,this.form
            ]
            ,listeners: {
                scope: this
                ,verify: this.onVerifyEvent
            }
        });

        this.callParent(arguments);
    }
    ,onSearchQuery: function(text, e){
        this.tree.filter(text, 'title');
    }
    ,onBeforeFormSave: function(){
        this.lastPath = '';
        var n = this.tree.getSelectionModel().getSelection()[0];
        if(n) this.lastPath = n.getPath('nid');
    }
    ,onFormSave: function(){
        this.tree.store.clearFilter();

        this.tree.store.reload({
            node: this.tree.getRootNode()
            ,scope: this
            ,callback: function(){
                //restore tree filter
                this.tree.filter(this.searchField.getValue(), 'title');

                //select same user
                this.tree.selectPath(
                    this.lastPath
                    ,'nid'
                    ,'/'
                    ,function(success){
                        if(!success){
                            this.form.setDisabled(true);
                            this.tree.getRootNode().cascadeBy({
                                before: function(n){
                                    if(n.data.id == this.form.data.id) {
                                        this.tree.getSelectionModel().select(n);
                                        this.form.setDisabled(false);
                                        return false;
                                    }
                                }
                                ,scope: this
                            });
                        }
                    }.bind(this)
                );
            }
        });
    }

    ,onTreeSelectionChange: function(sm, node){
        var n = this.tree.getSelectionModel().getSelection()[0];
        if((!n) || (n.getDepth() != 2)){
            this.form.setDisabled(true);
            if(this.loadFormTask) this.loadFormTask.cancel();
            return ;
        }
        this.loadId = n.data.nid;
        this.onLoadFormTask();
    }
//------------------------------------------------------------------------------------------------------------------------------------------------
    ,onTreeBeforeSelect: function(sm, newNode, oldNode){
        if(Ext.valueFrom(this._forceSelection, 0)){ this._forceSelection = 0; return true; }
        if(oldNode && this.form._isDirty){
            this.newNode = newNode;
            Ext.Msg.show({
                buttons: Ext.Msg.YESNO
                ,title: L.Confirmation
                ,msg: L.SaveChangesConfirmationMessage
                ,scope: this
                ,fn: function(btn, text){
                    if (btn == 'yes') {
                        this.form.saveData();
                    } else{
                        this._forceSelection = 1;
                        this.tree.getSelectionModel().select(this.newNode);
                        this.form.setDirty(false);
                    }
                }
            });
            return false;
        }
    }

    ,onLoadFormTask: function(){
        if(!this.loadFormTask) {
            this.loadFormTask = new Ext.util.DelayedTask(
                function(){
                    this.form.loadData(this.loadId);
                },
                this
            );
        }
        this.loadFormTask.delay(500);
    }
    ,onLoadFormData: function(data){
        this.tree.getRootNode().cascadeBy({
            before: function(n){
                if(n.data.nid == data.id) {
                    n.set(
                        'text'
                        ,Ext.valueFrom(n.data.title, n.data.name) +
                            ' <span class="cG">(id:' + data.id + ')</span>' +
                            ((data.enabled != 1)
                                ? ' <span class="cG">' + L.Disabled + '</span>'
                                : ''
                            )
                    );
                }
            }
            ,scope: this
        });
    }
    ,onEditUserData: function(){
        if(!this.form.canEditUserData) {
            return;
        }
        var data = Ext.apply({}, this.form.data);
        data.id = data.id.split('-').pop();
        var n = this.tree.getSelectionModel().getSelection()[0]
            ,iconCls = n ? n.data.iconCls : 'icon-user'
            ,w = new CB.UserEditWindow({
                title: data.title
                ,iconCls: iconCls
                ,data: data
                ,listeners: {
                    scope: this
                    ,savesuccess: function(){
                        this.form.loadData();
                    }
                    ,verify: this.onVerifyEvent
                }
            });
        w.show();
    }

    ,onVerifyEvent: function(cmp) {
        this.destroy();
        Ext.Msg.alert(L.Info, 'User management session has expired. Please access it and authenticate again.');
    }
});

// ----------------------------------------------------------- change password window
Ext.define('CB.ChangePasswordWindow', {
    extend: 'Ext.Window'
    ,modal: true
    ,title: L.ChangePassword
    ,autoWidth: true
    ,autoHeight: true
    ,border: false
    ,iconCls: 'icon-key'

    ,initComponent: function() {
        var items = [];

        this.data = this.config.data;

        if(this.data.id == App.loginData.id)
            items = [{
                xtype: 'textfield'
                ,fieldLabel: L.CurrentPassword
                ,inputType: 'password'
                ,name: 'currentpassword'
                ,allowBlank: (this.data.id != App.loginData.id)
            }];
        items.push({
                xtype: 'textfield'
                ,fieldLabel: L.Password
                ,inputType: 'password'
                ,name: 'password'
                ,allowBlank: false
                ,shouldMatch: true
            },{
                xtype: 'textfield'
                ,fieldLabel: L.ConfirmPassword
                ,inputType: 'password'
                ,name: 'confirmpassword'
                ,allowBlank: false
                ,shouldMatch: true
            },{
                xtype: 'displayfield'
                ,hideLabel: true
                ,cls: 'cR taC'
                ,anchor: '100%'
                ,id: 'msgTarget'
                ,value: '&nbsp;'
            });

        Ext.apply(this, {
            items: {
                xtype: 'form'
                ,autoWidth: true
                ,autoHeight: true
                ,border: false
                ,monitorValid: true
                ,extraParams: this.data
                ,api: {
                    submit: CB_UsersGroups.changePassword
                }
                ,items: {
                    xtype: 'fieldset'
                    ,labelWidth: 150
                    ,autoWidth: true
                    ,autoHeight: true
                    ,border: false
                    ,layout: 'anchor'
                    ,style: 'padding-top: 10px'
                    ,defaults: {
                        anchor: '100%'
                        ,listeners: {
                            scope: this
                            ,invalid: function(field, msg){
                                if(field.getEl().hasCls('x-form-invalid')) this.hasInvalidFields = true;
                            }
                        }
                    }
                    ,items: items
                }
                ,listeners: {
                    scope: this
                    ,clientvalidation: function(form, valid){
                        label = this.down('[id="msgTarget"]');
                        if(!valid && this.hasInvalidFields){
                            label.setValue(L.EmptyRequiredFields);
                            return;
                        }
                        var a = this.query('[shouldMatch=true]');
                        if(a[0].getValue() != a[1].getValue()){
                            this.down('form').buttons[0].setDisabled(true);
                            label.setValue(L.PasswordMissmatch);
                            return;
                        }
                        label.setValue('&nbsp;');
                    }
                }
                ,buttons: [
                    {
                        text: Ext.MessageBox.buttonText.ok
                        ,iconCls:'icon-tick'
                        ,formBind: true
                        ,type: 'submit'
                        ,scope: this
                        ,plugins: 'defaultButton'
                        ,handler: function(){
                            var f = this.down('form');
                            f.getForm().submit({
                                clientValidation: true
                                ,params: this.data
                                ,scope: this
                                ,success: this.onSubmitSuccess
                            });
                        }
                    },{
                        text: Ext.MessageBox.buttonText.cancel
                        ,iconCls:'icon-cancel'
                        ,handler: this.destroy
                        ,scope: this
                    }
                ]
            }

            ,listeners: {
                afterrender: function(){
                    f = this.down('form');
                    App.focusFirstField(f);
                }
            }
        });

        CB.ChangePasswordWindow.superclass.initComponent.apply(this, arguments);
    }
    ,onSubmitSuccess: function(r, e){
        this.fireEvent('passwordchanged');
        this.destroy();
    }
}
);
