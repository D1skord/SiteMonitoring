<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Site;
use App\Entity\StatusLog;
use App\Message\Notifier;
use App\Repository\SiteRepository;
use App\Repository\StatusLogRepository;
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
     * Проверяет статусы всех сайтов и отправляет уведомление, если статус плохой
     *
     * @throws TransportExceptionInterface
     */
    public function checkSiteStatuses(): int
    {
        $sites = $this->siteRepository->findAll();

        foreach ($sites as $site) {
            $status = $this->httpClient->request('GET', $site->getUrl())->getStatusCode();
            $this->sendNoticeIfBadStatus($site, $status);
            $this->insertStatusLog($site, $status);
        }

        return Command::SUCCESS;
    }

    /**
     * Отправляет уведомление, если статус плохой
     */
    private function sendNoticeIfBadStatus(Site $site, int $status): bool
    {
        if (!$this->isStatusSuccess($status)) {
            $this->bus->dispatch(new Notifier("У сайта {$site->getName()} статус {$status}", $site->getId()));
            return true;
        }
        return false;
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