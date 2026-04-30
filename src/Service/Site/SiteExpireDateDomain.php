<?php

namespace App\Service\Site;

class SiteExpireDateDomain extends SiteExpireDate
{
    public function parse(string $data): ?string
    {
        preg_match('/paid-till:\s{5}(.*)Z/', $data, $stringDate);
        return $stringDate[1] ?? null;
    }

    protected function getCommand(): string
    {
        return 'whois %1$s';
    }
}