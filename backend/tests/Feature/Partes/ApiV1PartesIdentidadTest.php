<?php

namespace Tests\Feature\Partes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiV1PartesIdentidadTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    public function test_login_sin_perfil_dominio_devuelve_403_sin_token(): void
    {
        $this->seed([
            \Database\Seeders\PqRolSeeder::class,
            \Database\Seeders\PqMenuSeeder::class,
            \Database\Seeders\PqPermisoSeeder::class,
            \Database\Seeders\PqPartesTiposTareaSeeder::class,
        ]);

        DB::table('PQ_PARTES_USUARIOS')->delete();

        $tokensBefore = PersonalAccessToken::query()->count();

        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());

        $response->assertStatus(403)
            ->assertJsonPath('error', 3003)
            ->assertJsonPath('respuesta', 'partes.auth.noFunctionalProfile');

        $this->assertSame($tokensBefore, PersonalAccessToken::query()->count());
    }

    public function test_login_admin_seed_devuelve_partes_supervisor(): void
    {
        $this->seed();

        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('resultado.partes.tipoFuncional', 'asistente')
            ->assertJsonPath('resultado.partes.esSupervisor', true)
            ->assertJsonPath('resultado.partes.code', 'admin');

        $this->assertNotEmpty($response->json('resultado.token'));
        $this->assertNotNull($response->json('resultado.partes.asistenteId'));
        $this->assertNull($response->json('resultado.partes.clienteId'));
    }

    public function test_es_supervisor_no_usa_users_supervisor(): void
    {
        $this->seed();

        $user = User::query()->where('usuario', 'admin')->firstOrFail();
        $user->supervisor = true;
        $user->save();

        DB::table('PQ_PARTES_USUARIOS')->where('user_id', $user->id)->update(['supervisor' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('resultado.partes.esSupervisor', false);
    }

    public function test_login_cliente_dominio_ok(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'usuario' => 'cli1',
            'email' => 'cli1@test.local',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);

        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TC1',
            'descripcion' => 'Tipo',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $user->id,
            'nombre' => 'Cliente Uno',
            'tipo_cliente_id' => $tipoId,
            'code' => 'C001',
            'email' => 'cli1@org.local',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'cli1',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('resultado.partes.tipoFuncional', 'cliente')
            ->assertJsonPath('resultado.partes.esSupervisor', false)
            ->assertJsonPath('resultado.partes.code', 'C001');
        $this->assertNull($response->json('resultado.partes.asistenteId'));
        $this->assertNotNull($response->json('resultado.partes.clienteId'));
    }

    public function test_perfil_inconsistente_403(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'usuario' => 'both1',
            'email' => 'both1@test.local',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $user->id,
            'code' => 'BOTH_A',
            'nombre' => 'Both Asist',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tipoId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TCB',
            'descripcion' => 'Tipo B',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => $user->id,
            'nombre' => 'Both Cliente',
            'tipo_cliente_id' => $tipoId,
            'code' => 'BOTH_C',
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'both1',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());

        $response->assertStatus(403)
            ->assertJsonPath('respuesta', 'partes.auth.inconsistentProfile');
    }

    public function test_me_expone_partes_y_falla_si_inhabilitan(): void
    {
        $this->seed();

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'admin',
            'password' => 'Paqsystems',
        ], $this->tenantHeaders());
        $token = $login->json('resultado.token');

        $meOk = $this->getJson('/api/v1/auth/me', array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]));
        $meOk->assertStatus(200)
            ->assertJsonPath('resultado.partes.tipoFuncional', 'asistente');

        $userId = (int) User::query()->where('usuario', 'admin')->value('id');
        DB::table('PQ_PARTES_USUARIOS')->where('user_id', $userId)->update(['inhabilitado' => true]);

        $meFail = $this->getJson('/api/v1/auth/me', array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]));
        $meFail->assertStatus(403)
            ->assertJsonPath('respuesta', 'partes.auth.noFunctionalProfile');
    }
}
