<?php

namespace App\Tests\Unit\Service\Notifier;

use App\Service\Notifier\VkHandler;
use App\Tests\Utils\UnitTest;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;

class VkHandlerLiveTest extends UnitTest
{
    public function testSendRealVkNotification(): void
    {
        $token = $this->getRequiredEnv('VK_BOT_TOKEN');
        $peerId = $this->getRequiredEnv('VK_PEER_ID');
        $apiVersion = $this->getRequiredEnv('VK_API_VERSION');

        $handler = new VkHandler(
            HttpClient::create(),
            new NullLogger(),
            $token,
            $peerId,
            $apiVersion
        );

        self::assertTrue($handler->send(sprintf(
            'SiteMonitoring test notification: %s',
            (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        )));
    }

    private function getRequiredEnv(string $name): string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        if (!is_string($value) || $value === '' || $value === 'change-me' || str_starts_with($value, 'test-')) {
            self::markTestSkipped(sprintf('%s is not configured.', $name));
        }

        return $value;
    }
}
