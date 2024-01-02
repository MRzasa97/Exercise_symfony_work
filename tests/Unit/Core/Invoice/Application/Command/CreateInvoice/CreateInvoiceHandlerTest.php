<?php

namespace App\Tests\Unit\Core\Invoice\Application\Command\CreateInvoice;

use App\Core\Invoice\Application\Command\CreateInvoice\CreateInvoiceCommand;
use App\Core\Invoice\Application\Command\CreateInvoice\CreateInvoiceHandler;
use App\Core\Invoice\Domain\Exception\InvoiceException;
use App\Core\Invoice\Domain\Invoice;
use App\Core\User\Domain\UserStatus;
use App\Core\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Core\User\Domain\Exception\UserNotFoundException;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateInvoiceHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;

    private InvoiceRepositoryInterface|MockObject $invoiceRepository;

    private CreateInvoiceHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CreateInvoiceHandler(
            $this->invoiceRepository = $this->createMock(
                InvoiceRepositoryInterface::class
            ),
            $this->userRepository = $this->createMock(
                UserRepositoryInterface::class
            )
        );
    }

    public function test_handle_success(): void
    {
        $user = $this->createMock(User::class);

        $invoice = new Invoice(
            $user, 12500
        );

        $this->userRepository->expects(self::once())
            ->method('getByEmail')
            ->willReturn($user);

        $this->invoiceRepository->expects(self::once())
            ->method('save')
            ->with($invoice);

        $this->invoiceRepository->expects(self::once())
            ->method('flush');

        $this->handler->__invoke((new CreateInvoiceCommand('test@test.pl', 12500)));
    }

    public function test_handle_user_not_exists(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->userRepository->expects(self::once())
            ->method('getByEmail')
            ->willThrowException(new UserNotFoundException());

        $this->handler->__invoke((new CreateInvoiceCommand('test@test.pl', 12500)));
    }

    public function test_handle_invoice_invalid_amount(): void
    {
        $this->expectException(InvoiceException::class);

        $this->handler->__invoke((new CreateInvoiceCommand('test@test.pl', -5)));
    }

    public function test_handler_if_user_is_active(): void
    {
        $invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $user = new User ('example@example.com');
        $user->setUserStatus(UserStatus::ACTIVE);

        $userRepository->expects($this->once())
            ->method('getByEmail')
            ->with('test@example.com')
            ->willReturn($user);

        $invoiceRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Invoice::class));

        $invoiceRepository->expects($this->once())
            ->method('flush');

        $handler = new CreateInvoiceHandler($invoiceRepository, $userRepository);

        $command = new CreateInvoiceCommand('test@example.com', 100);

        $handler->__invoke($command);
    }


    public function test_handler_if_user_is_inactive(): void
    {
        $invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $user = new User('example@example.com');
        $user->setUserStatus(UserStatus::INACTIVE);

        $userRepository->expects($this->once())
            ->method('getByEmail')
            ->with('test@example.com')
            ->willReturn($user);

        $handler = new CreateInvoiceHandler($invoiceRepository, $userRepository);

        $command = new CreateInvoiceCommand('test@example.com', 100);

        $this->expectException(InvoiceException::class);

        $handler->__invoke($command);
    }
}

