<?php

namespace Tests\Unit;

use App\Tenancy\EmpresasConexionSqlDirectClienteCatalog;
use App\Tenancy\SchedulerClienteResolver;
use Tests\TestCase;

class SchedulerClienteResolverTest extends TestCase
{
    public function test_explicit_cliente_gana_sobre_env_y_catalogo(): void
    {
        config([
            'paqsuite.proyecto' => 'partesatencion',
            'paqsuite.clienteCodigo' => 'DEMO',
            'paqsuite.tasks.schedulerClientes' => 'DEV,DEMO',
        ]);
        $resolver = new SchedulerClienteResolver(new FakeSqlDirectClienteCatalog(['PAQ', 'DEMO']));

        $this->assertSame(['PAQ'], $resolver->resolve('paq'));
    }

    public function test_env_csv_cuando_no_hay_cliente_explicito(): void
    {
        config([
            'paqsuite.proyecto' => 'partesatencion',
            'paqsuite.clienteCodigo' => 'DEMO',
            'paqsuite.tasks.schedulerClientes' => 'PAQ, demo, PAQ',
        ]);
        $resolver = new SchedulerClienteResolver(new FakeSqlDirectClienteCatalog(['DEV']));

        $this->assertSame(['PAQ', 'DEMO'], $resolver->resolve(null));
    }

    public function test_catalogo_sql_cuando_env_vacio(): void
    {
        config([
            'paqsuite.proyecto' => 'partesatencion',
            'paqsuite.clienteCodigo' => 'DEMO',
            'paqsuite.tasks.schedulerClientes' => '',
        ]);
        $resolver = new SchedulerClienteResolver(new FakeSqlDirectClienteCatalog(['DEMO', 'PAQ']));

        $this->assertSame(['DEMO', 'PAQ'], $resolver->resolve(null));
    }

    public function test_fallback_paqsuite_cliente_si_catalogo_vacio(): void
    {
        config([
            'paqsuite.proyecto' => 'partesatencion',
            'paqsuite.clienteCodigo' => 'dev',
            'paqsuite.tasks.schedulerClientes' => '',
        ]);
        $resolver = new SchedulerClienteResolver(new FakeSqlDirectClienteCatalog([]));

        $this->assertSame(['DEV'], $resolver->resolve(null));
    }

    public function test_vacio_si_no_hay_ninguna_fuente(): void
    {
        config([
            'paqsuite.proyecto' => '',
            'paqsuite.clienteCodigo' => '',
            'paqsuite.tasks.schedulerClientes' => '',
        ]);
        $resolver = new SchedulerClienteResolver(new FakeSqlDirectClienteCatalog([]));

        $this->assertSame([], $resolver->resolve(null));
    }
}

final class FakeSqlDirectClienteCatalog extends EmpresasConexionSqlDirectClienteCatalog
{
    /**
     * @param  list<string>  $clientes
     */
    public function __construct(
        private readonly array $clientes,
    ) {
    }

    public function listActiveClienteCodigos(string $proyecto): array
    {
        return $this->clientes;
    }
}
