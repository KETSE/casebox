<?php

namespace UnitTest\Data;

class Providers
{
    /**
     * provide data for creating users tests
     * @return array
     */
    public static function createUsersData()
    {
        $rez = [
            [
                [
                    'name' => 'andrew'
                    ,'first_name' => 'Andrei'
                    ,'last_name' => 'First User'
                    ,'email' => 'email1@test.com'
                ], [
                    'name' => 'bow'
                    ,'first_name' => 'Bow'
                    ,'last_name' => 'Second User'
                    ,'email' => 'email2@test.com'
                ], [
                    'name' => 'tim'
                    ,'first_name' => 'Timoty'
                    ,'last_name' => 'Third User'
                    ,'email' => 'email3@test.com'
                ], [
                    'name' => 'wael'
                    ,'first_name' => 'Wael'
                    ,'last_name' => 'Forth User'
                    ,'email' => 'email4@test.com'
                ]
            ]
        ];

        return $rez;
    }

    /**
     * provide data for creating tasks tests
     * @return array
     */
    public static function createTasksData()
    {
        $rez = [
            [
                [
                    'pid' => 1 //root folder
                    ,'name' => 'Task1'
                    ,'template_id' => 7
                    ,'data' => array(
                        '_title' => '~ Task 1 ~'
                        // ,'assigned' =>
                        ,'importance' => 54
                        ,'description' => 'Testing tasks description'
                    )
                ], [
                    'pid' => 1 //root folder
                    ,'name' => 'Task2'
                    ,'template_id' => 7
                    ,'data' => array(
                        '_title' => '~ Task 2 ~'
                        ,'importance' => 54
                        ,'description' => 'Testing tasks description'
                    )
                ], [
                    'pid' => 1 //root folder
                    ,'name' => 'Task3'
                    ,'template_id' => 7
                    ,'data' => array(
                        '_title' => '~ Task 3 ~'
                        ,'importance' => 54
                        ,'description' => 'Testing tasks description'
                    )
                ]
            ]
        ];

        return $rez;
    }

    /**
     * provide search queries data
     * @return array
     */
    public static function searchQueriesData()
    {
        $rez = [
            [
                [
                    'query' => [
                        'dstatus' => 1
                        ,'fq' => 'template_id:5'
                        ,'system' => 0
                        ,'pids' => 1
                        ,'template_types' => 'file'
                        ,'dateStart' => '2015-01-01'
                        ,'sort' => 'name'
                    ]
                    ,'result' => [
                        'success' => true
                        ,'total' => 0
                        ,'data' => []
                    ]
                ]
            ], [
                [
                    'query' => [
                        'template_types' => 'template'
                        ,'id' => 3
                        ,'pid' => 3
                        ,'showFoldersContent' => true
                        ,'facets' => "general"
                        ,'from' => 'grid'
                        ,'page' => 1
                        ,'path' => "0/1/2/3"
                        ,'facet.field' => 'cid'
                        ,'stats.field' => 'uid'
                        ,'sort' => ['name asc']
                    ]
                    ,'result' => [
                        'success' => true
                    ]
                ]
            ], [
                [
                    'query' => [
                        'template_types' => 'template'
                        ,'sort' => [
                            [
                                'property' => 'name'
                               ,'direction' => 'desc'
                            ]
                        ]
                    ]
                    ,'result' => [
                        'success' => true
                    ]
                ]
            ]
        ];

        return $rez;
    }

    public static function objectsProvider()
    {
        return [
                 [
                   [
                       'name' => 'test',
                       'pid' => 1,
                       'template_id' => 5,
                       'data' => [
                           '_title' => 'test'
                       ]
                   ]
                 ],

                 [
                   [
                       'name' => 'test2',
                       'pid' => 1,
                       'template_id' => 5,
                       'data' => [
                           '_title' => 'test2'
                       ]
                   ]
                 ]

               ];

    }

    public static function templatesProvider()
    {
        return [
                 [
                    [
                        "pid" => 3, // /Tree/System/Templates
                        "template_id" => 11,
                        "type" => "object",
                        "name" => "Test template",
                        // "title" => "Test template l1",
                        // 'l1' => "Test template l1",
                        // 'l2' => "Test template l2",
                        // 'l3' => "Test template l3",
                        // 'l4' => "Test template l4",
                        'order' => '1',
                        'visible' => '1',
                        'iconCls' => "icon-bell",
                        "cfg" => [
                            'createMethod' => 'inline',
                            'object_plugins' => [
                                'objectProperties',
                                'comments',
                                'systemProperties',
                            ]
                        ],
                        "title_template" => "{name}",
                   ],
                ], [
                   [
                        "pid" => 3, // /Tree/System/Templates
                        "template_id" => 11,
                        "type" => "object",
                        "name" => "Test template2",
                        // "title" => "Test template2 l1",
                        // 'l1' => "Test template2 l1",
                        // 'l2' => "Test template2 l2",
                        // 'l3' => "Test template2 l3",
                        // 'l4' => "Test template2 l4",
                        'order' => '1',
                        'visible' => '1',
                        'iconCls' => "icon-bell",
                        "cfg" => [
                            'createMethod' => 'inline',
                            'object_plugins' => [
                                'objectProperties',
                                'comments',
                                'systemProperties',
                            ]
                        ],
                        "title_template" => "{name}",
                   ],

             ]
           ];

    }

    /**
     *
     */
    public static function fieldsProvider()
    {
        /**
        * {"_title":"CheckBox","en":"CheckBox","type":"checkbox","order":1}
        *
        */

        return [
            [
                [
                    // 'id' => null,
                    // 'pid' => null, // id of parent (tempalte object)
                    'template_id' => 12,
                    'name' => "CheckBox",
                    // 'l1' => "CheckBox",
                    // 'l2' => "CheckBox",
                    // 'l3' => "CheckBox",
                    // 'l4' => "CheckBox",
                    'type' => "checkbox",
                    'order' => "1",
                    'cfg' => "",
                    'solr_column_name' => ""
                ]
             ],
             [
                [
                    // 'id' => null,
                    // 'pid' => null, // id of parent (tempalte object)
                    'template_id' => 12,
                    'name' => "Varchar",
                    // 'l1' => "Varchar",
                    // 'l2' => "Varchar",
                    // 'l3' => "Varchar",
                    // 'l4' => "Varchar",
                    'type' => "varchar",
                    'order' => "1",
                    'cfg' => "",
                    'solr_column_name' => ""
                ]
             ],
             [
                [
                    // 'id' => null,
                    // 'pid' => null, // id of parent (tempalte object)
                    'template_id' => 12,
                    'name' => "Date",
                    // 'l1' => "Date",
                    // 'l2' => "Date",
                    // 'l3' => "Date",
                    // 'l4' => "Date",
                    'type' => "date",
                    'order' => "1",
                    'cfg' => "",
                    'solr_column_name' => ""
                ]
             ],
             [

                [
                    // 'id' => null,
                    // 'pid' => null, // id of parent (tempalte object)
                    'template_id' => 12,
                    'name' => "Integer",
                    // 'l1' => "Integer",
                    // 'l2' => "Integer",
                    // 'l3' => "Integer",
                    // 'l4' => "Integer",
                    'type' => "integer",
                    'order' => "1",
                    'cfg' => "",
                    'solr_column_name' => ""
                ]
            ]
        ];

    }
}
