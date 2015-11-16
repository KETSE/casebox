Facets
======

All you have to do to facet an object is to declare “faceting”: true and name the solr column any name followed by _i if it is a single value object or _is if it is a multivalued object.

For custom solr columns see developer guide.

Once you've faceted the fields you'd need to set up where you want to display the faceting options. You can add it to a folder, a virtual folder or a search.

Setting up Facets
-----------------

For folders
............
Go to Settings/Config
Edit facet_configs
Add the facet titles you want to use

{ 
"case_lawyers": { 
"field": "case_lawyers", // SOLR field 
"title": "Lawyers", // Facet title in CB filtering panel 
"type": "objects" // type of facet: 'objects' most of the time or users in case of user lists } 
} 
This means you've set up the name case_lawyers to use as a facet in any of your folders, now you need to pick which folders use this facet.

In order to pick the folder
Go to Settings/Config
Edit the node_facets config field
Add in the facets to the folders as follows

{ "$objectId": ["facetName1", "facetName2", ...], "$objectId": ["facetName3", "facetName4", ...] } 

Example:
{
     "607": [
       "violations"
       ,"intervention"
       ,"case_status"
       ,"governorate"
       ,"programs"
       ,"office"
     ]
    , "8280" : [
    "unit"
    ,"program"
    ,"project_status"
    ,"tags"
    ,"internal_team"
    ]
}

Where the folder Ids are 607 and 8280.

That's it, when you click on a these folders and you click the filter icon you will be able to filter your folder using these facets.