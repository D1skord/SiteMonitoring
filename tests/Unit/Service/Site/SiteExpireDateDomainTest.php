<?php

namespace App\Tests\Unit\Service\Site;

use App\Service\Site\SiteExpireDateDomain;
use App\Tests\Utils\UnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

class SiteExpireDateDomainTest extends UnitTest
{
    private SiteExpireDateDomain $siteExpireDateDomain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteExpireDateDomain = new SiteExpireDateDomain();
    }

    public static function parseProvider(): array
    {
        return [
            ['paid-till:     2025-02-09T20:27:03Z', '2025-02-09T20:27:03'],
            ['error', null],
            ['', null]
        ];
    }

    #[DataProvider('parseProvider')]
    public function testParse(?string $data, ?string $expected): void
    {
        $this->assertEquals(
            $this->siteExpireDateDomain->parse($data),
            $expected
        );
    }
}