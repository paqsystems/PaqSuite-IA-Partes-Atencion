<?php

namespace Tests\Unit\Partes;

use App\Services\Partes\PartesMasivoLocale;
use PHPUnit\Framework\TestCase;

final class PartesMasivoLocaleTest extends TestCase
{
    public function test_normalize_supported_locales(): void
    {
        $this->assertSame('en', PartesMasivoLocale::normalize('en'));
        $this->assertSame('fr', PartesMasivoLocale::normalize('fr-CA'));
    }

    public function test_normalize_fallback_es(): void
    {
        $this->assertSame('es', PartesMasivoLocale::normalize(null));
        $this->assertSame('es', PartesMasivoLocale::normalize('de'));
    }
}
