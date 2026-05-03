<?php

namespace App\Tests\Integration\Service;

use App\DataFixtures\SiteFixture;
use App\DataFixtures\StatusLogFixture;
use App\Entity\StatusLog;
use App\Tests\Utils\WebTest;

class StatusLogServiceTest extends WebTest
{
    public function testCheckSiteStatus(): void
    {
        $this->databaseTool->loadFixtures([
            StatusLogFixture::class
        ]);

        $statusLog = $this->getEntity(StatusLog::class);

        self::assertIsInt($statusLog->getId());
        self::assertEquals($statusLog->getStatus(), StatusLogFixture::SITE_STATUS);
        self::assertEquals($statusLog->getResponseTimeMs(), StatusLogFixture::SITE_RESPONSE_TIME_MS);

        self::assertIsInt($statusLog->getSite()->getId());
        self::assertEquals($statusLog->getSite()->getName(), SiteFixture::SITE_NAME);
        self::assertEquals($statusLog->getSite()->getUrl(), SiteFixture::SITE_URL);
    }
}
