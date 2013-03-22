<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/
require_once realpath( dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR.'config.php';
$customConfigGroups = getCustomGroupsConfig();

return array_merge($customConfigGroups, array(
    'css' => array(	
		'//css/CB.css'
		,'//css/template_icons.css'
		,'//css/tasks.css'
		,'//css/casebox.css'
		,'//css/common.css'
		,'//css/facets.css'
		,'//css/Ext.ux.plugins.IconCombo.css'
		,'//css/calendar.css'
		,'//css/date-time-ux.css'
		,'//css/spinner/Spinner.css'
	)
    ,'js' => array(
		'//js/iso8601.min.js'
		,'//js/customFunctions.js'
		
		,'//js/CB.ViewPort.js'
		,'//js/CB.Login.js'
		,'//js/app.js'
		,'//js/CB.About.js'
		,'//js/CB.Case.js'
		,'//js/CB.GenericForm.js'
		,'//js/CB.ThesauriWindow.js'
		,'//js/CB.Browser.js'
		,'//js/CB.BrowserTree.js'
		,'//js/CB.FolderView.js'
		,'//js/CB.FolderViewGrid.js'
		,'//js/CB.ActionsViewGrid.js'
		,'//js/CB.TasksViewGrid.js'
		,'//js/CB.VerticalEditGrid.js'
		,'//js/CB.Objects.js'
		,'//js/CB.ObjectsField.js'
		,'//js/CB.Tasks.js'
		,'//js/CB.PasteFromWord.js'
		,'//js/CB.FileUploadWindow.js'
		,'//js/CB.Security.js'
		
		,'//js/plugins/CB.plugins.customInterface.js'
		,'//js/ux/Ext.ux.TagEditor.js'
		,'//js/ux/Ext.ux.TreeTagEditor.js'
		,'//js/ux/Ext.ux.TagField.js'
		,'//js/ux/Ext.ux.htmlEditor.js'
		,'//js/ux/Ext.ux.plugins.defaultButton.js'
		,'//js/ux/Ext.ux.plugins.IconCombo.js'
		,'//js/ux/Ext.ux.TitleField.js'
		,'//js/ux/Ext.ux.SearchField.js'
		,'//js/ux/Ext.ux.ThesauriField.js'
		,'//js/ux/spinner/Spinner.js'
		,'//js/ux/spinner/SpinnerStrategy.js'
		,'//js/ux/date-time-ux/BaseTimePicker.js'
		,'//js/ux/date-time-ux/ExBaseTimePicker.js'
		,'//js/ux/date-time-ux/DateTimePicker.js'
		,'//js/ux/date-time-ux/DateTimeMenu.js'
		,'//js/ux/date-time-ux/DateTimeField.js'

		,'//js/CB.TextEditWindow.js'
		,'//js/CB.HtmlEditWindow.js'
		,'//js/CB.UsersGroups.js'
		,'//js/CB.Facet.js'
		,'//js/CB.FacetText.js'
		,'//js/CB.FacetList.js'
		,'//js/CB.SystemManagementWindow.js'
		,'//js/CB.TemplatesManagementWindow.js'
		,'//js/CB.Clipboard.js'
		,'//js/CB.Dashboard.js'
		,'//js/CB.FolderViewSummary.js'
		,'//js/CB.PreviewPanel.js'
		,'//js/CB.FilterPanel.js'
		,'//js/CB.FileWindow.js'
		
		,'//js/calendar/calendar-all-debug.js'
		,'//js/CB.CalendarView.js'
		,'//js/CB.Favorites.js'
		
	)

	,'lang-en' => array('//js/locale/en.js')
	,'lang-ru' => array('//js/locale/ru.js')
	,'lang-fr' => array('//js/locale/fr.js')
	,'lang-hy' => array('//js/locale/hy.js')

    // custom source example
    /*'js2' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => create_function('$a', 'return $a;')
        ))
    ),//*/

    /*'js3' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => array('Minify_Packer', 'minify')
        ))
    ),//*/
)
);