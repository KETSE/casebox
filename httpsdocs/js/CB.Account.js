Ext.namespace('CB');

CB.Account = Ext.extend(Ext.Panel, {
    title: L.Account
    ,hideBorders: true
    ,closable: true
    ,initComponent: function() {
        
        this.menu = new Ext.Panel({
            region: 'west'
            ,collapsible: false
            ,width: 150
            ,animCollapse: false
            ,plain: true
            ,cls: 'account-menu'
            ,bodyStyle: 'border-right: 1px solid #eaeaea'
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
            ,hideBorders: true
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
        })

        Ext.apply(this, {
            iconCls: 'icon-user-' + App.loginData.sex
            ,layout: 'border'
            ,items:[
                this.menu
                ,this.cards

            ]
        });
        CB.Account.superclass.initComponent.apply(this, arguments);
        
        /* autoclose form if no activity in 5 minutes */
        // this.autoCloseTask = new Ext.util.DelayedTask(this.destroy, this);
        // this.autoCloseTask.delay(1000*60*5);

        CB_User.getAccountData( this.onGetData, this);

    }
    ,onGetData: function(r, e){
        if(r.success !== true){
            if(r.verify == true){
                // show verification form
                w = new CB.VerifyPassword({
                    listeners:{
                        scope: this
                        ,beforeclose: function(cmp){
                            if(w.success !== true) this.destroy();
                            else CB_User.getAccountData( this.onGetData, this);
                        }
                    }
                });
                w.show();
            }else this.close();
            return;
        }
        this.cards.items.itemAt(0).loadData(r.profile);
        this.cards.items.itemAt(1).loadData(r.security);
    }
    ,onMenuButtonClick: function(b, e){
        this.cards.getLayout().setActiveItem(this.menu.items.indexOf(b));
        // this.autoCloseTask.delay(1000*60*5);
    }
}
)

Ext.reg('CBAccount', CB.Account);

CB.ProfileForm = Ext.extend(Ext.form.FormPanel, {
    hideBorders: true
    ,fileUpload: true
    ,autoScroll: true
    ,data: {}
    ,initComponent: function(){

        this.photoField = new Ext.form.TextField({
            cls: 'fl'
            ,style: 'position:absolute;width:1px;height:1px;opacity:0;top:-100px'
            ,inputType: 'file'
            ,name: 'photo'
            ,listeners:{
                scope: this
                ,afterrender: function(c){
                    c.getEl().on('change', this.onPhotoChanged, this);
                }
            }
        });
        this.photoView = new Ext.DataView({
            tpl: ['<tpl for="."><div><img width="70" class="user-photo-field2 click icon-user70-{sex}" src="/photo/{id}.png?{[ (new Date()).format("His") ]}"></div>'
                ,'<div><a href="#" name="change" class="click">'+L.Change+'</a> &nbsp; <a href="#" name="remove" class="click">'+L.Delete+'</a></div>'
                ,'</tpl>'
            ]
            ,data: [{}]
            ,itemSelector:'.click'
            ,autoHeight: true
            ,listeners:{ scope: this, click: this.onPhotoClick }
        })

        Ext.apply(this,{
            items:[{
                hideBorders: true
                ,layout: 'hbox'
                ,layoutConfig: {
                    align: 'stretchmax'
                }
                ,autoHeight: true
                ,items: [{
                    autoHeight: true
                    ,hideBorders: true
                    ,width: 500
                    ,items: {
                            xtype: 'fieldset'
                            ,autoHeight: true
                            ,labelWidth: 140
                            ,defaults:{
                                width: 250
                                ,listeners: {
                                    scope: this
                                    ,change: this.onChange
                                    ,select: this.onChange
                                }
                            }
                            ,items: [
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
                                    ,mode: 'local'
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
                                    ,mode: 'local'
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
                                    xtype: 'numberfield'
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
                                    ,mode: 'local'
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
                                    ,mode: 'local'
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
                                    ,mode: 'local'
                                    ,triggerAction: 'all'
                                    ,editable: false
                                    ,store: CB.DB.shortDateFormats
                                    ,valueField: 'id'
                                    ,displayField: 'name'
                                    ,value: null
                                }
                            ]
                        }
                },{
                    xtype: 'panel'
                    ,width: 300
                    ,padding: 45
                    ,items: [
                        this.photoField
                        ,this.photoView
                    ]
                }

                ]
            },{
                xtype: 'CBVerticalEditGrid'
                ,refOwner: this
                ,width: 800
                ,style: 'margin-bottom: 50px'
                ,autoHeight: true
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
        })
        CB.ProfileForm.superclass.initComponent.apply(this, arguments);
        this.grid = this.items.itemAt(1);

        if(CB.DB.countries.getCount() == 0) CB.DB.countries.load();
        if(CB.DB.timezones.getCount() == 0) CB.DB.timezones.load();
    }
    ,onAfterRender: function(cmp){
        
    }
    ,loadData: function(data){
        this.data = data;
        this.getForm().setValues(data);
        this.grid.reload();
        this.photoView.update( [{id: this.data.id }] ) 
        this.syncSize();
        this.setDirty(false)
    }
    ,onPhotoClick: function(w, idx, el, ev){
        if(!ev) return;
        target = ev.getTarget();
        if( (target.localName == "img") || (target.name == 'change') )
            return this.photoField.getEl().dom.click();
        if (target.name == 'remove') {
            return this.onPhotoRemoveClick();
        }
    }
    ,onPhotoChanged: function(ev, el, o){
        if(Ext.isEmpty(this.photoField.getValue())) return;
        form = this.getForm();
        form.api = {submit: CB_User.uploadPhoto};

        form.submit({
            clientValidation: false
            ,params: {
                    id: this.data.id    
                }
            ,scope: this
            ,success: function(form, action) { 
                this.photoView.update( [{id: this.data.id }] ) 
            }
            ,failure: App.formSubmitFailure
        });
    }
    ,onPhotoRemoveClick: function(){
        Ext.Msg.confirm(L.Confirm, L.RemovePhotoConfirm, function(b, e){
            if(b == 'yes'){
                CB_User.removePhoto( { id: this.data.id }, function(){
                    this.photoView.update( [{id: this.data.id }] )
                }, this);
            }
        }, this)
    }
    ,onSaveClick: function(){
        Ext.apply(this.data, this.getForm().getValues());
        if(this.data.phone == this.find('name', 'phone')[0].emptyText) this.data.phone = null;
        this.grid.readValues();
        CB_User.saveProfileData(this.data, this.onSaveProcess, this)
    }
    ,onSaveProcess: function(r, e){
        if(r.success !== true) return;
        this.setDirty(false);
        this.fireEvent('savesuccess', this, e);
    }
    ,onResetClick: function(){
        this.getForm().reset();
        this.loadData(this.data);
    }
    ,onChange: function(){
        this.setDirty(true)
    }
    ,setDirty: function(dirty){
        this._isDirty = (dirty !== false);
        this.buttons[0].setDisabled(!this._isDirty);
        this.buttons[1].setDisabled(!this._isDirty);
    }

})

Ext.reg('CBProfileForm', CB.ProfileForm);

CB.SecurityForm = Ext.extend(Ext.form.FormPanel, {
    hideBorders: true
    ,autoScroll: true
    ,initComponent: function(){

        Ext.apply(this,{
            padding: 10
            ,items: [{
                title: L.Password
                ,headerCfg: { cls: 'x-panel-header panel-header-nobg block-header' }
                ,defaults: {style: 'padding: 5px 25px'}
                ,items: [
                    {
                        xtype: 'displayfield'
                        ,name: 'passwordchanged'
                        ,value: L.PasswordNeverChanged
                    },{
                        xtype: 'button'
                        ,html: '<a href="">'+L.ChangePassword+'</a>'
                        ,scope: this
                        ,handler: this.onChangePasswordClick
                    }
                ]
            },{
                title: L.RecoveryOptions
                ,headerCfg: { cls: 'x-panel-header panel-header-nobg block-header' }
                ,style: 'margin-top: 20px'
                ,defaults: {style: 'padding: 5px 0 15px 25px', border: false}
                ,items: [
                    {
                        xtype: 'compositefield'
                        ,items: [{
                            xtype: 'checkbox'
                            ,name: 'recovery_mobile'
                            ,listeners: {
                                scope: this
                                ,check: this.onCheckboxCheck
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
                            ,mode: 'local'
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
                            ,fieldLabel: 'Phone number'
                        }
                        ]
                    },{
                        xtype: 'compositefield'
                        ,items: [{
                            xtype: 'checkbox'
                            ,name: 'recovery_email'
                            ,inputValue: 1
                            ,listeners: {
                                scope: this
                                ,check: this.onCheckboxCheck
                            }
                        },{
                            xtype: 'displayfield'
                            ,cls: 'fwB'
                            ,value: L.Email
                        }
                        ]
                    },{
                        hidden: true
                        ,name: 'recovery_email_panel'
                        ,layout: 'form'
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
                        xtype: 'compositefield'
                        ,items: [{
                            xtype: 'checkbox'
                            ,name: 'recovery_question'
                            ,listeners: {
                                scope: this
                                ,check: this.onCheckboxCheck
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
                            ,mode: 'local'
                            ,triggerAction: 'all'
                            ,editable: false
                            ,forceSelection: true
                            ,typeAhead: true
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
                    ,style: 'margin-left: 18px'
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onSaveClick
                },{
                    text: L.Reset
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onResetClick
                }

                ]
            },{
                title: L.TSV
                ,headerCfg: { cls: 'x-panel-header panel-header-nobg block-header' }
                ,style: 'margin-top: 20px'
                ,defaults: {style: 'padding: 5px 25px', border: false}
                ,buttonAlign: 'left'
                ,items: [{
                    xtype: 'displayfield'
                    ,value: L.Status +': <span class="cG">'+ L.Disabled + '</span>'
                }]
                ,buttons: [{
                    xtype: 'displayfield'
                    ,width: 12
                },{
                    html: '<a>'+L.Enable+'</a>'
                    ,scope: this
                    ,handler: this.enableTSV
                },{
                    html: '<a>'+L.Disable+'</a>'
                    ,scope: this
                    ,handler: this.disableTSV
                    ,hidden: true
                },{
                    html: '<a>'+L.Change+'</a>'
                    ,scope: this
                    ,handler: this.enableTSV
                    ,hidden: true
                }
                ]   
            }
            ]
        })
        CB.SecurityForm.superclass.initComponent.apply(this, arguments);
        this.saveButton = this.items.itemAt(1).buttons[0];
        this.resetButton = this.items.itemAt(1).buttons[1];
    }
    ,loadData: function( data ){
        // this.items.itemAt(0).update(data)
        this.data = data;
        if(!Ext.isEmpty(data.password_change)) this.find( 'name', 'passwordchanged' )[0].setValue(L.PasswordChanged+': '+data.password_change);

        cb = this.items.itemAt(1).items.first().items.first()
        cb.setValue(data.recovery_mobile == true);
        this.find( 'name', 'country_code' )[0].setValue( Ext.value(data.country_code, null) );
        this.find( 'name', 'phone_number' )[0].setValue( Ext.value(data.phone_number, null) );

        cb = this.items.itemAt(1).items.itemAt(2).items.first();
        cb.setValue(data.recovery_email == true);
        this.find( 'name', 'email' )[0].setValue( Ext.value(data.email, null) );

        cb = this.items.itemAt(1).items.itemAt(4).items.first();
        cb.setValue(data.recovery_question == true);
        this.find( 'name', 'question_idx' )[0].setValue( Ext.value(data.question_idx, null) );
        this.find( 'name', 'answer' )[0].setValue( Ext.value(data.answer, null) );

        this.updateTSVStatus();

        this.setDirty(false);
    }
    ,onCheckboxCheck: function(cb){
        p = this.find('name', cb.name+'_panel')[0]
        p.setVisible(cb.checked);
        //this.data[cb.name] = cb.checked;
        this.setDirty()
    }
    ,onChangePasswordClick: function(b){
        pw = new CB.ChangePasswordWindow({
            data: {id: App.loginData.id}
            ,listeners: {
                scope: this
                ,passwordchanged: this.onPasswordChanged
            }
        });
        pw.show();
    }
    ,onPasswordChanged: function(w){
        this.find('name', 'passwordchanged')[0].setValue(L.PasswordChanged+': '+L.today);
    }
    ,onSaveClick: function(){
        cb = this.items.itemAt(1).items.first().items.first()
        this.data.recovery_mobile = cb.getValue();
        this.data.country_code = this.find( 'name', 'country_code' )[0].getValue();
        this.data.phone_number = this.find( 'name', 'phone_number' )[0].getValue();

        cb = this.items.itemAt(1).items.itemAt(2).items.first();
        this.data.recovery_email = cb.getValue();
        this.data.email = this.find( 'name', 'email' )[0].getValue();

        cb = this.items.itemAt(1).items.itemAt(4).items.first();
        this.data.recovery_question = cb.getValue();
        this.data.question_idx = this.find( 'name', 'question_idx' )[0].getValue();
        this.data.answer = this.find( 'name', 'answer' )[0].getValue();

        CB_User.saveSecurityData(this.data, this.onSaveProcess, this)
    }
    ,onSaveProcess: function(r, e){
        if(r.success !== true) return;
        this.setDirty(false);
    }
    ,onResetClick: function(){
        this.loadData(this.data);
    }
    ,onChange: function(){
        this.setDirty(true)
    }
    ,setDirty: function(dirty){
        this._isDirty = (dirty !== false);
        this.saveButton.setDisabled(!this._isDirty);
        this.resetButton.setDisabled(!this._isDirty);
    }   
    ,enableTSV: function(b, e){
        data = Ext.value(this.data.TSV, {});
        data.country_code = Ext.value(data.country_code, this.data.country_code );
        data.phone_number = Ext.value(data.phone_number, this.data.phone_number );
        w = new CB.TSVWindow({ 
            data: data
            ,listeners:{
                scope: this
                ,tsvchange: this.onTSVChange
            }
        });
        w.show();
    }
    ,onTSVChange: function(w, tsv){
        if(Ext.isEmpty(this.data['TSV'])) this.data.TSV = {};
        this.data.TSV.method = tsv;
        this.updateTSVStatus();
    }
    ,updateTSVStatus: function(){
        text = '<span class="cG">'+ L.Disabled + '</span>';
        
        if(Ext.isEmpty(this.data.TSV)) this.data.TSV = {}

        switch(this.data.TSV.method){
            case 'MGA': 
                text = 'Mobile Google Aplication'
                break;
            case 'GA': 
                text = 'Google Authenticator'
                break;
            case 'Yubikey':
                text = 'Yubikey'
                break;
        }
        this.items.itemAt(2).items.itemAt(0).setValue(L.Status+': ' + text);
        this.items.itemAt(2).buttons[1].setVisible(Ext.isEmpty(this.data.TSV.method));
        this.items.itemAt(2).buttons[2].setVisible(!Ext.isEmpty(this.data.TSV.method));
        this.items.itemAt(2).buttons[3].setVisible(!Ext.isEmpty(this.data.TSV.method));
    }
    ,disableTSV: function(){
        Ext.Msg.confirm(L.Confirm, 'Are you sure you want to disable '+L.TSV, function(b, e){
            if(b == 'yes'){
                CB_User.disableTSV( function(r, e){
                    if(r.success == true){
                        delete this.data.TSV.method;
                        this.updateTSVStatus();
                    }
                }, this);
            }
        }, this) 
    }
})
Ext.reg('CBSecurityForm', CB.SecurityForm);


/*
2-step verification
Keep the bad guys out of your account by using both your password and your phone.
 */
CB.TSVWindow = Ext.extend(Ext.Window, {
    modal: true
    ,title: L.TSV
    ,autoWidth: true
    ,autoHeight: true
    ,hideBorders: true
    ,iconCls: 'icon-key'
    ,layout: 'card'
    ,initComponent: function() {
        Ext.apply(this, {
            activeItem: 0
            ,bodyStyle: 'border: 20px solid white'
            ,items: [{
                items: [{
                    xtype: 'displayfield'
                    ,style: 'font-size: 20px'
                    ,value: 'Select authentication method'
                },{
                    xtype: 'button'
                    ,html: '<a>Mobile application</a>'
                    ,style: 'padding:10px'
                    ,scope: this
                    ,handler: this.onMobileApplicationClick
                },{
                    xtype: 'button'
                    ,html: '<a>Sms message</a>'
                    ,style: 'padding:10px'
                    ,scope: this
                    ,handler: this.onSMSMessageClick
                },{
                    xtype: 'button'
                    ,html: '<a class="cG">Yubikey</a>'
                    ,style: 'padding:10px'
                    ,disabled: true
                    ,scope: this
                    ,handler: this.onYubikeyClick
                }
                ]
            },{
                style: 'background-color: #fff'
                ,width:500
                ,items: [{
                    xtype: 'displayfield'
                    ,style: 'font-size: 20px; padding-bottom:15px'
                    ,value: 'Set up Google Authenticator'
                },{
                    autoHeight: true
                    ,autoWidth:true
                    ,border: false
                    ,tpl: ['<tpl for=".">'
                        ,'<p class="fwB"> Install the Google Authenticator app for your phone</p>'
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
                        ,'    <div class="fs14 fwB" dir="ltr">{sk}</div>'
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
                        ,name: 'mga_code'
                        ,width: '50'
                        ,enableKeyEvents: true
                        ,listeners: {
                            scope: this
                            ,keyup: function(field, e){
                                this.getLayout().activeItem.buttons[2].setDisabled(Ext.isEmpty(field.getValue()))
                            }
                        }
                    },{
                        xtype: 'button'
                        ,text: 'Verify and Save'
                        ,disabled: true
                        ,scope: this
                        ,handler: this.onVerifyAndSaveClick
                    },{
                        xtype: 'displayfield'
                        ,style: 'padding: 0 0 0 20px'
                        ,cls: 'cR'
                        ,value: '<img class="icon icon-exclamation fl" style="margin-right: 15px" src="/css/i/s.gif"> The code is incorrect. Try again'
                        ,hidden: true
                    }

                    ]
            },{
                xtype: 'form'
                ,monitorValid: true
                ,autoWidth: true
                ,autoHeight: true
                ,labelWidth: 120
                ,buttonAlign: 'left'
                ,cls: 'bgcW'
                ,items: [{
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
                    ,mode: 'local'
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
            }]
            ,listeners: {
                scope: this
                ,afterrender: function(){ 
                    f = this.findByType('form')[0];
                    f.getForm().setValues(this.data);
                    f.syncSize();
                    App.focusFirstField(f)
                }
            }
        });
        CB.TSVWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findByType('form')[0];
    }
    ,onMobileApplicationClick: function(){
        this.getLayout().setActiveItem(1);
        CB_User.getGASk( this.processGetSk, this)
        this.center();
    }
    ,onSMSMessageClick: function(){
        this.getLayout().setActiveItem(2);
    }
    ,processGetSk: function(r, e){
        if(r.success !== true) return;
        sk = '';
        while(!Ext.isEmpty(r.sk)){
            sk += r.sk.substr(0,4) + ' ';
            r.sk = r.sk.substr(4)
        }
        r.sk = sk;
        p = this.getLayout().activeItem.items.itemAt(1);
        p.data = r;
        p.update(r);
    }
    ,onVerifyAndSaveClick: function(){
        this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
        CB_User.TSVSaveMGA( { code: this.getLayout().activeItem.buttons[1].getValue() }, this.processTSVSaveMGA, this)
    }
    ,processTSVSaveMGA: function(r, e){
        this.getLayout().activeItem.buttons[3].setVisible(r.success !== true);
        this.getEl().unmask()
        this.syncSize();
        if(r.success === true){
            this.fireEvent('tsvchange', this, 'MGA');
            this.destroy()
        }
    }
    ,onVerifyPhoneClick: function(){
        if(this.form.getForm().isValid()){
            this.getEl().mask(L.Processing + ' ...', 'x-mask-loading');
            CB_User.verifyPhone(
                this.form.getForm().getValues()
                ,this.processPhoneVerification
                ,this
            )
        }
    }
    ,onSendCodeClick: function(b, e){
    }
    ,processPhoneVerification: function(r, e){
        this.getEl().unmask();
        clog('processPhoneVerification', arguments)
    }
    ,onSubmitSuccess: function(r, e){
        // this.fireEvent('passwordchanged');
        // this.destroy();
    }
}
)