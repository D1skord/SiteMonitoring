<?php

namespace App\Service\Site;

use App\Entity\Site;

class SiteExpireDateSSL extends SiteExpireDate
{
    public function parse(string $data): ?string
    {
        preg_match('/notAfter=(.*) GMT/', $data, $stringDate);
        return $stringDate[1] ?? null;
    }

    public function getCommand(): string
    {
        return 'openssl s_client -connect %1$s:443 -servername %1$s </dev/null 2>/dev/null | openssl x509 -noout -dates';
    }
}