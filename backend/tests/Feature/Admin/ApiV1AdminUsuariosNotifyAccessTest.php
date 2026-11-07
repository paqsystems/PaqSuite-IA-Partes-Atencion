<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Notifications\NotifyUsuarioAccessNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class ApiV1AdminUsuariosNotifyAccessTest extends TestCase
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

    /**
     * @return array{id: int, usuario: string}
     */
    private function createTargetUser(string $token, string $usuario = 'notif1'): array
    {
        $create = $this->postJson('/api/v1/admin/usuarios', [
            'usuario' => $usuario,
            'nombre' => 'Usuario Notificar',
            'email' => $usuario.'@partes.local',
            'password' => 'Secret123!',
            'activo' => true,
        ], $this->authHeaders($token));
        $create->assertStatus(201);
        $id = (int) $create->json('resultado.item.id');

        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $id,
            'code' => strtoupper($usuario),
            'nombre' => 'Usuario Notificar',
            'email' => $usuario.'@partes.local',
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['id' => $id, 'usuario' => $usuario];
    }

    public function test_notify_access_sin_email_devuelve_422(): void
    {
        Notification::fake();
        $token = $this->loginAdmin();
        $target = $this->createTargetUser($token, 'sinmail1');
        DB::table('users')->where('id', $target['id'])->update(['email' => '']);

        $response = $this->postJson(
            '/api/v1/admin/usuarios/'.$target['id'].'/notify-access',
            [],
            $this->authHeaders($token)
        );

        $response->assertStatus(422)
            ->assertJsonPath('respuesta', 'admin.usuarios.notify.emailRequired');
        Notification::assertNothingSent();
    }

    public function test_notify_access_genera_clave_first_login_y_mail_sin_password_en_json(): void
    {
        Notification::fake();
        $token = $this->loginAdmin();
        $target = $this->createTargetUser($token);
        DB::table('users')->where('id', $target['id'])->update(['first_login' => false]);

        $response = $this->postJson(
            '/api/v1/admin/usuarios/'.$target['id'].'/notify-access',
            [],
            $this->authHeaders($token)
        );

        $response->assertStatus(200)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.item.id', $target['id'])
            ->assertJsonPath('resultado.item.notified', true);
        $this->assertStringNotContainsStringIgnoringCase(
            'password',
            (string) $response->getContent()
        );

        $this->assertTrue((bool) DB::table('users')->where('id', $target['id'])->value('first_login'));

        $user = User::query()->findOrFail($target['id']);
        $plain = null;
        Notification::assertSentTo(
            $user,
            NotifyUsuarioAccessNotification::class,
            function (NotifyUsuarioAccessNotification $notification) use (&$plain, $target): bool {
                $plain = $notification->plainPassword;
                $this->assertSame('DEMO', $notification->empresaCodigo);
                $this->assertSame($target['usuario'], $notification->usuario);
                $this->assertSame(
                    'https://demo.partesatencion.paqsystems.com',
                    $notification->portalUrl
                );

                return $plain !== null && $plain !== '';
            }
        );

        $this->assertNotNull($plain);
        $this->assertTrue(Hash::check((string) $plain, (string) $user->fresh()->password));

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => $target['usuario'],
            'password' => $plain,
        ], $this->tenantHeaders());
        $login->assertStatus(200)->assertJsonPath('resultado.firstLogin', true);
    }

    public function test_notify_access_exige_gate_admin(): void
    {
        $this->seed();
        $target = User::factory()->create([
            'usuario' => 'gateusr',
            'email' => 'gateusr@partes.local',
            'password' => Hash::make('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);

        $empresaId = (int) DB::table('pq_empresa')->value('id');
        $rolId = DB::table('pq_roles')->insertGetId([
            'codigo' => 'NOTIFYBAS',
            'nombre' => 'Básico notify',
            'acceso_total' => false,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $basico = User::factory()->create([
            'usuario' => 'basicoNotify',
            'email' => 'basico.notify@partes.local',
            'password' => Hash::make('Secret123!'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);
        DB::table('pq_permisos')->insert([
            'user_id' => $basico->id,
            'empresa_id' => $empresaId,
            'rol_id' => $rolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('PQ_PARTES_USUARIOS')->insert([
            'user_id' => $basico->id,
            'code' => 'BASNOTIFY',
            'nombre' => 'Básico Notify',
            'email' => $basico->email,
            'supervisor' => false,
            'activo' => true,
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'usuario' => 'basicoNotify',
            'password' => 'Secret123!',
        ], $this->tenantHeaders());
        $login->assertStatus(200);
        $basicoToken = (string) $login->json('resultado.token');

        $response = $this->postJson(
            '/api/v1/admin/usuarios/'.$target['id'].'/notify-access',
            [],
            $this->authHeaders($basicoToken)
        );
        $response->assertStatus(403);
    }
}
