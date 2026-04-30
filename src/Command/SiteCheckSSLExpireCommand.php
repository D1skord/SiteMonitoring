<?php

namespace App\Command;

use App\Exception\BaseException;
use App\Exception\SiteNotFoundException;
use App\Service\Site\ExpireDateService;
use App\Service\Site\SiteService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'site:check-ssl-expire')]
class SiteCheckSSLExpireCommand extends Command
{
    public function __construct(
        private readonly ExpireDateService $expireDateService,
        private readonly LoggerInterface   $logger,
        private readonly SiteService $siteService
    )
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $siteId = $input->getArgument('siteId');

        try {
            if ($siteId) {
                $site = $this->siteService->getById($siteId);
                $this->expireDateService->checkSSLExpire($site);
            } else {
                $this->expireDateService->checkSSLsExpire();
            }
        } catch (SiteNotFoundException $e) {
            $this->logger->error($e->getMessage(), [
                'siteId' => $siteId,
                'exception' => BaseException::getBaseErrors($e)
            ]);
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('siteId', InputArgument::OPTIONAL, 'Site id?')
        ;
    }
}