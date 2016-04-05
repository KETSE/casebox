Ext.namespace('CB.plugin.field');

Ext.define('CB.plugin.field.RemainingCharsHint', {
    extends: 'Ext.util.Observable'

    ,alias: 'plugin.CBPluginFieldRemainingCharsHint'

    ,init: function(owner) {
        this.owner = owner;

        owner.labelSeparator = '.';
        owner.labelAlign = 'top';
        owner.initLabelable();
        owner.labelClsExtra = 'remaining-chars-hint';

        owner.on('boxready', this.onEditorBoxReady, this);
        owner.on('beforedestroy', this.onBeforeDestroy, this);
    }

    ,onEditorBoxReady: function(ed, e){
        //add listeners
        if (ed.maxLength) {
            ed.on('change', this.onValueChange, this);
            this.onValueChange(ed, e);
        }
    }

    ,onBeforeDestroy: function(ed){
        ed.un('render', this.onEditorBoxReady, this);
        ed.un('keyup', this.onValueChange, this);
    }

    ,onValueChange: function(ed, e){
        var value = ed.value
            ,label = L.RemainingCharsHint;

        if (Ext.isEmpty(value)) {
            label = L.TotalCharsHint;
        }

        ed.setFieldLabel(
            label.replace('{total}', ed.maxLength).replace('{left}', ed.maxLength - String(value).length)
        );
    }
});
