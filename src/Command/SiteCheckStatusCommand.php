<?php

namespace App\Command;

use App\Service\StatusLogService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'site:check-status')]
class SiteCheckStatusCommand extends Command
{
    public function __construct(
        private StatusLogService $statusLogService,
    )
    {
        parent::__construct();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->statusLogService->checkSiteStatuses();
    }
}