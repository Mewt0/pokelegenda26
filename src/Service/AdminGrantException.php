<?php
declare(strict_types=1);
namespace Pokemon8\Service;

final class AdminGrantException extends \RuntimeException
{
    public function __construct(public readonly string $errorCode, public readonly int $httpStatus)
    {
        parent::__construct($errorCode);
    }
}
