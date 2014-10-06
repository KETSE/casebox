Ext.namespace('CB.plugins');

CB.plugins.DropDownList =  Ext.extend(Ext.util.Observable, {

    constructor: function(config) {
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
            ,listTpl: new Ext.XTemplate(
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

        Ext.apply(owner, {
            enableKeyEvents: true
            ,store: this.store
            ,valueField: 'id'
            ,displayField: 'text'
            ,tpl: this.listTpl

            ,selectByValue: Ext.emptyFn
            ,onTriggerClick: Ext.emptyFn

            ,assertValue: function() {
                return '';
            }

            ,onSelect : function(record, index){
                if(this.fireEvent('beforeselect', this, record, index) !== false){
                    // this.setSelectedValue(record);
                    this.collapse();
                    this.fireEvent('select', this, record, index);
                }
            }

        });

        Ext.copyTo(
            this.owner
            ,Ext.form.ComboBox.prototype
            ,[
                'initList'
                ,'listAlign'
                ,'minHeight'
                ,'maxHeight'
                ,'selectedClass'
                ,'select'
                ,'selectNext'
                ,'selectPrev'
                // ,'onSelect'
                ,'initEvents'
                ,'initQuery'
                ,'doQuery'
                ,'getListParent'
                ,'getZIndex'
                ,'getParentZIndex'
                ,'bindStore'
                ,'onBeforeLoad'
                ,'validateBlur'
                ,'beforeBlur'
                ,'postBlur'
                ,'onLoad'
                ,'expand'
                ,'collapse'
                ,'collapseIf'
                ,'isExpanded'
                ,'restrictHeight'
                ,'onViewOver'
                ,'onViewMove'
                ,'onViewClick'
                ,'onKeyUp'
                ,'dqTask'
            ]
        );

        owner.on('render', this.onRender, this);
        owner.on('afterrender', this.onAfterRender, this);
        owner.on('select', this.setSelectedValue, this);
    }

    ,onRender: function(ed){
        this.owner.wrap = this.owner.getEl();
        this.owner.initList();

        //add listeners
        ed.on('keyup', this.onKeyUp, this);
    }

    ,onAfterRender: function(ed){

        ed.keyNav.down = function(e){
            if(!this.isExpanded()){
                return true;
            } else {
                this.inKeyMode = true;
                this.selectNext();
            }
        };
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

        if(Ext.isEmpty(value)) {
            return;
        }

        switch(e.getKey()) {
            case e.ENTER:
                return;
        }

        var el = ed.getEl()
            ,caretPosition = this.getCaretPosition(el.dom)
            ,parts = [
                value.substring(0, caretPosition)
                ,value.substring(caretPosition)
            ];

        //iterate each command and check if matches any
        for (var i = 0; i < this.commands.length; i++) {
            var cmd = this.commands[i];

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
            handler = handler.createDelegate(Ext.value(this.commands[i].scope, this));

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
        this.owner.tpl.lastQuery = query;

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

    ,setSelectedValue: function(ed, record, index) {
        var cmd = this.currentCommand
            ,field = Ext.value(cmd.insertField, 'id');

        var value = ed.getRawValue();

        var newValue = value.substring(0, cmd.queryStartIndex) +
            record.get(field);
            newCaretPosition = newValue.length;
        newValue += value.substring(cmd.queryStartIndex + cmd.query.length);

        ed.setRawValue(newValue);
        this.setCaretPosition(ed.getEl().dom, newCaretPosition);
    }

    ,getCaretPosition: function (el) {
        var caretPos = 0;   // IE Support
        if (document.selection) {
            el.focus();
            var sel = document.selection.createRange();
            sel.moveStart ('character', -el.value.length);
            caretPos = sel.text.length;
        } else if (el.selectionStart || el.selectionStart == '0') {
            // Firefox support
            caretPos = el.selectionStart;
        }

        return (caretPos);
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

Ext.ComponentMgr.registerPlugin('CBPluginsDropDownList', CB.plugins.DropDownList);
