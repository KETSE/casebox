<?php
namespace UnitTest;

use CB\Util;

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
        $data = Data\Providers::objectsProvider();

        return $data;
    }

    /**
     *  test CRUD (temp: for one record) for future create data providers
     * @dataProvider objectsProvider
     */
    public function testObjectCRUD($data)
    {
        // try to add one folder to root tree
        $obj = new \CB\Objects\Object();

        // first create object
        $data['id'] = $obj->create($data);

        $this->assertTrue($data['id'] > 0, ' Error on Object create');

        // second read created object
        $obj->load($data['id']);
        $read_data = $obj->getData();
        $this->assertArraySubset($data, $read_data, false, ' readed data: '.print_r($read_data, true));

        // third update created object

        $data['name']           = $data['name'].' (updated)';
        $data['data']['_title'] = $data['data']['_title'].' (updated)';

        $obj->update($data);
        $obj->load($data['id']);
        $read_data = $obj->getData();
        $this->assertArraySubset($data, $read_data, false, ' error on updated object data ');

        // four delete object

        $obj->delete(false);
        $obj->delete(true);

        $obj->load($data['id']);

        $read_data = $obj->getData();

        $this->assertTrue(empty($read_data['id']), 'error delete object data');
    }

    /**
     *
     * @return array of templates
     */
    public function templatesProvider()
    {
        $data = Data\Providers::templatesProvider();

        return $data;
    }

    /**
     * @dataProvider templatesProvider
     */
    public function testTemplateCRUD($td)
    {

        $obj = new \CB\Objects\Template();

        // first create object
        $td['id'] = $obj->create($td);

        $this->assertTrue($td['id'] > 0, ' Error on create Template');

        // second read created object
        $obj->load($td['id']);
        $readTd = $obj->getData();

        $vtd = $td;
        $vtd['cfg'] = Util\toJsonArray($td['cfg']);

        $this->assertArraySubset(
            $vtd,
            $readTd,
            false,
            ' Error read template data ' . print_r($readTd, true)
        );

        // third update created object

        $td['data']['visible'] = 0;
        $td['data']['order'] = 2;
        $td['data']['iconCls'] = '';

        $obj->update($td);

        $obj->load($td['id']);
        $readTd = $obj->getData();

        $vtd = $td;
        $vtd['cfg'] = Util\toJsonArray($td['cfg']);

        $this->assertArraySubset(
            $vtd,
            $readTd,
            false,
            ' error on updated template data '.print_r($readTd, true)
        );
    }

     /**
     *
     * @return array of templates
     */
    public function fieldsTeplateProvider()
    {
        $data = Data\Providers::fieldsProvider();

        return $data;
    }

    /**
     * @dataProvider fieldsTeplateProvider
     */
    public function testFieldsTemplateCRUD($data_field)
    {

         $tpl_obj = new \CB\Objects\Template();

        $TPL_SQL = 'select id from tree where `name` like "Test fields CRUD" and pid = 3 and template_id = 11 limit 1';

        $tpl_r = \CB\DB\dbQuery($TPL_SQL);

        if ($tpl = $tpl_r->fetch_assoc()) {
            $data_template['id'] = $tpl['id'];
            $this->assertTrue($data_template['id'] > 0, ' Error on load Template');
        } else/**/ {
            // first add empty template
            $data_template = [
                "pid" => 3, // /Tree/System/Templates
                "template_id" => 11,
                "type" => "object",
                "name" => "Test fields CRUD",
                "title" => "Test fields CRUD",
                // 'l1' => "Test fields CRUD",
                // 'l2' => "Test fields CRUD",
                // 'l3' => "Test fields CRUD",
                // 'l4' => "Test fields CRUD",
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
                "title_template" => "{name}"
            ];

            $data_template['id'] = $tpl_obj->create($data_template);

            $this->assertTrue($data_template['id'] > 0, ' Error on create Template');

        }

        // CREATE FIELDS

        if (isset($data_template['id']) && $data_template['id'] > 0) {

            $data_field['pid'] = $data_template['id'];
            $obj_field         = new \CB\Objects\TemplateField();
            $data_field['id']  = $obj_field->create($data_field);
            $this->assertTrue($data_field['id'] > 0, ' Error on create Field');
        }
    }

    /*  public function testGetPluginsData()
    {
        $result = Helpers::getIncludeContents(
            \CB\DOC_ROOT.'remote/router.php',
            [ 'postdata' => '{"action":"CB_Objects","method":"getPluginsData","data":[{"id":"2"}],"type":"rpc","tid":1413}' ]
        );

        $result = json_decode($result, true);

        $this->assertArraySubset(
            array(
                'result' => array(
                    'success' => true
                ),
            ),
            $result
        );
    } */
}
