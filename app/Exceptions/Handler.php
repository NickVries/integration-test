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
     * @param Throwable $exception
     * @return JsonResponse
     */
    public function render($request, Throwable $exception)
    {
        if (method_exists($exception, 'render') && $response = $exception->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($exception instanceof Responsable) {
            return $exception->toResponse($request);
        }

        if ($exception instanceof ValidationException) {
            return $this->responseFactory->json(
                [
                    'errors' => $this->mapValidationException($exception),
                ],
                $exception->getCode(),
                [
                    'Content-Type' => 'application/vnd.api+json',
                ]
            );
        }

        $error = [
            'status' => $exception->getCode(),
            'detail' => $exception->getMessage(),
        ];

        if ($this->debug === true) {
            $error['trace'] = $this->getTrace($exception);
        }

        return $this->responseFactory->json(
            [
                'errors' => [
                    $error,
                ],
            ],
            $exception->getCode(),
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

    private function getTrace(Throwable $exception): string
    {
        try {
            if (json_encode($exception->getTrace()) === false) {
                return 'Trace is not available.';
            }
        } catch (Throwable $e) {
            return 'Trace is not available.';
        }

        return json_encode($exception->getTrace());
    }
}
