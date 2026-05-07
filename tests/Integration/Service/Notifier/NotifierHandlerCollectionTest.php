<?php

namespace App\Tests\Integration\Service\Notifier;

use App\Model\Notifier\Notifier;
use App\Service\Notifier\NotifierHandlerCollection;
use App\Tests\Utils\WebTest;
use PHPUnit\Framework\Attributes\DataProvider;

class NotifierHandlerCollectionTest extends WebTest
{
    private ?NotifierHandlerCollection $notifierHandlerCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notifierHandlerCollection = $this->client->getContainer()->get(NotifierHandlerCollection::class);
    }

    #[DataProvider('getHandlerProvider')]
    public function testGetHandler(string $name): void
    {
        $handler = $this->notifierHandlerCollection->getHandlerByName($name);
        $this->assertEquals($name, $handler->getName());
    }

    public static function getHandlerProvider(): array
    {
        return [
            [Notifier::TELEGRAM],
            [Notifier::VK],
        ];
    }
}
