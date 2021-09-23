<?php

declare(strict_types=1);

namespace App\Statuses\Http\Requests;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function response;

class RequestException extends InvalidArgumentException
{
    #[Pure]
    public function __construct(
        private string $title,
        string $detail,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($detail, $code, $previous);
    }

    public function render(): Response
    {
        return response()->json([
            'errors' => [
                [
                    'status' => (string) $this->code,
                    'title'  => $this->title,
                    'detail' => $this->message,
                ],
            ],
        ], $this->code);
    }
}
