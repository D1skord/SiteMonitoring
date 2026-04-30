<?php

namespace App\MessageHandler;

use App\Message\Notifier;
use App\Repository\SiteRepository;
use App\Service\Notifier\NotifierHandlerCollection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
class NotifierHandler
{
    public function __construct(
        private readonly SiteRepository $siteRepository,
        private readonly NotifierHandlerCollection $notifierHandlerCollection
    )
    {
    }

    public function __invoke(Notifier $notifier): void
    {
        $site = $this->siteRepository->find($notifier->getSiteId());
        foreach ($site->getTransport() as $transportName) {
            $notifierHandler = $this->notifierHandlerCollection->getHandlerByNamespace($transportName);
            $notifierHandler->send($notifier->getMessage());
        }
    }
}