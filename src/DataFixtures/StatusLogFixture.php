<?php
declare(strict_types=1);

namespace App\DataFixtures;
use App\Entity\Site;
use App\Entity\StatusLog;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StatusLogFixture extends Fixture implements DependentFixtureInterface
{
    public const int SITE_STATUS = 200;

    public function load(ObjectManager $manager): void
    {
        $site = $this->getReference(SiteFixture::SITE_TEST_REFERENCE, Site::class);

        $statusLog = new StatusLog();
        $statusLog->setSite($site);
        $statusLog->setTimestamp(new DateTime());
        $statusLog->setStatus(self::SITE_STATUS);
        $manager->persist($statusLog);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteFixture::class,
        ];
    }
}