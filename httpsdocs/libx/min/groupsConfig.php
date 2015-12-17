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
            ,'//css/taskbar.css'
            ,'//css/casebox.css'
            ,'//css/common.css'
            ,'//css/facets.css'
            ,'//css/obj_plugins.css'
            ,'//css/activity-stream-view.css'

            ,'//css/extensible-all.css'
            ,'//css/calendar.css'
            ,'//css/calendar-colors.css'

            ,'//libx/highlight/default.css'

            ,'//css/fix.css'
        )

        ,'js' => array(
            '//js/DB/Models.js'

            ,'//js/iso8601.min.js'
            ,'//js/ux/md5/spark-md5.min.js'
            ,'//js/ux/md5/Ext.ux.FileMD5.js'
            ,'//js/ux/Ext.ux.WebkitEntriesIterator.js'

            ,'//js/calendar/data/EventMappings.js'
            ,'//js/calendar/data/EventModel.js'
            ,'//js/calendar/data/CalendarMappings.js'
            ,'//js/calendar/data/CalendarModel.js'
            ,'//js/calendar/data/MemoryCalendarStore.js'
            ,'//js/calendar/data/MemoryEventStore.js'

            ,'//js/calendar/util/Date.js'
            ,'//js/calendar/util/WeekEventRenderer.js'

            ,'//js/calendar/dd/StatusProxy.js'
            ,'//js/calendar/dd/DragZone.js'
            ,'//js/calendar/dd/DropZone.js'
            ,'//js/calendar/dd/DayDragZone.js'
            ,'//js/calendar/dd/DayDropZone.js'

            ,'//js/calendar/form/field/CalendarCombo.js'
            ,'//js/calendar/form/field/DateRange.js'
            ,'//js/calendar/form/field/ReminderCombo.js'

            ,'//js/calendar/form/EventDetails.js'
            ,'//js/calendar/form/EventWindow.js'

            ,'//js/calendar/template/BoxLayout.js'
            ,'//js/calendar/template/DayBody.js'
            ,'//js/calendar/template/DayHeader.js'
            ,'//js/calendar/template/Month.js'

            ,'//js/calendar/view/AbstractCalendar.js'
            ,'//js/calendar/view/MonthDayDetail.js'
            ,'//js/calendar/view/Month.js'
            ,'//js/calendar/view/DayHeader.js'
            ,'//js/calendar/view/DayBody.js'
            ,'//js/calendar/view/Day.js'
            ,'//js/calendar/view/Week.js'

            ,'//js/calendar/CalendarPanel.js'
            // ,'//js/calendar/extensible-all-debug.js'

            ,'//js/CB.Login.js'
            ,'//js/CB.GenericForm.js'

            ,'//js/CB.ObjectsField.js'

            ,'//js/browser/Actions.js'
            ,'//js/browser/Tree.js'
            ,'//js/browser/ViewContainer.js'
            ,'//js/browser/view/Interface.js'
            ,'//js/browser/view/Grid.js'
            ,'//js/browser/view/ActivityStream.js'
            ,'//js/browser/view/grid/toolbar/Paging.js'
            ,'//js/browser/view/grid/feature/Grouping.js'
            ,'//js/browser/view/Calendar.js'
            ,'//js/browser/view/Charts.js'
            ,'//js/browser/view/Dashboard.js'
            ,'//js/browser/view/Map.js'
            ,'//js/browser/view/Pivot.js'

            ,'//js/CB.VerticalEditGrid.js'
            ,'//js/CB.VerticalSearchEditGrid.js'
            ,'//js/CB.PasteFromWord.js'
            ,'//js/CB.FileUploadWindow.js'

            ,'//js/plugin/CustomInterface.js'
            ,'//js/plugin/field/DropDownList.js'
            ,'//js/ux/Ext.ux.htmlEditor.js'
            ,'//js/ux/Ext.ux.plugins.defaultButton.js'
            ,'//js/ux/Ext.ux.plugins.IconCombo.js'

            ,'//js/CB.TextEditWindow.js'
            ,'//js/CB.HtmlEditWindow.js'
            ,'//js/facet/Base.js'
            ,'//js/facet/Text.js'
            ,'//js/facet/List.js'
            ,'//js/facet/Calendar.js'
            ,'//js/facet/UsersColor.js'
            ,'//js/CB.Clipboard.js'
            ,'//js/CB.FilterPanel.js'

            ,'//js/favorites/Panel.js'
            // ,'//js/favorites/Button.js'

            ,'//js/plugin/dd/FilesDropZone.js'
            ,'//js/CB.Uploader.js'

            ,'//js/CB.Security.js'

            ,'//js/CB.UsersGroups.js'

            ,'//js/CB.Account.js'
            ,'//js/Validators.js'
            ,'//js/Util.js'

            ,'//js/CB.DD.js'
            ,'//js/DD/Tree.js'
            ,'//js/DD/Grid.js'
            ,'//js/DD/Panel.js'

            ,'//js/CB.VerticalEditGridHelperTree.js'
            ,'//js/DB/ObjectsStore.js'
            ,'//js/DB/DirectObjectsStore.js'
            ,'//js/DB/TemplateStore.js'

            ,'//js/ViewPort.js'

            ,'//js/plugin/Panel.js'
            ,'//js/object/view/Preview.js'
            ,'//js/object/view/Properties.js'
            ,'//js/object/edit/Form.js'
            ,'//js/object/edit/Window.js'
            ,'//js/object/widget/TitleView.js'
            ,'//js/object/ViewContainer.js'
            ,'//js/search/edit/Panel.js'
            ,'//js/search/edit/Window.js'
            ,'//js/search/Field.js'
            ,'//js/object/plugin/Base.js'
            ,'//js/object/plugin/Thumb.js'
            ,'//js/object/plugin/Comments.js'
            ,'//js/object/plugin/ContentItems.js'
            ,'//js/object/plugin/Files.js'
            ,'//js/object/plugin/ObjectProperties.js'
            ,'//js/object/plugin/SystemProperties.js'
            ,'//js/object/plugin/Meta.js'
            ,'//js/object/plugin/Tasks.js'
            ,'//js/object/plugin/Versions.js'
            ,'//js/object/plugin/CurrentVersion.js'
            ,'//js/file/edit/Window.js'
            ,'//js/CB.WebdavWindow.js'

            ,'//js/state/DBProvider.js'

            ,'//js/field/Comment.js'
            ,'//js/field/CommentLight.js'

            ,'//js/widget/Breadcrumb.js'
            ,'//js/widget/DataSorter.js'
            ,'//js/widget/LeafletPanel.js'
            ,'//js/widget/LeafletWindow.js'
            ,'//js/widget/TaskBar.js'
            ,'//js/widget/block/Base.js'
            ,'//js/widget/block/Chart.js'
            ,'//js/widget/block/Grid.js'
            ,'//js/widget/block/Map.js'
            ,'//js/widget/block/Pivot.js'
            ,'//js/widget/block/Template.js'
        )

        ,'jsdev' => array(
            '//js/app.js'
            ,'//js/controller/Browsing.js'
            ,'//js/controller/History.js'

            ,'//js/object/field/editor/Form.js'
            ,'//js/object/field/editor/Tag.js'
            ,'//js/view/BoundListKeyNav.js'
            ,'//js/notifications/View.js'
            ,'//js/notifications/SettingsWindow.js'
            // ,'//js/overrides/form/action/Submit.js'
        )

        ,'jsoverrides' => array(
            '//js/overrides/Ajax.js'
            ,'//js/overrides/Patches.js'

            ,'//js/overrides/calendar/dd/DayDropZone.js'
            ,'//js/overrides/calendar/form/field/DateRange.js'
            ,'//js/overrides/calendar/template/BoxLayout.js'
            ,'//js/overrides/calendar/view/Day.js'
            ,'//js/overrides/calendar/view/DayBody.js'
            ,'//js/overrides/calendar/view/Month.js'
            ,'//js/overrides/calendar/CalendarPanel.js'

            // ,'//js/overrides/direct/JsonProvider.js'
            ,'//js/overrides/data/Store.js'

            ,'//js/overrides/grid/plugin/CellEditing.js'
            ,'//js/overrides/grid/CellEditor.js'
            ,'//js/overrides/grid/GridPanel.js'

            ,'//js/overrides/tree/ViewDragZone.js'

            ,'//js/overrides/toolbar/Toolbar.js'

            ,'//js/overrides/util/Collection.js'
            ,'//js/overrides/util/AbstractMixedCollection.js'
            ,'//js/overrides/util/Format.js'
        )
    )
);
