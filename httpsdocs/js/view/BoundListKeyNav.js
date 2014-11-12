/**
 * A specialized {@link Ext.util.KeyNav} implementation for navigating a {@link Ext.view.BoundList} using
 * the keyboard. The up, down, pageup, pagedown, home, and end keys move the active highlight
 * through the list. The enter key invokes the selection model's select action using the highlighted item.
 */
Ext.define('CB.view.BoundListKeyNav', {
    extend: 'Ext.view.BoundListKeyNav'

    ,alias: 'view.navigation.CBboundlist'

    /**
     * @cfg {Ext.view.BoundList} boundList (required)
     * The {@link Ext.view.BoundList} instance for which key navigation will be managed.
     */

    ,initKeyNav: function(view) {
        var me = this,
            field = me.view.pickerField;

        // BoundLists must be able to function standalone with no bound field
        if (!view.pickerField) {
            return;
        }

        if (!field.rendered) {
            field.on('render', Ext.Function.bind(me.initKeyNav, me, [view], 0), me, {single: true});
            return;
        }

        me.keyNav = new Ext.util.KeyNav({
            target: field.inputEl
            ,forceKeyDown: true
        });

        Ext.apply(
            field
            ,{
                onKeyUp: Ext.Function.bind(this.onKeyUp, this)
                ,onKeyDown: Ext.Function.bind(this.onKeyDown, this)
                ,onKeyEnter: Ext.Function.bind(this.onKeyEnter, this)
                ,onKeyTab: Ext.Function.bind(this.onKeyTab, this)
                ,onKeyEsc: Ext.Function.bind(this.onKeyEsc, this)
                ,setPosition: Ext.Function.bind(this.setPosition, this)
            }
        );
    }
});
