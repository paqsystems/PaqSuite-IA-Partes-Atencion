<?php

namespace Tests\Feature\Partes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** CC PQ #13 — FilterRow remoto (API / Operations). */
final class ApiV1PartesCc13FilterRowTest extends TestCase
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

    public function test_asistentes_list_filtra_por_nombre(): void
    {
        $token = $this->loginAdmin();
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => null,
            'code' => 'AS-FIL',
            'nombre' => 'ANKAS Asistente',
            'email' => null,
            'supervisor' => 0,
            'activo' => 1,
            'inhabilitado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => null,
            'code' => 'AS-OTR',
            'nombre' => 'Otro Asistente',
            'email' => null,
            'supervisor' => 0,
            'activo' => 1,
            'inhabilitado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filtered = $this->getJson('/api/v1/partes/asistentes?nombre=ANKAS&pageSize=50', $this->authHeaders($token));
        $filtered->assertStatus(200);
        $nombres = collect($filtered->json('resultado.items'))->pluck('nombre');
        $this->assertGreaterThanOrEqual(1, $nombres->count());
        $this->assertTrue($nombres->every(fn ($n) => str_contains((string) $n, 'ANKAS')));
        $this->assertFalse($nombres->contains('Otro Asistente'));
    }

    public function test_clientes_list_filtra_por_nombre(): void
    {
        $token = $this->loginAdmin();
        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCF13',
            'descripcion' => 'Filtro CC13',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'code' => 'CL-ANK',
            'nombre' => 'ANKAS Cliente',
            'tipo_cliente_id' => $tipoId,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'code' => 'CL-XXX',
            'nombre' => 'Cliente Ajeno',
            'tipo_cliente_id' => $tipoId,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filtered = $this->getJson('/api/v1/partes/clientes?nombre=ANKAS&pageSize=50', $this->authHeaders($token));
        $filtered->assertStatus(200);
        $nombres = collect($filtered->json('resultado.items'))->pluck('nombre');
        $this->assertTrue($nombres->every(fn ($n) => str_contains((string) $n, 'ANKAS')));
        $this->assertFalse($nombres->contains('Cliente Ajeno'));
    }

    public function test_tipos_cliente_list_filtra_por_descripcion(): void
    {
        $token = $this->loginAdmin();
        DB::table('PQ_PARTES_TIPOS_CLIENTE')->insert([
            'code' => 'TC-A1',
            'descripcion' => 'Premium ANKAS',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_TIPOS_CLIENTE')->insert([
            'code' => 'TC-B2',
            'descripcion' => 'Standard',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filtered = $this->getJson('/api/v1/partes/tipos-cliente?descripcion=ANKAS&pageSize=50', $this->authHeaders($token));
        $filtered->assertStatus(200);
        $desc = collect($filtered->json('resultado.items'))->pluck('descripcion');
        $this->assertTrue($desc->every(fn ($d) => str_contains((string) $d, 'ANKAS')));
        $this->assertFalse($desc->contains('Standard'));
    }

    public function test_tipos_tarea_list_filtra_por_descripcion(): void
    {
        $token = $this->loginAdmin();
        DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
            'code' => 'TT-A1',
            'descripcion' => 'Soporte ANKAS',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_TIPOS_TAREA')->insert([
            'code' => 'TT-B2',
            'descripcion' => 'Desarrollo',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filtered = $this->getJson('/api/v1/partes/tipos-tarea?descripcion=ANKAS&pageSize=50', $this->authHeaders($token));
        $filtered->assertStatus(200);
        $desc = collect($filtered->json('resultado.items'))->pluck('descripcion');
        $this->assertTrue($desc->every(fn ($d) => str_contains((string) $d, 'ANKAS')));
        $this->assertFalse($desc->contains('Desarrollo'));
    }

    public function test_masivo_list_filtra_por_cliente_nombre_y_tipo_tarea(): void
    {
        $token = $this->loginAdmin();
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCM13',
            'descripcion' => 'Masivo',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteAnkasId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'code' => 'C-ANK',
            'nombre' => 'ANKAS SA',
            'tipo_cliente_id' => $tipoClienteId,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteOtroId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'code' => 'C-OTR',
            'nombre' => 'Otro SA',
            'tipo_cliente_id' => $tipoClienteId,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoAnkasId = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId([
            'code' => 'TT-ANK',
            'descripcion' => 'Consultoría ANKAS',
            'is_generico' => false,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoOtroId = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId([
            'code' => 'TT-GEN2',
            'descripcion' => 'Genérico',
            'is_generico' => true,
            'is_default' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $asistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');
        $hoy = now()->toDateString();

        foreach ([$clienteAnkasId, $clienteOtroId] as $clienteId) {
            foreach ([$tipoAnkasId, $tipoOtroId] as $tipoTareaId) {
                DB::table('PQ_PARTES_REGISTRO_TAREA')->insert([
                    'usuario_id' => $asistenteId,
                    'cliente_id' => $clienteId,
                    'tipo_tarea_id' => $tipoTareaId,
                    'fecha' => $hoy,
                    'duracion_minutos' => 15,
                    'sin_cargo' => false,
                    'presencial' => false,
                    'observacion' => 'CC13-'.$clienteId.'-'.$tipoTareaId,
                    'cerrado' => false,
                    'es_tarea' => true,
                    'row_version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $base = '/api/v1/partes/tareas?fechaDesde='.$hoy.'&fechaHasta='.$hoy.'&pageSize=50';

        $byCliente = $this->getJson($base.'&clienteNombre=ANKAS', $this->authHeaders($token));
        $byCliente->assertStatus(200);
        $this->assertSame(2, (int) $byCliente->json('resultado.total'));
        $this->assertTrue(
            collect($byCliente->json('resultado.items'))->every(
                fn ($row) => str_contains((string) ($row['clienteNombre'] ?? ''), 'ANKAS')
            )
        );

        $byTipo = $this->getJson($base.'&tipoTareaDescripcion=ANKAS', $this->authHeaders($token));
        $byTipo->assertStatus(200);
        $this->assertSame(2, (int) $byTipo->json('resultado.total'));
        $this->assertTrue(
            collect($byTipo->json('resultado.items'))->every(
                fn ($row) => str_contains((string) ($row['tipoTareaDescripcion'] ?? ''), 'ANKAS')
            )
        );

        $combined = $this->getJson($base.'&clienteNombre=ANKAS&tipoTareaDescripcion=ANKAS', $this->authHeaders($token));
        $combined->assertStatus(200);
        $this->assertSame(1, (int) $combined->json('resultado.total'));
    }
}
