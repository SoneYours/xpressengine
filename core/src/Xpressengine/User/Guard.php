<?php
/**
 *  This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category    User
 * @package     Xpressengine\User
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\User;

use Illuminate\Auth\Guard as LaravelGuard;
use Xpressengine\User\Models\Guest;

/**
 * 이 클래스는 Xpressengine의 회원 인증 기능을 처리하는 클래스이다.
 * 이 클래스의 주요 기능은 사용자를 로그인, 현재 로그인된 사용자의 로그아웃,
 * 로그인된 사용자의 정보를 반환하는 역할을 한다.
 *
 * @category    User
 * @package     Xpressengine\User
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Guard extends LaravelGuard implements GuardInterface
{

    /**
     * 현재 사용자의 로그인 여부를 체크한다.
     *
     * @return bool 로그인 상태일 경우 true, 로그아웃 상태일 경우 false를 반환
     */
    public function check()
    {
        return $this->user() instanceof Guest ? false : true;
    }

    /**
     * 현재 로그인한 사용자의 정보를 반환한다.
     * 만약 로그인한 사용자가 없을 경우 Guest의 인스턴스를 반환한다.
     *
     * @return UserInterface
     */
    public function user()
    {
        $user = parent::user();
        if ($user === null) {
            $user = $this->makeGuest();
        }

        return $this->user = $user;
    }

    /**
     * 현재 로그인한 사용자의 id를 반환한다.
     *
     * @return string|void 로그인 사용자의 id
     */
    public function id()
    {
        if ($this->loggedOut) {
            return;
        }

        $id = $this->session->get($this->getName(), $this->getRecallerId());

        if (is_null($id) && $this->check()) {
            $id = $this->user()->getAuthIdentifier();
        }

        return $id;
    }

    /**
     * 현재 로그인한 사용자를 로그아웃 시킨다.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        $this->clearUserDataFromStorage();

        if ($this->check()) {
            $this->refreshRememberToken($user);
        }

        if (isset($this->events)) {
            $this->events->fire('auth.logout', [$user]);
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = $this->makeGuest();

        $this->loggedOut = true;
    }

    /**
     * Guest 회원 인스턴스를 생성하여 반환한다.
     * Guest 회원 인스턴스는 보통 로그아웃 상태인 회원을 위해 사용된다.
     *
     * @return Guest
     */
    public function makeGuest()
    {
        return new Guest();
    }
}
