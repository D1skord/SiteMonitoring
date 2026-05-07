<?php

namespace App\Tests\Unit\Service\Notifier;

use App\Model\Notifier\Notifier;
use App\Service\Notifier\VkHandler;
use App\Tests\Utils\UnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class VkHandlerTest extends UnitTest
{
    private HttpClientInterface&MockObject $httpClient;
    private LoggerInterface&MockObject $logger;
    private VkHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new VkHandler(
            $this->httpClient,
            $this->logger,
            'vk-token',
            '12345',
            '5.199'
        );
    }

    public function testGetName(): void
    {
        self::assertSame(Notifier::VK, $this->handler->getName());
    }

    public function testSendUsesVkMessagesSendApi(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())
            ->method('toArray')
            ->with(false)
            ->willReturn(['response' => 1]);

        $this->httpClient->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'https://api.vk.com/method/messages.send',
                self::callback(static function (array $options): bool {
                    return $options['body']['access_token'] === 'vk-token'
                        && $options['body']['peer_id'] === '12345'
                        && $options['body']['message'] === 'Проверка уведомления'
                        && $options['body']['v'] === '5.199'
                        && is_int($options['body']['random_id'])
                        && $options['body']['random_id'] > 0;
                })
            )
            ->willReturn($response);

        $this->logger->expects(self::never())
            ->method('error');

        self::assertTrue($this->handler->send('Проверка уведомления'));
    }

    public function testSendReturnsFalseWhenVkReturnsApiError(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())
            ->method('toArray')
            ->with(false)
            ->willReturn([
                'error' => [
                    'error_code' => 901,
                    'error_msg' => 'Can not send messages for users without permission',
                ],
            ]);

        $this->httpClient->expects(self::once())
            ->method('request')
            ->willReturn($response);

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Ошибка отправки уведомления в {messenger}',
                [
                    'messenger' => Notifier::VK,
                    'error_code' => 901,
                    'error_msg' => 'Can not send messages for users without permission',
                ]
            );

        self::assertFalse($this->handler->send('Проверка уведомления'));
    }
}
