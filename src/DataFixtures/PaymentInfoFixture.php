<?php
declare(strict_types=1);

namespace App\DataFixtures;
use App\Entity\PaymentInfo;
use App\Entity\Site;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PaymentInfoFixture extends Fixture implements DependentFixtureInterface
{
    public const int COST = 5000;

    public function load(ObjectManager $manager): void
    {
        $site = $this->getReference(SiteFixture::SITE_TEST_REFERENCE, Site::class);

        $paymentInfo = new PaymentInfo();
        $paymentInfo->setCost(self::COST);
        $paymentInfo->setPaymentDate(new DateTime());

        $site->setPaymentInfo($paymentInfo);
        $manager->persist($site);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteFixture::class,
        ];
    }
}