<?php

namespace App\Service\Notifier;

use App\Exception\BaseException;
use App\Model\Notifier\Notifier;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramHandler implements NotifierHandlerInterface
{
    public array $config;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    )
    {
        $this->config = Notifier::TELEGRAM_DATA;
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
        $this->config['text'] = $message;
        try {
            $this->httpClient->request('POST', $this->config['url'], [
                'body' => $this->config
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Ошибка отправки уведомления в {messenger}', [
                'messenger' => $this->getName(),
                'exception' => BaseException::getBaseErrors($e)
            ]);
        }

        return true;
    }

    public function getName(): string
    {
        return Notifier::TELEGRAM;
    }
}