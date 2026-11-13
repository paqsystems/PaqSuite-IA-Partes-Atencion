<?php

namespace App\Services\Emissions\DesignAssistant;

final class DesignAssistantValidationException extends \RuntimeException
{
    public function __construct(
        public readonly string $respuestaKey,
    ) {
        parent::__construct($respuestaKey);
    }
}
