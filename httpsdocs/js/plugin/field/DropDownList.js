Ext.namespace('CB.plugin.field');

Ext.define('CB.plugin.field.DropDownList', {
    extends: 'Ext.util.Observable'

    ,alias: 'plugin.CBPluginFieldDropDownList'
    ,xtype: 'CB.plugin.field.DropDownList'

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
                }
            ]
            ,displayTpl: new Ext.XTemplate(
                '<tpl for=".">'
                    ,'<li role="option" class="x-boundlist-item users-list-item">'
                        ,'<div class="thumb">'
                            ,'<img class="i32" src="' + App.config.photoPath + '{id}.jpg?32={[ CB.DB.usersStore.getPhotoParam(values.id) ]}" title="{text}">'
                        ,'</div>'
                        ,'<div class="text">'
                            ,'<span class="info">{[this.replaceLastQuery(values.info)]}</span>'
                            ,'{[this.replaceLastQuery(values.text)]}'
                            ,'<div class="descr">{[this.replaceLastQuery(values.descr)]}</div>'
                        ,'</div>'
                    ,'</li>'
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

        this.callParent(arguments);
        // CB.plugin.field.DropDownList.superclass.constructor.call(defaultConfig);
    }

    ,init: function(owner) {
        this.owner = owner;

        this.store = new Ext.data.JsonStore({
            model: 'DropDownListItems'
        });

        Ext.copyTo(
            this.owner
            ,Ext.form.field.Picker.prototype
            ,[
                'matchFieldWidth'
                ,'pickerAlign'
                ,'openCls'
                ,'initEvents' //overwriten in Casebox
                ,'expand'
                ,'onExpand'
                ,'doAlign'
                ,'collapse'
                ,'collapseIf'
                ,'getPicker'
                ,'getRefItems'
                ,'onOtherFocus'
                ,'alignPicker'
                ,'beforeDestroy'
            ]
        );

        Ext.copyTo(
            this.owner
            ,Ext.form.field.ComboBox.prototype
            ,[
                ,'defaultListConfig'
                ,'listConfig'
                ,'childEls'
                ,'delimiter'
                ,'getStore'
                ,'updateBindSelection'
                ,'onPageChange'
                ,'onListRefresh'
                ,'onBeforeSelect'
                ,'onBeforeDeselect'
                // ,'onDestroy'
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
            ,syncSelection: Ext.emptyFn
            ,setSelection: Ext.emptyFn

            ,onCollapse: function() {
                if(this.preventEditComplete !== Ext.EventObject.time) {
                    delete this.preventEditComplete;
                }
            }
            ,onEsc: Ext.emptyFn
            ,bindStore: Ext.emptyFn

            ,assertValue: function() {
                return '';
            }

            ,select : function(record, index){
                if(this.fireEvent('beforeselect', this, record, index) !== false){
                    ed.preventEditComplete = true;
                    this.collapse();
                    this.fireEvent('select', this, record, index);
                }
            }

            ,createPicker: function() {
                var me = this,
                    picker,
                    pickerCfg = Ext.apply(
                        {
                            xtype: 'boundlist'
                            ,pickerField: me
                            ,selModel: {
                                mode: me.multiSelect ? 'SIMPLE' : 'SINGLE'
                                // ,enableInitialSelection: true
                            }
                            ,floating: true
                            ,hidden: true
                            ,store: me.store
                            ,displayField: me.displayField
                            ,preserveScrollOnRefresh: true
                            ,pageSize: me.pageSize
                            ,tpl: me.displayTpl
                            ,navigationModel: 'CBboundlist'
                        }
                        ,me.listConfig
                        ,me.defaultListConfig
                    );

                picker = me.picker = Ext.widget(pickerCfg);
                if (me.pageSize) {
                    picker.pagingToolbar.on('beforechange', me.onPageChange, me);
                }

                // me.mon(picker, {
                //     refresh: me.onListRefresh,
                //     scope: me
                // });

                me.mon(picker.getSelectionModel(), {
                    beforeselect: me.onBeforeSelect,
                    beforedeselect: me.onBeforeDeselect,
                    scope: me
                });

                return picker;
            }

        });

        owner.on('render', this.onRender, this);
        owner.on('beforeselect', this.setSelectedValue, this);
        owner.on('beforedestroy', this.onBeforeDestroy, this);
    }


    ,onRender: function(ed){
        this.owner.wrap = this.owner.getEl();

        //add listeners
        ed.on('keydown', this.onKeyDown, this);
        ed.on('keyup', this.onKeyUp, this);
    }

    ,onBeforeDestroy: function(ed){
        ed.un('keydown', this.onKeyDown, this);
        ed.un('keyup', this.onKeyUp, this);
        this.owner.un('beforeselect', this.setSelectedValue, this);
    }

    ,onKeyDown: function(ed, e){
        if(!ed.picker) {
            return;
        }

        var me = this
            ,picker = ed.picker
            ,allItems = picker.all
            ,oldItem = picker.highlightedItem
            ,oldItemIdx
            ,newItemIdx;

        switch(e.getKey()) {
            case e.ENTER:
                if(ed.isExpanded) {
                    e.stopEvent();
                    ed.onKeyEnter(e);
                }
                break;

            case e.ESC:
                if(ed.isExpanded) {
                    e.stopEvent();
                    ed.preventEditComplete = true;
                    ed.collapse();
                }
                break;

            case e.UP:
                if(ed.isExpanded) {
                    e.stopEvent();
                    ed.onKeyUp(e);
                }
                break;

            case e.DOWN:
                if(ed.isExpanded) {
                    e.stopEvent();
                    ed.onKeyDown(e);
                }
                break;

            case e.LEFT:
            case e.RIGHT:
                // do nothing and let the event propagate
                break;
        }
    }

    ,onKeyUp: function(ed, e){
        var value = ed.getRawValue();

        if(Ext.isEmpty(value) || e.isSpecialKey()) {
            return;
        }

        var el = ed.inputEl
            ,caretPosition = this.getCaretPosition(el.dom)
            ,parts = [
                value.substring(0, caretPosition)
                ,value.substring(caretPosition)
            ];

        //iterate each command and check if matches any
        for (var i = 0; i < this.commands.length; i++) {
            var cmd = this.commands[i];

            //transform enters and comma's to spaces for "space" prefix
            if(cmd.prefix === ' ') {
                parts[0] = parts[0].replace(/[\n\r,]/g, ' ');
            }

            //split left part by command prefix
            var t = parts[0].split(cmd.prefix);
            //skip if no comand prefix found, but not for space
            if((cmd.prefix !== ' ') && (t.length < 2)) {
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
        if(!r || (r.success !== true)) {
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
        this.owner.getPicker().getSelectionModel().deselectAll();
        this.owner.expand();
        this.owner.setPosition();
    }

    ,setSelectedValue: function(ed, record, index) {
        var cmd = this.currentCommand
            ,field = Ext.valueFrom(cmd.insertField, 'id')
            ,value = ed.getRawValue()
            ,newValue = value.substring(0, cmd.queryStartIndex) + record.get(field)
            ,newCaretPosition = newValue.length;

        newValue += value.substring(cmd.queryStartIndex + cmd.query.length);
        if(Ext.EventObject.type != "click") {
            ed.preventEditComplete = Ext.EventObject.time;
        }

        ed.collapse();
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
