Ext.namespace('CB');

Ext.define('CB.Login', {
    extend: 'Ext.Window'
    ,title: L.Authorization
    ,plain: true
    ,closable: false
    ,iconCls: 'icon-key'
    ,id: 'CBLoginWindow'
    ,modal: true
    ,frame: true
    ,autoHeight: true
    ,width: 315
    ,closeAction: 'close'
    ,border: false
    ,resizable: false
    ,buttonAlign: 'center'

    ,initComponent: function() {
        Ext.apply(this,{
            items : [{
                xtype: 'form'
                ,border: false
                ,layout: 'table'
                ,layoutConfig: {columns: 2, padding: 0}
                ,autoHeight: true
                ,monitorValid: true
                ,items: [{  html: '<img id="logo" style="padding-top: -15px" src="css/i/CaseBox-Logo_briefcase.png"/>'
                    ,cls: 'taC'
                    ,border: false
                    ,width: 130
                },{
                    xtype: 'fieldset'
                    ,border: false
                    ,defaults:{ width: 150 }
                    ,padding: 0
                    ,defaultType: 'textfield'
                    ,labelAlign: 'top'
                    ,bodyStyle: 'padding: 10px 5px 0 5px'
                    ,items:[{
                            name: 'username'
                            ,fieldLabel: L.User
                        },{
                            name: 'password'
                            ,fieldLabel: L.Password
                            ,inputType: 'password'
                        }
                    ]
                },{
                    border: false
                    ,colspan: 2
                    ,cls: 'taC fwB cR'
                    ,html:'&nbsp;'
                    ,name: 'infoPanel'
                    ,xtype: 'panel'
                }
                ]
                ,buttons: [{text: L.Login, handler: this.doLogin, scope: this, formBind: true} ]
            }
            ]
            ,keys: [{key: 13, fn: this.doLogin, scope: this}]
        });
        this.on('afterrender', this.doShow);
        CB.Login.superclass.initComponent.apply(this, arguments);
    }

    ,doShow: function(w) {
        var lku = Ext.util.Cookies.get('lastUser');
        var user = w.down('[name="username"]');
        var pass = w.down('[name="password"]');

        user.setValue(lku);
        pass.reset();

        if(Ext.isEmpty(lku)) {
            user.focus(true, 550);
        } else {
            pass.focus(true, 550);
        }
    }

    ,doLogin: function(){
        user = this.child('[name="username"]');
        pass = this.child('[name="password"]');
        if(!user.isValid() || !pass.isValid()) return false;
        //Ext.util.Cookies.set('lastUser', user.getValue());

        CB_User.login(user.getValue(), pass.getValue(), this.processLoginResponse);
    }

    ,processLoginResponse: function(response, e){
        lw = Ext.getCmp('CBLoginWindow');
        if(e.result.success === true){
            if(App.loginData && (App.loginData.id != response.user.id) ) return window.location.reload();
            App.config = response.config;
            App.loginData = response.user;
            lw.close();
        }else{
            ip = lw.child('[name="infoPanel"]');
            ip.body.update(response.msg);
        }
     }

});


Ext.define('CB.VerifyPassword', {
    extend: 'Ext.Window'

    ,title: L.Verify
    ,plain: true
    ,iconCls: 'icon-key'
    ,modal: true
    ,frame: true
    ,autoHeight: true
    ,width: 320
    ,closeAction: 'close'
    ,border: false
    ,resizable: false
    ,buttonAlign: 'center'

    ,initComponent: function() {
        Ext.apply(this,{
            items : [{
                xtype: 'form'
                ,border: false
                ,autoHeight: true
                ,monitorValid: true
                ,items: [{
                    xtype: 'fieldset'
                    ,border: false
                    ,defaults: {anchor: '100%'}
                    ,layout: 'anchor'
                    ,defaultType: 'textfield'
                    ,labelAlign: 'left'
                    ,bodyStyle: 'padding: 10px 5px 0 5px'
                    ,items:[{
                            name: 'username'
                            ,fieldLabel: L.User
                            ,xtype: 'displayfield'
                            ,value: getUserDisplayName()
                        },{
                            name: 'password'
                            ,fieldLabel: L.Password
                            ,inputType: 'password'
                            ,enableKeyEvents: true
                            ,keys: [{key: 13, fn: this.doVerify, scope: this}]
                            ,listeners: {
                                afterrender: function() {
                                    this.focus();
                                }
                            }
                        }
                    ]
                },{
                    border: false
                    ,bodyCls: 'taC fwB cR'
                    ,html:'&nbsp;'
                    ,name: 'infoPanel'
                    ,xtype: 'panel'
                    ,hidden: true
                    ,bodyStyle: 'padding:5px'
                }
                ]
                ,buttons: [{
                    text: L.Verify
                    ,handler: this.doVerify
                    ,scope: this
                    ,formBind: true
                }]
            }
            ]

            ,listeners: {
                scope: this
                ,afterrender: function() {
                    var nav = Ext.create('Ext.util.KeyNav', this.getEl(), {
                        scope: this,
                        enter: this.doVerify
                    });

                }
            }
        });

        this.on('show', this.doShow, this);

        CB.VerifyPassword.superclass.initComponent.apply(this, arguments);
    }

    ,doShow: function(w) {
        var pass = this.down('[name="password"]');
        pass.reset();
        // pass.focus(true, 1850);
    }

    ,doVerify: function(){
        var pass = this.down('[name="password"]');

        if(!pass.isValid()) {
            return false;
        }

        CB_User.verifyPassword( pass.getValue(), this.processVerifyResponse, this);
    }

    ,processVerifyResponse: function(response, e){
        if(e.result.success === true){
            this.success = true;
            this.close();
        } else {
            ip = this.down('[name="infoPanel"]');
            ip.show();
            ip.body.update(response.msg);
            // this.syncSize();
            pass = this.down('[name="password"]');
            pass.reset();
            pass.focus(true, 550);
        }
     }

});
