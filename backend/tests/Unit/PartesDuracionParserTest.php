<?php

namespace Tests\Unit;

use App\Services\Partes\SmartCapture\PartesDuracionParser;
use PHPUnit\Framework\TestCase;

final class PartesDuracionParserTest extends TestCase
{
    public function test_reloj_no_es_decimal(): void
    {
        $this->assertSame(85, PartesDuracionParser::toMinutos('1:25'));
        $this->assertSame(135, PartesDuracionParser::toMinutos('02:15'));
        $this->assertSame(75, PartesDuracionParser::toMinutos(1.25));
        $this->assertSame(75, PartesDuracionParser::toMinutos('1.25 h'));
        $this->assertSame(85, PartesDuracionParser::toMinutos(85));
        $this->assertFalse(PartesDuracionParser::isValidTramo(85, 15));
        $this->assertTrue(PartesDuracionParser::isValidTramo(75, 15));
    }
}
