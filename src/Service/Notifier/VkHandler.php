<?php

namespace App\Service\Notifier;

use App\Exception\BaseException;
use App\Model\Notifier\Notifier;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VkHandler implements NotifierHandlerInterface
{
    public array $config;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        string $vkBotToken,
        string $vkPeerId,
        string $vkApiVersion
    )
    {
        $this->config = [
            'url' => 'https://api.vk.com/method/messages.send',
            'access_token' => $vkBotToken,
            'peer_id' => $vkPeerId,
            'v' => $vkApiVersion,
        ];
    }

    public function setConfig(array $data = []): self
    {
        foreach ($data as $dataName => $dataValue) {
            $this->config[$dataName] = $dataValue;
        }

        return $this;
    }

    public function send(string $message): bool
    {
        try {
            $response = $this->httpClient->request('POST', $this->config['url'], [
                'body' => [
                    'access_token' => $this->config['access_token'],
                    'peer_id' => $this->config['peer_id'],
                    'message' => $message,
                    'random_id' => random_int(1, 2147483647),
                    'v' => $this->config['v'],
                ],
            ]);

            $data = $response->toArray(false);
            if (isset($data['error'])) {
                $this->logger->error('Ошибка отправки уведомления в {messenger}', [
                    'messenger' => $this->getName(),
                    'error_code' => $data['error']['error_code'] ?? null,
                    'error_msg' => $data['error']['error_msg'] ?? null,
                ]);

                return false;
            }
        } catch (TransportExceptionInterface|DecodingExceptionInterface $e) {
            $this->logger->error('Ошибка отправки уведомления в {messenger}', [
                'messenger' => $this->getName(),
                'exception' => BaseException::getBaseErrors($e)
            ]);

            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return Notifier::VK;
    }
}
