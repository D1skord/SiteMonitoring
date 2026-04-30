<?php

namespace App\Tests\Unit\Service;

use App\Repository\PaymentInfoRepository;
use App\Repository\SiteRepository;
use App\Service\PaymentInfoService;
use App\Tests\Utils\UnitTest;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Messenger\MessageBusInterface;

class PaymentInfoServiceTest extends UnitTest
{
    private PaymentInfoService $paymentInfoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentInfoService = new PaymentInfoService(
            $this->createMock(SiteRepository::class),
            $this->createMock(PaymentInfoRepository::class),
            $this->createMock(MessageBusInterface::class)
        );
    }

    public static function isPaymentDateNeedToUpdateProvider(): array
    {
        return [
            [false, (new DateTime())->modify('+1 day')],
            [true, (new DateTime())->modify('-1 day')],
            [true, new DateTime()]
        ];
    }

    #[DataProvider('isPaymentDateNeedToUpdateProvider')]
    public function testIsPaymentDateNeedToUpdate(bool $result, DateTime $date): void
    {
        $this->assertEquals(
            $result,
            $this->paymentInfoService->isPaymentDateNeedToUpdate($date)
        );
    }
}