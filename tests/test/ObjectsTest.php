<?php

namespace CB\UNITTESTS;

/**
 * Description of ObjectsTest
 *
 * @author ghindows
 */
class ObjectsTest extends \PHPUnit_Framework_TestCase
{
    private $DB;

    public function setUp()
    {

        $this->DB = \CB\Cache::get('dbh');
    }

    /**
     *  test CRUD (temp: for one record) for future create data providers 
     */
    public function testCRUD()
    {

        $result = false;
        // try to add one folder to root tree 
        $data   = [
            'name' => 'test',
            'pid' => 1,
            'template_id' => 5,
            'data' => [
                '_title' => 'test'
            ]
        ];

        $obj = new \CB\Objects\Object();

        // first create object
        $data['id'] = $obj->create($data);


        $this->assertTrue($data['id'] > 0, ' NOT Object created');

        // second read created object
        $obj->load($data['id']);
        $read_data = $obj->getData();
        $this->assertArraySubset($data, $read_data, false,
            ' readed data: '.print_r($read_data, true));

        // third update created object

        $data['name']           = $data['name']. ' (updated)';
        $data['data']['_title'] = $data['data']['_title'] . ' (updated)';
        
        $obj->update($data);

        $read_data = $obj->getData();
        $this->assertArraySubset($data, $read_data, false,
            ' readed updated data: '.print_r($read_data, true));


        // four delete object

        $obj->delete(false);
        $obj->delete(true);

        $obj->load($data['id']);

        $read_data = $obj->getData();

        $this->assertTrue(empty($read_data['id']),
            'error delete data: '.print_r($read_data, true));
    }
}