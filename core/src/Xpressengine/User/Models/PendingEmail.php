<?php
/**
 * PendingEmail
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
namespace Xpressengine\User\Models;

use Xpressengine\Database\Eloquent\DynamicModel;
use Xpressengine\User\EmailInterface;

/**
 * @category    User
 * @package     Xpressengine\User
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class PendingEmail extends DynamicModel implements EmailInterface
{

    protected $table = 'user_pending_email';

    /**
     * @var bool use dynamic query
     */
    protected $dynamic = false;

    protected $fillable = [
        'userId',
        'address',
        'confirmationCode'
    ];

    /**
     * set relationship with user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    /**
     * 이메일 주소를 반환한다.
     *
     * @param bool $onlyEmailId true일 경우, 이메일의 `@`와 도메인을 제외한 앞부분만 반환한다.
     *
     * @return mixed
     */
    public function getAddress($onlyEmailId = false)
    {
        return $this->getAttribute('address');
    }

    /**
     * 이메일을 소유한 회원의 아이디를 반환한다.
     *
     * @return string
     */
    public function getUserId()
    {
        $user = $this->getAttribute('user');
        return $user->getId();
    }

    /**
     * 메일의 인증코드를 반환한다.
     *
     * @return bool
     */
    public function getConfirmationCode()
    {
        return $this->getAttribute('confirmationCode');
    }


    /**
     * 인증된 메일인지 확인한다
     *
     * @return bool
     */
    public function isConfirmed()
    {
        return false;
    }
}
