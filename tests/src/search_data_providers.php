<?php
namespace CB\UNITTESTS\DATA;

function get_basic_search_data() {

    /**
      *        [
                   ['postdata' => 'json', 'expected_response' => 'json' ]
               ]

     */
   $data = [
               [
                   /*
                    * first cb load, grid reload
                    */
                   [
                    'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","id":0,"from":"grid","path":"0","query":null,"start":0,"page":1,"limit":50}],"type":"rpc","tid":89}',
                    'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
                   ]
               ], 
               [ // click on root note on tree
                   [
                    'postdata' => '{"action":"CB_BrowserTree","method":"getChildren","data":[{"from":"tree","path":"/0","showFoldersContent":false,"node":"root"}],"type":"rpc","tid":219}',
                    'expected_response' => '{"action":"CB_BrowserTree","method":"getChildren","result":[{"name":"Tasks","iconCls"
:"icon-task","has_childs":true,"nid":"3-tasks"},{"name":"All Folders","iconCls":"icon-folder"
,"has_childs":true,"nid":"1"},{"name":"Recycle Bin","iconCls":"icon-trash","has_childs"
:true,"nid":"27-recycleBin"}]}'
                   ]
               ],
               [
                   // try general search suery = test
                   [
                    'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","query":"test","descendants":true,"path":"/","from":"grid","page":1,"start":0,"limit":50}],"type":"rpc","tid":18}',
                    'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
                   ]
               ]
           ];

  return $data;
  
}

