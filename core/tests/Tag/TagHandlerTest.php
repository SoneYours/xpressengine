<?php
namespace Xpressengine\Tests\Tag;

use Illuminate\Database\Eloquent\Collection;
use Mockery as m;
use Xpressengine\Tag\TagHandler;

class TagHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSet()
    {
        list($decomposer) = $this->getMocks();

        $instance = $this->getMock(TagHandler::class, ['createModel', 'relate', 'unrelate'], [$decomposer]);

        $mockModel = m::mock('Xpressengine\Tag\Tag');

        $instance->expects($this->once())->method('createModel')->will($this->returnValue($mockModel));

        $mockTagExists1 = m::mock('Xpressengine\Tag\Tag');
        $mockTagExists1->shouldReceive('getKey')->andReturn(1);
        $mockTagExists1->shouldReceive('getAttribute')->with('word')->andReturn('laravel');
        $mockTagExists1->shouldReceive('offsetExists')->andReturn(true);
        $mockTagExists1->shouldReceive('offsetGet')->with('word')->andReturn('laravel');
        $mockTagExists2 = m::mock('Xpressengine\Tag\Tag');
        $mockTagExists2->shouldReceive('getKey')->andReturn(2);
        $mockTagExists2->shouldReceive('getAttribute')->with('word')->andReturn('cms');
        $mockTagExists2->shouldReceive('offsetExists')->andReturn(true);
        $mockTagExists2->shouldReceive('offsetGet')->with('word')->andReturn('cms');

        $mockModel->shouldReceive('newQuery')->andReturnSelf();
        $mockModel->shouldReceive('where')->once()->with('instanceId', null)->andReturnSelf();
        $mockModel->shouldReceive('whereIn')->once()->with('word', ['laravel', 'cms', 'xpressengine'])->andReturnSelf();
        $mockModel->shouldReceive('get')->once()->andReturn(new Collection([$mockTagExists1, $mockTagExists2]));


        $decomposer->shouldReceive('execute')->once()->with('xpressengine')->andReturn('xpressengine');
        $mockTagNew = m::mock('Xpressengine\Tag\Tag');
        $mockTagNew->shouldReceive('getKey')->andReturn(5);
        $mockTagNew->shouldReceive('getAttribute')->with('word')->andReturn('xpressengine');

        $mockModel->shouldReceive('create')->once()->with([
            'word' => 'xpressengine',
            'decomposed' => 'xpressengine',
            'instanceId' => null,
        ])->andReturn($mockTagNew);

        $instance->expects($this->once())->method('relate')->with(
            'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            [$mockTagExists1, $mockTagExists2, $mockTagNew]
        )->will($this->returnValue(null));

        $mockTagOld1 = m::mock('Xpressengine\Tag\Tag');
        $mockTagOld1->shouldReceive('getKey')->andReturn(3);
        $mockTagOld2 = m::mock('Xpressengine\Tag\Tag');
        $mockTagOld2->shouldReceive('getKey')->andReturn(4);


        $instance->expects($this->once())->method('unrelate')->with(
            'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            new Collection([$mockTagOld1, $mockTagOld2])
        )->will($this->returnValue(null));

        $mockModel->shouldReceive('getByTaggable')->once()->with('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')->andReturn(
            new Collection([$mockTagExists1, $mockTagExists2, $mockTagOld1, $mockTagOld2])
        );

        $mockModel->shouldReceive('newCollection')->once()
            ->with([$mockTagExists1, $mockTagExists2, $mockTagNew])
            ->andReturn(new Collection([$mockTagExists1, $mockTagExists2, $mockTagNew]));


        $collection = $instance->set('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', ['laravel', 'cms', 'xpressengine']);

        $keys = [];
        foreach ($collection as $item) {
            $keys[] = $item->getKey();
        }

        $this->assertEquals([1, 2, 5], $keys);
    }

    public function testRelate()
    {
        list($decomposer) = $this->getMocks();

        $instance = new TagHandler($decomposer);

        $mockTag = m::mock('Xpressengine\Tag\Tag')->shouldAllowMockingProtectedMethods();
        $mockConn = m::mock('stdClass');

        $mockTag->shouldReceive('getConnection')->andReturn($mockConn);
        $mockTag->shouldReceive('getTaggableTable')->andReturn('taggable');
        $mockTag->shouldReceive('getKey')->andReturn(1);

        $mockConn->shouldReceive('table')->with('taggable')->andReturnSelf();
        $mockConn->shouldReceive('insert')->once()->with(m::on(function ($args) {
            return $args['tagId'] === 1
                && $args['taggableId'] === 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
                && $args['position'] === 0;
        }))->andReturnNull();

        $mockTag->shouldReceive('increment')->once()->with('count')->andReturnNull();

        $this->invokeMethod($instance, 'relate', ['xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', [$mockTag]]);
    }

    public function testRelateDoUpdateWhenThrowsException()
    {
        list($decomposer) = $this->getMocks();

        $instance = new TagHandler($decomposer);

        $mockTag = m::mock('Xpressengine\Tag\Tag');
        $mockConn = m::mock('stdClass');

        $mockTag->shouldReceive('getConnection')->andReturn($mockConn);
        $mockTag->shouldReceive('getTaggableTable')->andReturn('taggable');
        $mockTag->shouldReceive('getKey')->andReturn(1);

        $mockConn->shouldReceive('table')->with('taggable')->andReturnSelf();
        $mockConn->shouldReceive('insert')->once()->with(m::on(function ($args) {
            return $args['tagId'] === 1
            && $args['taggableId'] === 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
            && $args['position'] === 0;
        }))->andThrowExceptions([new \Exception]);

        $mockConn->shouldReceive('where')->once()->with('tagId', 1)->andReturnSelf();
        $mockConn->shouldReceive('where')->once()
            ->with('taggableId', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
            ->andReturnSelf();
        $mockConn->shouldReceive('update')->once()->with(['position' => 0])->andReturnNull();


        $this->invokeMethod($instance, 'relate', ['xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', [$mockTag]]);
    }

    public function testUnrelate()
    {
        list($decomposer) = $this->getMocks();

        $instance = new TagHandler($decomposer);

        $mockTag = m::mock('Xpressengine\Tag\Tag')->shouldAllowMockingProtectedMethods();
        $mockConn = m::mock('stdClass');

        $mockTag->shouldReceive('getConnection')->andReturn($mockConn);
        $mockTag->shouldReceive('getTaggableTable')->andReturn('taggable');
        $mockTag->shouldReceive('getKey')->andReturn(1);

        $mockConn->shouldReceive('table')->with('taggable')->andReturnSelf();
        $mockConn->shouldReceive('where')->once()->with('tagId', 1)->andReturnSelf();
        $mockConn->shouldReceive('where')->once()
            ->with('taggableId', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
            ->andReturnSelf();
        $mockConn->shouldReceive('delete')->once()->andReturnNull();

        $mockTag->shouldReceive('decrement')->once()->with('count')->andReturnNull();

        $this->invokeMethod($instance, 'unrelate', ['xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', [$mockTag]]);
    }

    public function testSimilar()
    {
        list($decomposer) = $this->getMocks();

        $instance = $this->getMock(TagHandler::class, ['createModel'], [$decomposer]);

        $mockModel = m::mock('Xpressengine\Tag\Tag');

        $instance->expects($this->once())->method('createModel')->will($this->returnValue($mockModel));

        $mockConn = m::mock('stdClass');
        $mockModel->shouldReceive('newQuery')->andReturn($mockConn);

        $decomposer->shouldReceive('execute')->once()->with('x')->andReturn('x');

        $mockConn->shouldReceive('where')->once()->with('decomposed', 'like', 'x%')->andReturnSelf();
        $mockConn->shouldReceive('orderBy')->once()->withAnyArgs()->andReturnSelf();
        $mockConn->shouldReceive('take')->once()->with(10)->andReturnSelf();
        $mockConn->shouldReceive('where')->once()->with('instanceId', 'someId')->andReturnSelf();
        $mockConn->shouldReceive('get')->once()->andReturn([m::mock('Xpressengine\Tag\Tag')]);

        $tags = $instance->similar('x', 10, 'someId');

        $this->assertInstanceOf('Xpressengine\Tag\Tag', current($tags));
    }

    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function getMocks()
    {
        return [
            m::mock('Xpressengine\Tag\Decomposer')
        ];
    }
}
