Introduction
============

Administrator Manual describes what's possible to configure directly from Casebox front-end without the need for server level access or development.

This includes a set of HOW-TOs for simple form creation and administration as well as other configurations that help customize Casebox to your organization specific needs.





In order to configure new objects here are a few quick steps to help you get started.

To start adding a new template to input data records, you need to create a new Form/Template. 

Once you've added a template you can configure the template, and add new fields to it. The fields can be of various types, number, date, or an object you can look up from a thesaurus or from other objects you've created the tree or from a list of users. You can also set up conditional fields that rely on a value chosen by the user in the form.

Once you have your form set up, you can choose where it appears by configuring your menus. When a form is created it is not added to the menu by default, you must explicitly add it to menu. The form can appear globally, or under a specific folder or template.

You may wish to configure your form so that some fields are easily used for search, this is called faceting. You can define your facets by assigning the field to the SOLR database which is used for indexing your data. This is a simple task that involves adding a SOLR column name to your field. Next you would configure your facets in your casebox configuration, and add that to your filters or smart folders.

Now that you have configured your forms in the database, you may choose to navigate your data using the facets you created by creating smart folders. These folders navigate your data based on search values, think of it as a constant running search. 

You may also want to set up search templates if you want to search for specific items that match specific fields in your form. 

These are simple guidelines to creating your own forms and administering your Casebox.
