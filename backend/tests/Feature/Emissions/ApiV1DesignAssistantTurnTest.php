<?php

namespace Tests\Feature\Emissions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ApiV1DesignAssistantTurnTest extends TestCase
{
    use RefreshDatabase;

    private const PROCESS = 'partes.informes.consultaDetallada';

    private const TURN = '/api/v1/emissions/design/processes/'.self::PROCESS.'/assistant/turn';

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

    private function login(string $usuario = 'admin', string $password = 'Paqsystems'): string
    {
        $this->seed();
        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => $usuario,
            'password' => $password,
        ], $this->tenantHeaders());
        $login->assertStatus(200);

        return (string) $login->json('resultado.token');
    }

    private function createCredential(string $token): int
    {
        $create = $this->postJson('/api/v1/llm-credentials', [
            'nombre' => 'Design Fake',
            'proveedor' => 'openai',
            'modelo' => 'gpt-4o-mini',
            'secreto' => 'sk-fake',
            'enabled' => true,
        ], $this->authHeaders($token));
        $create->assertStatus(201);

        return (int) $create->json('resultado.item.id');
    }

    /** @param array<string, mixed> $overrides */
    private function turnBody(array $overrides = []): array
    {
        return array_merge([
            'contractVersion' => 1,
            'message' => 'agrupá por cliente y sumá los minutos',
            'modality' => 'texto',
            'credentialId' => 1,
            'reportUrl' => 'partes.consultaDetallada.principal',
            'draftContext' => [
                'processCode' => self::PROCESS,
                'reportUrl' => 'partes.consultaDetallada.principal',
                'locale' => 'es',
            ],
            'pendingChoice' => null,
            'images' => [],
        ], $overrides);
    }

    public function test_turn_requiere_auth(): void
    {
        $this->postJson(self::TURN, $this->turnBody(), $this->tenantHeaders())
            ->assertStatus(401);
    }

    public function test_turn_sin_acceso_total_4709(): void
    {
        $this->seed();
        $asstUser = \App\Models\User::factory()->create([
            'usuario' => 'asstDesign',
            'password' => bcrypt('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $asstUser->id,
            'code' => 'ADES',
            'nombre' => 'Asist Design',
            'email' => null,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $rolId = DB::table('pq_roles')->insertGetId([
            'codigo' => 'ASISTENTE_DES',
            'nombre' => 'Asistente design',
            'acceso_total' => false,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $empresaId = DB::table('pq_empresa')->value('id') ?? 1;
        DB::table('pq_permisos')->insert([
            'user_id' => $asstUser->id,
            'empresa_id' => $empresaId,
            'rol_id' => $rolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = (string) $this->postJson('/api/v1/auth/login', [
            'usuario' => 'asstDesign',
            'password' => 'Secret123!',
        ], $this->tenantHeaders())->json('resultado.token');

        $this->app['auth']->forgetGuards();
        $this->postJson(self::TURN, $this->turnBody(), $this->authHeaders($token))
            ->assertStatus(403)
            ->assertJsonPath('error', 4709);
    }

    public function test_turn_sin_credencial_configuration_required(): void
    {
        $token = $this->login();

        $this->postJson(self::TURN, $this->turnBody([
            'credentialId' => 99999,
        ]), $this->authHeaders($token))
            ->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.configurationRequired', true)
            ->assertJsonPath('resultado.actions', []);
    }

    public function test_turn_process_no_soportado_422(): void
    {
        $token = $this->login();
        $credentialId = $this->createCredential($token);

        $this->postJson(
            '/api/v1/emissions/design/processes/partes.informes.paqueteHoras/assistant/turn',
            $this->turnBody(['credentialId' => $credentialId]),
            $this->authHeaders($token)
        )
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.emissionDesign.processNotSupported');
    }

    public function test_turn_sin_report_url_422(): void
    {
        $token = $this->login();
        $credentialId = $this->createCredential($token);

        $this->postJson(self::TURN, $this->turnBody([
            'credentialId' => $credentialId,
            'reportUrl' => '',
        ]), $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'partes.emissionDesign.reportUrlRequired');
    }

    public function test_turn_heuristica_devuelve_acciones_whitelist(): void
    {
        $token = $this->login();
        $credentialId = $this->createCredential($token);

        $response = $this->postJson(self::TURN, $this->turnBody([
            'credentialId' => $credentialId,
        ]), $this->authHeaders($token));

        $response
            ->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.configurationRequired', false);

        $actions = $response->json('resultado.actions');
        $this->assertIsArray($actions);
        $types = array_column($actions, 'action');
        $this->assertContains('addGroupByField', $types);
        $this->assertContains('addGroupSum', $types);
    }
}
