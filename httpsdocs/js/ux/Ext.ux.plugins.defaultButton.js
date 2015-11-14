Ext.ns('Ext.ux.plugins');

Ext.define('Ext.ux.plugins.DefaultButton', {
    alias: 'plugin.defaultButton'

    ,init: function(button) {
        button.on('afterRender', this.setupKeyListener, button);
    }

    ,setupKeyListener: function() {
        var formPanel = this.findParentByType('form');
        new Ext.KeyMap(formPanel.el, {
            key: Ext.event.Event.ENTER
            ,shift: false
            ,alt: false
            ,fn: function(keyCode, e) {
                if (e.target.type === 'textarea' && !e.ctrlKey)  {
                    return true;
                }

                this.el.dom.click();

                return false;
            }
            ,scope: this
        });
    }
});
