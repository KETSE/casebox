
Ext.namespace('CB.browser');
/**
 * generic methods that could be used from anywere to avoid code duplication
 * @type {Object}
 */

CB.browser.Actions = {

    deleteSelection: function(selection, callback, scope) {
        if(Ext.isEmpty(selection) || !Ext.isArray(selection)) {
            plog('Wrong slection given for deleteSelection', selection);
            return;
        }

        this.lastArguments = arguments;

        Ext.Msg.confirm(
            L.DeleteConfirmation
            ,(selection.length == 1)
                ? L.DeleteConfirmationMessage + ' "' + selection[0].name + '"?'
                : L.DeleteSelectedConfirmationMessage
            ,this.onDeleteSelection
            ,this
        );
    }

    ,onDeleteSelection: function (btn, e) {
        if(btn !== 'yes') {
            this.processDelete(false, e);
            return;
        }

        var selection = this.lastArguments[0];
        if(Ext.isEmpty(selection)) {
            return;
        }

        var ids = [];
        for (var i = 0; i < selection.length; i++) {
            ids.push(Ext.valueFrom(selection[i].nid, selection[i].id));
        }
        CB_BrowserView['delete'](ids, this.processDelete, this);
    }

    ,processDelete: function(r, e) {
        var args = this.lastArguments
            ,callback = args[1]
            ,scope = args[2];

        if(callback) {
            if(scope) {
                callback = Ext.Function.bind(callback, scope);
            }
            callback(r, e);
        }

        if(r && (r.success === true)) {
            App.mainViewPort.fireEvent('objectsdeleted', r.ids, e);
        }
    }

};
