<?php

namespace App\Service\Notifier;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('notifier.handler')]
interface NotifierHandlerInterface
{
    public function setConfig(array $data): self;
    public function send(string $message): bool;
    public function getName(): string;
}