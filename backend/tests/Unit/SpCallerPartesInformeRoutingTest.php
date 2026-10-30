<?php

namespace Tests\Unit;

use App\Repositories\Sp\SpCaller;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class SpCallerPartesInformeRoutingTest extends TestCase
{
    public function test_informe_paquete_horas_no_se_clasifica_como_maestros(): void
    {
        $caller = new SpCaller();
        $method = new ReflectionMethod(SpCaller::class, 'isPartesMaestrosProcedure');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($caller, 'pq_sp_partes_informe_paquete_horas'));
        $this->assertFalse($method->invoke($caller, 'pq_sp_partes_informe_agrupado'));
        $this->assertFalse($method->invoke($caller, 'pq_sp_partes_dashboard_snapshot'));
    }
}
