<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AuthSessionExpiredException extends BadRequestException
{
    public function render(): Response
    {
        return response('Authentication session with ExactOnline.nl has expired.', Response::HTTP_BAD_REQUEST);
    }
}
