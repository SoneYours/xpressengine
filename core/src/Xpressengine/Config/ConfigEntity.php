<?php
/**
 * Config object class
 *
 * PHP version 5
 *
 * @category    Config
 * @package     Xpressengine\Config
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Config;

use IteratorAggregate;
use ArrayIterator;
use Xpressengine\Support\Entity;
use Xpressengine\Support\Caster;
use Xpressengine\Config\Exceptions\NoParentException;

/**
 * 대상의 설정을 가지고 제공해주는 클래스
 *
 * @category    Config
 * @package     Xpressengine\Config
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class ConfigEntity extends Entity implements IteratorAggregate
{
    /**
     * config value object
     *
     * @var ConfigVO
     */
    protected $vo;

    /**
     * parent by this config object
     *
     * @var ConfigEntity
     */
    protected $parent;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['name'];

    /**
     * constructor
     *
     * @param array $attributes name and variables
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setValueObject($attributes);
    }

    /**
     * Set value object
     *
     * @param array $attributes name and variables
     * @return void
     */
    private function setValueObject(array $attributes)
    {
        $info = isset($attributes['vars'])
            && empty($attributes['vars']) === false ? json_decode($attributes['vars'], true) : [];
        $this->vo = new ConfigVO($info);
    }

    /**
     * Fill all attributes
     *
     * @param array $attributes attributes in object
     * @return void
     */
    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (isset($attributes['vars'])) {
            $this->setValueObject($attributes);
        }
    }

    /**
     * get value, chain of responsibility
     *
     * @param string $name    variable name
     * @param mixed  $default default value
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $var = $this->getPure($name);

        if ($var !== null) {
            return $var instanceof \Closure ? $var($this) : $var;
        } elseif ($this->parent !== null) {
            return $this->parent->get($name, $default);
        }

        return $default;
    }

    /**
     * get pure object to array
     *
     * @return array
     */
    public function getPureAll()
    {
        return $this->vo->getAttributes();
    }

    /**
     * get pure value
     *
     * @param string $name    variable name
     * @param mixed  $default default value
     * @return mixed
     */
    public function getPure($name, $default = null)
    {
        return $this->vo->{$name} !== null ? $this->vo->{$name} : $default;
    }

    /**
     * set entity value
     *
     * @param string $name  variable name
     * @param mixed  $value variable value
     * @return void
     */
    public function set($name, $value)
    {
        if ($value === null) {
            unset($this->vo->{$name});
        } else {
            $this->vo->{$name} = Caster::cast($value);
        }
    }

    /**
     * entities clear
     *
     * @return void
     */
    public function clear()
    {
        $this->vo = new ConfigVO();
    }

    /**
     * make hierarchy to upper
     *
     * @param ConfigEntity $ancestor config object
     * @return void
     * @throws NoParentException
     */
    public function setParent(ConfigEntity $ancestor)
    {
        if ($this->isAdjacency($ancestor->name, $this->name)) {
            $this->parent = $ancestor;
        } else {
            if (is_null($this->parent)) {
                throw new NoParentException('Parent is not exists');
            }
            $this->parent->setParent($ancestor);
        }
    }

    /**
     * get adjacency parent
     *
     * @return ConfigEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * depth level
     *
     * @return int
     */
    public function getDepth()
    {
        return count(explode('.', $this->name));
    }

    /**
     * check adjacency
     *
     * @param string $ancestorName   higher level target name
     * @param string $descendantName lower level target name
     * @return boolean
     */
    private function isAdjacency($ancestorName, $descendantName)
    {
        return count(explode('.', $descendantName)) - count(explode('.', $ancestorName)) === 1;
    }

    /**
     * Determine if a given offset exists.
     *
     * @param string $offset array key name
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->get($offset) !== null;
    }

    /**
     * Get the value at a given offset.
     *
     * @param string $offset array key name
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $offset array key name
     * @param mixed  $value  array value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $offset array key name
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->vo->{$offset});
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $parentAttributes = ($this->parent !== null) ? $this->parent->getIterator()->getArrayCopy() : [];

        return new ArrayIterator(array_merge($parentAttributes, $this->vo->getAttributes()));
    }

    /**
     * returns current attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $this->attributes['vars'] = json_encode($this->vo);

        return $this->attributes;
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key key
     * @return mixed
     */
    public function __get($key)
    {
        return parent::get($key);
    }
}
