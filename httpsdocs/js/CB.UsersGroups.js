Ext.namespace('CB');

// ----------------------------------------------------------- add user form
CB.AddUserForm = Ext.extend(Ext.Window, {
    data: {}
    ,layout: 'fit'
    ,autoWidth: true
    ,title: L.AddUser
    ,iconCls: 'icon-user-gray'
    ,initComponent: function(){
        recs = CB.DB.roles.queryBy(
            function(r){
                return ( (r.get('id') !=3) &&
                    (r.get('id') !=1) &&
                    (App.loginData.manage || (r.get('id') !=2))
                );
            }
        );//&& (App.loginData.admin || (r.get('id') !=1))

        data = [];
        recs.each(function(r){data.push(r.data);}, this);
        this.rolesStore = new Ext.data.JsonStore({
            autoLoad: true
            ,autoDestroy: true
            ,fields: [{name: "id", dataIndex: "id"} ,{name: "name", dataIndex: "name"}]
            ,proxy: new Ext.data.MemoryProxy()
            ,data: data
        });

        data = [];
        CB.DB.groupsStore.each( function(r){
            if(r.get('system') == 0) data.push(r.data);
        }, this);

        this.groupsStore = new Ext.data.JsonStore({
            autoLoad: true
            ,fields: [ {name: 'id', type: 'int'}, 'name', 'title', {name: 'system', type: 'int'}, {name: 'enabled', type: 'int'} ]
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
            },{
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
            },{
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
                ,mode: 'local'
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
                    this.syncSize();
                    this.center()
                }
            }
        });
        CB.AddUserForm.superclass.initComponent.apply(this, arguments);

        this.on('show', App.focusFirstField, this)
        this.on('close', function(){CB.DB.roles.clearFilter();}, this)
    }
    ,setDirty: function(value){
        this._isDirty = value;
        required = true;
        a = this.find('isFormField', true);
        Ext.each(a, function(i){
            if(!i.allowBlank) {
                required = required && !Ext.isEmpty(i.getValue());
                return required;
            }
        }, this);

        p = this.find('name', 'password')[0];
        pc = this.find('name', 'confirm_password')[0];
        pm = (p.getValue() != pc.getValue());
        msg = required ? '' : L.EmptyRequiredFields;
        this.find('name', 'E')[0].setText( pm ? L.PasswordMissmatch : msg);
        this.fbar.items.get(0).setDisabled(!value || !required || pm);
    }
    ,saveData: function(){
        params = {}
        a = this.find('isFormField', true);
        Ext.each(a, function(i){params[i.name] = i.getValue()}, this);
        if(this.data.callback) this.data.callback(params, this.ownerCt);
        this.destroy();
    }
})
// ----------------------------------------------------------- end of add user form
CB.UsersGroupsTree = Ext.extend(Ext.tree.TreePanel, {
    autoScroll: true
    ,containerScroll: true
    ,stateId: 'CB_UsersGroupsState'
    ,stateful: true
    ,rootVisible: false
    ,animate: false
    ,border: false
    ,enableDD: true
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
                ,iconCls: 'icon-minus'
                ,disabled: true
                ,handler: this.delNode
                ,scope: this
            })
            ,remove: new Ext.Action({
                text: L.Remove
                ,iconCls: 'icon-user-arrow'
                ,disabled: true
                ,handler: this.deassociateNode
                ,scope: this
            })
            ,reload: new Ext.Action({
                iconCls: 'icon-reload'
                ,qtip: L.Reload
                ,scope:this
                ,handler: function(){this.getRootNode().reload();}
            })

        }

        this.editor = new Ext.tree.TreeEditor(this, {
            allowBlank: false
            ,blankText: 'A name is required'
            ,selectOnFocus: true
            ,ignoreNoChange: true
        });
        this.editor.on('beforestartedit', this.onBeforeStartEdit, this);
        this.editor.on('startedit', this.onStartEdit, this);
        this.editor.on('beforecomplete', this.onBeforeEditComplete, this);

        Ext.apply(this, {
            loader: new Ext.tree.TreeLoader({
                directFn: CB_UsersGroups.getChildren
                ,paramsAsHash: true
                ,preloadChildren: true
                ,listeners:{
                    scope: this
                    ,beforeload: function(treeLoader, node) {
                        // Add NodePath to the params
                        treeLoader.baseParams.path = node.getPath('nid');
                    }
                    ,load: function(o, n, r) {
                        if(n.attributes.kind > 1) n.sort(this.sortTree)
                    }
                    ,loadexception: function(loader, node, response) {
                        node.leaf = false; //force it to folder?
                        node.loaded = false;
                    }
                }
            })
            ,root: {
                nodeType: 'async'
                ,expanded: false
                ,expandable: true
                ,iconCls: 'icon-home'
                ,leaf: false
                ,nid: 'root'
                ,text: 'root'
            }
            ,tbar: [
                {
                    text: L.Add
                    ,iconCls: 'icon-plus'
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
                afterlayout: function(){this.getRootNode().expand()}
                ,nodedragover: function(o){
                    if( (o.point != 'append')
                        || (o.target == o.dropNode.parentNode)
                        || (o.target.getDepth() != 1)
                        || (o.target.attributes.nid < 1) // || (o.target.attributes.role_id > 2)
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
                        CB_UsersGroups.associate(this.sourceNode.attributes.nid, this.targetNode.attributes.nid, this.processAssociate, this);
                    }
                }
                ,beforeappend: { scope: this, fn: function(t, p, n){
                    text = Ext.value(n.attributes.title, n.attributes.name);
                    n.attributes.title = text;

                    if( p.getDepth() == 1 ){ //n.attributes.role_id > 0 &&
                        text += ' <span class="cG">(id:' + n.attributes.nid + ')</span>';
                        if(n.attributes.enabled != 1){
                            text += ' <span class="cG">' + L.inactive + '</span>';
                        }
                        n.attributes.iconCls = 'icon-user-' + Ext.value(n.attributes.sex, '');
                    }
                    if(n.attributes.type == 1){ n.attributes.cls = n.attributes.cls + ' fwB'; n.getUI().addClass('fwB') };
                    if(n.attributes.cid == App.loginData.id){n.attributes.cls = n.attributes.cls + ' cDB'; n.getUI().addClass('cDB')};
                    n.setText(text);
                } }
                ,remove: this.updateChildrenCount
                ,append: this.updateChildrenCount
            }
            ,selModel: new Ext.tree.DefaultSelectionModel({
                listeners: {
                    selectionchange: {scope: this, fn: function(sm, node){
                            if(Ext.isEmpty(node)){
                                this.actions.del.setDisabled(true);
                                this.actions.remove.setDisabled(true);
                            }else{
                                this.actions.del.setDisabled(node.attributes.system == 1);
                                this.actions.remove.setDisabled( (node.getDepth() <2) || (node.parentNode.attributes.nid <1 ) );
                            }
                        }
                    }
                }
            })
            ,keys: [{
                key: Ext.EventObject.F2
                ,alt: false
                ,ctrl: false
                ,stopEvent: true
                ,fn: this.onRenameClick
                ,scope: this
            }]
        });
        CB.UsersGroupsTree.superclass.initComponent.apply(this, arguments);
    }
    ,afterRender: function() {
        CB.UsersGroupsTree.superclass.afterRender.apply(this, arguments);
    }
    ,onRenameClick: function(b, e){
        n = this.getSelectionModel().getSelectedNode();
        if(!n) return;
        this.editor.editNode = n;
        this.editor.startEdit(n.ui.textNode);
    }
    ,onBeforeStartEdit: function(editor, boundEl, value){
        n = this.getSelectionModel().getSelectedNode();
        if( (n.attributes.type != 1) || (n.attributes.nid < 1) ) return false;
    }
    ,onStartEdit: function(boundEl, value){
        n = this.getSelectionModel().getSelectedNode();
        if(n.attributes.type != 1) return false;
        value = n.attributes.title;
        this.editor.setValue(value);
    }
    ,onBeforeEditComplete: function(editor, newVal, oldVal) {
        n = this.getSelectionModel().getSelectedNode();
        oldVal = n.attributes.title;
        if(newVal === oldVal) return;
        var n = editor.editNode;
        editor.cancelEdit();
        this.getEl().mask(L.Processing, 'x-mask-loading');
        CB_UsersGroups.renameGroup({id: n.attributes.nid, title: newVal}, this.processRenameGroup, this);
        return false;
    }
    ,processRenameGroup: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) return;
        this.editor.editNode.attributes.title = r.title
        this.updateChildrenCount(this, this.editor.editNode);
    }
    ,onAddUserClick: function(b, e){
        w = new CB.AddUserForm({modal: true, ownerCt: this, data: {callback: this.addUser}});
        w.show();
    }
    ,addUser: function(params, t){
        CB_UsersGroups.addUser(params, t.processAddUser, t);
    }
    ,processAddUser: function(r, e){
        if(r.success !== true) return false;
        path = '/root/'+r.data.group_id+'/'+r.data.nid;
        this.getRootNode().reload(function(){this.selectPath(path, 'nid')}, this);
        App.mainViewPort.fireEvent('useradded', r.data);
    }
    ,onAddGroupClick: function(b, e){
        Ext.Msg.prompt(L.Group, L.Name, function(b, text){
            if((b == 'ok') && !Ext.isEmpty(text)){
                rec = new CB.DB.groupsStore.recordType({
                    id: 0
                    ,name: text
                    ,title: text
                })
                CB.DB.groupsStore.addSorted(rec)
                this.getRootNode().reload();
            }
        }, this)
    }
    ,sortTree: function(n1, n2){
        return (n1.text < n2.text) ? -1 : 1;
    }
    ,deassociateNode: function(){
        n = this.getSelectionModel().getSelectedNode();
        if(!n) return;
        Ext.Msg.confirm(L.ExtractUser, L.ExtractUserMessage.replace('{user}', n.attributes.name).replace('{group}', n.parentNode.attributes.text),
            function(b){if(b == 'yes') CB_UsersGroups.deassociate(n.attributes.nid, n.parentNode.attributes.nid, this.processDeassociate, this); }, this
        )
    }
    ,processDeassociate: function(r, e){
        if(r.success !== true) return false;
        n = this.getSelectionModel().getSelectedNode();
        attr = n.attributes;
        attr.iconCls = 'icon-user-gray';
        n.remove(true);
        if(r.outOfGroup){
            p = this.getRootNode().findChild( 'nid', '-1');
            if(p.loaded){
                p.appendChild(attr);
                p.sort(this.sortTree);
            }else{
                p.attributes.users++;
                p.setText(p.attributes.text.split('<')[0] + ' <span class="cG">(' + p.attributes.users + ')</span>');
            }
        }
    }
    ,processAssociate: function(r, e){
        if(r.success !== true) return false;
        attr = Ext.apply({}, this.sourceNode.attributes);
        if(this.targetNode.loaded){
            attr.id = Ext.id();
            this.targetNode.appendChild(attr);
            this.targetNode.sort(this.sortTree);
        }else{
            this.targetNode.attributes.users++;
            this.targetNode.setText(this.targetNode.attributes.text.split('<')[0] + ' <span class="cG">(' + this.targetNode.attributes.users + ')</span>');
        }
        if(this.sourceNode.parentNode.attributes.nid == '-1') this.sourceNode.remove(true);
    }
    ,delNode: function(){
        n = this.getSelectionModel().getSelectedNode();
        if(!n) return;
        switch(n.getDepth()){
        case 2:
            this.deletedUserData = n.attributes;
            Ext.MessageBox.confirm(L.Confirmation, L.DeleteUser + ' "'+n.attributes.text+'"?',
            function(btn, text){
                if(btn == 'yes'){
                    n = this.getSelectionModel().getSelectedNode();
                    CB_UsersGroups.deleteUser(n.attributes.nid, this.processDelNode, this);
                }
            }
            , this);
            break;
        case 1:
            Ext.MessageBox.confirm(L.Confirmation, L.DeleteGroupConfirmationMessage + ' "'+n.attributes.text+'"?',
            function(btn, text){
                if(btn == 'yes') CB_Security.destroyUserGroup(n.attributes.nid, this.processDestroyUserGroup, this);
            }
            , this);
            break;
        }
    }
    ,processDestroyUserGroup: function(r, e){
        if(r.success !== true) return false;
        this.processDelNode(r, e);
        CB.DB.groupsStore.reload();
    }
    ,processDelNode: function(r, e){
        if(r.success !== true) return false;
        nid = this.getSelectionModel().getSelectedNode().attributes.nid;
        deleteNodes = [];
        this.getRootNode().cascade(function(n){if(n.attributes.nid == nid) deleteNodes.push(n)}, this);
        for(i = 0; i< deleteNodes.length; i++) deleteNodes[i].remove(true);
        if(this.deletedUserData){
            App.mainViewPort.fireEvent('userdeleted', this.deletedUserData);
            delete this.deletedUserData;
        }
    }
    ,updateChildrenCount: function( t, p ){
        if(Ext.isEmpty(p)) return;
        if(Ext.isEmpty(p.childNodes)){
            if(!Ext.isEmpty(p.attributes)) {
                p.setText(p.attributes.title);
            }
            return;
        }
        p.attributes.users = p.childNodes.length;
        p.setText(p.attributes.title + ' <span class="cG">(' + p.attributes.users + ')</span>');
    }
    ,filter: function(text, property){
        if(Ext.isEmpty(text)){
            this.clearFilter();
             return;
        }
        text = text.toLowerCase();
        rn = this.getRootNode();
        visibleNodes = [];
        rn.cascade(function(n){
            visible = (n.attributes[property].toLowerCase().indexOf(text) >=0 );
            if(visible){
                n.ui.show();
                p = n.parentNode;
                while(p){
                    p.ui.show();
                    p.expand()
                    p = p.parentNode;
                }
            }else n.ui.hide();
        }, this);

    }
    ,clearFilter: function(){
        rn = this.getRootNode();
        rn.cascade(function(n){
            n.ui.show();
        }, this);
    }
})
// ----------------------------------------------------------- edit user form
CB.UserEditWindow = Ext.extend(Ext.Window, {
    iconCls: 'icon-user'
    ,title: L.User
    ,modal: true
    ,closeAction: 'destroy'
    ,y: 150
    ,autoWidth: true
    ,autoHeight: true
    ,layout: 'fit'
    ,autoShow: true
    ,initComponent: function() {
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
                    this.destroy()
                }
                ,cancel: this.destroy
                ,change: function(){
                    this.syncSize()
                }
            }
        });

        Ext.apply(this, {
            items: this.profileForm
            ,listeners: {
                scope: this
                ,afterrender: this.onAfterRender
            }
        });
        CB.UserEditWindow.superclass.initComponent.apply(this, arguments);
    }
    ,onAfterRender: function(){
        this.getEl().mask(L.LoadingData + ' ...');
        CB_User.getProfileData(this.data.id, this.onLoadProfileData, this);
    }
    ,onLoadProfileData: function(r, e){
        this.getEl().unmask();
        if(r.success !== true) {
            this.destroy();
            return;
        }
        this.profileForm.loadData(r);
    }
})
// ----------------------------------------------------------- form
CB.UsersGroupsForm = Ext.extend(Ext.form.FormPanel, {
    border: false
    ,disabled: true
    ,fileUpload: true
    ,data: {}
    ,initComponent: function(){
        bulletRenderer = function(v, m){
            m.css = 'taC';
            if(v == 1) return '<span class="icon-padding icon-tick"></span>';
            return '';
        }
        this.userInfo = new Ext.DataView({
            tpl: ['<img class="fl user-photo-field click icon-user32-{sex}" src="/' + App.config.coreName + '/photo/{id}.png?{[ (new Date()).format("His") ]}">'
                ,'<span class="fwB click">{title}</span><br />'
                ,'<span class="cG">'+L.User+':</span> {name}, <span class="cG">'+L.lastAction+':</span> {[ Ext.isEmpty(values.last_action_time) ? "" : values.last_action_time ]}<br />'
                ,'<span class="cG">'+L.addedByUser+':</span> {owner}, {cdate}'
            ]
            ,itemSelector:'.click'
            ,autoHeight: true
            ,listeners:{ scope: this, click: this.onEditUserDataClick }
        });

        Ext.apply(this, {
            layout: 'border'
            ,api: {submit: CB_User.uploadPhoto }
            ,hideBorders: true
            ,tbar:[{text: L.Save, iconCls: 'icon-save', disabled: true, handler: this.saveData, scope: this}
                ,{text: Ext.MessageBox.buttonText.cancel, iconCls: 'icon-cancel', disabled: true, handler: function(b, e){e.stopPropagation();this.loadData();}, scope: this}
                ,{xtype: 'tbseparator', hidden: true}
                ,{text: L.Edit, iconCls: 'icon-pencil', handler: this.onEditUserDataClick, scope: this, hidden: true}
                ,{xtype: 'tbseparator', hidden: true}
                ,{text: L.Options, hidden: true, menu: [
                    {text: L.ChangePassword, iconCls: 'icon-key', handler: this.onEditUserPasswordClick, scope: this}
                    ,'-'
                    ,{text: L.ChangeUsername, iconCls: 'icon-pencil', handler: this.onEditUsernameClick, scope: this}
                ]}
            ]
            ,items: [{
                    region: 'north'
                    ,height: 60
                    ,bodyStyle: 'padding: 10px'
                    ,items: [{
                        xtype: 'textfield'
                        ,name: 'photo'
                        ,cls: 'fl'
                        ,style: 'position:absolute;width:1px;height:1px;opacity:0;top:-100px'
                        ,inputType: 'file'
                        ,allowBlank: false
                        ,listeners:{
                            scope: this
                            ,afterrender: function(c){
                                c.getEl().on('change', this.onPhotoChanged, this);
                            }
                        }
                    }, this.userInfo ]
                },{
                region: 'center'
                ,hideBorders: true
                ,autoScroll: true
                ,bodyStyle: 'padding: 0 20px'
                ,items: [{
                        border: false
                        ,anchor: '100%'
                        ,minHeight: 100
                        ,autoHeight: true
                        ,xtype: 'grid'
                        ,style: 'margin-top: 15px'
                        ,stripeRows: true
                        ,sm: new Ext.grid.CellSelectionModel()
                        ,store: new Ext.data.JsonStore({
                            autoDestroy: true
                            ,fields: [{name: 'id', type: 'int'}, 'name', {name: 'active', type: 'int'}]
                        })
                        ,colModel: new Ext.grid.ColumnModel({
                            defaults: {
                                width: 120
                                ,sortable: true
                            }
                            ,columns: [
                                {header: L.Groups, width: 150, dataIndex: 'name'}
                                ,{header: L.Active, dataIndex: 'active', renderer: bulletRenderer}
                            ]
                        })
                        ,viewConfig: {
                            forceFit: true
                            ,markDirty: false
                            ,getRowClass: function(r, index) {
                                return (r.get('active') != 1) ? '' : 'fwB';
                            }
                        }
                        ,listeners:{
                            celldblclick: {scope: this, fn: function(g, ri, ci, e){
                                    r = g.getStore().getAt(ri);
                                    switch(g.getColumnModel().getDataIndex(ci)){
                                        case 'active':
                                            r.set('active', (r.get('active') == 1) ? null : 1);
                                            break;
                                        default: return;
                                    }
                                    this.fireEvent('change');
                                }
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
                change: { scope: this, fn: function(e){this.setDirty(true);} }
                ,show: {scope: this, fn: function() {
                        f = function(){ a = this.find('isFormField', true); a[0].focus();}
                        f.defer(500);
                    }
                }
            }
        });
        CB.UsersGroupsForm.superclass.initComponent.apply(this, arguments);
        this.addEvents('beforesave', 'save');
        this.grid = this.findByType('grid')[0];
    }
    ,setDirty: function(value){
        this._isDirty = (value !== false);
        this.getTopToolbar().items.get(0).setDisabled(!this._isDirty);
        this.getTopToolbar().items.get(1).setDisabled(!this._isDirty);
    }
    //------------------------------------------------------------------------------------------------------------------------------------------------
    ,loadData: function(id){
        if(!Ext.isEmpty(id)) {
            this.data.id = id;
        }
        this.getEl().mask(L.LoadingData + ' ...');
        CB_UsersGroups.getAccessData(this.data.id, this.processLoadedData, this);
    }
    ,processLoadedData: function(response, e){
        if(response.success === true){
            this.data.name = Ext.value(response.data.name);
            this.data.title = Ext.value(response.data.title, response.data.name);
            response.data.title = this.data.title
            this.data.template_id = response.data.template_id;
            this.userInfo.data = response.data;
            this.userInfo.update(response.data);
            this.grid.setDisabled(response.data.id == App.loginData.id);//disable editing access for self

            accessData = [];
            CB.DB.groupsStore.each( function(r){
                if(r.get('system') == 0)
                    accessData.push( {id: r.get('id'), 'name': r.get('title'), 'active': (response.data.groups.indexOf(String(r.get('id') ) ) >=0 ) ? 1: 0})
            }, this)
            this.grid.getStore().loadData(accessData, false);

            this.canEditUserData = ((App.loginData.admin) || (response.data.cid == App.loginData.id) || (response.data.id == App.loginData.id));
            eb = this.getTopToolbar().find('iconCls', 'icon-pencil')[0];
            eb.setVisible(this.canEditUserData); // edit button
            idx = this.getTopToolbar().items.indexOf(eb);
            this.getTopToolbar().items.itemAt(idx -1).setVisible(this.canEditUserData);// divider for edit button
            visible = (this.canEditUserData || (response.data.id == App.loginData.id));
            this.getTopToolbar().items.itemAt(idx + 1).setVisible(visible); //divider for options button
            this.getTopToolbar().items.itemAt(idx + 2).setVisible(visible); // options button
            this.updatePhoto(response.data.photo);
            this.setDisabled(false);

            this.fireEvent('loaded', this.data);
        }
        this.getEl().unmask();
        this.setDirty(false);
    }
    ,saveData: function(){
        this.fireEvent('beforesave');
        this.getEl().mask(L.SavingChanges + ' ...')
        params = { groups: [] };
        this.grid.getStore().each(
            function(r){
                if(r.get('active') == 1) {
                    params.groups.push(r.get('id'))
                }
            },
            this
        );
        params.id = this.data.id;
        CB_UsersGroups.saveAccessData(
            params,
            function(r, e){
                this.setDirty(false);
                this.getEl().unmask();
                this.fireEvent('save');
            },
            this
        );
    }
    ,onEditUserDataClick: function(w, idx, el, ev){
        if(ev && (ev.getTarget().localName == "img") ) {
            el = this.find('name', 'photo')[0].getEl();
            el.dom.click();
        }else this.fireEvent('edit');
    }
    ,onPhotoChanged: function(ev, el, o){
        if(!this.getForm().isValid()) return;
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
            del = this.userInfo.getEl().query('img.user-photo-field')[0]
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
                        if(r.success !== true) return Ext.Msg.alert(L.Error, Ext.value(e.msg, L.ErrorOccured) );
                        this.data.name = r.name;
                        this.userInfo.data.name = r.name;
                        this.userInfo.update(this.userInfo.data);
                    }, this)
            }
        }, this, false, this.data.name);
    }
    ,onEditUserPasswordClick: function(){
        w = new CB.ChangePasswordWindow({data: this.data});
        w.show();
    }
})
// ----------------------------------------------------------- end of form
// ---------------------------------------------- Main panel
CB.UsersGroups = Ext.extend(Ext.Panel, {
    layout: 'border'
    ,border: false
    ,closable: true
    ,iconCls: 'icon-users'
    ,title: L.UserManagement
    ,initComponent: function(){
        this.tree = new CB.UsersGroupsTree({
            region: 'center'
            ,width: 250
            ,split: true
            ,collapseMode: 'mini'
        });//west region
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
            }
        } );//center region
        this.searchField = new Ext.ux.SearchField({region: 'south', listeners: {scope: this, 'search': this.onSearchQuery} } )

        Ext.apply(this, { items: [{
            layout: 'border'
            ,region: 'west'
            ,width: 250
            ,border: false
            ,split: true
            ,items: [
                this.tree
                ,this.searchField
            ]
        }, this.form] });

        CB.UsersGroups.superclass.initComponent.apply(this, arguments);
    }
    ,onSearchQuery: function(text, e){
        this.tree.filter(text, 'text');
    }
    ,onBeforeFormSave: function(){
        this.lastPath = '';
        n = this.tree.getSelectionModel().getSelectedNode();
        if(n) this.lastPath = n.getPath('nid');
    }
    ,onFormSave: function(){
        this.tree.getRootNode().reload(function(){this.tree.selectPath(this.lastPath, 'nid',
            function(success){
                if(!success){
                    this.form.setDisabled(true);
                    this.tree.getRootNode().cascade(function(n){
                        if(n.attributes.id == this.form.data.id) {
                            this.tree.getSelectionModel().select(n);
                            this.form.setDisabled(false);
                            return false;
                        }
                    }, this);
                }
            }.createDelegate(this)
        )}, this);
    }
    ,onTreeSelectionChange: function(sm, node){
        n = this.tree.getSelectionModel().getSelectedNode();
        if((!n) || (n.getDepth() != 2)){
            this.form.setDisabled(true);
            if(this.loadFormTask) this.loadFormTask.cancel();
            return ;
        }
        this.loadId = n.attributes.nid;
        this.onLoadFormTask();
    }
//------------------------------------------------------------------------------------------------------------------------------------------------
    ,onTreeBeforeSelect: function(sm, newNode, oldNode){
        if(Ext.value(this._forceSelection, 0)){ this._forceSelection = 0; return true; }
        if(oldNode && this.form._isDirty){
            this.newNode = newNode;
            Ext.Msg.show({
                buttons: Ext.Msg.YESNO
                ,title: L.Confirmation
                ,msg: L.SaveChangesConfirmationMessage
                ,scope: this
                ,fn: function(btn, text){
                    if (btn == 'yes') this.form.saveData();
                    else{
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
        this.tree.getRootNode().cascade(function(n){
            if(n.attributes.nid == data.id) {
                n.setText(
                    Ext.value(n.attributes.title, n.attributes.name) +
                    ' <span class="cG">(id:' + data.id + ')</span>'
                );
            }
        }, this);
    }
    ,onEditUserData: function(){
        if(!this.form.canEditUserData) return;
        data = Ext.apply({}, this.form.data);
        data.id = data.id.split('-').pop();
        n = this.tree.getSelectionModel().getSelectedNode();
        iconCls = n ? n.attributes.iconCls : 'icon-user';
        w = new CB.UserEditWindow({
            title: data.title
            ,iconCls: iconCls
            ,data: data
            ,listeners: {
                scope: this
                ,savesuccess: function(){
                    this.form.loadData()
                }
            }
        });
        w.show();
    }
});
Ext.reg('CBUsersGroups', CB.UsersGroups); // register xtype
// ----------------------------------------------------------- change password window

CB.ChangePasswordWindow = Ext.extend(Ext.Window, {
    modal: true
    ,title: L.ChangePassword
    ,autoWidth: true
    ,autoHeight: true
    ,hideBorders: true
    ,iconCls: 'icon-key'
    ,initComponent: function() {
        items = [];
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
                ,hideBorders: true
                ,monitorValid: true
                ,baseParams: this.data
                ,api: {
                    submit: CB_UsersGroups.changePassword
                }
                ,items: {
                    xtype: 'fieldset'
                    ,labelWidth: 150
                    ,autoWidth: true
                    ,autoHeight: true
                    ,hideBorders: true
                    ,style: 'margin:0'
                    ,defaults: {
                        listeners: {
                            scope: this
                            ,invalid: function(field, msg){
                                if(field.getEl().hasClass('x-form-invalid')) this.hasInvalidFields = true;
                            }
                        }
                    }
                    ,items: items
                }
                ,listeners: {
                    scope: this
                    ,clientvalidation: function(form, valid){
                        label = this.find('id', 'msgTarget')[0];
                        if(!valid && this.hasInvalidFields){
                            label.setValue(L.EmptyRequiredFields);
                            return;
                        }
                        a = this.find('shouldMatch', true);
                        if(a[0].getValue() != a[1].getValue()){
                            this.findByType('form')[0].buttons[0].setDisabled(true);
                            label.setValue(L.PasswordMissmatch);
                            return;
                        }
                        label.setValue('&nbsp;');
                    }
                }
                ,buttons: [{text: Ext.MessageBox.buttonText.ok, iconCls:'icon-tick', formBind: true, type: 'submit', scope: this, plugins: 'defaultButton'
                        ,handler: function(){
                            f = this.findByType('form')[0];
                            f.getForm().submit({
                                clientValidation: true
                                ,params: this.data
                                ,scope: this
                                ,success: this.onSubmitSuccess
                            })
                        }
                    }
                    ,{text: Ext.MessageBox.buttonText.cancel, iconCls:'icon-cancel', handler: this.destroy, scope: this}
                    ]
            }
            ,listeners: {
                afterrender: function(){ f = this.findByType('form')[0]; f.syncSize(); App.focusFirstField(f) }
            }
        });
        this.addEvents('passwordchanged');
        CB.ChangePasswordWindow.superclass.initComponent.apply(this, arguments);
    }
    ,onSubmitSuccess: function(r, e){
        this.fireEvent('passwordchanged');
        this.destroy();
    }
}
)
