<?php
namespace CB\UNITTESTS\DATA;

function objectsProvider()
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


   function templatesProvider()
    {

        return [
                 [
                    [
                        "pid" => 3, // /Tree/System/Templates
                        "template_id" => 11,
                        "type" => "object",
                        "name" => "Test template",
                        "title" => "Test template l1",
                        'l1' => "Test template l1",
                        'l2' => "Test template l2",
                        'l3' => "Test template l3",
                        'l4' => "Test template l4",
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
                        "title" => "Test template2 l1",
                        'l1' => "Test template2 l1",
                        'l2' => "Test template2 l2",
                        'l3' => "Test template2 l3",
                        'l4' => "Test template2 l4",
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
    function fieldsProvider() {
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
                'l1' => "CheckBox",
                'l2' => "CheckBox",
                'l3' => "CheckBox",
                'l4' => "CheckBox",
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
                'l1' => "Varchar",
                'l2' => "Varchar",
                'l3' => "Varchar",
                'l4' => "Varchar",
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
                'l1' => "Date",
                'l2' => "Date",
                'l3' => "Date",
                'l4' => "Date",
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
                'l1' => "Integer",
                'l2' => "Integer",
                'l3' => "Integer",
                'l4' => "Integer",
                'type' => "integer",
                'order' => "1",
                'cfg' => "",
                'solr_column_name' => ""
            ]
        ]
    ];
 
}

