<?php

namespace App\Tests\Unit\Service;

use App\Entity\Site;
use App\Message\Notifier;
use App\Repository\SiteRepository;
use App\Service\StatusLogService;
use App\Tests\Utils\UnitTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StatusLogServiceTest extends UnitTest
{
    private StatusLogService $statusLogService;
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;
    private SiteRepository $siteRepository;
    private MessageBusInterface $bus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->siteRepository = $this->createMock(SiteRepository::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->statusLogService = new StatusLogService(
            $this->httpClient,
            $this->entityManager,
            $this->siteRepository,
            $this->bus
        );
    }

    public static function isStatusSuccessProvider(): array
    {
        return [
            [200, true],
            [0, false],
            [500, false]
        ];
    }

    #[DataProvider('isStatusSuccessProvider')]
    public function testIsStatusSuccess(int $status, bool $result): void
    {
        $this->assertEquals(
            $this->statusLogService->isStatusSuccess($status),
            $result
        );
    }

    public function testCheckSiteStatusesDoesNotSendNoticeWhenStatusSame(): void
    {
        $site = (new Site())
            ->setName('Example')
            ->setUrl('https://example.com')
            ->setStatus(500);

        $this->siteRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([$site]);

        $this->mockStatusResponse(500);

        $this->bus->expects(self::never())
            ->method('dispatch');

        $this->entityManager->expects(self::once())
            ->method('persist');
        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->statusLogService->checkSiteStatuses();

        self::assertSame(500, $site->getStatus());
    }

    public function testCheckSiteStatusesSendsNoticeWhenStatusChangedToBad(): void
    {
        $site = (new Site())
            ->setName('Example')
            ->setUrl('https://example.com')
            ->setStatus(200);

        $this->siteRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([$site]);

        $this->mockStatusResponse(500);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn (Notifier $message): bool => $message->getMessage() === 'У сайта Example статус 500'
            ))
            ->willReturnCallback(static fn (Notifier $message): Envelope => new Envelope($message));

        $this->entityManager->expects(self::once())
            ->method('persist');
        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->statusLogService->checkSiteStatuses();

        self::assertSame(500, $site->getStatus());
    }

    public function testCheckSiteStatusesSendsNoticeWhenSiteRecovered(): void
    {
        $site = (new Site())
            ->setName('Example')
            ->setUrl('https://example.com')
            ->setStatus(500);

        $this->siteRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([$site]);

        $this->mockStatusResponse(200);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn (Notifier $message): bool => $message->getMessage() === 'Сайт Example восстановился, статус 200'
            ))
            ->willReturnCallback(static fn (Notifier $message): Envelope => new Envelope($message));

        $this->entityManager->expects(self::once())
            ->method('persist');
        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->statusLogService->checkSiteStatuses();

        self::assertSame(200, $site->getStatus());
    }

    private function mockStatusResponse(int $status): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())
            ->method('getStatusCode')
            ->willReturn($status);

        $this->httpClient->expects(self::once())
            ->method('request')
            ->willReturn($response);
    }
}
