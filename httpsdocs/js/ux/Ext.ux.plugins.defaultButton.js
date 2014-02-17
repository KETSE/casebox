(function() {
    var ns = Ext.ns('Ext.ux.plugins');
    /**
     * @class Ext.ux.plugins.DefaultButton
     * @extends Object
     *
     * Plugin for Button that will click() the button if the user presses ENTER while
     * a component in the button's form has focus.
     *
     * @author Stephen Friedrich
     * @date 09-DEC-2009
     * @version 0.1
     *
     */
    ns.DefaultButton =  Ext.extend(Object, {
        init: function(button) {
            button.on('afterRender', setupKeyListener, button);
        }
    });

    function setupKeyListener() {
        var formPanel = this.findParentByType('form');
        new Ext.KeyMap(formPanel.el, {
            key: Ext.EventObject.ENTER
            ,shift: false
            ,alt: false
            ,fn: function(keyCode, e) {
                if (e.target.type === 'textarea' && !e.ctrlKey)  return true;
                this.el.select('button').item(0).dom.click();
                return false;
            }
            ,scope: this
        });
    }
    Ext.ComponentMgr.registerPlugin('defaultButton', ns.DefaultButton);
})();