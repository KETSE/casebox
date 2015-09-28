Simple Fields Configuration
============================

Create a new thesauri 
------------------------
Use the tree window to browse to System/Templates folder

Create a new folder with the name of the Thesaurus you want to create

Browse to the folder 

Create new Thesauri item

Repeat until you have added all the items in your thesaurus



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

Populated from users
.........................


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


Auto-create a default set of subfolders while creating an object
-----------------------------------------------------------------

If you want to create a set of subfolders with the creation of each new object, all you have to do is create a folder structure and configure your template using the “systems_folders” JSON directive.
