<?php

namespace App\DataFixtures;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixture extends Fixture
{
    public const string SITE_TEST_REFERENCE = 'My site';
    public const string SITE_NAME = 'vinichenko-ivan.ru';
    public const string SITE_URL = "https://".self::SITE_NAME;

    public function load(ObjectManager $manager): void
    {
        $site = new Site();
        $site->setName(self::SITE_NAME);
        $site->setUrl(self::SITE_URL);
        $manager->persist($site);
        $manager->flush();

        $this->addReference(self::SITE_TEST_REFERENCE, $site);
    }
}