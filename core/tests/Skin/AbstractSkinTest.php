<?php
namespace Xpressengine\Tests\Skin;

use Xpressengine\Skin\AbstractSkin;

class AbstractSkinTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testSetData()
    {
        $skin = new TestSkin();
        $data = 'data';
        $skin = $skin->setData($data);

        $skin->setView('data');

        $this->assertEquals($data, $skin->render());
    }

    public function testGetConfig()
    {
        $config = 'config';
        $skin = new TestSkin($config);

        $this->assertEquals($config, $skin->getConfig());
    }

    public function testRender()
    {
        $skin = new TestSkin();
        $skin->setView('board.list');

        $this->assertEquals('boardList', $skin->render());
    }
}


class TestSkin extends AbstractSkin
{
    protected static $id = 'test.skin';

    protected static $supportDesktop = true;
    protected static $supportMobile = true;

    protected function boardList()
    {
        return 'boardList';
    }

    protected function data()
    {
        return $this->data;
    }

    public static function getScreenshot()
    {
        return 'screenshot';
    }

    /**
     * get settings uri
     *
     * @return string|null
     */
    public static function getSettingsURI()
    {
        return 'http://foo.bar';
    }


}
