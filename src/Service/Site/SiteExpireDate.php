<?php

namespace App\Service\Site;

use App\Entity\Site;
use DateTime;
use Symfony\Component\Process\Process;

abstract class SiteExpireDate
{
    public function getDate(Site $site): ?DateTime
    {
        $siteDomain = parse_url($site->getUrl())['host'];

        $process = Process::fromShellCommandline(sprintf($this->getCommand(), $siteDomain));
        $process->run();

        $dateOutput = $this->parse($process->getOutput());

        if (empty($dateOutput)) {
            return null;
        }

        try {
            $date = new DateTime($dateOutput);
        } catch (\Exception $e) {
            $date = null;
        }

        return $date;
    }

    abstract protected function parse(string $data): ?string;
    abstract protected function getCommand(): string;
}