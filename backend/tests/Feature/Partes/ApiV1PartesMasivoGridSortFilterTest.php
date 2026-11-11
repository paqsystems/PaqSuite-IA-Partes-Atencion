<?php

namespace Tests\Feature\Partes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ApiV1PartesMasivoGridSortFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    /** @return array<string, string> */
    private function authHeaders(string $token): array
    {
        return array_merge($this->tenantHeaders(), ['Authorization' => 'Bearer '.$token]);
    }

    private function loginAdmin(): string
    {
        $this->seed();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());
        $login->assertStatus(200);

        return (string) $login->json('resultado.token');
    }

    /**
     * @return array{base: string, alpha: string, zebra: string}
     */
    private function seedTwoClientTareas(): array
    {
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCSORT',
            'descripcion' => 'Sort',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $alphaId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'code' => 'C-ALP',
            'nombre' => 'Alpha SA',
            'tipo_cliente_id' => $tipoClienteId,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $zebraId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'code' => 'C-ZEB',
            'nombre' => 'Zebra SA',
            'tipo_cliente_id' => $tipoClienteId,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoId = (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('id');
        if ($tipoId <= 0) {
            $tipoId = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId([
                'code' => 'GEN-SORT',
                'descripcion' => 'General sort',
                'is_generico' => true,
                'is_default' => false,
                'activo' => true,
                'inhabilitado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $asistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');
        $hoy = now()->toDateString();
        foreach ([$zebraId, $alphaId] as $clienteId) {
            DB::table('PQ_PARTES_REGISTRO_TAREA')->insert([
                'usuario_id' => $asistenteId,
                'cliente_id' => $clienteId,
                'tipo_tarea_id' => $tipoId,
                'fecha' => $hoy,
                'duracion_minutos' => 15,
                'sin_cargo' => false,
                'presencial' => false,
                'observacion' => 'sort-'.$clienteId,
                'cerrado' => false,
                'es_tarea' => true,
                'row_version' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'base' => '/api/v1/partes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy.'&pageSize=50',
            'alpha' => 'Alpha SA',
            'zebra' => 'Zebra SA',
        ];
    }

    public function test_list_ordena_por_cliente_nombre(): void
    {
        $token = $this->loginAdmin();
        $seed = $this->seedTwoClientTareas();

        $asc = $this->getJson($seed['base'].'&sortField=clienteNombre&sortOrder=asc', $this->authHeaders($token));
        $asc->assertStatus(200);
        $nombresAsc = collect($asc->json('resultado.items'))->pluck('clienteNombre')->all();
        $this->assertSame([$seed['alpha'], $seed['zebra']], $nombresAsc);

        $desc = $this->getJson($seed['base'].'&sortField=clienteNombre&sortOrder=desc', $this->authHeaders($token));
        $desc->assertStatus(200);
        $nombresDesc = collect($desc->json('resultado.items'))->pluck('clienteNombre')->all();
        $this->assertSame([$seed['zebra'], $seed['alpha']], $nombresDesc);
    }

    public function test_list_distinct_devuelve_valores_unicos_de_cliente(): void
    {
        $token = $this->loginAdmin();
        $seed = $this->seedTwoClientTareas();

        $res = $this->getJson($seed['base'].'&distinctField=clienteNombre', $this->authHeaders($token));
        $res->assertStatus(200);
        $values = collect($res->json('resultado.items'))->pluck('value')->sort()->values()->all();
        $this->assertSame([$seed['alpha'], $seed['zebra']], $values);
        $this->assertSame(2, (int) $res->json('resultado.total'));
        $this->assertTrue(collect($res->json('resultado.items'))->every(
            fn ($row) => isset($row['value']) && isset($row['count'])
        ));
    }

    public function test_list_eq_filtra_cliente_exacto(): void
    {
        $token = $this->loginAdmin();
        $seed = $this->seedTwoClientTareas();
        $eq = urlencode(json_encode(['clienteNombre' => [$seed['alpha']]], JSON_UNESCAPED_UNICODE));

        $res = $this->getJson($seed['base'].'&eq='.$eq, $this->authHeaders($token));
        $res->assertStatus(200);
        $this->assertSame(1, (int) $res->json('resultado.total'));
        $this->assertSame($seed['alpha'], $res->json('resultado.items.0.clienteNombre'));
    }
}
