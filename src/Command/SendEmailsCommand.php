<?php

namespace App\Command;

use App\Manager\QueueManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-emails',
    description: 'Send emails with stock information.',
)]
class SendEmailsCommand extends Command
{
    public function __construct(private readonly QueueManager $queueManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $lines = $this->queueManager->processQueue();
        foreach ($lines as $line) {
            if ($line[0]) {
                !$io->warning($line[1]);

                continue;
            }
            $io->info($line[1]);

        }

        $io->success('The messages were processed.');

        return Command::SUCCESS;
    }
}
