<?php

namespace Tests\Feature\Partes;

use App\Services\Partes\SmartCapture\PartesSmartCaptureProposalPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ApiV1PartesSmartCaptureTurnTest extends TestCase
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

    /**
     * @return array{clienteId: int, tipoId: int, asistenteId: int}
     */
    private function seedCatalogos(): array
    {
        $tipoClienteId = DB::table('PQ_PARTES_TIPOS_CLIENTE')->insertGetId([
            'code' => 'TC1',
            'descripcion' => 'Tipo',
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clienteId = DB::table('PQ_PARTES_CLIENTES')->insertGetId([
            'user_id' => null,
            'code' => 'CL1',
            'nombre' => 'Cliente Uno',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_CLIENTES')->insert([
            'user_id' => null,
            'code' => 'CL2',
            'nombre' => 'Cliente Dos Similar',
            'tipo_cliente_id' => $tipoClienteId,
            'email' => null,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoId = (int) DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', 'GEN')->value('id');
        $asistenteId = (int) DB::table('PQ_PARTES_USUARIOS')->where('code', 'admin')->value('id');

        return compact('clienteId', 'tipoId', 'asistenteId');
    }

    private function createCredential(string $token): int
    {
        $create = $this->postJson('/api/v1/llm-credentials', [
            'nombre' => 'SC Fake',
            'proveedor' => 'openai',
            'modelo' => 'gpt-4o-mini',
            'secreto' => 'sk-fake',
            'enabled' => true,
        ], $this->authHeaders($token));
        $create->assertStatus(201);

        return (int) $create->json('resultado.item.id');
    }

    /** @param array<string, mixed> $proposal */
    private function bindProposal(array $proposal): void
    {
        $this->app->instance(PartesSmartCaptureProposalPort::class, new class($proposal) implements PartesSmartCaptureProposalPort
        {
            /** @param array{replyText: string, save: bool, fields: array<string, mixed>} $proposal */
            public function __construct(private readonly array $proposal)
            {
            }

            public function propose(
                string $message,
                array $draftContext,
                ?array $pendingChoice,
                array $images,
                object $credentialContext,
            ): array {
                return $this->proposal;
            }
        });
        $this->app->forgetInstance(\App\Services\Partes\SmartCapture\PartesTareaSmartCaptureTurnService::class);
    }

    /** @return array<string, mixed> */
    private function draftContext(array $cat, bool $esSupervisor = true): array
    {
        return [
            'mode' => 'create',
            'id' => null,
            'cerrado' => false,
            'clienteId' => null,
            'clienteCode' => null,
            'clienteNombre' => null,
            'asistenteId' => $cat['asistenteId'],
            'asistenteCode' => 'admin',
            'tipoTareaId' => null,
            'tipoTareaCode' => null,
            'fecha' => now()->toDateString(),
            'duracionMinutos' => 15,
            'observacion' => '',
            'sinCargo' => false,
            'presencial' => false,
            'esSupervisor' => $esSupervisor,
            'rowVersion' => null,
        ];
    }

    public function test_turn_requiere_auth(): void
    {
        $this->postJson('/api/v1/partes/tareas/asistente/turn', [], $this->tenantHeaders())
            ->assertStatus(401);
    }

    public function test_turn_sin_credencial_devuelve_4201(): void
    {
        $token = $this->login();
        $this->seedCatalogos();

        $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'cliente CL1',
            'modality' => 'texto',
            'credentialId' => 99999,
            'draftContext' => $this->draftContext(['asistenteId' => 1]),
            'pendingChoice' => null,
            'images' => [],
        ], $this->authHeaders($token))
            ->assertStatus(409)
            ->assertJsonPath('error', 4201)
            ->assertJsonPath('resultado.configurationRequired', true);
    }

    public function test_turn_set_field_cliente_unico(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $credentialId = $this->createCredential($token);
        $this->bindProposal([
            'replyText' => 'ok cliente',
            'save' => false,
            'fields' => ['cliente' => 'CL1'],
        ]);

        $response = $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'cliente CL1 una hora',
            'modality' => 'texto',
            'credentialId' => $credentialId,
            'draftContext' => $this->draftContext($cat),
            'pendingChoice' => null,
            'images' => [],
        ], $this->authHeaders($token));

        $response->assertOk()->assertJsonPath('error', 0);
        $actions = $response->json('resultado.actions') ?? [];
        $this->assertTrue(collect($actions)->contains(
            fn ($a) => ($a['action'] ?? null) === 'setField'
                && ($a['payload']['field'] ?? null) === 'clienteId'
                && (int) ($a['payload']['value'] ?? 0) === $cat['clienteId']
        ));
        $this->assertSame(
            (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->count(),
            0,
            'El turno no debe persistir tareas'
        );
    }

    public function test_turn_needs_choice_cliente_ambiguo(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $credentialId = $this->createCredential($token);
        $this->bindProposal([
            'replyText' => 'varios',
            'save' => false,
            'fields' => ['cliente' => 'Cliente'],
        ]);

        $response = $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'cliente Cliente',
            'modality' => 'texto',
            'credentialId' => $credentialId,
            'draftContext' => $this->draftContext($cat),
            'pendingChoice' => null,
            'images' => [],
        ], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('resultado.pendingChoice.kind', 'chooseCliente');
        $this->assertGreaterThanOrEqual(2, count($response->json('resultado.pendingChoice.options') ?? []));
        $reply = (string) $response->json('resultado.replyText');
        $this->assertStringContainsString('partes.smartCapture.clienteAmbiguo', $reply);
        $this->assertStringContainsString('1 — ', $reply);
        $this->assertStringContainsString('2 — ', $reply);
        $this->assertStringNotContainsString('varios', $reply);
    }

    public function test_turn_cliente_inexistente_rechaza_sin_prosa_llm_ni_save(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $credentialId = $this->createCredential($token);
        $this->bindProposal([
            'replyText' => "Se actualizó el cliente a 'pirulin'. ¿Desea guardar los cambios?",
            'save' => true,
            'fields' => ['cliente' => 'pirulin'],
        ]);

        $response = $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'cliente pirulin',
            'modality' => 'texto',
            'credentialId' => $credentialId,
            'draftContext' => $this->draftContext($cat),
            'pendingChoice' => null,
            'images' => [],
        ], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('resultado.replyText', 'partes.smartCapture.clienteNoEncontrado')
            ->assertJsonPath('resultado.pendingChoice', null);

        $actions = $response->json('resultado.actions') ?? [];
        $actionNames = array_map(static fn ($a) => $a['action'] ?? '', $actions);
        $this->assertContains('needsRefine', $actionNames);
        $this->assertNotContains('save', $actionNames);
        $this->assertNotContains('setField', $actionNames);
    }

    public function test_turn_fecha_futura_pide_confirmacion(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $credentialId = $this->createCredential($token);
        $future = now()->addDays(3)->toDateString();
        $this->bindProposal([
            'replyText' => 'fecha',
            'save' => false,
            'fields' => ['fecha' => $future],
        ]);

        $response = $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'fecha '.$future,
            'modality' => 'texto',
            'credentialId' => $credentialId,
            'draftContext' => $this->draftContext($cat),
            'pendingChoice' => null,
            'images' => [],
        ], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('resultado.pendingChoice.kind', 'confirmFutureDate');
        $actions = $response->json('resultado.actions') ?? [];
        $this->assertFalse(collect($actions)->contains(
            fn ($a) => ($a['action'] ?? null) === 'setField' && ($a['payload']['field'] ?? null) === 'fecha'
        ));
    }

    public function test_turn_confirma_fecha_futura(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $credentialId = $this->createCredential($token);
        $future = now()->addDays(2)->toDateString();

        $response = $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'sí',
            'modality' => 'texto',
            'credentialId' => $credentialId,
            'draftContext' => $this->draftContext($cat),
            'pendingChoice' => [
                'kind' => 'confirmFutureDate',
                'options' => [
                    ['id' => 1, 'label' => 'Sí'],
                    ['id' => 2, 'label' => 'No'],
                ],
                'deferred' => [
                    ['cause' => 'confirmationRequired', 'payload' => ['fecha' => $future]],
                ],
            ],
            'images' => [],
        ], $this->authHeaders($token));

        $response->assertOk();
        $actions = $response->json('resultado.actions') ?? [];
        $this->assertTrue(collect($actions)->contains(
            fn ($a) => ($a['action'] ?? null) === 'setField'
                && ($a['payload']['field'] ?? null) === 'fecha'
                && ($a['payload']['value'] ?? null) === $future
        ));
        $this->assertNull($response->json('resultado.pendingChoice'));
    }

    public function test_turn_save_sin_insert(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $credentialId = $this->createCredential($token);
        $this->bindProposal([
            'replyText' => 'guardar',
            'save' => true,
            'fields' => [],
        ]);

        $before = (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->count();
        $response = $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'guardar tarea',
            'modality' => 'texto',
            'credentialId' => $credentialId,
            'draftContext' => $this->draftContext($cat),
            'pendingChoice' => null,
            'images' => [],
        ], $this->authHeaders($token));

        $response->assertOk();
        $this->assertTrue(collect($response->json('resultado.actions') ?? [])->contains(
            fn ($a) => ($a['action'] ?? null) === 'save'
        ));
        $this->assertSame($before, (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->count());
    }

    public function test_turn_asistente_negado_si_no_supervisor(): void
    {
        $token = $this->login();
        $cat = $this->seedCatalogos();
        $credentialId = $this->createCredential($token);
        $this->bindProposal([
            'replyText' => 'asistente',
            'save' => false,
            'fields' => ['asistente' => 'admin'],
        ]);

        $response = $this->postJson('/api/v1/partes/tareas/asistente/turn', [
            'contractVersion' => 1,
            'message' => 'asistente admin',
            'modality' => 'texto',
            'credentialId' => $credentialId,
            'draftContext' => $this->draftContext($cat, false),
            'pendingChoice' => null,
            'images' => [],
        ], $this->authHeaders($token));

        $response->assertOk();
        $actions = $response->json('resultado.actions') ?? [];
        $this->assertFalse(collect($actions)->contains(
            fn ($a) => ($a['action'] ?? null) === 'setField' && ($a['payload']['field'] ?? null) === 'asistenteId'
        ));
        $this->assertStringContainsString(
            'asistenteSoloSupervisor',
            (string) $response->json('resultado.replyText')
        );
    }
}
