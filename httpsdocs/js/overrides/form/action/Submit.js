Ext.namespace('Ext.form.action');

Ext.override(Ext.form.action.Submit, {
    /**
     * @private
     * Builds a form element containing fields corresponding to all the parameters to be
     * submitted (everything returned by {@link #getParams}.
     *
     * NOTE: the form element is automatically added to the DOM, so any code that uses
     * it must remove it from the DOM after finishing with it.
     *
     * @return {HTMLElement}
     */
    buildForm: function() {
        var me = this,
            fieldsSpec = [],
            formSpec,
            formEl,
            basicForm = me.form,
            params = me.getParams(),
            uploadFields = [],
            uploadEls = [],
            fields = basicForm.getFields().items,
            i,
            len   = fields.length,
            field, key, value, v, vLen,
            el;

        for (i = 0; i < len; ++i) {
            field = fields[i];

            if (field.isFileUpload()) {
                uploadFields.push(field);
            }
        }

        for (key in params) {
            if (params.hasOwnProperty(key)) {
                value = params[key];

                if (Ext.isArray(value)) {
                    vLen = value.length;
                    for (v = 0; v < vLen; v++) {
                        fieldsSpec.push(me.getFieldConfig(key, value[v]));
                    }
                } else {
                    fieldsSpec.push(me.getFieldConfig(key, value));
                }
            }
        }

        formSpec = {
            tag: 'form',
            role: 'presentation',
            action: me.getUrl(),
            method: me.getMethod(),
            target: me.target || '_self',
            style: 'display:none',
            cn: fieldsSpec
        };

        // Set the proper encoding for file uploads
        if (uploadFields.length) {
            formSpec.encoding = formSpec.enctype = 'multipart/form-data';
        }

        // Create the form
        formEl = Ext.DomHelper.append(Ext.getBody(), formSpec);

        // Special handling for file upload fields: since browser security measures prevent setting
        // their values programatically, and prevent carrying their selected values over when cloning,
        // we have to move the actual field instances out of their components and into the form.
        len = uploadFields.length;

        for (i = 0; i < len; ++i) {
            el = uploadFields[i].extractFileInput();
            formEl.appendChild(el);
            uploadEls.push(el);
        }

        return {
            formEl: formEl,
            uploadFields: uploadFields,
            uploadEls: uploadEls
        };
    }
});
