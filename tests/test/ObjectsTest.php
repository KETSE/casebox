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

    public function objectsProvider()
    {
        $data =  \CB\UNITTESTS\DATA\objectsProvider();
        return $data;
    }
    /**
     *  test CRUD (temp: for one record) for future create data providers
     * @dataProvider objectsProvider
     */
    public function testObjectCRUD($data)
    {

        $result = false;
        // try to add one folder to root tree 

        $obj = new \CB\Objects\Object();

        // first create object
        $data['id'] = $obj->create($data);


        $this->assertTrue($data['id'] > 0, ' Error on Object create');

        // second read created object
        $obj->load($data['id']);
        $read_data = $obj->getData();
        $this->assertArraySubset($data, $read_data, false,
            ' readed data: '.print_r($read_data, true));

        // third update created object

        $data['name']           = $data['name'].' (updated)';
        $data['data']['_title'] = $data['data']['_title'].' (updated)';

        $obj->update($data);
        $obj->load($data['id']);
        $read_data = $obj->getData();
        $this->assertArraySubset($data, $read_data, false,
            ' error on updated object data ');


        // four delete object

        $obj->delete(false);
        $obj->delete(true);

        $obj->load($data['id']);

        $read_data = $obj->getData();

        $this->assertTrue(empty($read_data['id']),
            'error delete object data');
    }


    public function templatesProvider()
    {
        $data =  \CB\UNITTESTS\DATA\templatesProvider();
        return $data;
    }
   
    /**
     * @dataProvider templatesProvider
     */
    public function testTemplateCRUD($data_template)
    {

        
        ini_set('display_errors', 1);

        $obj = new \CB\Objects\Template();

        // first create object
        $data_template['id'] = $obj->create($data_template);

        $this->assertTrue($data_template['id'] > 0, ' Error on create Template');

        // second read created object
        $obj->load($data_template['id']);
        $read_data_template = $obj->getData();
        $this->assertArraySubset($data_template, $read_data_template, false,
            ' Error read template data '.print_r($read_data_template, true));

        // third update created object

       /* $data_template['title']           = $data_template['title'].' (updated)';

        $obj->update($data_template);

        $obj->load($data_template['id']);
        $read_data_template = $obj->getData();
        $this->assertArraySubset($data_template, $read_data_template, false,
            ' error on updated template data '.print_r($read_data_template, true)); */
        

    }

    /**
     *
     */
    public function testUserCRUD()
    {
        
    }
}