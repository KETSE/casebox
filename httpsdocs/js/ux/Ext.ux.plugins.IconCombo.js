// create namespace for plugins
Ext.namespace('Ext.ux.plugins');

/**
 * Ext.ux.plugins.IconCombo plugin for Ext.form.Combobox
 *
 * @author  Ing. Jozef Sakalos
 * @date    January 7, 2008
 *
 * @class Ext.ux.plugins.IconCombo
 * @extends Ext.util.Observable
 */

Ext.define('Ext.ux.plugins.IconCombo', {
    extend: 'Ext.util.Observable'

    ,constructor: function(config) {
        Ext.apply(this, config);
    }

    ,init: function(combo) {
        this.combo = combo;
        Ext.apply(combo, {
            tpl:  '<tpl for=".">'
                + '<div class="x-combo-list-item ux-icon-combo-item '
                + '{[ Ext.isEmpty(values.id) ? \'-\': \'\']}' + Ext.valueFrom(combo.customIcon, '{' + combo.iconClsField + '}') + '">'
                + '{' + combo.displayField + '}'
                + '</div></tpl>',

            onRender: Ext.Function.createSequence(
                    combo.onRender
                    ,function(ct, position) {
                        // adjust styles
                        this.wrap.applyStyles({position:'relative'});
                        this.el.addCls('ux-icon-combo-input');

                        // add div for icon
                        this.icon = Ext.DomHelper.append(this.el.up('div.x-form-field-wrap'), {
                            tag: 'div', style:'position:absolute'
                        });
                    }
                    ,combo
                ), // end of function onRender

            setIconCls:function() {
                var rec = this.store.query(this.valueField, this.getValue()).getAt(0);
                if(rec && this.icon) {
                    this.icon.className = 'ux-icon-combo-icon ' + (Ext.isEmpty(this.getValue()) ? '' : Ext.valueFrom( this.customIcon, rec.get(this.iconClsField) ) );
                }
            }, // end of function setIconCls

            setValue: Ext.Function.createSequence(
                    combo.setValue
                    ,function(value) {
                        this.setIconCls();
                    }
                    ,combo
                )
        });
    }
});
