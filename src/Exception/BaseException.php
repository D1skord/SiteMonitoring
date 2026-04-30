<?php

namespace App\Exception;

class BaseException extends \Exception
{
    public static function getBaseErrors(\Throwable $e): array {
        return [
            'exceptionMessage' => $e->getMessage(),
            'exceptionTrace' => $e->getTrace(),
            'exceptionPrevious' => $e->getPrevious()
        ];
    }
}