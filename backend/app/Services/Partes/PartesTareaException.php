<?php

namespace App\Services\Partes;

use RuntimeException;

final class PartesTareaException extends RuntimeException
{
    public function __construct(
        public readonly string $respuesta,
        public readonly int $httpStatus = 422
    ) {
        parent::__construct($respuesta);
    }
}
