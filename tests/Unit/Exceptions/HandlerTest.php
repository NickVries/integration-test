<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function testItTransformsAGenericExceptionIntoJson()
    {
        $containerMock = Mockery::mock(Container::class);
        $handler = new Handler($containerMock);

        $responseFactoryMock = Mockery::mock(ResponseFactory::class);
        $responseFactoryMock->shouldReceive('json')->andReturnUsing(function ($response) {
            $this->assertEquals([
                'errors' => [
                    [
                        'status'  => 500,
                        'detail' => 'Some internal error',
                    ],
                ],
            ], $response);
        });

        $handler->setResponseFactory($responseFactoryMock);

        $requestMock = Mockery::mock(Request::class);

        $handler->render($requestMock, new ClientException(
            'Some internal error',
            new GuzzleRequest('POST', 'localhost'),
            new Response(500)));
    }

    /** @test */
    public function testItTransformsAValidationExceptionIntoAMultiErrorException()
    {
        $containerMock = Mockery::mock(Container::class);
        $handler = new Handler($containerMock);

        $responseFactoryMock = Mockery::mock(ResponseFactory::class);
        $responseFactoryMock->shouldReceive('json')->andReturnUsing(function ($response) {
            $this->assertCount(2, $response['errors']);
        });
        $handler->setResponseFactory($responseFactoryMock);

        $requestMock = Mockery::mock(Request::class);

        $messageBag = Mockery::mock(MessageBag::class);
        $messageBag->shouldReceive('get')->once()->with('some.missing.pointer')->andReturn(['You are missing required input bro!']);
        $messageBag->shouldReceive('get')->once()->with('some.invalid.pointer')->andReturn(['Your input is invalid yo!']);

        $validator = Mockery::mock(Validator::class, [
            'failed' => [
                'some.missing.pointer' => [],
                'some.invalid.pointer' => [],
            ],
            'errors' => $messageBag,
        ]);

        $validationException = Mockery::mock(ValidationException::class);
        $validationException->validator = $validator;

        $handler->render($requestMock, $validationException);
    }
}
