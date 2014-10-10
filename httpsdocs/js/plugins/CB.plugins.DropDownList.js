Ext.namespace('CB.plugins');

Ext.define('CB.plugins.DropDownList', {
    extends: 'Ext.extend(Ext.util.Observable'

    ,alias: 'plugin.CBPluginsDropDownList'
    ,xtype: 'CB.plugins.DropDownList'

    ,constructor: function(config) {
        var defaultConfig = {
            commands: [
                {
                    prefix: '@'
                    ,regex: /^([\w\d_\.]+)/i

                    ,insertField: 'info'

                    ,handler: this.onAtCommand
                    ,scope: this
                }
                ,{
                    prefix: '#'
                    ,regex: /^(\d+)/i

                    ,handler: this.onDiezCommand
                    //,scope: this
                }
            ]
            ,displayTpl: new Ext.XTemplate(
                '<tpl for=".">'
                    ,'<div class="x-combo-list-item users-list-item">'
                        ,'<div class="thumb">'
                            ,'<img class="i32" src="/' + App.config.coreName + '/photo/{id}.jpg?32={[ CB.DB.usersStore.getPhotoParam(values.id) ]}" title="{text}">'
                        ,'</div>'
                        ,'<div class="text">'
                            ,'<span class="info">{[this.replaceLastQuery(values.info)]}</span>'
                            ,'{[this.replaceLastQuery(values.text)]}'
                            ,'<div class="descr">{[this.replaceLastQuery(values.descr)]}</div>'
                        ,'</div>'
                    ,'</div>'
                ,'</tpl>'
                ,'<div class="x-clear"></div>'
                ,{
                    // XTemplate configuration:
                    compiled: true
                    ,replaceLastQuery: function(value){
                        if(!Ext.isEmpty(this.lastQuery)) {
                            return String(value).replace(this.lastQuery, '<span class="fwB">' + this.lastQuery + '</span>');
                        }

                        return value;
                    }
                }
            )

        };

        if(config) {
            Ext.apply(defaultConfig, config);
        }

        Ext.apply(this, defaultConfig);

        CB.plugins.DropDownList.superclass.constructor.call(defaultConfig);
    }

    ,init: function(owner) {
        this.owner = owner;

        this.store = new Ext.data.JsonStore({
            fields: ['id', 'text', 'info', 'descr']
        });

        Ext.copyTo(
            this.owner
            ,Ext.form.field.Picker.prototype
            ,[
                'matchFieldWidth'
                ,'pickerAlign'
                ,'openCls'
                // ,'applyTriggers'
                // ,'initEvents' //overwriten in Casebox
                // ,'onEsc'
                // ,'onDownArrow'
                ,'expand'
                ,'onExpand'
                ,'doAlign'
                ,'collapse'
                ,'collapseIf'
                ,'getPicker'
                ,'getRefItems'
                // ,'onTriggerClick'
                ,'onOtherFocus'
                ,'alignPicker'
                ,'beforeDestroy'
            ]
        );

        Ext.copyTo(
            this.owner
            ,Ext.form.field.ComboBox.prototype
            ,[
                // 'mixins'
                // ,'config'
                // ,'publishes'
                // ,'twoWayBindable'
                // ,'triggerCls'
                // 'hiddenName'
                // ,'hiddenDataCls'
                // ,'ariaRole'
                ,'childEls'
                // ,'filtered'
                // ,'afterRender'
                ,'delimiter'
                // ,'triggerAction'
                // ,'allQuery'
                // ,'queryParam'
                ,'getStore'
                // ,'createPicker'
                // ,'setHiddenValue'
                ,'updateBindSelection'
                ,'onPageChange'
                ,'initEvents'
                // ,'onAdded'
                // ,'onPaste'
                ,'onListRefresh'
                ,'onBeforeSelect'
                ,'onBeforeDeselect'
                ,'onListSelectionChange'
                // ,'onKeyUp'
            ]
        );

        Ext.apply(owner, {
            enableKeyEvents: true
            ,store: this.store
            ,valueField: 'id'
            ,displayField: 'text'
            ,displayTpl: this.displayTpl

            ,selectByValue: Ext.emptyFn
            ,onTriggerClick: Ext.emptyFn
            ,onMouseDown: Ext.emptyFn
            ,onBlur: Ext.emptyFn
            ,onPaste: Ext.emptyFn
            ,syncSelection: Ext.emptyFn
            ,setSelection: Ext.emptyFn
            ,onCollapse: Ext.emptyFn
            // ,createPicker: Ext.emptyFn
            // ,initEvents: Ext.emptyFn
            // ,
            ,assertValue: function() {
                clog('assertValue', this, arguments);
                return '';
            }

            ,select : function(record, index){
                clog('select', this, arguments);
                if(this.fireEvent('beforeselect', this, record, index) !== false){
                    // this.setSelectedValue(record);
                    this.collapse();
                    this.fireEvent('select', this, record, index);
                }
            }

            ,createPicker: function() {
                var me = this,
                    picker,
                    pickerCfg = Ext.apply({
                        xtype: 'boundlist',
                        pickerField: me,
                        selModel: {
                            mode: me.multiSelect ? 'SIMPLE' : 'SINGLE',
                            enableInitialSelection: false
                        },
                        floating: true,
                        hidden: true,
                        store: me.store,
                        displayField: me.displayField,
                        preserveScrollOnRefresh: true,
                        pageSize: me.pageSize,
                        tpl: me.tpl
                    }, me.listConfig, me.defaultListConfig);

                picker = me.picker = Ext.widget(pickerCfg);
                if (me.pageSize) {
                    picker.pagingToolbar.on('beforechange', me.onPageChange, me);
                }

                me.mon(picker, {
                    refresh: me.onListRefresh,
                    scope: me
                });

                me.mon(picker.getSelectionModel(), {
                    beforeselect: me.onBeforeSelect,
                    beforedeselect: me.onBeforeDeselect,
                    selectionchange: me.onListSelectionChange,
                    scope: me
                });

                return picker;
            }

        });

        // owner.initEvents();

        owner.on('render', this.onRender, this);
        owner.on('afterrender', this.onAfterRender, this);
        owner.on('select', this.setSelectedValue, this);
    }

    ,onRender: function(ed){
        this.owner.wrap = this.owner.getEl();
        // this.owner.createPicker();

        //add listeners
        ed.on('keyup', this.onKeyUp, this);
    }

    ,onAfterRender: function(ed){

        // ed.keyNav.down = function(e){
        //     if(!this.isExpanded()){
        //         return true;
        //     } else {
        //         this.inKeyMode = true;
        //         this.selectNext();
        //     }
        // };
        // ed.keyNav.enter = function(e){
        //     // add flag so that the grid doesnt complete the edit if list expanded
        //     this.listSelection = this.isExpanded() && (e.getKey() == e.ENTER);
        //     clog('enter', this.listSelection, arguments);
        //     this.onViewClick();
        // };
    }

    ,onBeforeDestroy: function(){
        App.un('keyup', this.onKeyUp, this);
    }

    ,onKeyUp: function(ed, e){
        var value = ed.getRawValue();

        clog('onKeyUp', this, e, e.getKey());
        if(Ext.isEmpty(value)) {
            return;
        }

        switch(e.getKey()) {
            case e.ENTER:
                return;
        }

        var el = ed.inputEl
            ,caretPosition = this.getCaretPosition(el.dom)
            ,parts = [
                value.substring(0, caretPosition)
                ,value.substring(caretPosition)
            ];
        clog('!!', this.commands, arguments, caretPosition, parts);
        //iterate each command and check if matches any
        for (var i = 0; i < this.commands.length; i++) {
            var cmd = this.commands[i];

            //transform enters to spaces for "space" prefix
            if(cmd.prefix == ' ') {
                parts[0] = parts[0].replace(/[\n\r]/g, ' ');
            }

            //split left part by command prefix
            var t = parts[0].split(cmd.prefix);
            //skip if no comand prefix found, but not for space
            if((cmd.prefix != ' ') && (t.length < 2)) {
               continue;
            }

            var leftpart = t[t.length - 1];
            var str = t[t.length - 1] + parts[1];
            //execute regex match and check result
            var rez = cmd.regex.exec(str);

            //if no match or not all left part from cursor included then skip
            if(Ext.isEmpty(rez) || (rez[1].length < leftpart.length)) {
               continue;
            }

            var handler = cmd.handler || Ext.emptyFn;
            handler = Ext.Function.bind(handler, Ext.valueFrom(this.commands[i].scope, this));

            cmd.caretPosition = caretPosition;
            cmd.query = rez[1];
            cmd.queryStartIndex = parts[0].length - leftpart.length;

            this.currentCommand = cmd;

            handler(cmd, rez[1]);
            return true;
         }
         this.owner.collapse();
    }

    ,onAtCommand: function(cmdParams, query) {
        if(query == this.lastQuery) {
            return;
        }

        this.lastQuery = query;
        this.owner.displayTpl.lastQuery = query;

        CB_Security.searchUserGroups(
            {
                source: 'users'
                ,query: query
            }
            ,this.onSearchUsersProcess
            ,this
        );
    }

    ,onSearchUsersProcess: function(r, e) {
        if(r.success !== true) {
            return;
        }

        var items = [];
        for (var i = 0; i < r.data.length; i++) {
            var d = r.data[i];
            items.push({
                id: d.id
                ,text: d.name
                ,info: d.user
                ,descr: d.email
            });

        }

        this.showItems(items);
    }

    /**
     * TODO: discuss and implement diez command
     * @param  object cmdParams
     * @param  varchar query
     * @return void
     */
    ,onDiezCommand: function(cmdParams, query) {
        plog('Diez', arguments, query);
    }

    ,showItems: function(itemsArray){
        this.store.loadData(itemsArray);
        this.owner.expand();
    }

    ,setSelectedValue: function(ed, records, index) {
        clog('setSelectedValue', arguments);
        var cmd = this.currentCommand
            ,field = Ext.valueFrom(cmd.insertField, 'id');

        var value = ed.getRawValue();

        var newValue = value.substring(0, cmd.queryStartIndex) +
            records[0].get(field);
            newCaretPosition = newValue.length;
        newValue += value.substring(cmd.queryStartIndex + cmd.query.length);

        ed.setRawValue(newValue);
        this.setCaretPosition(ed.inputEl.dom, newCaretPosition);
    }

    ,getCaretPosition: function(el) {
        if (typeof(el.selectionStart) === "number") {
            return el.selectionStart;
        } else if (document.selection && el.createTextRange){
            var range = document.selection.createRange();
            range.collapse(true);
            range.moveStart("character", -el.value.length);
            return range.text.length;
        } else {
            throw 'getCaretPosition() not supported';
        }
    }

    ,setCaretPosition: function (el, pos){
        if(el.setSelectionRange)
        {
            el.focus();
            el.setSelectionRange(pos,pos);
        }
        else if (el.createTextRange) {
            var range = el.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    }
});
