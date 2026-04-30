<?php

namespace App\Service\Site;

use App\Entity\ExpireDate;
use App\Entity\Site;
use App\Repository\SiteRepository;

class ExpireDateService
{
    public function __construct(
        private readonly SiteRepository $siteRepository,
        private readonly SiteExpireDateDomain $siteExpireDateDomain,
        private readonly SiteExpireDateSSL $siteExpireDateSSL
    )
    {
    }

    public function checkDomainExpire(Site $site): Site
    {
        $expireDomainDate = $this->siteExpireDateDomain->getDate($site);

        if (!$site->getExpireDate()) {
            $expireDate = new ExpireDate();
            $expireDate->setDomain($expireDomainDate);
            $site->setExpireDate($expireDate);
        } else {
            $site->getExpireDate()->setDomain($expireDomainDate);
        }

        return $this->siteRepository->save($site, true);
    }

    public function checkDomainsExpire(): void
    {
        $sites = $this->siteRepository->findAll();
        foreach ($sites as $site) {
            $this->checkDomainExpire($site);
        }
    }

    public function checkSSLExpire(Site $site): Site
    {
        $expireSSLDate = $this->siteExpireDateSSL->getDate($site);

        if (!$site->getExpireDate()) {
            $expireDate = new ExpireDate();
            $expireDate->setSsl($expireSSLDate);
            $site->setExpireDate($expireDate);
        } else {
            $site->getExpireDate()->setSsl($expireSSLDate);
        }

        return $this->siteRepository->save($site, true);
    }

    public function checkSSLsExpire(): void
    {
        $sites = $this->siteRepository->findAll();
        foreach ($sites as $site) {
            $this->checkSSLExpire($site);
        }
    }
}