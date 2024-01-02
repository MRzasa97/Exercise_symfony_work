<?php

namespace App\Core\User\Domain;

use App\Core\User\Domain\Event\UserCreatedEvent;
use App\Common\EventManager\EventsCollectorTrait;
use App\Core\User\Domain\Exception\UserException;
use App\Core\User\Domain\UserStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    use EventsCollectorTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=300, nullable=false)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=16, nullable=false, enumType="\App\Core\User\Domain\UserStatus")
     */
    private UserStatus $userStatus;

    public function __construct(string $email)
    {
        $this->id = null;
        $this->email = $email;
        $this->userStatus = UserStatus::INACTIVE;

        $this->record(new UserCreatedEvent($this->email));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUserStatus(): string
    {
        return $this->userStatus->value;
    }

    public function setUserStatus(UserStatus $userStatus): void
    {
        $this->userStatus = $userStatus;
    }
}
