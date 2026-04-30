<?php

namespace App\Tests\Functional\Service;

use App\DataFixtures\PaymentInfoFixture;
use App\Entity\PaymentInfo;
use App\Model\PaymentInfoModel;
use App\Service\PaymentInfoService;
use App\Tests\Utils\WebTest;
use DateTime;

class PaymentInfoServiceTest extends WebTest
{
    private PaymentInfoService $paymentInfoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentInfoService = $this->client->getContainer()->get(PaymentInfoService::class);
    }

    public function testCheckPaymentDate(): void
    {
        $this->databaseTool->loadFixtures([
            PaymentInfoFixture::class,
        ]);

        $this->paymentInfoService->checkPaymentDate();

        $paymentInfo = $this->getEntity(PaymentInfo::class);

        self::assertEquals(
            $paymentInfo->getPaymentDate()->format('d/m/Y'),
            (new DateTime())->modify(PaymentInfoModel::PAYMENT_PERIOD_MODIFIER)->format('d/m/Y')
        );
    }
}