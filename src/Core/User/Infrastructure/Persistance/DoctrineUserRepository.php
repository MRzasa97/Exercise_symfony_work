<?php

namespace App\Core\User\Infrastructure\Persistance;

use App\Core\User\Domain\Exception\UserNotFoundException;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use App\Core\User\Domain\UserStatus;

class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getByEmail(string $email): User
    {
        $user = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :user_email')
            ->setParameter(':user_email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $user) {
            throw new UserNotFoundException('Użytkownik nie istnieje');
        }

        return $user;
    }

    public function getInactiveUsersEmails(): array
    {
        $emails = $this->entityManager->createQueryBuilder()
            ->select('u.email')
            ->from(User::class, 'u')
            ->Where('u.userStatus = :user_status')
            ->setParameter(':user_status', UserStatus::INACTIVE)
            ->getQuery()
            ->getScalarResult();

        if (null === $emails) {
            throw new UserNotFoundException('Nie znaleziono nieaktywnych użytkowników');
        }

        $emailArray = array_column($emails, 'email');

        return $emailArray;
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
