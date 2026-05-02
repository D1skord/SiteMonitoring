<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Site;
use App\Entity\StatusLog;
use App\Message\Notifier;
use App\Repository\SiteRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StatusLogService
{
    const STATUS_SUCCESS = 200;

    public function __construct(
        private readonly HttpClientInterface    $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $siteRepository,
        private readonly MessageBusInterface $bus
    )
    {
    }

    /**
     * Проверяет статусы всех сайтов и отправляет уведомление, если статус изменился.
     */
    public function checkSiteStatuses(): int
    {
        $sites = $this->siteRepository->findAll();

        foreach ($sites as $site) {
            $previousStatus = $site->getStatus();
            $status = $this->getSiteStatus($site);

            $site->setStatus($status);
            $this->sendNoticeIfStatusChanged($site, $previousStatus, $status);
            $this->insertStatusLog($site, $status);
        }

        return Command::SUCCESS;
    }

    private function getSiteStatus(Site $site): int
    {
        try {
            return $this->httpClient->request('GET', $site->getUrl())->getStatusCode();
        } catch (TransportExceptionInterface) {
            return 0;
        }
    }

    private function sendNoticeIfStatusChanged(Site $site, ?int $previousStatus, int $status): bool
    {
        if ($previousStatus === $status || ($previousStatus === null && $this->isStatusSuccess($status))) {
            return false;
        }

        $message = $this->isStatusSuccess($status)
            ? "Сайт {$site->getName()} восстановился, статус {$status}"
            : "У сайта {$site->getName()} статус {$status}";

        $this->bus->dispatch(new Notifier($message, $site->getId()));

        return true;
    }

    public function isStatusSuccess(int $status): bool
    {
        return $status == self::STATUS_SUCCESS;
    }

    public function insertStatusLog(Site $site, int $status): StatusLog
    {
        $statusLog = new StatusLog();
        $statusLog->setSite($site);
        $statusLog->setStatus($status);
        $statusLog->setTimestamp(new DateTime());
        $this->entityManager->persist($statusLog);
        $this->entityManager->flush();

        return $statusLog;
    }
}
