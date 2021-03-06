<?php
namespace Xpressengine\Tests\Menu;

use Mockery as m;
use Xpressengine\Menu\MenuHandler;

class MenuHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['createModel', 'generateNewId', 'registerDefaultPermission'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('fill')->once()->with([
            'title' => 'test title',
            'description' => 'test description'
        ]);
        $mockMenu->shouldReceive('getKeyName')->andReturn('id');
        $mockMenu->shouldReceive('save')->once();
        $mockMenu->shouldReceive('setAttribute')->with('id', 'abcdefg');

        $instance->expects($this->once())->method('createModel')->willReturn($mockMenu);
        $instance->expects($this->once())->method('generateNewId')->willReturn('abcdefg');
        $instance->expects($this->once())->method('registerDefaultPermission');


        $menu = $instance->create([
            'title' => 'test title',
            'description' => 'test description'
        ]);
    }

    public function testPut()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = new MenuHandler($keygen, $configs, $permissoins, $modules,  $routes);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('isDirty')->andReturn(true);
        $mockMenu->shouldReceive('save')->once();

        $instance->put($mockMenu);
    }

    public function testRemove()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['deleteMenuTheme', 'deleteMenuPermission'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $collection = m::mock('stdClass');
        $collection->shouldReceive('count')->andReturn(0);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getAttribute')->with('items')->andReturn($collection);

        $instance->expects($this->once())->method('deleteMenuTheme');
        $instance->expects($this->once())->method('deleteMenuPermission');

        $mockMenu->shouldReceive('delete')->once();

        $instance->remove($mockMenu);
    }

    public function testRemoveThrowsExceptionWhenHasItem()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['deleteMenuTheme', 'deleteMenuPermission'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $collection = m::mock('stdClass');
        $collection->shouldReceive('count')->andReturn(1);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getAttribute')->with('items')->andReturn($collection);

        try {
            $instance->remove($mockMenu);

            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Xpressengine\Menu\Exceptions\CanNotDeleteMenuEntityHaveChildException', $e);
        }
    }

    public function testCreateItem()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(
            MenuHandler::class,
            ['createItemModel', 'generateNewId', 'setOrder', 'registerItemPermission', 'storeMenuType'],
            [$keygen, $configs, $permissoins, $modules,  $routes]
        );

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getKey')->andReturn('menuKey');

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItem->shouldReceive('fill')->with([
            'parentId' => 'pid',
            'title' => 'test title',
            'url' => 'test_url',
            'ordering' => 1,
        ]);
        $mockMenuItem->shouldReceive('setAttribute')->once()->with('menuId', 'menuKey');

        $mockMenuItem->shouldReceive('getKeyName')->andReturn('id');
        $mockMenuItem->shouldReceive('save')->once();
        $mockMenuItem->shouldReceive('getDepthName')->andReturn('depth');
        $mockMenuItem->shouldReceive('getKey');
        $mockMenuItem->shouldReceive('getParentIdName')->andReturn('parentId');
        $mockMenuItem->shouldReceive('getAttribute')->with('parentId')->andReturn('pid');

        $instance->expects($this->any())->method('createItemModel')->with($mockMenu)->willReturn($mockMenuItem);
        $instance->expects($this->any())->method('generateNewId')->willReturn('abcdefg');

        $mockRelate = m::mock('stdClass');
        $mockRelate->shouldReceive('attach')->once()->with(m::on(function () { return true; }), ['depth' => 0]);
        $mockMenuItem->shouldReceive('ancestors')->andReturn($mockRelate);


        $mockMenuItemParent = m::mock('stdClass');
        $mockMenuItemParent->shouldReceive('getBreadcrumbs')->andReturn(['ppid', 'pid']);

        $mockMenuItem->shouldReceive('newQuery')->once()->andReturn($mockMenuItem);
        $mockMenuItem->shouldReceive('find')->once()->with('pid')->andReturn($mockMenuItemParent);

        $mockMenuItem->shouldReceive('setAttribute')->once()->with('id', 'abcdefg');

        $mockRelate->shouldReceive('attach')->once()->with('pid', m::on(function () { return true; }));
        $mockRelate->shouldReceive('attach')->once()->with('ppid', m::on(function () { return true; }));


        $instance->expects($this->any())->method('setOrder')->with($mockMenuItem, 1);
        $instance->expects($this->once())->method('registerItemPermission')->with($mockMenuItem, $this->anything());
        $instance->expects($this->once())->method('storeMenuType')->with($mockMenuItem, ['foo' => 'var']);


        $item = $instance->createItem($mockMenu, [
            'parentId' => 'pid',
            'title' => 'test title',
            'url' => 'test_url',
            'ordering' => 1,
        ], ['foo' => 'var']);

        $this->assertInstanceOf('Xpressengine\Menu\Models\MenuItem', $item);
    }

    public function testPutItem()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(
            MenuHandler::class,
            ['updateMenuType'],
            [$keygen, $configs, $permissoins, $modules,  $routes]
        );

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItem->shouldReceive('isDirty')->andReturn(true);
        $mockMenuItem->shouldReceive('save')->once();

        $instance->expects($this->once())->method('updateMenuType')->with($mockMenuItem, ['foo' => 'var']);

        $instance->putItem($mockMenuItem, ['foo' => 'var']);
    }

    public function testRemoveItem()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(
            MenuHandler::class,
            ['deleteItemPermission', 'destroyMenuType'],
            [$keygen, $configs, $permissoins, $modules,  $routes]
        );

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItem->shouldReceive('getDescendantCount')->andReturn(0);

        $instance->expects($this->once())->method('deleteItemPermission');

        $mockRelate = m::mock('stdClass');
        $mockRelate->shouldReceive('detach')->once()->with($mockMenuItem);

        $mockMenuItem->shouldReceive('ancestors')->andReturn($mockRelate);
        $mockMenuItem->shouldReceive('delete')->once();

        $instance->expects($this->once())->method('destroyMenuType')->with($mockMenuItem);

        $instance->removeItem($mockMenuItem);
    }

    public function testRemoveItemThrowsExceptionWhenHasItem()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['deleteItemPermission'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItem->shouldReceive('getDescendantCount')->andReturn(1);

        try {
            $instance->removeItem($mockMenuItem);

            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Xpressengine\Menu\Exceptions\CanNotDeleteMenuItemHaveChildException', $e);
        }
    }

    public function testSetOrder()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = new MenuHandler($keygen, $configs, $permissoins, $modules,  $routes);

        $collection = m::mock('stdClass');
        $collection->shouldReceive('filter')->andReturn($collection);

        $mockMenuItemParent = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItemParent->shouldReceive('getChildren')->andReturn($collection);

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItem->shouldReceive('getParent')->andReturn($mockMenuItemParent);

        $collection->shouldReceive('slice')->once()->with(0, 1)->andReturnSelf();
        $collection->shouldReceive('merge')->once()->with([$mockMenuItem])->andReturnSelf();
        $collection->shouldReceive('slice')->once()->with(1)->andReturnSelf();
        $collection->shouldReceive('merge')->once()->with($collection)->andReturnSelf();

        $collection->shouldReceive('each')->once();


        $instance->setOrder($mockMenuItem, 1);
    }

    public function testMoveItem()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['createItemModel', 'unlinkHierarchy', 'linkHierarchy'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getKey')->andReturn('menuKey');

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItem->shouldReceive('getParentIdName')->andReturn('parentId');
        $mockMenuItem->shouldReceive('getAttribute')->with('parentId')->andReturn('pid');

        $mockMenuItemNewParent = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockMenuItemNewParent->shouldReceive('getAttribute')->with('menu')->andReturn($mockMenu);
        $mockMenuItemNewParent->shouldReceive('getKey')->andReturn('pnid');

        $mockMenuItem->shouldReceive('setAttribute')->with('parentId', 'pnid');

        $mockMenuItemOldParent = m::mock('Xpressengine\Menu\Models\MenuItem');

        $mockModel = m::mock('stdClass');
        $mockModel->shouldReceive('newQuery')->once()->andReturnSelf();
        $mockModel->shouldReceive('find')->once()->with('pid')->andReturn($mockMenuItemOldParent);

        $instance->expects($this->once())->method('createItemModel')->with($mockMenu)->willReturn($mockModel);
        $instance->expects($this->once())->method('unlinkHierarchy')->with($mockMenuItem, $mockMenuItemOldParent);

        $mockMenuItem->shouldReceive('setAttribute')->with('parentId', null);

        $instance->expects($this->once())->method('linkHierarchy')->with($mockMenuItem, $mockMenuItemNewParent);

        $mockMenuItem->shouldReceive('setAttribute')->with('menuId', 'menuKey');
        $mockMenuItem->shouldReceive('save')->once();

        $mockMenuItem->shouldReceive('getRelations')->once()->andReturn(['relate' => true]);
        $mockMenuItem->shouldReceive('setRelation')->once()->with('menu', $mockMenu);
        $mockMenuItem->shouldReceive('__unset');

        $instance->moveItem($mockMenu, $mockMenuItem, $mockMenuItemNewParent);
    }

    public function testSetMenuTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getKey')->andReturn('menuKey');

        $instance->expects($this->once())->method('menuKeyString')->with('menuKey')->willReturn('configMenuKey');

        $configs->shouldReceive('add')->once()->with('configMenuKey', [
            'desktopTheme' => 'theme1',
            'mobileTheme' => 'theme2'
        ]);

        $instance->setMenuTheme($mockMenu, 'theme1', 'theme2');
    }

    public function testGetMenuTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getKey')->andReturn('menuKey');

        $instance->expects($this->once())->method('menuKeyString')->with('menuKey')->willReturn('configMenuKey');

        $configs->shouldReceive('get')->once()->with('configMenuKey')->andReturn('config');

        $config = $instance->getMenuTheme($mockMenu);

        $this->assertEquals('config', $config);
    }

    public function testUpdateMenuTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getKey')->andReturn('menuKey');

        $instance->expects($this->once())->method('menuKeyString')->with('menuKey')->willReturn('configMenuKey');

        $mockConfig = m::mock('Xpressengine\Config\ConfigEntity');
        $mockConfig->shouldReceive('set')->once()->with('desktopTheme', 'theme1');
        $mockConfig->shouldReceive('set')->once()->with('mobileTheme', 'theme2');

        $configs->shouldReceive('get')->once()->with('configMenuKey')->andReturn($mockConfig);

        $configs->shouldReceive('modify')->once()->with($mockConfig);

        $instance->updateMenuTheme($mockMenu, 'theme1', 'theme2');
    }

    public function testDeleteMenuTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenu = m::mock('Xpressengine\Menu\Models\Menu');
        $mockMenu->shouldReceive('getKey')->andReturn('menuKey');

        $instance->expects($this->once())->method('menuKeyString')->with('menuKey')->willReturn('configMenuKey');

        $configs->shouldReceive('removeByName')->once()->with('configMenuKey');

        $instance->deleteMenuTheme($mockMenu);
    }

    public function testSetMenuItemTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');

        $instance->expects($this->once())->method('menuKeyString')->with($mockMenuItem)->willReturn('configMenuItemKey');

        $configs->shouldReceive('add')->once()->with('configMenuItemKey', [
            'desktopTheme' => 'theme1',
            'mobileTheme' => 'theme2'
        ]);

        $instance->setMenuItemTheme($mockMenuItem, 'theme1', 'theme2');
    }

    public function testGetMenuItemTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');

        $instance->expects($this->once())->method('menuKeyString')->with($mockMenuItem)->willReturn('configMenuItemKey');

        $configs->shouldReceive('get')->once()->with('configMenuItemKey')->andReturn('config');

        $config = $instance->getMenuItemTheme($mockMenuItem);

        $this->assertEquals('config', $config);
    }

    public function testUpdateMenuItemTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');

        $instance->expects($this->once())->method('menuKeyString')->with($mockMenuItem)->willReturn('configMenuItemKey');

        $mockConfig = m::mock('Xpressengine\Config\ConfigEntity');
        $mockConfig->shouldReceive('set')->once()->with('desktopTheme', 'theme1');
        $mockConfig->shouldReceive('set')->once()->with('mobileTheme', 'theme2');

        $configs->shouldReceive('get')->once()->with('configMenuItemKey')->andReturn($mockConfig);

        $configs->shouldReceive('modify')->once()->with($mockConfig);

        $instance->updateMenuItemTheme($mockMenuItem, 'theme1', 'theme2');
    }

    public function testDeleteMenuItemTheme()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockMenuItem = m::mock('Xpressengine\Menu\Models\MenuItem');

        $instance->expects($this->once())->method('menuKeyString')->with($mockMenuItem)->willReturn('configMenuItemKey');

        $configs->shouldReceive('removeByName')->once()->with('configMenuItemKey');

        $instance->deleteMenuItemTheme($mockMenuItem);
    }

    public function testMoveItemConfig()
    {
        list($keygen, $configs, $permissoins, $modules,  $routes) = $this->getMocks();
        $instance = $this->getMock(MenuHandler::class, ['menuKeyString'], [$keygen, $configs, $permissoins, $modules,  $routes]);

        $mockBefore = m::mock('Xpressengine\Menu\Models\MenuItem');
        $mockAfter = m::mock('Xpressengine\Menu\Models\MenuItem');

        $map = [
            [$mockBefore, 'configBeforeKey'],
            [$mockAfter, 'configAfter.Key']
        ];

        $instance->expects($this->any())->method('menuKeyString')->will($this->returnValueMap($map));

        $mockConfig = m::mock('Xpressengine\Config\ConfigEntity');

        $configs->shouldReceive('get')->once()->with('configBeforeKey')->andReturn($mockConfig);
        $configs->shouldReceive('move')->once()->with($mockConfig, 'configAfter');

        $instance->moveItemConfig($mockBefore, $mockAfter);
    }

    private function invokedMethod($object, $methodName, $parameters = [])
    {
        $rfc = new \ReflectionClass($object);
        $method = $rfc->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function getMocks()
    {
        return [
            m::mock('Xpressengine\Keygen\Keygen'),
            m::mock('Xpressengine\Config\ConfigManager'),
            m::mock('Xpressengine\Permission\PermissionHandler'),
            m::mock('Xpressengine\Module\ModuleHandler'),
            m::mock('Xpressengine\Routing\RouteRepository')
        ];
    }
}
