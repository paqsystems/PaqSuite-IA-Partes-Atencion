<?php

namespace Tests\Unit;

use App\Services\Partes\SmartCapture\PartesSmartCaptureFieldExtractor;
use PHPUnit\Framework\TestCase;

final class PartesSmartCaptureFieldExtractorTest extends TestCase
{
    public function test_extrae_prompt_cc33(): void
    {
        $parsed = PartesSmartCaptureFieldExtractor::fromText(
            'asistente PQ cliente LACAPOL duracion 1:25 hrs'
        );

        $this->assertSame('PQ', $parsed['fields']['asistente']);
        $this->assertSame('LACAPOL', $parsed['fields']['cliente']);
        $this->assertSame('1:25', $parsed['fields']['duracionMinutos']);
        $this->assertFalse($parsed['save']);
    }

    public function test_extrae_prosa_del_modelo_con_minutos(): void
    {
        $parsed = PartesSmartCaptureFieldExtractor::fromText(
            'Registré al asistente PQ con el cliente LACAPOL y una duración de 85 minutos. ¿Querés guardar la tarea?'
        );

        $this->assertSame('PQ', $parsed['fields']['asistente']);
        $this->assertSame('LACAPOL', $parsed['fields']['cliente']);
        $this->assertSame(85, $parsed['fields']['duracionMinutos']);
        $this->assertTrue($parsed['save']);
    }

    public function test_merge_llm_pisa_extractor(): void
    {
        $merged = PartesSmartCaptureFieldExtractor::mergeFields(
            ['cliente' => 'PQ', 'asistente' => 'X'],
            ['cliente' => null, 'asistente' => 'admin'],
        );

        $this->assertSame('PQ', $merged['cliente']);
        $this->assertSame('admin', $merged['asistente']);
    }
}
