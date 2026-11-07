<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Notifications\NotifyUsuarioAccessNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PaqSuite\LaravelCore\Auth\PasswordPolicy;
use Tests\TestCase;

/**
 * CC #16 — alta sin password explícita; clave generada conforme política (simple/segura + longitud mínima).
 */
final class ApiV1AdminUsuariosAutoPasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO', 'X-Company-Id' => '1'];
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

    public function test_store_sin_password_y_notify_entrega_clave_conforme_politica(): void
    {
        Notification::fake();
        $token = $this->loginAdmin();
        $headers = $this->authHeaders($token);

        $policyResponse = $this->getJson('/api/v1/auth/password-policy', $headers);
        $policyResponse->assertStatus(200);
        $minLen = (int) $policyResponse->json('resultado.passwordLongitudMin');
        $mode = (string) $policyResponse->json('resultado.passwordComplejidad');

        $usuario = 'autopol'.substr(uniqid('', true), -6);
        $create = $this->postJson('/api/v1/admin/usuarios', [
            'usuario' => $usuario,
            'name' => 'Auto Password Policy',
            'email' => $usuario.'@partes.local',
            'activo' => true,
        ], $headers);
        $create->assertStatus(201);
        $id = (int) $create->json('resultado.item.id');

        $userBeforeNotify = User::query()->findOrFail($id);
        $this->assertNotSame('', (string) $userBeforeNotify->password);

        $notify = $this->postJson(
            '/api/v1/admin/usuarios/'.$id.'/notify-access',
            [],
            $headers
        );
        $notify->assertStatus(200);

        $plain = null;
        $user = User::query()->findOrFail($id);
        Notification::assertSentTo(
            $user,
            NotifyUsuarioAccessNotification::class,
            function (NotifyUsuarioAccessNotification $notification) use (&$plain, $usuario): bool {
                $plain = $notification->plainPassword;
                $this->assertSame($usuario, $notification->usuario);

                return $plain !== null && $plain !== '';
            }
        );

        $this->assertNotNull($plain);
        $this->assertGreaterThanOrEqual($minLen, strlen((string) $plain));

        /** @var PasswordPolicy $passwordPolicy */
        $passwordPolicy = $this->app->make(PasswordPolicy::class);
        $this->assertTrue($passwordPolicy->isOk((string) $plain));

        if ($mode === 'segura') {
            $this->assertMatchesRegularExpression('/[A-Z]/', (string) $plain);
            $this->assertMatchesRegularExpression('/[a-z]/', (string) $plain);
            $this->assertMatchesRegularExpression('/\d/', (string) $plain);
            $this->assertMatchesRegularExpression('/[!@#$%&*_\-+=.?]/', (string) $plain);
        }

        $this->assertTrue(Hash::check((string) $plain, (string) $user->fresh()->password));
    }
}
