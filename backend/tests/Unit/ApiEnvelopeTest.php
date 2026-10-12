<?php

namespace Tests\Unit;

use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use Tests\TestCase;

/**
 * PA-02 / GEN-20: el host no tiene ApiEnvelope propio; el contrato es ApiResponse.
 */
class ApiEnvelopeTest extends TestCase
{
    public function test_success_serializa_error_cero_y_resultado_objeto(): void
    {
        $payload = ApiResponse::success()->getData(true);

        $this->assertSame(0, $payload['error']);
        $this->assertIsArray($payload['resultado']);
        $this->assertSame([], $payload['resultado']);
    }

    public function test_success_conserva_resultado_asociativo(): void
    {
        $payload = ApiResponse::success(['token' => 'abc'])->getData(true);

        $this->assertSame(0, $payload['error']);
        $this->assertSame('abc', $payload['resultado']['token']);
        $this->assertNotNull($payload['resultado']);
    }
}
