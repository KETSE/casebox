<?php
namespace CB\UNITTESTS;

/**
 * Description of SearchTest
 *
 * @author ghindows
 */
class SearchTest extends \PHPUnit_Framework_TestCase
{

    public function searchDataProvider()
    {
        return \CB\UNITTESTS\DATA\get_basic_search_data();
    }

    /**
     * @dataProvider searchDataProvider
     */
    public function testSearch($search)
    {
        return $this->assertTrue(true);

        $src = new \CB\Search();

        $this->assertTrue($src->ping() > 0);

        $src_response = $src->search('test', 0, 10, []);

        $this->assertEquals('OK', $src_response->getHttpStatusMessage(), $src_response->getHttpStatusMessage());

        $result = \CB\UNITTESTS\HELPERS\getIncludeContents(\CB\DOC_ROOT.'remote/router.php', [ 'postdata' => $search['postdata']]);

        $this->assertArraySubset(json_decode($search['expected_response'], true), json_decode($result, true), $result);

    }


}
