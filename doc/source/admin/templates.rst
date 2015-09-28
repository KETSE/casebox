Simple Object Creation
======================


Simple Form/Template Creation
---------------------
Templates are managed in ``/System/Templates/`` folder, by default it's accessible only to the ``root`` user.


Create new form
..................

Use the tree window to browse to System/Templates folder



Use the “New” button and choose “Templates Template” (We should call it Form Template)





Create form/object name by filling out the form




These are features available in CB platform, but an instance may store any template properties in config JSON.
Property
Description
Example
id
a unique identificator of the template
Used in configuring the Menu and the filter in _objects field type.
Name
the name of a template

$lang
template title in a given language
example 'en'. CB will show the template title if the language specified by the user
Type
see Template Types
Most of the time you'll manage 'object' templates
Active
yes/no
template can be turned on/off. Notice that you need to specify in which menu the template will appear. See New menu
Icon class
what icon to use when displaying the object in the grid
Additinal css files can be added to the configuration, thus allowing for custom icons.
Title template
The title can be automatically generated from other fields
Product N{nr} from {country}
Config
a JSON cfg: available object_plugins, grid display columns etc
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

object_plugins: what plugins are available in the preview panel when the record is selected
layout: how the Preview&Edit Window will display the node, with a right panel with plugins, or the plugins displayed below the vGrid.
DC: Display columns for the Grid when navigating inside the record
defaultPid: if parent node is not specified when a record is created, use defaultPid from template
leaf: double clicking the node in Grid will Edit it instead of opening it as a folder. Default = false
acceptChildren: Allow D&D operations over the node, fileUpload for ex. Default = true





Add fields to form 
...................

Browse inside the newly created template (by double clicking newly created template) and adding different fields through the new “Fields template” button.








Choose the name of the field and the display title and type



​ Field types
Casebox supports the following list of field types:
Varchar: simplest one-line text field
Date
Datetime
Time
Float
Integer
Group: it's not a visible field, but a way to group several fields.
Header: a Header to visually separate fields
Html: HTML editor
Text: Text editor (in a popup window)
Memo: Inline text editor (multiline text editor inside the grid)
IconCombo: used in Templates to select the icon
Objects: lookup field with values from the Tree itself


Form items created!



Add the newly created template to a menu

Now you have to enable creating the new form you created by adding it to the menu.

Browse to System/Menus



Edit General Menu
...................

Edit the 'Menu' label and add your object by enabling the checkbox next to its title


Sort your menus by clicking sort value and arrange your menus



That's it you have a form with fields you've created and you can create numerous objects and attach files to them.
