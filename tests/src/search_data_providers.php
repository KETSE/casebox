<?php
namespace CB\UNITTESTS\DATA;

function getBasicSearchData()
{
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
                'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","id":0,"from":"grid","path":"0","query":null,"start":0,"page":1,"limit":50,"sort":["order asc",{"property":"name", "direction":"asc"}]}],"type":"rpc","tid":89}',
                'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
            ]
        ],
        [ // click on root note on tree
            [
                'postdata' => '{"action":"CB_BrowserTree","method":"getChildren","data":[{"from":"tree","path":"/0","showFoldersContent":false,"node":"root","sort":"name asc"}],"type":"rpc","tid":219}',
                'expected_response' => '{"action":"CB_BrowserTree","method":"getChildren"}'
            ]
        ],
        [
            // try general search query = test
            [
                'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","query":"test","descendants":true,"path":"/","from":"grid","page":1,"start":0,"limit":50,"strictSort":"order asc"}],"type":"rpc","tid":18}',
                'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
            ]
        ],
        [
            // general search query = test with wmpty sorting
            [
                'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","query":"test","descendants":true,"path":"/","from":"grid","page":1,"start":0,"limit":50,"sort":[]}],"type":"rpc","tid":18}',
                'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
            ]
        ],
        [
            // search with acet filtering
            [
                'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","id":3,"from":"grid","path":"0/1/2/3","query":null,"start":0,"page":1,"filters":{"template_type":[{"f":"template_type","mode":"OR","values":["template"]}],"cid":[{"f":"cid","mode":"OR","values":["1"]}]},"limit":50}],"type":"rpc","tid":67}',
                'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
            ]
        ]

    ];

    return $data;

}
