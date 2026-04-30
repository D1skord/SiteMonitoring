<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PaymentInfo;
use App\Message\Notifier;
use App\Model\PaymentInfoModel;
use App\Repository\PaymentInfoRepository;
use App\Repository\SiteRepository;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Messenger\MessageBusInterface;

class PaymentInfoService
{
    public function __construct(
        private readonly SiteRepository         $siteRepository,
        private readonly PaymentInfoRepository  $paymentInfoRepository,
        private readonly MessageBusInterface    $bus
    )
    {
    }

    /**
     * Проверяет даты оплаты поддержки, отправляет уведомления и ставит след. дату
     */
    public function checkPaymentDate(): int
    {
        $sites = $this->siteRepository->findAll();

        foreach ($sites as $site) {

            $paymentDate = $site->getPaymentInfo()?->getPaymentDate();
            if (empty($paymentDate)) continue;

            if ($this->isPaymentDateNeedToUpdate($paymentDate)) {
                $this->bus->dispatch(new Notifier(
                        "Необходимо оплатить поддержку сайту {$site->getName()}, сумма {$site->getPaymentInfo()->getCost()}", $site->getId()
                    )
                );
                $this->paymentDateUpdate($site->getPaymentInfo());
            }
        }

        return Command::SUCCESS;
    }

    public function isPaymentDateNeedToUpdate(DateTimeInterface $paymentDate): bool
    {
        return $paymentDate <= new DateTime();
    }


    private function paymentDateUpdate(PaymentInfo $paymentInfo): void
    {
        /* @phpstan-ignore-next-line */
        $paymentInfo->setPaymentDate($paymentInfo->getPaymentDate()->modify(modifier: PaymentInfoModel::PAYMENT_PERIOD_MODIFIER));
        $this->paymentInfoRepository->save($paymentInfo, true);
    }
}
