<?php

namespace App\Service\Site;

use App\Entity\Site;
use App\Exception\SiteNotFoundException;
use App\Repository\SiteRepository;

class SiteService
{

    public function __construct(
        private readonly SiteRepository $siteRepository
    )
    {

    }

    /**
     * @throws SiteNotFoundException
     */
    public function getById(int $siteId): Site
    {
        $site = $this->siteRepository->find($siteId);

        if (!$site) {
            throw new SiteNotFoundException("Site with id {$siteId} not found");
        }
        return $site;
    }
}