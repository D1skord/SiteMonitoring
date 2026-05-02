<?php

namespace App\Tests\Integration\Repository;

use App\DataFixtures\SiteFixture;
use App\Entity\Site;
use App\Entity\StatusLog;
use App\Repository\StatusLogRepository;
use App\Tests\Utils\WebTest;
use DateTimeImmutable;
use DateTimeInterface;

class StatusLogRepositoryTest extends WebTest
{
    public function testFindBySiteSinceReturnsRecentLogsForSite(): void
    {
        $this->databaseTool->loadFixtures([
            SiteFixture::class,
        ]);

        $site = $this->getEntity(Site::class);
        self::assertInstanceOf(Site::class, $site);

        $otherSite = (new Site())
            ->setName('Other site')
            ->setUrl('https://other.example.com');

        $this->entityManager->persist($otherSite);
        $this->entityManager->persist($this->createStatusLog($site, 200, new DateTimeImmutable('-23 hours')));
        $this->entityManager->persist($this->createStatusLog($site, 500, new DateTimeImmutable('-1 hour')));
        $this->entityManager->persist($this->createStatusLog($site, 404, new DateTimeImmutable('-25 hours')));
        $this->entityManager->persist($this->createStatusLog($otherSite, 503, new DateTimeImmutable('-1 hour')));
        $this->entityManager->flush();

        /** @var StatusLogRepository $repository */
        $repository = $this->entityManager->getRepository(StatusLog::class);
        $statusLogs = $repository->findBySiteSince($site, new DateTimeImmutable('-24 hours'));

        self::assertCount(2, $statusLogs);
        self::assertSame([200, 500], array_map(
            static fn (StatusLog $statusLog): ?int => $statusLog->getStatus(),
            $statusLogs
        ));
    }

    private function createStatusLog(Site $site, int $status, DateTimeInterface $timestamp): StatusLog
    {
        return (new StatusLog())
            ->setSite($site)
            ->setStatus($status)
            ->setTimestamp($timestamp);
    }
}
