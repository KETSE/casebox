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
                   [
                    'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","id":0,"from":"grid","path":"0","query":null,"start":0,"page":1,"limit":50}],"type":"rpc","tid":89}',
                    'expected_response' => '{"type":"rpc","tid":89,"action":"CB_BrowserView","method":"getChildren","result":{"success":true,"pathtext"
:"My CaseBox","folderProperties":{"name":"","path":"\/0","menu":""},"data":[{"name":"Tasks","iconCls"
:"icon-task","has_childs":true,"nid":"3-tasks"},{"name":"All Folders","iconCls":"icon-folder","has_childs"
:true,"nid":"1"},{"name":"Recycle Bin","iconCls":"icon-trash","has_childs":true,"nid":"27-recycleBin"
}],"total":3,"page":1}}'
                   ]
               ], 
               [
                   [
                    'postdata' => '{"action":"CB_BrowserTree","method":"getChildren","data":[{"from":"tree","path":"/0","showFoldersContent":false,"node":"root"}],"type":"rpc","tid":219}',
                    'expected_response' => '{"type":"rpc","tid":219,"action":"CB_BrowserTree","method":"getChildren","result":[{"name":"Tasks","iconCls"
:"icon-task","has_childs":true,"nid":"3-tasks","loaded":false},{"name":"All Folders","iconCls":"icon-folder"
,"has_childs":true,"nid":"1","loaded":false},{"name":"Recycle Bin","iconCls":"icon-trash","has_childs"
:true,"nid":"27-recycleBin","loaded":false}]}'
                   ]
               ],
               [
                   [
                    'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","query":"test","descendants":true,"path":"/","from":"grid","page":1,"start":0,"limit":50}],"type":"rpc","tid":18}',
                    'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
                   ]
               ]
       /*,
               [
                   [
                    'postdata' => '{"action":"CB_Objects","method":"getPluginsData","data":[{"id":"1","name":"All Folders","viewIndex":0
}],"type":"rpc","tid":107}',
                    'expected_response' => '{"type":"rpc","tid":107,"action":"CB_Objects","method":"getPluginsData","result":{"success":true,"data"
:{"objectProperties":{"success":true,"data":{"preview":["",""],"path":"","id":"1","template_id":"5","name"
:"Tree","date_end":null,"cid":"1","cdate":"2012-11-17T15:10:21Z","uid":"1","udate":"2014-01-17T11:53
:00Z","udate_ago_text":"2014, January 17","can":[]}},"comments":{"success":true,"data":[],"total":0}
,"systemProperties":{"success":true,"data":{"id":"1","template_id":"5","size":null,"cid":"1","cdate"
:"2012-11-17T15:10:21Z","uid":"1","udate":"2014-01-17T11:53:00Z","did":null,"dstatus":"0","path":"","template_name"
:"folder","subscription":"ignore","cid_text":"Administrator","cdate_text":"2012, November 17","uid_text"
:"Administrator","udate_text":"2014, January 17"}}},"menu":"7,-,5"}}'
                   ]
               ] */
           ];

  return $data;
  
}

