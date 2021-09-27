<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Authentication\Domain\Exceptions\AuthRequestException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /** @var ResponseFactory */
    private $responseFactory;

    /** @var bool */
    private $debug;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthRequestException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [];

    public function setResponseFactory(ResponseFactory $factory): self
    {
        $this->responseFactory = $factory;

        return $this;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request   $request
     * @param Throwable $e
     * @return JsonResponse
     */
    public function render($request, Throwable $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        if ($e instanceof ValidationException) {
            return $this->responseFactory->json(
                [
                    'errors' => $this->mapValidationException($e),
                ],
                $e->getCode(),
                [
                    'Content-Type' => 'application/vnd.api+json',
                ]
            );
        }

        $error = [
            'status' => $e->getCode(),
            'detail' => $e->getMessage(),
        ];

        if ($this->debug) {
            $error['trace'] = $e->getTrace();
        }

        return $this->responseFactory->json(
            [
                'errors' => [
                    $error,
                ],
            ],
            $e->getCode(),
            [
                'Content-Type' => 'application/vnd.api+json',
            ]
        );
    }

    private function mapValidationException(ValidationException $exception): array
    {
        $validator = $exception->validator;
        $invalidAttributes = array_keys($validator->failed());
        $errors = [];

        foreach ($invalidAttributes as $invalidAttribute) {
            $errorMessages = $validator->errors()->get($invalidAttribute);

            foreach ($errorMessages as $errorMessage) {
                $pointer = str_replace('.', '/', $invalidAttribute);
                $title = strpos($errorMessage, 'required') !== false ? 'Missing input' : 'Invalid input';
                $errors[] = [
                    'status' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'title' => $title,
                    'detail' => $errorMessage,
                    'source' => ['pointer' => $pointer],
                ];
            }
        }

        return $errors;
    }
}
