Forms
=======

These are the steps required to be able to use a new form/object in Casebox

1. Create a new form
2. Add fields to the newly created form
3. Make the form available in menus


Create New Form
----------------
Use the tree window to browse to System/Templates folder

.. image:: /i/admin/System-Template.PNG

Use the “New” button and choose “Template” 

.. image:: /i/admin/New-Template.PNG


Create form/object name by filling out the form

.. image:: /i/admin/New-Template-Form.PNG






Add fields to form 
-------------------

Browse inside the newly created template (by double clicking newly created template) and adding different fields through the new “Field"  button.

.. image:: /i/admin/new-field-menu.PNG

Add a new field

.. image:: /i/admin/new-field.PNG

For more information on field creation visit: Fields





Auto-create a default set of subfolders
---------------------------------------

If you want to create a set of subfolders with the creation of each new object, all you have to do is create a folder structure and configure your template using the “systems_folders” JSON directive.

.. image:: /i/admin/auto-create-subfolders.png




'_title' field name and '_auto_title' field type
-------------------------------------------------

Each template should have a field named _title, it tells CaseBox to use the value of the field for display purposes (in the tree, grid). Usually its of varchar type. When the title can be automatically generated out of other fields, then a special _auto_title field type is used.

_auto_title is specified in `templates` table. Examples:

{f34} {f35} {f36} - use field id's from `templates_structure`
{template_title}: {where_submitted} {where_submitted_info} - or use field names

Advanced Template Configuration
--------------------------------

For Template configuration

+----------------+---------------------------------------+------------------------------------------------------------------------------+
| Property       | Description                           | Example                                                                      |
+================+=======================================+==============================================================================+
| id             | a unique identificator of the template| Used in configuring the Menu and the filter in _objects field type.          |
+----------------+---------------------------------------+------------------------------------------------------------------------------+
| Name           | the name of a template                |                                                                              |
+----------------+---------------------------------------+------------------------------------------------------------------------------+
| $lang          | template title in a given language    | example 'en'. CB will show the template title if  language specified by  user|
+----------------+---------------------------------------+------------------------------------------------------------------------------+
| Type           | see Template Types                    | Most of the time you'll manage 'object' templates                            |
+----------------+---------------------------------------+------------------------------------------------------------------------------+
| Active         | yes/no                                | template can be turned on/off. Notice that you need to specify in which menu | 
|                |                                       | the template will appear. See New menu                                       |
+----------------+---------------------------------------+------------------------------------------------------------------------------+
|Icon Class      |what icon to use when displaying       | Additinal css files can be added to the configuration                        |
|                |the object in the grid                 | thus allowing for custom icons.                                              | 
+----------------+---------------------------------------+------------------------------------------------------------------------------+
| Title template | The title can be automatically        | Product N{nr} from {country}                                                 |
|                | generated from other fields           |                                                                              |
+----------------+---------------------------------------+------------------------------------------------------------------------------+
| Config         | a JSON cfg                            | See below                                                                    |
+----------------+---------------------------------------+------------------------------------------------------------------------------+



Config a JSON cfg: available object_plugins, grid display columns etc

.. code-block:: json

	{
	    "object_plugins": [
	        "objectProperties",
	        "thumb",
	        "meta",
	        "files",
	        "contentItems",
	        "comments",
	        "systemProperties"
	    ],
	    "layout": [vertical, horizontal],
	    "DC": {
	        "type": {},
	        "order": {}
	    },
	    "defaultPid": int,
	    "leaf": true/false,
	    "acceptChildren": true/false
	}

**object_plugins**: what plugins are available in the preview panel when the record is selected

**layout**: how the Preview&Edit Window will display the node, with a right panel with plugins, or the plugins displayed below the vGrid.

**DC**: Display columns for the Grid when navigating inside the record

**defaultPid**: if parent node is not specified when a record is created, use defaultPid from template

**leaf**: double clicking the node in Grid will Edit it instead of opening it as a folder. Default = false

**acceptChildren**: Allow D&D operations over the node, fileUpload for ex. Default = true




​











