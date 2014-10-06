<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/**
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/
$customConfig = \CB\Config::getMinifyGroups();

return array_merge(
    $customConfig,
    array(
        'css' => array(
            '//css/CB.css'
            ,'//css/template_icons.css'
            ,'//css/tasks.css'
            ,'//css/casebox.css'
            ,'//css/common.css'
            ,'//css/facets.css'
            ,'//css/obj_plugins.css'
            ,'//css/Ext.ux.plugins.IconCombo.css'
            ,'//css/calendar.css'
            ,'//css/date-time-ux.css'
            ,'//css/spinner/Spinner.css'
            ,'//libx/highlight/default.css'
        )

        ,'js' => array(
            '//js/iso8601.min.js'
            ,'//js/ux/md5/spark-md5.min.js'
            ,'//js/ux/md5/Ext.ux.FileMD5.js'
            ,'//js/ux/Ext.ux.WebkitEntriesIterator.js'

            ,'//js/calendar/calendar-all-debug.js'

            ,'//js/CB.Login.js'
            ,'//js/CB.GenericForm.js'

            ,'//js/CB.Breadcrumb.js'
            ,'//js/browser/Tree.js'
            ,'//js/browser/ViewContainer.js'
            ,'//js/browser/view/Interface.js'
            ,'//js/browser/view/Grid.js'
            ,'//js/browser/view/Calendar.js'
            ,'//js/browser/view/Charts.js'
            ,'//js/browser/view/Pivot.js'

            ,'//js/CB.VerticalEditGrid.js'
            ,'//js/CB.VerticalSearchEditGrid.js'
            ,'//js/CB.Objects.js'
            ,'//js/CB.ObjectsField.js'
            ,'//js/CB.PasteFromWord.js'
            ,'//js/CB.FileUploadWindow.js'

            ,'//js/plugins/CB.plugins.customInterface.js'
            ,'//js/plugins/CB.plugins.DropDownList.js'
            ,'//js/ux/Ext.ux.htmlEditor.js'
            ,'//js/ux/Ext.ux.plugins.defaultButton.js'
            ,'//js/ux/Ext.ux.plugins.IconCombo.js'
            ,'//js/ux/Ext.ux.TitleField.js'
            ,'//js/ux/Ext.ux.SearchField.js'

            ,'//js/ux/spinner/Spinner.js'
            ,'//js/ux/spinner/SpinnerStrategy.js'
            ,'//js/ux/date-time-ux/BaseTimePicker.js'
            ,'//js/ux/date-time-ux/ExBaseTimePicker.js'
            ,'//js/ux/date-time-ux/DateTimePicker.js'
            ,'//js/ux/date-time-ux/DateTimeMenu.js'
            ,'//js/ux/date-time-ux/DateTimeField.js'

            ,'//js/CB.TextEditWindow.js'
            ,'//js/CB.HtmlEditWindow.js'
            ,'//js/CB.Facet.js'
            ,'//js/CB.FacetText.js'
            ,'//js/CB.FacetList.js'
            ,'//js/CB.Clipboard.js'
            ,'//js/CB.FilterPanel.js'
            ,'//js/CB.FileWindow.js'

            ,'//js/CB.Favorites.js'

            ,'//js/plugins/CB.plugins.FilesDropZone.js'
            ,'//js/CB.Uploader.js'

            ,'//js/CB.Security.js'

            ,'//js/CB.UsersGroups.js'

            ,'//js/CB.Account.js'
            ,'//js/Validators.js'
            ,'//js/Util.js'

            ,'//js/CB.DD.js'
            ,'//js/DD/CB.DD.Tree.js'
            ,'//js/DD/CB.DD.Grid.js'
            ,'//js/DD/CB.DD.Panel.js'

            ,'//js/CB.VerticalEditGridHelperTree.js'
            ,'//js/DB/ObjectsStore.js'
            ,'//js/DB/DirectObjectsStore.js'
            ,'//js/DB/TemplateStore.js'

            ,'//js/CB.ViewPort.js'

            ,'//js/PluginPanel.js'
            ,'//js/PluginsPanel.js'
            ,'//js/form/view/object/Preview.js'
            ,'//js/form/view/object/Properties.js'
            ,'//js/form/edit/Object.js'
            ,'//js/ObjectCardView.js'
            ,'//js/objects/plugins/Base.js'
            ,'//js/objects/plugins/Thumb.js'
            ,'//js/objects/plugins/Comments.js'
            ,'//js/objects/plugins/ContentItems.js'
            ,'//js/objects/plugins/Files.js'
            ,'//js/objects/plugins/ObjectProperties.js'
            ,'//js/objects/plugins/SystemProperties.js'
            ,'//js/objects/plugins/Meta.js'
            ,'//js/objects/plugins/Tasks.js'
            ,'//js/objects/plugins/Versions.js'
            ,'//js/objects/plugins/CurrentVersion.js'
            ,'//js/CB.WebdavWindow.js'
            ,'//js/SearchPanel.js'
        )

        ,'jsdev' => array(
            '//js/app.js'

            ,'//js/state/DBProvider.js'
        )
    )
);
