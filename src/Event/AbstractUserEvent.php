<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 3/2/2021
 * Time: 10:24 AM
 */
namespace LydicGroup\RapidApiCrudBundle\Event;

use App\Entity\User;

abstract class AbstractUserEvent
{
    protected User $user;

    /**
     * AbstractUserEvent constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
