<?php

namespace App\Core\User\Application\Command\GetEmail;

use App\Core\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetInactiveUserEmailHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function __invoke(GetInactiveUserEmailCommand $command): array
    {
        return $this->userRepository->getInactiveUsersEmails();
    }
}