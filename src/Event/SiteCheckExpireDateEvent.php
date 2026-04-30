<?php

namespace App\Event;

use App\Entity\Site;
use Symfony\Contracts\EventDispatcher\Event;

class SiteCheckExpireDateEvent extends Event
{
    public function __construct(
        private readonly ?Site $site
    ) {
    }

    public function getSite(): Site
    {
        return $this->site;
    }
}