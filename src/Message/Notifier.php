<?php

namespace App\Message;

class Notifier
{
    public function __construct(
        private readonly ?string $message,
        private readonly ?int $siteId
    ) {
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getSiteId(): ?int
    {
        return $this->siteId;
    }
}