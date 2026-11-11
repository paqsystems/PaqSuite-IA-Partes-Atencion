<?php

namespace Tests\Unit\Partes;

use App\Services\Partes\SmartCapture\PartesLocaleDateParser;
use PHPUnit\Framework\TestCase;

final class PartesLocaleDateParserTest extends TestCase
{
    public function test_iso_no_cambia_con_el_locale(): void
    {
        $this->assertSame('2026-11-07', PartesLocaleDateParser::toIso('2026-11-07', 'en'));
        $this->assertSame('2026-11-07', PartesLocaleDateParser::toIso('2026-11-07', 'es'));
    }

    public function test_fecha_ambigua_sigue_el_locale(): void
    {
        $this->assertSame('2026-11-07', PartesLocaleDateParser::toIso('07/11/2026', 'es'));
        $this->assertSame('2026-11-07', PartesLocaleDateParser::toIso('07/11/2026', 'pt'));
        $this->assertSame('2026-11-07', PartesLocaleDateParser::toIso('07/11/2026', 'fr'));
        $this->assertSame('2026-11-07', PartesLocaleDateParser::toIso('07/11/2026', 'it'));
        $this->assertSame('2026-07-11', PartesLocaleDateParser::toIso('07/11/2026', 'en'));
    }

    public function test_fecha_no_ambigua_no_invierte_el_dia(): void
    {
        $this->assertSame('2026-11-13', PartesLocaleDateParser::toIso('13/11/2026', 'en'));
        $this->assertSame('2026-11-13', PartesLocaleDateParser::toIso('13/11/2026', 'es'));
        $this->assertSame('2026-11-13', PartesLocaleDateParser::toIso('11/13/2026', 'es'));
        $this->assertSame('2026-11-13', PartesLocaleDateParser::toIso('11/13/2026', 'en'));
    }

    public function test_locale_desconocido_cae_a_es(): void
    {
        $this->assertSame('2026-11-07', PartesLocaleDateParser::toIso('07/11/2026', 'de'));
    }
}
