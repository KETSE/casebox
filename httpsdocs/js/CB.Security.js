Ext.namespace('CB');

Ext.define('CB.SecurityWindow', {
    extend: 'Ext.Window'

    ,alias: 'CBSecurityWindow'

    ,xtype: 'CBSecurityWindow'

    ,closable: true

    ,initComponent: function(){
        this.data = Ext.valueFrom(this.config.data, {});

        this.actions = {
            edit: new Ext.Action({
                text: L.Edit
                ,scope: this
                ,handler: this.onEditClick
            })
            ,add: new Ext.Action({
                text: L.Add
                ,scope: this
                ,handler: this.onAddClick
                ,hidden: true
            })
            ,del: new Ext.Action({
                text: L.Delete
                ,scope: this
                ,handler: this.onDeleteClick
                ,hidden: true
                ,disabled: true
            })

            ,advanced: new Ext.Action({
                text: L.Advanced
                ,scope: this
                ,disabled: true
                ,handler: this.onAdvancedClick
            })
            ,save: new Ext.Action({
                text: L.Save
                ,scope: this
                ,handler: this.onSavePermissionsClick
                ,hidden: true
                ,disabled: true
            })
            ,apply: new Ext.Action({
                text: L.Apply
                ,scope: this
                ,handler: this.onApplyPermissionsClick
                ,hidden: true
                ,disabled: true
            })
            ,cancel: new Ext.Action({
                text: L.Cancel
                ,scope: this
                ,handler: this.onCancelPermissionsChangeClick
                ,hidden: true
                ,disabled: true
            })
            ,removeChildPermissions: new Ext.Action({
                text: L.RemoveChildPermissions
                ,iconCls: 'icon-key-minus'
                ,scope: this
                ,handler: this.onRemoveChildPermissionsClick
            })
        };

        this.objectLabel = new Ext.form.DisplayField({
            value: 'Object name: '
            ,style:'padding: 10px; background-color: #fff'
            ,reg_ion: 'north'
        });

        this.editLabel = new Ext.form.DisplayField({
            value: 'To change permissions, click Edit'
        });

        this.aclStore = new Ext.data.DirectStore({
            autoSave: false
            ,autoSync: false
            // ,restful: true
            ,model: 'AclRecord'
            ,proxy: {
                type: 'direct'
                ,paramsAsHash: true
                ,extraParams:{ id: this.data.id }
                ,api:{
                    read: CB_Security.getObjectAcl
                    ,create: CB_Security.addObjectAccess
                    ,update: CB_Security.updateObjectAccess
                    ,destroy: CB_Security.destroyObjectAccess
                }
                ,reader: {
                    type: 'json'
                    ,rootProperty: 'data'
                }
                ,writer: new Ext.data.JsonWriter({
                    encode: false
                    ,writeAllFields: true
                    ,transform: Ext.Function.bind(
                        function(data, request) {
                            return {
                                id: this.data.id
                                ,data: data
                            };
                        }
                        ,this
                    )
                })
            }
            ,listeners: {
                scope: this
                ,load: this.onAclStoreLoad
            }
        });

        this.aclList = new Ext.list.ListView({
            store: this.aclStore
            ,singleSelect: true
            ,emptyText: L.noData
            ,reserveScrollOffset: true
            ,hideHeaders: true
            ,boxMinHeight: 100
            ,height: 300
            ,style: 'border: 1px solid #aeaeae'
            ,forceFit: true
            ,columns: [{
                header: 'Group or user'
                ,dataIndex: 'name'
                ,renderer: function(v, m, r, ri, ci, s){
                    m.css = 'icon-grid-column-top '+ r.get('iconCls');

                    return v;
                }
            }]
            ,listeners: {
                scope: this
                ,selectionchange: this.onAclListSelectionChange
            }
        });

        this.specialPermissionsLabel = new Ext.form.DisplayField({value: 'For special permissions or advanced settings,<br /> click Advanced.'});

        this.permissionsStore = new Ext.data.ArrayStore({
            fields: [
                'id'
                ,'name'
                ,'allow'
                ,'deny'
            ]

            ,accessGroups: {
                'FullControl':  [ 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1 ]
                ,'Modify':  [ 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 1 ]
                ,'Read':    [ 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1 ]
                ,'Write':   [ 0, 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0 ]
            }
        });

        this.permissionsList = new Ext.list.ListView({
            store: this.permissionsStore
            ,singleSelect: true
            ,emptyText: L.noData
            ,reserveScrollOffset: true
            ,boxMinHeight: 100
            ,height: 300
            ,style: 'border: 1px solid #aeaeae'
            ,columnSort: false
            ,forceFit:true
            ,readOnly: true
            ,columns: [
                {
                    header: 'Permission'
                    ,width: 100
                    ,dataIndex: 'name'
                },{
                    header: 'Allow'
                    ,width: 50
                    ,dataIndex: 'allow'
                    ,renderer: function(v, m, r, ri, ci, s){
                        return (this.readOnly
                            ? ( (v > 0)
                                ? '<input type="checkbox" disabled="disabled" checked="checked" value="'+v+'">'
                                : ""
                            )
                            : '<input type="checkbox" ' + (
                                (v == 2)
                                ? 'disabled="disabled" value="2" '
                                : 'value="1" '
                            ) + ((v > 0) ? 'checked="checked" ': "")+" />"
                        );
                    }
                    ,align: 'center'
                },{
                    header: 'Deny'
                    ,dataIndex: 'deny'
                    ,renderer: function(v, m, r, ri, ci, s){
                        return (this.readOnly ?
                            ((v < 0)
                                ? '<input type="checkbox" disabled="disabled" checked="checked" value="' + v + '">'
                                : ""
                            )
                            : '<input type="checkbox" ' +
                                ((v == -2)
                                    ? 'disabled="disabled" value="-2" ': 'value="-1" '
                                )+ ((v < 0) ? 'checked="checked" ': "")+" />"
                        );
                    }
                    ,align: 'center'
                }
            ]
            ,listeners:{
                scope: this
                ,itemclick: this.onPermissionNodeClick
            }
        });
        this.cbInherit = new Ext.form.Checkbox({
            checked: true
            ,id: 'cb_inherit' + this.data.id
            ,listeners: {
                scope: this
                ,change: this.onCbInheritClick
            }
        });

        var topToolbar = null;

        if(App.loginData.admin){
            topToolbar = [ this.actions.removeChildPermissions ];
        }

        Ext.apply(this, {
            title: L.Security
            ,iconCls: 'icon-key'
            ,autoHeight: true
            ,cls: 'x-panel-white'
            ,bodyStyle: 'background-color: white'
            ,tbar: topToolbar

            ,items: [
                this.objectLabel
                ,{
                    layout: 'hbox'
                    ,border: false
                    ,autoHeight: true
                    ,scrollable: true
                    ,items: [{
                        title: L.GroupOrUserNames
                        ,layout: 'fit'
                        ,items: this.aclList
                        ,unstyled: true
                        ,width: 400
                        ,padding: 10
                        ,border: 0
                        ,buttonAlign: 'left'
                        ,buttons: [
                            this.editLabel
                            ,'->'
                            ,this.actions.edit
                            ,this.actions.add
                            ,this.actions.del
                        ]

                    },{
                        title: L.PermissionsForItem
                        ,layout: 'fit'
                        ,items: this.permissionsList
                        ,unstyled: true
                        ,width: 400
                        ,padding: 10
                        ,border: 0
                        ,style: 'margin-left: 50px'
                        ,buttonAlign: 'left'
                        ,buttons: [this.specialPermissionsLabel
                            ,'->'
                            ,this.actions.save
                            ,this.actions.apply
                            ,this.actions.cancel
                            ,this.actions.advanced
                        ]

                    }
                    ]
                },{
                    xtype: 'panel'
                    ,region: 'south'
                    ,layout: 'fit'
                    ,autoHeight: true
                    ,border: false
                    ,padding: 10
                    ,items:[
                        {
                            xtype: 'fieldcontainer'
                            ,layout: 'hbox'
                            ,items: [
                                this.cbInherit
                                ,{
                                    xtype: 'label'
                                    ,text: ' ' + L.InheritPermissionsMsg
                                    ,style: 'margin-top:3px'
                                    ,forId: 'cb_inherit' + this.data.id
                                    ,listeners: {
                                        scope: this
                                        ,click: this.onCbInheritLabelClick
                                    }
                                }
                            ]
                        }

                    ]
                }
            ]
            ,listeners:{
                scope: this
                ,afterrender: this.onAfterRender
            }
        });

        this.callParent(arguments);
    }

    ,onAfterRender: function(){
        this.getEl().mask(L.loading, 'x-mask-loading');
        this.aclStore.load();
    }

    ,onAclStoreLoad: function(store, records, options){
        this.getEl().unmask();

        var rawData = Ext.valueFrom(store.proxy.reader.rawData, {});

        this.objectLabel.setValue('Object name: ' + Ext.valueFrom(rawData.path, '') + rawData.name);
        this.setTitle(rawData.name);

        this.cbInherit.settingValue = true;
        this.cbInherit.setValue(rawData.inherit_acl == 1);

        delete this.cbInherit.settingValue;
    }

    ,onEditClick: function(b, e){
        this.setReadOnly(false);
    }

    ,setReadOnly: function(readOnly){
        this.editLabel.setVisible(readOnly);
        this.actions.edit.setHidden(!readOnly);
        this.actions.add.setHidden(readOnly);
        this.actions.del.setHidden(readOnly);

        this.updateDeleteAction();

        this.permissionsList.readOnly = readOnly;

        this.permissionsList.getView().refresh();

        this.specialPermissionsLabel.setVisible(readOnly);
        this.actions.advanced.setHidden(!readOnly);
        this.actions.save.setHidden(readOnly);
        this.actions.apply.setHidden(readOnly);
        this.actions.cancel.setHidden(readOnly);
    }

    ,updateDeleteAction: function(){
        var canDelete = true
            ,sr = this.aclList.getSelectionModel().getSelection();

        if(!Ext.isEmpty(sr)){
            var r = sr[0];
            canDelete = ( ( r.get('allow').indexOf('2') < 0 ) && ( r.get('deny').indexOf('-2') < 0 ));
        }

        this.actions.del.setDisabled(!canDelete);
    }

    ,onAddClick: function(b, e){
        var w = new CB.ObjectsSelectionForm({
            config: {
                autoLoad: true
                ,source: 'usersgroups'
                ,renderer: 'listObjIcons'
            }
            ,data: {}
        });

        w.on(
            'setvalue'
            ,function(data){
                if(Ext.isEmpty(data)) {
                    return;
                }

                var sm = this.aclList.getSelectionModel()
                    ,d = data[0]
                    ,rec = this.aclStore.findRecord('user_group_id', d.id, 0, false, false, true);

                if(rec){
                    sm.select([rec]);
                    return;
                }

                var rd = {
                    id: null
                    ,user_group_id: d.id
                    ,name: d.name
                    ,iconCls: d.iconCls
                    ,allow: '0,0,0,0,0,0,0,0,0,0,0,0'
                    ,deny: '0,0,0,0,0,0,0,0,0,0,0,0'
                    ,phantom: true
                };

                this.aclStore.beginUpdate();

                rec = Ext.create(
                    this.aclStore.getModel().getName()
                    ,rd
                );

                this.aclStore.add([rec]);

                this.aclStore.endUpdate();

                this.aclStore.sync();
                sm.select(this.aclStore.getCount()-1);
            }
            ,this
        );
        w.show();
    }

    ,onDeleteClick: function(b, e){
        var ra = this.aclList.getSelectionModel().getSelection();

        if(Ext.isEmpty(ra)) {
            return;
        }

        Ext.Msg.confirm(
            L.Delete
            ,L.DeleteSelectedConfirmationMessage
            ,function(b){
                if(b == 'yes'){
                    this.aclStore.remove(ra);
                    this.aclStore.save();
                }
            }
            ,this
        );
    }

    ,onAclListSelectionChange: function(listView, selections){
        this.permissionsStore.removeAll();
        if(!Ext.isEmpty(selections)) {
            this.reloadPermissionsStore();
        }

        this.updateDeleteAction();
    }

    ,onPermissionNodeClick: function(list, record, item, index, e, eOpts) { //dataView, index, node, e
        var cb = e.getTarget('input');

        if(Ext.isEmpty(record) || Ext.isEmpty(cb) || cb.disabled ) {
            return;
        }
        this.changeAccesses(record, cb.checked ? cb.value : 0);

        this.actions.save.setDisabled(false);
        this.actions.apply.setDisabled(false);
        this.actions.cancel.setDisabled(false);
    }

    ,accessToGroupsData: function(accessRecord, groups){
        var rez = []
            ,allow = accessRecord.get('allow')
            ,deny = accessRecord.get('deny');

        if(!Ext.isArray(allow)) {
            allow = allow.split(',');
        }

        if(!Ext.isArray(deny)) {
            deny = deny.split(',');
        }

        Ext.iterate(
            groups
            ,function(g, gv, obj){
                rez.push( [g, L[g], this.accessToGroupValue(allow, gv), this.accessToGroupValue(deny, gv) ]);
            }
            ,this
        );

        return rez;
    }

    ,accessToGroupValue: function(accessArray, groupBitsArray){
        var lastBit = null
            ,bitsMatch = true
            ,bitsCombinedMatch = false
            ,i = 0;

        while( (i < accessArray.length ) && bitsMatch){
            var currentBit = parseInt(accessArray[i], 10);
            if (groupBitsArray[i] == 1){
                if(Ext.isEmpty(lastBit)){
                    lastBit = currentBit;
                } else if( (currentBit * lastBit) > 0 ){
                    if (currentBit != lastBit) {
                        bitsCombinedMatch = true;
                    }
                } else {
                    bitsMatch = false;
                }
            }
            i++;
        }

        return bitsMatch
            ? ( bitsCombinedMatch
                    ? ( (lastBit < 0) ? -1 : 1 )
                    : lastBit
              )
            : 0;
    }

    ,changeAccesses: function(groupRecord, newValue){
        var r = this.aclList.getSelectionModel().getSelection()[0]; //user or group record

        if(Ext.isEmpty(r)) {
            return;
        }

        var allow = r.get('allow').split(',')
            ,deny = r.get('deny').split(',')
            ,group = this.permissionsStore.accessGroups[groupRecord.get('id')];

        if(Ext.isEmpty(group)) {
            return;
        }

        newValue = parseInt(newValue, 10);

        for (var i = 0; i < group.length; i++) {
            if (group[i] == 1) {
                if (newValue > -1) {
                    if ((allow[i] > -2) && (allow[i] < 2)) {
                        allow[i] = newValue;
                    }
                    if (deny[i] > -2) {
                        deny[i] = 0;
                    }
                }
                if (newValue < 1 ){
                    if ((deny[i] > -2) && (deny[i] < 2)) {
                        deny[i] = newValue;
                    }

                    if(allow[i] < 2) {
                        allow[i] = 0;
                    }
                }
            }
        }

        r.set('allow', allow.join(','));
        r.set('deny', deny.join(','));

        this.reloadPermissionsStore();
    }

    ,reloadPermissionsStore: function(){
        var data = []
            ,sr = this.aclList.getSelectionModel().getSelection();

        if(!Ext.isEmpty(sr)) {
            data = this.accessToGroupsData(sr[0], this.permissionsStore.accessGroups);
        }

        this.permissionsStore.loadData(data);
    }

    ,onSavePermissionsClick: function(){
        this.aclStore.sync();

        this.setReadOnly(true);
    }

    ,onApplyPermissionsClick: function(){
        this.aclStore.sync();
        this.actions.save.setDisabled(true);
        this.actions.apply.setDisabled(true);
        this.actions.cancel.setDisabled(true);
    }

    ,onCancelPermissionsChangeClick: function(b, e){
        this.aclStore.rejectChanges();
        this.reloadPermissionsStore();
        this.actions.save.setDisabled(true);
        this.actions.apply.setDisabled(true);
        this.actions.cancel.setDisabled(true);
    }

    ,onRemoveChildPermissionsClick: function(b, e){
        Ext.Msg.confirm(
            L.Confirmation
            ,'Are you sure you want to remove child permissions and inherit all permissions from parent?'
            ,function (button){
                if(button == 'yes'){
                    CB_Security.removeChildPermissions(
                        {id: this.data.id}
                        ,function(r, e) {
                            Ext.Msg.alert(L.Info, 'Child permissions revoked successfully.');
                        }
                    );
                }
            }
            ,this
        );
    }

    ,onCbInheritLabelClick: function(){
        cb.setValue(!cb.getValue());
    }

    ,onCbInheritClick: function(cb, checked){
        if(cb.settingValue) {
            return;
        }
        if(checked){
            Ext.Msg.confirm(
                L.Confirmation
                ,'Are you sure you want to remove current rules and inherit all permissions from parent?'
                ,this.onCbInheritSet
                ,this
            );
        } else {
            Ext.Msg.show({
                title: L.Confirmation
                ,msg: 'Warning: If you proceed, inheritable parent permissions will no longer propagate to this object.<br />'+
                    '<br />'+
                    '- Click Add to convert and add inherited parent permissions as explicit permissions to this object.<br />'+
                    '- Click Remove to remove inherited parent permissions from this object.<br />'+
                    '- Click Cancel if you do not want to modify inheritance settings at this time.<br />'

                ,buttons: Ext.MessageBox.YESNOCANCEL
                ,buttonText: {
                    yes: L.Add
                    ,no: L.Remove
                }
                ,scope: this
                ,fn: this.onCbInheritRemove
                ,icon: Ext.MessageBox.WARNING
            });
        }
    }

    ,onCbInheritSet: function(button){
        if(button == 'yes'){
            this.getEl().mask(L.loading, 'x-mask-loading');
            CB_Security.setInheritance(
                {
                    id: this.data.id
                    ,inherit: true
                }
                ,this.onSetInheritanceProcess
                ,this
            );
        } else{
            this.cbInherit.settingValue = true;
            this.cbInherit.setValue(false);
            delete this.cbInherit.settingValue;
        }
    }

    ,onCbInheritRemove: function(button, text, cfg){
        if( (button == 'yes') || (button == 'no') ){
            this.getEl().mask(L.loading, 'x-mask-loading');
            CB_Security.setInheritance(
                {
                    id: this.data.id
                    ,inherit: false
                    ,copyRules: button
                }
                ,this.onSetInheritanceProcess
                ,this
            );
        } else{
            this.cbInherit.settingValue = true;
            this.cbInherit.setValue(true);
            delete this.cbInherit.settingValue;
        }
    }

    ,onSetInheritanceProcess: function(r, e){
        this.getEl().unmask();
        this.aclStore.load();
    }
});
