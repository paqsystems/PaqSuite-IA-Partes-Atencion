<?php

namespace Tests\Unit\Tenancy;

use App\Tenancy\HostMenuProcedimientoChecker;
use Mockery;
use PaqSuite\LaravelCore\Menu\MenuQueryRepository;
use PaqSuite\LaravelCore\Security\AccesoTotalChecker;
use PaqSuite\LaravelCore\Security\CompanyAllowedChecker;
use Tests\TestCase;

class HostMenuProcedimientoCheckerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_emission_design_existe_si_hay_menu_disenador(): void
    {
        $menu = Mockery::mock(MenuQueryRepository::class);
        $menu->shouldReceive('listEnabled')->once()->andReturn([
            ['procedimiento' => 'partes_disenador_emisiones'],
        ]);

        $checker = new HostMenuProcedimientoChecker(
            Mockery::mock(AccesoTotalChecker::class),
            Mockery::mock(CompanyAllowedChecker::class),
            $menu,
        );

        $this->assertTrue($checker->existsInMenu('emission.design'));
    }

    public function test_emission_design_ausente_sin_menu_disenador(): void
    {
        $menu = Mockery::mock(MenuQueryRepository::class);
        $menu->shouldReceive('listEnabled')->once()->andReturn([
            ['procedimiento' => 'partes_consulta_detallada'],
        ]);

        $checker = new HostMenuProcedimientoChecker(
            Mockery::mock(AccesoTotalChecker::class),
            Mockery::mock(CompanyAllowedChecker::class),
            $menu,
        );

        $this->assertFalse($checker->existsInMenu('emission.design'));
    }
}
