<?php
use App\Core\User\Application\Command\CreateUser\CreateUserCommand;
use App\Core\User\Application\Command\CreateUser\CreateUserHandler;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Core\User\Domain\Event\UserCreatedEvent;


class CreateUserHandlerTest extends TestCase
{
    public function test_create_user_handler_invoke(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (User $user) {
                $this->assertInstanceOf(User::class, $user);
                $this->assertEquals('test@example.com', $user->getEmail());
            });

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->never())
            ->method('dispatch');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserCreatedEvent::class));

        $handler = new CreateUserHandler($userRepository, $messageBus, $eventDispatcher);

        $command = new CreateUserCommand('test@example.com');

        $handler->__invoke($command);
    }
}
?>