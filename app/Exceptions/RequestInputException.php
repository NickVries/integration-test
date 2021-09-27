<?php

declare(strict_types=1);

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class RequestInputException extends AbstractRequestException
{
    #[Pure]
    public function __construct(
        protected string $title,
        string $detail,
        int $code = 400,
        Throwable $previous = null
    ) {
        parent::__construct($title, $detail, $code, $previous);
    }
}
