<?php

namespace Tests\Unit\Support;

use App\Support\EmpresaThemeCatalog;
use PHPUnit\Framework\TestCase;

final class EmpresaThemeCatalogTest extends TestCase
{
    public function test_normalize_paqsuite_to_stock(): void
    {
        $this->assertSame(
            'generic.dark.compact',
            EmpresaThemeCatalog::normalizeForPersistence('paqsuite.dark.compact')
        );
        $this->assertSame(
            'material.orange.light',
            EmpresaThemeCatalog::normalizeForPersistence('paqsuite.orange.material')
        );
    }

    public function test_normalize_keeps_stock(): void
    {
        $this->assertSame(
            'generic.light',
            EmpresaThemeCatalog::normalizeForPersistence('generic.light')
        );
    }

    public function test_to_ui_theme_from_stock(): void
    {
        $this->assertSame(
            'paqsuite.dark.compact',
            EmpresaThemeCatalog::toUiTheme('generic.dark.compact')
        );
        $this->assertSame(
            'paqsuite.light.generic',
            EmpresaThemeCatalog::toUiTheme('generic.light')
        );
    }

    public function test_accepted_input_includes_paqsuite_and_stock(): void
    {
        $this->assertTrue(EmpresaThemeCatalog::isAcceptedInput('paqsuite.violet.generic'));
        $this->assertTrue(EmpresaThemeCatalog::isAcceptedInput('generic.darkviolet'));
        $this->assertFalse(EmpresaThemeCatalog::isAcceptedInput('no-existe'));
    }
}
