<?php

namespace App\Command;

use App\Service\PaymentInfoService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'site:check-payment-date')]
class SiteCheckPaymentDate extends Command
{
    public function __construct(
        private readonly PaymentInfoService $paymentInfoService,
    )
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->paymentInfoService->checkPaymentDate();
    }
}