<?php

namespace App\Tests\Unit\Service\Site;

use App\Service\Site\SiteExpireDateSSL;
use App\Tests\Utils\UnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

class SiteExpireDateSSLTest extends UnitTest
{
    private SiteExpireDateSSL $siteExpireDateSSL;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteExpireDateSSL = new SiteExpireDateSSL();
    }

    public static function parseProvider(): array
    {
        return [
            ['notAfter=Sep 29 23:49:15 2024 GMT', 'Sep 29 23:49:15 2024'],
            ['error', null],
            ['', null]
        ];
    }

    #[DataProvider('parseProvider')]
    public function testParse(?string $data, ?string $expected): void
    {
        $this->assertEquals(
            $this->siteExpireDateSSL->parse($data),
            $expected
        );
    }
}