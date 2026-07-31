<?php

namespace App\Services\Auth;

use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PostLoginBusinessGateException extends \RuntimeException
{
    public function __construct(
        public readonly int $catalogError = PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
        public readonly string $respuesta = PaqSuiteEnvelopeCatalog::RESPUESTA_AUTH_FORBIDDEN,
        public readonly int $httpStatus = 403,
        string $message = 'Post-login business gate rejected'
    ) {
        parent::__construct($message);
    }
}
