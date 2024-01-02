<?php
namespace App\Tests\Unit\Core\Invoice\Application\Query\GetInvoicesByStatusAndAmountGreater;
use App\Core\Invoice\Application\DTO\InvoiceDTO;
use App\Core\Invoice\Application\Query\GetInvoicesByStatusAndAmountGreater\GetInvoicesByStatusAndAmountGreaterHandler;
use App\Core\Invoice\Application\Query\GetInvoicesByStatusAndAmountGreater\GetInvoicesByStatusAndAmountGreaterQuery;
use App\Core\Invoice\Domain\Invoice;
use App\Core\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Core\Invoice\Domain\Status\InvoiceStatus;
use App\Core\User\Domain\User;
use App\Core\User\Domain\UserStatus;
use PHPUnit\Framework\TestCase;
class GetInvoicesByStatusAndAmountGreaterHandlerTest extends TestCase
{
    public function test_handler_returns_invoice_dto_array(): void
    {
        $invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);

        $expectedStatus = InvoiceStatus::PAID;

        $expectedInvoices = [
            $this->createInvoiceWithId(1, 'user1@example.com', 100, $expectedStatus),
            $this->createInvoiceWithId(2, 'user2@example.com', 200, $expectedStatus),
        ];

        $invoiceRepository->expects($this->once())
            ->method('getInvoicesWithGreaterAmountAndStatus')
            ->with(100, InvoiceStatus::PAID)
            ->willReturn([
                $expectedInvoices[0],
                $expectedInvoices[1],
            ]);

        $handler = new GetInvoicesByStatusAndAmountGreaterHandler($invoiceRepository);

        $query = new GetInvoicesByStatusAndAmountGreaterQuery(100, InvoiceStatus::PAID);

        $result = $handler($query);

        $this->assertCount(2, $result);

        $this->assertInstanceOf(InvoiceDTO::class, $result[0]);
        $this->assertInstanceOf(InvoiceDTO::class, $result[1]);

        $this->assertEquals($expectedInvoices[0]->getId(), $result[0]->id);
        $this->assertEquals($expectedInvoices[0]->getUser()->getEmail(), $result[0]->email);
        $this->assertEquals($expectedInvoices[0]->getAmount(), $result[0]->amount);
        $this->assertEquals($expectedInvoices[1]->getId(), $result[1]->id);
        $this->assertEquals($expectedInvoices[1]->getUser()->getEmail(), $result[1]->email);
        $this->assertEquals($expectedInvoices[1]->getAmount(), $result[1]->amount);
    }

    public function test_handler_returns_invoice_when_one_invoice_has_different_value_then_another(): void
    {
        $invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);

        $expectedStatus = InvoiceStatus::PAID;

        $expectedInvoices = [
            $this->createInvoiceWithId(1, 'user1@example.com', 100, $expectedStatus),
            $this->createInvoiceWithId(2, 'user2@example.com', 200, $expectedStatus),
        ];

        $invoiceRepository->expects($this->once())
            ->method('getInvoicesWithGreaterAmountAndStatus')
            ->with(200, InvoiceStatus::PAID)
            ->willReturn([
                $expectedInvoices[1],
            ]);

        $handler = new GetInvoicesByStatusAndAmountGreaterHandler($invoiceRepository);

        $query = new GetInvoicesByStatusAndAmountGreaterQuery(200, InvoiceStatus::PAID);

        $result = $handler($query);

        $this->assertCount(1, $result);

        $this->assertInstanceOf(InvoiceDTO::class, $result[0]);

        $this->assertNotEquals($expectedInvoices[0]->getId(), $result[0]->id);
        $this->assertNotEquals($expectedInvoices[0]->getUser()->getEmail(), $result[0]->email);
        $this->assertNotEquals($expectedInvoices[0]->getAmount(), $result[0]->amount);
        $this->assertEquals($expectedInvoices[1]->getId(), $result[0]->id);
        $this->assertEquals($expectedInvoices[1]->getUser()->getEmail(), $result[0]->email);
        $this->assertEquals($expectedInvoices[1]->getAmount(), $result[0]->amount);
    }



    private function createInvoiceWithId(int $id, string $userEmail, int $amount, InvoiceStatus $status): Invoice
    {
        $user = new User($userEmail, UserStatus::ACTIVE);
        $user->setUserStatus(UserStatus::ACTIVE);
        $invoice = new Invoice($user, $amount, $status);

        $reflectionClass = new \ReflectionClass(Invoice::class);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($invoice, $id);

        return $invoice;
    }
}