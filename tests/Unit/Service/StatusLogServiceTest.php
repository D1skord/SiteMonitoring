<?php

namespace App\Tests\Unit\Service;

use App\Repository\SiteRepository;
use App\Service\StatusLogService;
use App\Tests\Utils\UnitTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StatusLogServiceTest extends UnitTest
{
    private StatusLogService $statusLogService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statusLogService = new StatusLogService(
            $this->createMock(HttpClientInterface::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SiteRepository::class),
            $this->createMock(MessageBusInterface::class)
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
}