<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\PartesRemoteDxReportingEngine;
use PHPUnit\Framework\TestCase;

final class PartesRemoteDxReportingEngineTest extends TestCase
{
    public function test_parse_report_pk(): void
    {
        $this->assertSame(7, PartesRemoteDxReportingEngine::parseReportPk(7));
        $this->assertSame(7, PartesRemoteDxReportingEngine::parseReportPk('7'));
        $this->assertNull(PartesRemoteDxReportingEngine::parseReportPk('Consulta-Partes'));
        $this->assertNull(PartesRemoteDxReportingEngine::parseReportPk(null));
    }
}
