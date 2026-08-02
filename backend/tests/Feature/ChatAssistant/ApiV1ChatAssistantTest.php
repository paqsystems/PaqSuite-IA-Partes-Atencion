<?php

namespace Tests\Feature\ChatAssistant;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\Sanctum;
use PaqSuite\LaravelCore\ChatAssistant\Contracts\LlmChatCompletionClient;
use PaqSuite\LaravelCore\Llm\LlmCredentialContext;
use Tests\TestCase;

final class ApiV1ChatAssistantTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    public function test_turns_requiere_auth(): void
    {
        $this->postJson('/api/v1/chat-assistant/turns', [], $this->tenantHeaders())
            ->assertStatus(401);
    }

    public function test_turns_sin_credencial_valida_devuelve_4301(): void
    {
        $this->seed();
        $user = User::query()->where('usuario', 'admin')->firstOrFail();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/chat-assistant/turns', [
            'contractVersion' => 1,
            'credentialId' => 999,
            'message' => 'hola',
        ], $this->tenantHeaders())
            ->assertStatus(409)
            ->assertJsonPath('error', 4301)
            ->assertJsonPath('resultado.configurationRequired', true);
    }

    public function test_llm_credentials_crud_sin_secreto_en_respuesta(): void
    {
        $this->seed();
        $user = User::query()->where('usuario', 'admin')->firstOrFail();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/llm-credentials', [
            'nombre' => 'Demo OpenAI',
            'proveedor' => 'openai',
            'modelo' => 'gpt-4o-mini',
            'secreto' => 'sk-test-secret',
            'supportsVision' => false,
            'enabled' => true,
        ], $this->tenantHeaders());

        $create->assertStatus(201)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.item.hasSecret', true);
        $this->assertArrayNotHasKey('secreto', $create->json('resultado.item') ?? []);
        $this->assertArrayNotHasKey('secreto_cifrado', $create->json('resultado.item') ?? []);

        $id = (int) $create->json('resultado.item.id');

        $list = $this->getJson('/api/v1/llm-credentials', $this->tenantHeaders());
        $list->assertOk()->assertJsonPath('error', 0);
        $this->assertNotEmpty($list->json('resultado.items'));

        $this->putJson('/api/v1/llm-credentials/active', [
            'activeLlmCredentialId' => $id,
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.activeLlmCredentialId', $id);
    }

    public function test_turno_ok_con_cliente_fake(): void
    {
        $this->seed();
        $user = User::query()->where('usuario', 'admin')->firstOrFail();
        Sanctum::actingAs($user);

        $this->app->instance(LlmChatCompletionClient::class, new class implements LlmChatCompletionClient
        {
            public function complete(LlmCredentialContext $credential, array $messages, array $images = []): string
            {
                return 'Respuesta orientativa de prueba.';
            }
        });

        // Rebuild turn service with rebound client
        $this->app->forgetInstance(\PaqSuite\LaravelCore\ChatAssistant\ChatAssistantTurnService::class);

        $create = $this->postJson('/api/v1/llm-credentials', [
            'nombre' => 'Fake',
            'proveedor' => 'openai',
            'modelo' => 'gpt-4o-mini',
            'secreto' => 'sk-fake',
            'enabled' => true,
        ], $this->tenantHeaders());
        $credentialId = (int) $create->json('resultado.item.id');

        $turn = $this->postJson('/api/v1/chat-assistant/turns', [
            'contractVersion' => 1,
            'credentialId' => $credentialId,
            'message' => 'como cargar un parte diario',
        ], $this->tenantHeaders());

        $turn->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.reply', 'Respuesta orientativa de prueba.')
            ->assertJsonPath('resultado.configurationRequired', false);
        $this->assertArrayNotHasKey('actions', $turn->json('resultado') ?? []);
    }
}
