<?php

namespace App\Core\User\UserInterface\Cli;

use App\Core\User\Application\Command\GetEmail\GetInactiveUserEmailCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'app:user:inactive:getemail',
    description: 'Pobieranie emaila nieaktywnego użytkownika'
)]

class GetEmailIfUserInactive extends Command
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bus->dispatch(new GetInactiveUserEmailCommand());

        return Command::SUCCESS;
    }
}