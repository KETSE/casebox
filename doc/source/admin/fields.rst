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





Create a drop down list field
---------------------------------------------------

Populated from a Thesaurus
...........................

When creating the field pick type object
In config add the ID of the Thesaurus folder

{
"scope": thesaurus_folder_id
} 

You may also choose if you want a default value for a new object using the “value” JSON directive.

Note: You can get the Id of any folder/object by clicking on the object and reading the ID from the top panel.


Populated from an Object
..........................
When creating the field pick type object
In config add the ID of the folder

{
"source":"tree",
"scope": folder_id
} 

Populated from users
.........................
To choose from a list of users use the "Source" directive with the value of "users".
{
"source":"users"
}

Create a multi select field
----------------------------

When creating the field pick type object

In config add the ID of the Thesaurus folder indicating it is a multi value field

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

First create a thesaurus with the parent object as shown before, but with cities as children thesaurus items as shown below.



Next create a subfield and configure it so that its scope is 'variable' and add a 'dependency' directive, that way you tell casebox that once the parent country is chosen, the options of cities will appear based on the chosen country.


Congratulations! You've created a conditional field.


