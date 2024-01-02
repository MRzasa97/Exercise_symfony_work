<?php
use App\Core\User\Domain\Exception\UserNotFoundException;
use App\Core\User\Domain\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use App\Core\User\Infrastructure\Persistance\DoctrineUserRepository;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use App\Core\User\Domain\UserStatus;

class DoctrineUserRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrineUserRepository $userRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = new DoctrineUserRepository($this->entityManager);
    }

    public function test_get_by_email_existing_user(): void
    {
        $userEmail = 'existinguser@example.com';
        $existingUser = new User($userEmail, 1000);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with(':user_email', $userEmail)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn($existingUser);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $result = $this->userRepository->getByEmail($userEmail);

        $this->assertSame($existingUser, $result);
    }

    public function test_get_by_email_non_existing_user(): void
    {
        $nonExistingUserEmail = 'nonexistinguser@example.com';

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with(':user_email', $nonExistingUserEmail)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->expectException(UserNotFoundException::class);

        $this->userRepository->getByEmail($nonExistingUserEmail);
    }

    public function test_get_inactive_users_emails(): void
    {
        $inactiveUser1 = new User('inactiveuser1@example.com', 'password', UserStatus::INACTIVE);
        $inactiveUser2 = new User('inactiveuser2@example.com', 'password', UserStatus::INACTIVE);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with(':user_status', UserStatus::INACTIVE)
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getScalarResult')
            ->willReturn([
                ['email' => 'inactiveuser1@example.com'],
                ['email' => 'inactiveuser2@example.com'],
            ]);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $result = $this->userRepository->getInactiveUsersEmails();

        $this->assertEquals(['inactiveuser1@example.com', 'inactiveuser2@example.com'], $result);
    }

    public function test_get_inactive_users_emails_no_Inactive_users(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with(':user_status', UserStatus::INACTIVE)
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getScalarResult')
            ->willReturn([]);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $result = $this->userRepository->getInactiveUsersEmails();

        $this->assertEquals([], $result);
    }

    public function test_save(): void
    {
        $user = new User('testuser@example.com', 1000, UserStatus::ACTIVE);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($user);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->userRepository->save($user);
    }
}
