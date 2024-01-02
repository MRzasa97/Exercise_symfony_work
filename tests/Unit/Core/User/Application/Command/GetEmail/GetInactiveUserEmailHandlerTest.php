<?php
namespace App\Tests\Unit\Core\User\Application\Command\GetEmail;

use App\Core\User\Application\Command\GetEmail\GetInactiveUserEmailCommand;
use App\Core\User\Application\Command\GetEmail\GetInactiveUserEmailHandler;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Tests\Unit\Core\User\UserTestHelper;
use PHPUnit\Framework\TestCase;

class GetInactiveUserEmailHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;

    private GetInactiveUserEmailHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new GetInactiveUserEmailHandler(
            $this->userRepository = $this->createMock(
                UserRepositoryInterface::class
            )
        );
    }

    public function test_handle_success(): void
    {
        $exampleEmails = 
        [
            'exampleEmail1@example.com',
            'exampleEmail2@example.com'
        ];
        $this->userRepository->expects(self::once())
            ->method('getInactiveUsersEmails')
            ->willReturn(
                $exampleEmails
            );

        $emails = $this->handler->__invoke(new GetInactiveUserEmailCommand());
        $this->assertEquals($emails, $exampleEmails);
        $this->assertIsArray($emails);
    }
    public function test_handle_user_not_found(): void
    {
        $this->userRepository->expects(self::once())
            ->method('getInactiveUsersEmails')
            ->willReturn([]);

        $this->assertEmpty($this->handler->__invoke(new GetInactiveUserEmailCommand()));

    }
}

