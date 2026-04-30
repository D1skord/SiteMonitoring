<?php

namespace App\Tests\Functional\Service\Site;

use App\DataFixtures\SiteFixture;
use App\Entity\ExpireDate;
use App\Entity\Site;
use App\Service\Site\ExpireDateService;
use App\Tests\Utils\WebTest;

class ExpireDateServiceTest extends WebTest
{
    private ExpireDateService $expireDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->expireDate = $this->client->getContainer()->get(ExpireDateService::class);
    }

    /**
     * Тест получения даты окончания домена всех сайтов
     *
     * @throws \Exception
     */
    public function testCheckDomainsExpire(): void
    {
        $this->databaseTool->loadFixtures([
            SiteFixture::class
        ]);

        $this->expireDate->checkDomainsExpire();

        $site = $this->getEntity(Site::class);
        self::assertInstanceOf(ExpireDate::class, $site->getExpireDate());
        self::assertInstanceOf('DateTime', $site->getExpireDate()->getDomain());
    }

    /**
     * Тест получения даты окончания ssl-сертификата всех сайтов
     *
     * @throws \Exception
     */
    public function testCheckSSLsExpire(): void
    {
        $this->databaseTool->loadFixtures([
            SiteFixture::class
        ]);

        $this->expireDate->checkSSLsExpire();

        $site = $this->getEntity(Site::class);
        self::assertInstanceOf(ExpireDate::class, $site->getExpireDate());
        self::assertInstanceOf('DateTime', $site->getExpireDate()->getSsl());
    }
}