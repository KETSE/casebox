// helper functions for caret positioning in HTML text fields
// http://www.sencha.com/forum/showthread.php?95486#post609639
Ext.override(Ext.form.field.Text, {
    setCaretPosition: function(pos) {
        var el = this.inputEl.dom;

        if (typeof(el.selectionStart) === "number") {
            el.focus();
            el.setSelectionRange(pos, pos);

        } else if (el.createTextRange) {
            var range = el.createTextRange();
            range.move("character", pos);
            range.select();

        } else {
            throw 'setCaretPosition() not supported';
        }
    }

    ,getCaretPosition: function() {
        var el = this.inputEl.dom;

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
});
