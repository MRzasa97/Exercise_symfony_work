<?php

namespace App\Core\User\Application\Command\CreateUser;

use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Core\User\Domain\Event\UserCreatedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;



#[AsMessageHandler]
class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $bus,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function __invoke(CreateUserCommand $command): void
    {
        $user = new User(
            $command->email
        );
        $this->userRepository->save($user);

        $events = $user->getEvents();
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}