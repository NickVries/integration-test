<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Exceptions;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Utils;
use Illuminate\Support\Arr;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthRequestException extends BadRequestException
{
    #[Pure]
    public function __construct(
        private string $title,
        string $message,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromRequestException(RequestException $requestException): self
    {
        if (!$requestException->getResponse()) {
            return new self('Authentication error', 'Unknown request exception', 400, $requestException);
        }

        $response = Utils::jsonDecode((string) $requestException->getResponse()->getBody(), true);

        return new self(
            Arr::get($response, 'error', 'Authentication error'),
            Arr::get($response, 'error_description', 'Unknown request exception'),
            $requestException->getResponse()->getStatusCode(),
            $requestException
        );
    }

    public function render(): Response
    {
        return response()->json([
            'errors' => [
                [
                    'status' => (string) $this->code,
                    'title'  => $this->title,
                    'message' => $this->message,
                ],
            ],
        ], $this->code);
    }
}
