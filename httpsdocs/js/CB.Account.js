Ext.namespace('CB');

Ext.define('CB.Account', {
    extend: 'Ext.Window'

    ,alias: 'CBAccount'

    ,xtype: 'CBAccount'

    ,title: L.Account
    ,border: false
    ,closable: true
    ,minimizable: true
    ,width: 850
    ,height: 600

    ,initComponent: function() {

        this.menu = new Ext.Panel({
            region: 'west'
            ,collapsible: false
            ,width: 130
            ,animCollapse: false
            ,plain: true
            ,cls: 'account-menu'
            ,style: 'border-right: 1px solid #cacaca'
            ,border: false
            ,layout: 'anchor'
            ,defaults: {
                enableToggle: true
                ,toggleGroup: 'menu'
                ,allowDepress: false
                ,scope: this
                ,handler: this.onMenuButtonClick
            }
            ,items:[
                {
                    xtype: 'button'
                    ,html: L.Profile
                    ,anchor: '100%'
                    ,pressed: true
                },{
                    xtype: 'button'
                    ,html: L.Security
                    ,anchor: '100%'
                }
            ]
        });

        this.cards = new Ext.Panel({
            region: 'center'
            ,border: false
            ,tbarCssClass: 'x-panel-gray'
            ,layout:'card'
            ,activeItem: 0
            ,items: [{
                xtype: 'CBProfileForm'
                ,listeners: {
                    scope: this
                    ,change: function(){ /*this.autoCloseTask.delay(1000*60*5);/**/ }
                }
            },{
                xtype: 'CBSecurityForm'
                ,listeners: {
                    scope: this
                    ,change: function(){ /*this.autoCloseTask.delay(1000*60*5);/**/ }
                }
            }
            ]
            ,deferredRender: true
        });

        Ext.apply(
            this
            ,{
                iconCls: 'icon-user-' + App.loginData.sex
                ,layout: 'border'
                ,items:[
                    this.menu
                    ,this.cards

                ]
            }
        );

        this.callParent(arguments);

        /* autoclose form if no activity in 5 minutes */
        // this.autoCloseTask = new Ext.util.DelayedTask(this.destroy, this);
        // this.autoCloseTask.delay(1000*60*5);

        CB_User.getAccountData( this.onGetData, this);

    }

    ,onGetData: function(r, e){
        if(!r) {
            return;
        }

        if(r.success !== true) {
            if(r.verify === true){
                // show verification form
                var w = new CB.VerifyPassword({
                    listeners:{
                        scope: this
                        ,beforeclose: function(cmp){
                            if(w.success !== true) {
                                this.destroy();
                            } else {
                                CB_User.getAccountData( this.onGetData, this);
                            }
                        }
                    }
                });
                w.show();
            } else {
                this.destroy();
            }
            return;
        }
        this.cards.items.getAt(0).loadData(r.profile);
        this.cards.items.getAt(1).loadData(r.security);
    }

    ,onMenuButtonClick: function(b, e){
        this.cards.getLayout().setActiveItem(this.menu.items.indexOf(b));
        // this.autoCloseTask.delay(1000*60*5);
    }
}
);

Ext.define('CB.ProfileForm', {
    extend: 'Ext.form.FormPanel'
    ,alias: 'widget.CBProfileForm'

    ,border: false
    ,fileUpload: true
    ,scrollable: true
    ,bodyPadding: 10
    ,data: {}
    ,initComponent: function(){

        this.data = this.config.data;

        this.objectsStore = new CB.DB.ObjectsStore();

        this.photoField = new Ext.form.field.File({
            cls: 'fl'
            ,style: 'position:absolute;width:1px;height:1px;opacity:0;top:-100px'
            ,buttonOnly: true
            ,name: 'photo'
            ,border: false
            ,listeners:{
                scope: this
                ,afterrender: function(c){
                    c.button.fileInputEl.on('change', this.onPhotoChanged, this);
                }
            }
        });

        this.photoView = new Ext.DataView({
            tpl: ['<tpl for="."><div>'
                ,'<img width="70" height="70" class="user-photo-field2 click icon-user70-{sex}" src="/' + App.config.coreName + '/photo/{id}.png?{[ Ext.Date.format(new Date(), "His") ]}">'
                ,'</div>'
                ,'<div><a name="change" class="click">'+L.Change+'</a> &nbsp; <a name="remove" class="click">'+L.Delete+'</a></div>'
                ,'</tpl>'
            ]
            ,data: [{}]
            ,itemSelector:'.none'
            ,autoHeight: true
            ,listeners:{
                scope: this
                ,containerclick: this.onPhotoContainerClick
            }
        });

        var fields = [
            {
                xtype: 'textfield'
                ,name: 'first_name'
                ,fieldLabel: L.FirstName
                ,listeners: {scope: this, change: this.onChange }
            },{
                xtype: 'textfield'
                ,name: 'last_name'
                ,fieldLabel: L.LastName
                ,listeners: {scope: this, change: this.onChange }
            },{
                xtype: 'combo'
                ,name: 'sex'
                ,hiddenName: 'sex'
                ,fieldLabel: L.Gender
                ,queryMode: 'local'
                ,triggerAction: 'all'
                ,editable: false
                ,store: CB.DB.sex
                ,valueField: 'id'
                ,displayField: 'name'
            },{
                xtype: 'textfield'
                ,name: 'email'
                ,fieldLabel: L.PrimaryEmail
                ,vtype: 'email'
            },{
                xtype: 'combo'
                ,name: 'country_code'
                ,hiddenName: 'country_code'
                ,fieldLabel: L.Country
                ,queryMode: 'local'
                ,triggerAction: 'all'
                ,editable: true
                ,forceSelection: true
                ,typeAhead: true
                ,store: CB.DB.phone_codes
                ,valueField: 'code'
                ,displayField: 'name'
                ,value: null
                ,width: 250
            },{
                xtype: 'textfield'
                ,name: 'phone'
                ,fieldLabel: L.Phone
                ,allowDecimals: false
                ,allowNegative: false
            },{

                html: '&nbsp;'
                ,border: false
            },{
                xtype: 'combo'
                ,name: 'language_id'
                ,hiddenName: 'language_id'
                ,fieldLabel: L.Language
                ,queryMode: 'local'
                ,triggerAction: 'all'
                ,editable: true
                ,forceSelection: true
                ,typeAhead: true
                ,store: CB.DB.languages
                ,valueField: 'id'
                ,displayField: 'name'
            },{
                xtype: 'combo'
                ,name: 'timezone'
                ,hiddenName: 'timezone'
                ,fieldLabel: L.Timezone
                ,queryMode: 'local'
                ,triggerAction: 'all'
                ,editable: false
                ,store: CB.DB.timezones
                ,valueField: 'id'
                ,displayField: 'caption'
                ,value: null
            },{
                xtype: 'combo'
                ,name: 'short_date_format'
                ,hiddenName: 'short_date_format'
                ,fieldLabel: L.DateFormat
                ,queryMode: 'local'
                ,triggerAction: 'all'
                ,editable: false
                ,store: CB.DB.shortDateFormats
                ,valueField: 'id'
                ,displayField: 'name'
                ,value: null
            }
        ];

        if(App.loginData.id != this.data.id) {
            if(App.loginData.admin || App.loginData.cfg.canAddUsers) {
                fields.push({
                    xtype: 'checkbox'
                    ,name: 'canAddUsers'
                    ,fieldLabel: Ext.valueFrom(L.CanAddUsers, 'Can add users')
                    ,inputValue: true
                });
            }
            if(App.loginData.admin || App.loginData.cfg.canAddGroups) {
                fields.push({
                    xtype: 'checkbox'
                    ,name: 'canAddGroups'
                    ,fieldLabel: Ext.valueFrom(L.CanAddGroups, 'Can add groups')
                    ,inputValue: true
                });
            }
        }

        Ext.apply(this,{
            items:[{
                border: false
                ,layout: 'hbox'
                ,layoutConfig: {
                    align: 'stretchmax'
                }
                ,autoHeight: true
                ,items: [{
                    autoHeight: true
                    ,border: false
                    ,width: 500
                    ,items: {
                            xtype: 'fieldset'
                            ,padding: 10
                            ,autoHeight: true
                            ,labelWidth: 140
                            ,defaults:{
                                width: 250
                                ,matchFieldWidth: false
                                ,listeners: {
                                    scope: this
                                    ,change: this.onChange
                                    ,select: this.onChange
                                }
                            }
                            ,items: fields
                        }
                },{
                    xtype: 'panel'
                    ,width: 300
                    ,padding: 45
                    ,border: false
                    ,items: [
                        this.photoField
                        ,this.photoView
                    ]
                }

                ]
            },{
                xtype: 'CBVerticalEditGrid'
                ,refOwner: this
                ,width: 500
                ,style: 'margin-bottom: 50px'
                ,autoHeight: true
                ,viewConfig: {
                    forceFit: true
                    ,autoFill: true
                }

            }
            ]
            ,buttonAlign: 'left'
            ,buttons: [{
                text: L.Save
                ,scope: this
                ,handler: this.onSaveClick
            },{
                text: L.Reset
                ,scope: this
                ,handler: this.onResetClick
            }

            ]
            ,listeners: {
                scope: this
                ,afterrender: this.onAfterRender
                ,change: this.onChange
            }
        });

        this.callParent(arguments);

        this.grid = this.items.getAt(1);

        if(CB.DB.countries.getCount() === 0) {
            CB.DB.countries.load();
        }

        if(CB.DB.timezones.getCount() === 0) {
            CB.DB.timezones.load();
        }

        this.enableBubble(['verify']);
    }
    ,onAfterRender: function(cmp){

    }

    ,loadData: function(data){
        if(!Ext.isEmpty(data.assocObjects) && Ext.isArray(data.assocObjects)) {
            for (var i = 0; i < data.assocObjects.length; i++) {
                data.assocObjects[i].iconCls = getItemIcon(data.assocObjects[i]);
            }
            this.objectsStore.loadData(data.assocObjects);
            delete data.assocObjects;
        }

        if(Ext.isDefined(data.language_id)) {
            data.language_id = parseInt(data.language_id, 10);
        }

        this.data = data;
        this.getForm().setValues(data);
        this.grid.reload();
        this.photoView.update([{id: this.data.id }]);
        // this.syncSize();
        this.setDirty(false);
    }

    ,onPhotoContainerClick: function(cmp, e, eOpts){ //w, idx, el, ev
        if(!e) {
            return;
        }
        var target = e.getTarget();

        if((target.localName == "img") || (target.name === 'change')) {
            return this.photoField.button.fileInputEl.dom.click();
        }

        if (target.name === 'remove') {
            return this.onPhotoRemoveClick();
        }
    }

    ,onPhotoChanged: function(ev, el, o){
        if(Ext.isEmpty(this.photoField.getValue())) {
            return;
        }
        var form = this.getForm();

        form.api = {submit: CB_User.uploadPhoto};

        form.submit({
            clientValidation: false
            ,params: {
                    id: this.data.id
                }
            ,scope: this
            ,success: function(form, action) {
                this.photoField.reset();
                this.photoField.button.fileInputEl.on('change', this.onPhotoChanged, this);
                this.photoView.update([{id: this.data.id }]);
            }
            ,failure: App.formSubmitFailure
        });
    }

    ,onPhotoRemoveClick: function(){
        Ext.Msg.confirm(
            L.Confirmation
            ,L.RemovePhotoConfirm
            ,function(b, e){
                if(b === 'yes'){
                    CB_User.removePhoto( { id: this.data.id }, function(){
                        this.photoView.update([{id: this.data.id }]);
                    }, this);
                }
            }
            ,this
        );
    }

    ,onSaveClick: function(){
        delete this.data.canAddUsers;
        delete this.data.canAddGroups;
        Ext.apply(this.data, this.getForm().getValues());

        if (this.data.phone == this.down('[name="phone"]').emptyText) {
            this.data.phone = null;
        }

        this.grid.readValues();
        CB_User.saveProfileData(this.data, this.onSaveProcess, this);
    }

    ,onSaveProcess: function(r, e){
        if (!r) {
            return;
        }

        if(r.success !== true) {
            if(r.verify) {
                this.fireEvent('verify', this);
            } else if(!Ext.isEmpty(r.msg)) {
                Ext.Msg.alert(L.Error, r.msg);
            }
            return;
        }
        this.setDirty(false);
        this.fireEvent('savesuccess', this, e);
        App.fireEvent('userprofileupdated', this.data, e);
    }

    ,onResetClick: function(){
        this.getForm().reset();
        this.loadData(this.data);
    }

    ,onChange: function(){
        this.setDirty(true);
    }

    ,setDirty: function(dirty){
        this._isDirty = (dirty !== false);

        var bbar = this.dockedItems.getAt(0);
        bbar.items.getAt(0).setDisabled(!this._isDirty);
        bbar.items.getAt(1).setDisabled(!this._isDirty);
    }
});


Ext.define('CB.SecurityForm', {
    extend: 'Ext.form.FormPanel'
    ,alias: 'widget.CBSecurityForm'

    ,border: false
    ,scrollable: true

    ,initComponent: function(){

        Ext.apply(this,{
            padding: 10
            ,items: [{
                title: L.Password
                ,componentCls: 'x-panel-header panel-header-nobg block-header'
                ,border: false
                ,defaults: {style: 'padding: 5px 25px'}
                ,items: [
                    {
                        xtype: 'displayfield'
                        ,name: 'passwordchanged'
                        ,value: L.PasswordNeverChanged
                    },{
                        xtype: 'displayfield'
                        ,frame: false
                        ,value: '<a>'+L.ChangePassword+'</a>'
                        ,listeners: {
                            scope: this
                            ,afterrender: function(cmp, eOpts) {
                                cmp.getEl().on('click', this.onChangePasswordClick, this);
                            }
                        }
                    }
                ]
            },{
                title: L.RecoveryOptions
                ,componentCls: 'x-panel-header panel-header-nobg block-header'
                ,border: false
                ,style: 'margin-top: 20px'
                ,defaults: {style: 'padding: 5px 0 15px 25px', border: false}
                ,items: [
                    {
                        xtype: 'fieldcontainer'
                        ,layout: 'hbox'
                        ,hidden: true
                        ,items: [{
                            xtype: 'checkbox'
                            ,name: 'recovery_mobile'
                            ,listeners: {
                                scope: this
                                // ,check: this.onCheckboxCheck
                            }
                        },{
                            xtype: 'displayfield'
                            ,cls: 'fwB'
                            ,value: L.Mobile
                        }
                        ]
                    },{
                        hidden: true
                        ,name: 'recovery_mobile_panel'
                        ,layout: 'form'
                        ,defaults: {
                            width: 200
                            ,listeners: {
                                scope: this
                                ,change: this.onChange
                                ,select: this.onChange
                            }
                        }
                        ,items: [{
                            xtype: 'combo'
                            ,name: 'country_code'
                            ,hiddenName: 'country_code'
                            ,fieldLabel: L.Country
                            ,queryMode: 'local'
                            ,triggerAction: 'all'
                            ,editable: true
                            ,forceSelection: true
                            ,typeAhead: true
                            ,store: CB.DB.phone_codes
                            ,valueField: 'code'
                            ,displayField: 'name'
                        },{
                            xtype: 'numberfield'
                            ,name: 'phone_number'
                            ,fieldLabel: L.PhoneNumber
                            ,allowDecimals: false
                            ,allowNegative: false
                        }
                        ]
                    },{
                        xtype: 'fieldcontainer'
                        ,layout: 'hbox'
                        ,items: [{
                            xtype: 'checkbox'
                            ,name: 'recovery_email'
                            ,boxLabel: L.Email
                            ,inputValue: 1
                            ,listeners: {
                                scope: this
                                ,change: this.onCheckboxCheck
                            }
                        }
                        ]
                    },{
                        hidden: true
                        ,name: 'recovery_email_panel'
                        ,layout: 'form'
                        ,width: 350
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: 'Email'
                            ,name: 'email'
                            ,vtype: 'email'
                            ,width: 200
                            ,listeners: {
                                scope: this
                                ,change: this.onChange
                            }
                        }
                        ]
                    },{
                        xtype: 'fieldcontainer'
                        ,layout: 'hbox'
                        ,hidden: true
                        ,items: [{
                            xtype: 'checkbox'
                            ,name: 'recovery_question'
                            ,listeners: {
                                scope: this
                                // ,check: this.onCheckboxCheck
                            }
                        },{
                            xtype: 'displayfield'
                            ,cls: 'fwB'
                            ,value: L.SecurityQuestion
                        }
                        ]
                    },{
                        hidden: true
                        ,name: 'recovery_question_panel'
                        ,layout: 'form'
                        ,defaults: {
                            width: 200
                            ,listeners: {
                                scope: this
                                ,change: this.onChange
                                ,select: this.onChange
                            }
                        }
                        ,items: [{
                            xtype: 'combo'
                            ,store: CB.DB.securityQuestions
                            ,name: 'question_idx'
                            ,hiddenName: 'question_idx'
                            ,fieldLabel: L.Question
                            ,queryMode: 'local'
                            ,triggerAction: 'all'
                            ,editable: false
                            ,forceSelection: true
                            //,typeAhead: true
                            ,valueField: 'id'
                            ,displayField: 'text'
                            ,width: 400
                        },{
                            xtype: 'textfield'
                            ,fieldLabel: L.Answer
                            ,name: 'answer'
                            //,inputType: 'password'
                        }
                        ]
                    }
                ]
                ,buttonAlign: 'left'
                ,buttons: [{
                    text: L.Save
                    ,itemId: 'saveButton'
                    ,style: 'margin-left: 18px'
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onSaveClick
                },{
                    text: L.Reset
                    ,itemId: 'resetButton'
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onResetClick
                }

                ]
            },{
                title: L.TSV
                ,componentCls: 'x-panel-header panel-header-nobg block-header'
                ,border: false
                ,style: 'margin-top: 20px'
                ,defaults: {style: 'padding: 5px 25px', border: false}
                ,buttonAlign: 'left'
                ,items: [{
                    xtype: 'displayfield'
                    ,itemId: 'tsvStatusText'
                    ,value: L.Status +': <span class="cG">'+ L.Disabled + '</span>'
                }]

                ,dockedItems: [
                    {
                        xtype: 'toolbar'
                        ,dock: 'bottom'
                        ,ui: 'footer'
                        ,layout: {
                            type: 'hbox'
                            ,pack: 'left'
                        }
                        //,defaults: {minWidth: minButtonWidth}
                        ,items: [
                            {
                                xtype: 'displayfield'
                                ,value: '<a>'+L.Enable+'</a>'
                                ,itemId: 'btnEnableTsv'
                                ,width: 50
                                ,listeners:{
                                    scope: this
                                    ,afterrender: function(c){
                                        c.getEl().on('click', this.enableTSV, this);
                                    }
                                }
                            },{
                                xtype: 'displayfield'
                                ,value: '<a>'+L.Disable+'</a>'
                                ,itemId: 'btnDisableTsv'
                                ,width: 50
                                ,listeners:{
                                    scope: this
                                    ,afterrender: function(c){
                                        c.getEl().on('click', this.disableTSV, this);
                                    }
                                }
                                ,hidden: true
                            },{
                                xtype: 'displayfield'
                                ,value: '<a>'+L.Change+'</a>'
                                ,itemId: 'btnChangeTsv'
                                ,width: 50
                                ,listeners:{
                                    scope: this
                                    ,afterrender: function(c){
                                        c.getEl().on('click', this.enableTSV, this);
                                    }
                                }
                                ,hidden: true
                            }
                       ]
                    }
                ]
            }
            ]
        });

        this.callParent(arguments);

        this.saveButton = this.down('#saveButton');
        this.resetButton = this.down('#resetButton');
    }

    ,loadData: function( data ){
        // this.items.getAt(0).update(data)
        this.data = data;
        if(!Ext.isEmpty(data.password_change)) {
            this.down('[name="passwordchanged"]').setValue(L.PasswordChanged+': '+data.password_change);
        }

        var cb = this.items.getAt(1).items.first().items.first();
        cb.setValue(data.recovery_mobile === true);
        this.down('[name="country_code"]').setValue( Ext.valueFrom(data.country_code, null) );
        this.down('[name="phone_number"]').setValue( Ext.valueFrom(data.phone_number, null) );

        cb = this.items.getAt(1).items.getAt(2).items.first();
        cb.setValue(data.recovery_email === true);
        this.down('[name="email"]').setValue( Ext.valueFrom(data.email, null) );

        cb = this.items.getAt(1).items.getAt(4).items.first();
        cb.setValue(data.recovery_question === true);
        this.down('[name="question_idx"]').setValue( Ext.valueFrom(data.question_idx, null) );
        this.down('[name="answer"]').setValue( Ext.valueFrom(data.answer, null) );

        this.updateTSVStatus();

        this.setDirty(false);
    }

    ,onCheckboxCheck: function(cb){
        var p = this.down('[name="' + cb.name + '_panel"]');
        p.setVisible(cb.checked);
        //this.data[cb.name] = cb.checked;
        this.setDirty();
    }

    ,onChangePasswordClick: function(b){
        var pw = new CB.ChangePasswordWindow({
            data: {id: App.loginData.id}
            ,listeners: {
                scope: this
                ,passwordchanged: this.onPasswordChanged
            }
        });
        pw.show();
    }

    ,onPasswordChanged: function(w){
        this.down('[name="passwordchanged"]').setValue(L.PasswordChanged+': '+L.today);
    }

    ,onSaveClick: function(){
        cb = this.items.getAt(1).items.first().items.first();
        this.data.recovery_mobile = cb.getValue();
        this.data.country_code = this.down('[name="country_code"]').getValue();
        this.data.phone_number = this.down('[name="phone_number"]').getValue();

        cb = this.items.getAt(1).items.getAt(2).items.first();
        this.data.recovery_email = cb.getValue();
        this.data.email = this.down('[name="email"]').getValue();

        cb = this.items.getAt(1).items.getAt(4).items.first();
        this.data.recovery_question = cb.getValue();
        this.data.question_idx = this.down('[name="question_idx"]').getValue();
        this.data.answer = this.down('[name="answer"]').getValue();

        CB_User.saveSecurityData(this.data, this.onSaveProcess, this);
    }

    ,onSaveProcess: function(r, e){
        if(!r || (r.success !== true)) {
            return;
        }

        this.setDirty(false);
    }

    ,onResetClick: function(){
        this.loadData(this.data);
    }

    ,onChange: function(){
        this.setDirty(true);
    }

    ,setDirty: function(dirty){
        this._isDirty = (dirty !== false);
        this.saveButton.setDisabled(!this._isDirty);
        this.resetButton.setDisabled(!this._isDirty);
    }

    ,enableTSV: function(b, e){
        var data = Ext.valueFrom(this.data.TSV, {});

        data.country_code = Ext.valueFrom(data.country_code, this.data.country_code );
        data.phone_number = Ext.valueFrom(data.phone_number, this.data.phone_number );

        var w = new CB.TSVWindow({
            data: data
            ,listeners:{
                scope: this
                ,tsvchange: this.onTSVChange
            }
        });

        w.show();
    }

    ,onTSVChange: function(w, tsv){
        if(Ext.isEmpty(this.data['TSV'])) {
            this.data.TSV = {};
        }
        this.data.TSV.method = tsv;
        this.updateTSVStatus();
    }

    ,updateTSVStatus: function(){
        var text = '<span class="cG">'+ L.Disabled + '</span>';

        if(Ext.isEmpty(this.data.TSV)) {
            this.data.TSV = {};
        }

        switch(this.data.TSV.method){
            case 'ga':
                text = 'Mobile Google Aplication';
                break;

            case 'sms':
                text = 'Google Authentication using SMS';
                break;

            case 'ybk':
                text = 'Yubikey';
                break;
        }

        this.down('#tsvStatusText').setValue(L.Status+': ' + text);
        this.down('#btnEnableTsv').setVisible(Ext.isEmpty(this.data.TSV.method));
        this.down('#btnDisableTsv').setVisible(!Ext.isEmpty(this.data.TSV.method));
        this.down('#btnChangeTsv').setVisible(!Ext.isEmpty(this.data.TSV.method));
    }

    ,disableTSV: function(){
        Ext.Msg.show({
            title: L.Confirm
            ,message: L.DisableTSVConfirmation
            ,buttons: Ext.Msg.YESNO
            ,icon: Ext.window.MessageBox.INFO
            ,scope: this
            ,fn: function(b, e){
                if(b === 'yes'){
                    CB_User.disableTSV(
                        function(r, e){
                            if(r && (r.success === true)) {
                                delete this.data.TSV.method;
                                this.updateTSVStatus();
                            }
                        }
                        ,this
                    );
                }
            }
        });
    }
});


/*
2-step verification
Keep the bad guys out of your account by using both your password and your phone.
 */
Ext.define('CB.TSVWindow', {
    extend: 'Ext.Window'
    ,modal: true
    ,title: L.TSV
    ,autoWidth: true
    ,autoHeight: true
    ,border: false
    ,iconCls: 'icon-key'
    ,layout: 'card'

    ,initComponent: function() {
        Ext.apply(this, {
            activeItem: 0
            ,bodyBorder: false
            ,items: [{
                items: [{
                    xtype: 'displayfield'
                    ,style: 'padding: 10px; font-size: 20px;'
                    ,value: 'Select authentication method'
                },{
                    xtype: 'displayfield'
                    ,value: '<a class="click" name="ga">Google Authenticator</a>'
                    ,style: 'padding:10px'
                    ,name: 'ga'
                    ,listeners:{
                        scope: this
                        ,afterrender: function(c){
                            c.getEl().on('click', this.onTSVMechanismClick, this);
                        }
                    }
                // },{
                //     xtype: 'button'
                //     ,html: '<a>Sms message</a>'
                //     ,style: 'padding:10px'
                //     ,name: 'sms'
                //     ,scope: this
                //     ,handler: this.onTSVMechanismClick
                },{
                    xtype: 'displayfield'
                    ,value: '<a class="click" name="ybk">Yubikey</a>'
                    ,style: 'padding:10px'
                    ,name: 'ybk'
                    ,listeners:{
                        scope: this
                        ,afterrender: function(c){
                            c.getEl().on('click', this.onTSVMechanismClick, this);
                        }
                        ,loaded: this.onViewLoaded
                        ,verifyandsave: this.onVerifyAndSave
                    }
                }
                ]
            },{
                xtype: 'TSVgaForm'
                ,itemId: 'ga'
                ,listeners: {
                    scope: this
                    ,loaded: this.onViewLoaded
                    ,verifyandsave: this.onVerifyAndSave
                }
            // },{
            //     xtype: 'TSVsmsForm'
            },{
                xtype: 'TSVybkForm'
                ,itemId: 'ybk'
                ,listeners: {
                    scope: this
                    ,loaded: this.onViewLoaded
                    ,verifyandsave: this.onVerifyAndSave
                }
            }]
        });

        this.callParent(arguments);

        this.form = this.down('form');
    }

    ,onTSVMechanismClick: function(ev, el){
        this.TSVmethod = el.name;

        this.getLayout().setActiveItem(el.name);
        this.getLayout().activeItem.prepareInterface(this.data);
    }

    ,onVerifyAndSave: function(data){
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_User.enableTSV({
            method: this.TSVmethod
            ,data: data
        }, this.processEnableTSV, this);
    }

    ,onYubikeySaveClick: function(){
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_User.TSVSaveYubikey( { code: this.getLayout().activeItem.buttons[1].getValue() }, this.processEnableTSV, this);
    }

    ,processEnableTSV: function(r, e){
        this.getEl().unmask();

        if(r && (r.success === true)) {
            this.fireEvent('tsvchange', this, this.TSVmethod);
            this.destroy();
        } else {
            this.getLayout().activeItem.showError(r.msg);
            // this.syncSize();
        }
    }

    ,onViewLoaded: function() {
        this.center();
        this.center();

    }
}
);


Ext.define('CB.TSVgaForm', {
    extend: 'Ext.Panel'
    ,alias: 'widget.TSVgaForm'

    ,style: 'background-color: #fff'
    ,width:500

    ,initComponent: function(){
        Ext.apply(this, {
            bodyStyle: 'padding: 10px'
            ,items: [{
                xtype: 'displayfield'
                ,style: 'font-size: 20px; padding-bottom:15px'
                ,value: 'Set up Google Authenticator'
            },{
                autoHeight: true
                ,autoWidth:true
                ,border: false
                ,tpl: [
                    '<tpl for="data">'
                    ,'<p class="fwB">Install the Google Authenticator app for your phone</p>'
                    ,'<ol class="ol p10">'
                    ,'<li> On your phone, open a web browser. </li>'
                    ,'<li> Go to <span class="fwB">m.google.com/authenticator</span>. </li>'
                    ,'<li> Download and install the Google Authenticator application. </li>'
                    ,'</ol>'
                    ,'<p class="fwB"> Now open and configure Google Authenticator. </p>'
                    ,'<br /><p>Scan following Barcode to register the application automaticly:<p>'
                    ,'<div class="taC p10">'
                    ,'    <img src="{url}" width="100" height="100" />'
                    ,'</div>'
                    ,'<p> Or use the following secret key to register the aplication manually:</p>'
                    ,'<div class="taC p10 bgcY">'
                    ,'    <div class="fs14 fwB" dir="ltr">{sd}</div>'
                    ,'    <div class="fs10 cG">Spaces don\'t matter.</div>'
                    ,'</div><br />'
                    ,'<p> Once you manually entered and saved your key, enter the 6-digit verification code generated<br /> by the Authenticator app. </p>'
                    ,'</tpl>'
                ]
                ,data: {}
            }
            ]
            ,buttonAlign: 'left'
            ,buttons: [{
                    xtype: 'displayfield'
                    ,value: 'Code: '
                },{
                    xtype: 'textfield'
                    ,name: 'code'
                    ,width: '50'
                    ,enableKeyEvents: true
                    ,listeners: {
                        scope: this
                        ,keyup: function(field, e){
                            this.down('[name="btnVS"]').setDisabled(Ext.isEmpty(field.getValue()));
                        }
                    }
                },{
                    xtype: 'button'
                    ,text: L.VerifyAndSave
                    ,name: 'btnVS'
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onVerifyAndSaveClick
                },{
                    xtype: 'displayfield'
                    ,style: 'padding: 0 0 0 20px'
                    ,name: 'errorMsg'
                    ,cls: 'cR'
                    ,value: ''
                    ,hidden: true
                }
            ]
        });

        this.callParent(arguments);
        // this.enableBubble(['verifyandsave']);
    }

    ,prepareInterface: function(data){
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_User.getTSVTemplateData('ga', this.processGetTSVTemplateData, this);
    }

    ,processGetTSVTemplateData: function(r, e){
        this.getEl().unmask();

        if(!r || (r.success !== true)) {
            return;
        }

        var p = this.items.getAt(1);

        p.data = r;
        p.update(r);

        this.down('[name="code"]').focus();
        this.fireEvent('loaded', this, e);
    }

    ,onVerifyAndSaveClick: function(){
        this.fireEvent('verifyandsave', {
            code: this.down('[name="code"]').getValue()
        });
    }

    ,showError: function(msg){
        if(Ext.isEmpty(msg)) {
            msg = 'The code is incorrect. Try again';
        }
        msg = '<img class="icon icon-exclamation fl" style="margin-right: 15px" src="/css/i/s.gif">'+ msg;

        var t = this.down('[name="errorMsg"]');
        t.setValue(msg);
        t.setVisible(true);
    }
});

Ext.define('CB.TSVsmsForm', {
    extend: 'Ext.form.FormPanel'
    ,monitorValid: true
    ,autoWidth: true
    ,autoHeight: true
    ,labelWidth: 120
    ,buttonAlign: 'left'
    ,cls: 'bgcW'

    ,initComponent: function(){
        Ext.apply(this, {
            items: [{
                xtype: 'displayfield'
                ,hideLabel: true
                ,value: L.SpecifyPhone
                ,style: 'font-size: 20px'
            },{
                xtype: 'displayfield'
                ,hideLabel: true
                ,value: L.SpecifyPhoneMsg
                ,style: 'padding: 15px 0'
            },{
                xtype: 'combo'
                ,name: 'country_code'
                ,hiddenName: 'country_code'
                ,fieldLabel: L.Country
                ,queryMode: 'local'
                ,triggerAction: 'all'
                ,editable: true
                ,forceSelection: true
                ,typeAhead: true
                ,store: CB.DB.phone_codes
                ,valueField: 'code'
                ,displayField: 'name'
                ,width: 200
                ,allowBlank: false
            },{
                xtype: 'numberfield'
                ,name: 'phone_number'
                ,fieldLabel: L.PhoneNumber
                ,allowDecimals: false
                ,allowNegative: false
                ,width: 200
                ,allowBlank: false
            }
            ]
            ,buttons: [{
                text: L.Verify
                ,formBind: true
                ,scope: this
                ,handler: this.onVerifyPhoneClick
            },{
                text: L.SendCode
                ,type: 'submit'
                ,formBind: true
                ,scope: this
                ,handler: this.onSendCodeClick
            }
            ]
        });

        this.callParent(arguments);
    }
    ,prepareInterface: function(data){
        this.getForm().setValues(data);
        // this.syncSize();
    }
    ,onVerifyPhoneClick: function(){
        if(this.form.getForm().isValid()){
            this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
            CB_User.verifyPhone(
                this.form.getForm().getValues()
                ,this.processPhoneVerification
                ,this
            );
        }
    }
    ,onSendCodeClick: function(b, e){
    }
    ,processPhoneVerification: function(r, e){
        this.getEl().unmask();
    }
    ,showError: function(msg){}

});

Ext.define('CB.TSVybkForm', {
    extend: 'Ext.form.FormPanel'
    ,alias: 'widget.TSVybkForm'
    ,xtype: 'CBTSVybkForm'

    ,monitorValid: true
    ,autoWidth: true
    ,autoHeight: true
    ,labelWidth: 70
    ,buttonAlign: 'left'
    ,cls: 'bgcW'

    ,initComponent: function(){
        Ext.apply(this, {
            bodyStyle: 'padding: 10px'
            ,items: [{
                xtype: 'displayfield'
                ,hideLabel: true
                ,style: 'font-size: 20px; padding-bottom:15px'
                ,value: 'Set up Yubikey Authenticator'
            },{
                autoHeight: true
                ,autoWidth:true
                ,border: false
                ,style: 'font-size: 13px; padding-bottom:15px'
                ,tpl: ['<tpl for=".">'
                    ,'<ol>'
                    ,'<li>1. Insert your YubiKey into a USB port.</li>'
                    ,'<li>2. Enter your email in the email field. </li>'
                    ,'<li>3. Select/Click the Code field, and touch the YubiKey button. </li>'
                    ,'<li>4. Click Save.</li>'
                    ,'</ol>'
                    ,'<br />'
                    ,'<p>Note that it may take up until 5 minutes until all validation servers know about your newly generated client.</p>'
                    ,'</tpl>'
                ]
                ,data: {}
            },{
                xtype: 'textfield'
                ,vtype: 'email'
                ,name: 'email'
                ,fieldLabel: L.Email
                ,width: 250
                ,allowBlank: false
                ,value: App.loginData.email
            },{
                xtype: 'textfield'
                ,name: 'code'
                ,fieldLabel: L.Code
                ,width: 250
                ,allowBlank: false
            },{
                xtype: 'displayfield'
                ,style: 'padding: 0 0 0 20px; display: block'
                ,cls: 'cR'
                ,value: ''
                ,hideLabel: true
                ,hidden: true
            }
            ]
            ,buttonAlign: 'left'
            ,buttons: [{
                    xtype: 'button'
                    ,text: L.Save
                    ,formBind: true
                    ,scope: this
                    ,handler: this.onSaveClick
                }
            ]
        });

        this.callParent(arguments);
        // this.enableBubble(['verifyandsave']);
    }
    ,prepareInterface: function(data){
        this.getForm().setValues(data);
    }
    ,onSaveClick: function(){
        this.fireEvent('verifyandsave', this.getForm().getValues());
    }
    ,showError: function(msg){
        if(Ext.isEmpty(msg)) {
            msg = 'The code is incorrect. Try again';
        }
        msg = '<img class="icon icon-exclamation fl" style="margin-right: 15px" src="/css/i/s.gif">'+ msg;
        this.items.getAt(4).setValue(msg);
        this.items.getAt(4).setVisible(true);
    }
});
