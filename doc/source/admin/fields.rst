Fields
============================


Field types
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





Populate a field from a Thesaurus
---------------------------------

When creating the field pick type object
In config add the ID of the Thesaurus folder

.. code-block:: json

	{
	"scope": thesaurus_folder_id
	} 

You may also choose if you want a default value for a new object using the “value” JSON directive.

Note: You can get the Id of any folder/object by clicking on the object and reading the ID from the top panel.


Populated a field from an Object
--------------------------------
When creating the field pick type object
In config add the ID of the folder

.. code-block:: json

	{
	"source":"tree",
	"scope": folder_id
	} 

Populated a field from users
----------------------------
To choose from a list of users use the "Source" directive with the value of "users".

.. code-block:: json

	{
	"source":"users"
	}


Populated a field from a list of specific objects
--------------------------------------------------
To choose from a list of users use the "Template" directive with value of the template id.

.. code-block:: json

	{
	"templates":[8178]
	}


Where 8178 is the template ID

Create a multi select field
----------------------------

When creating the field pick type object. 

You can add the following directive to the JSON configuration *"multiValued":true*

For example this is the config of a field looking up values from a thesaurus indicating it is a multi value field

.. code-block:: json

	{
		"scope": thesaurus_folder_id
		"multiValued": true,
		"editor": "form", 
		"renderer": "listObjIcons"
	} 

Note: You can get the Id of any folder/object by clicking on the object and reading the ID from the top panel.



Create a conditional field 
----------------------------
Let's say you want to create a conditional field, for example if you pick a country and you want to automatically find options of cities within a country. 

Conditional Field from a Thesaurus
...................................

First create a thesaurus with the parent object as shown before, but with cities as children thesaurus items as shown below.


.. image:: /i/admin/template-conditional1.png

Next create a subfield and configure it so that its scope is 'variable' and add a 'dependency' directive, that way you tell casebox that once the parent country is chosen, the options of cities will appear based on the chosen country.

.. image:: /i/admin/template-conditional2.png

Conditional Field from Different Objects
........................................

You can  have conditional fields which appear based on specific values of the parent field. You need to add the 'dependency' directive and indicate what value of the parent field needs to be fulfilled

Example:

.. code-block:: json

	{
		"dependency": {
			"pidValues" : [391]
		}
	}

If you want a field to be a drop down or multi-select, add to it the source as shown in instructions above.

Example:

.. code-block:: json

	{
		"source":"tree",
		"scope": [390],
		"dependency": {
			"pidValues" : [391]
		}
	}


