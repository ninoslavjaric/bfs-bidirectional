<?php
namespace Htec\Traits\Service;

use Htec\Service\User;

trait UserServiceTrait
{
    private User $userService;

    /**
     * @return User
     */
    public function getUserService(): User
    {
        if (!isset($this->userService)) {
            $this->setUserService(new User());
        }

        return $this->userService;
    }

    /**
     * @param User $userService
     */
    public function setUserService(User $userService): void
    {
        $this->userService = $userService;
    }


}
