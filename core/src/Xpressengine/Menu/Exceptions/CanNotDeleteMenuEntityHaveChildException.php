<?php
/**
 * Class CanNotDeleteMenuEntityHaveChildException
 *
 * PHP version 5
 *
 * @category  Menu
 * @package   Xpressengine\Menu
 * @author    XE Team (developers) <developers@xpressengine.com>
 * @copyright 2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license   http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link      http://www.xpressengine.com
 */

namespace Xpressengine\Menu\Exceptions;

use Xpressengine\Menu\MenuException;

/**
 * Menu RuntimeException
 *
 * @category Menu
 * @package  Xpressengine\Menu
 * @author   XE Team (developers) <developers@xpressengine.com>
 * @license  http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link     http://www.xpressengine.com
 */

class CanNotDeleteMenuEntityHaveChildException extends MenuException
{
    protected $message = 'MenuItem 을 가지고 있는 MenuEntity 는 삭제할 수 없습니다.';
}
