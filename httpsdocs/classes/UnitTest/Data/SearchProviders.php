<?php
namespace UnitTest\Data;

class SearchProviders
{
    public static function getBasicSearchData()
    {
        /**
          *        [
                       ['postdata' => 'json', 'expected_response' => 'json' ]
                   ]

         */
        $data = [
           /* [
                /
                * first cb load, grid reload
                * /
                [
                    'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","id":0,"from":"grid","path":"0","query":null,"start":0,"page":1,"limit":50,"sort":["order asc",{"property":"name", "direction":"asc"}]}],"type":"rpc","tid":89}',
                    'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
                ]
            ], */
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
            ],
            [
                // query for calendar view
                [
                    'postdata' => '{"action":"CB_BrowserView","method":"getChildren","data":[{"facets":"general","id":3,"from":"calendar","start":0,"page":1,"limit":50}],"type":"rpc","tid":67}',
                    'expected_response' => '{"action":"CB_BrowserView","method":"getChildren","result":{"success":true}}'
                ]
            ],

            //should be moved to State testing
            [
                // save grid view state
                [
                    'postdata' => '{"action":"CB_State_DBProvider","method":"saveGridViewState","data":[{"params":{"id":"1","from":"tree","path":"0/1","query":null,"start":0,"page":1},"state":{"columns":{"nid":{"idx":0,"width":66,"sortable":true},"name":{"idx":1,"width":341,"sortable":true},"date":{"idx":2,"width":130,"sortable":true},"size":{"idx":3,"width":80,"sortable":true},"cid":{"idx":4,"width":200,"sortable":true},"oid":{"idx":5,"width":200,"sortable":true},"cdate":{"idx":6,"width":130,"sortable":true},"udate":{"idx":7,"width":130,"sortable":true},"path":{"idx":8,"width":150,"hidden":true,"sortable":true},"case":{"idx":9,"width":150,"hidden":true,"sortable":true},"uid":{"idx":10,"width":200,"hidden":true,"sortable":true},"comment_user_id":{"idx":11,"width":200,"hidden":true,"sortable":true},"comment_date":{"idx":12,"width":120,"hidden":true,"sortable":true}},"group":null,"weight":0}}],"type":"rpc","tid":16}',
                    'expected_response' => '{"action":"CB_State_DBProvider","method":"saveGridViewState","result":{"success":true}}'
                ]
            ]

        ];

        return $data;
    }
}
