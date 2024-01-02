<?php
namespace App\Core\User\Domain\Event;

class UserCreatedEvent extends AbstractUserEvent
{
    public readonly string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }
}