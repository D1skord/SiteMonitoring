<?php

namespace App\EventListener;

use App\Event\SiteCheckExpireDateEvent;
use App\Service\Site\ExpireDateService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: SiteCheckExpireDateEvent::class, method: 'onCheckDomainExpire')]
class SiteCheckExpireDateListener
{
    public function __construct(
        private readonly ExpireDateService $expireDateService
    )
    {
    }

    public function onCheckDomainExpire(SiteCheckExpireDateEvent $siteEvent): void
    {
        $this->expireDateService->checkDomainExpire($siteEvent->getSite());
        $this->expireDateService->checkSSLExpire($siteEvent->getSite());
    }
}