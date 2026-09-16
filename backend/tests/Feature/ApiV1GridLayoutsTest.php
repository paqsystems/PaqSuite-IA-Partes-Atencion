<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApiV1GridLayoutsTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'DEMO'];
    }

    public function test_listado_incluye_layout_ajeno_y_put_delete_solo_owner(): void
    {
        $this->seed();
        $userA = User::query()->where('usuario', 'admin')->firstOrFail();
        $userB = User::query()->where('usuario', 'PQ')->firstOrFail();
        $this->assertNotSame((int) $userA->id, (int) $userB->id);

        $createB = $this->actingAs($userB, 'sanctum')->postJson('/api/v1/grid-layouts', [
            'proceso' => 'partes.carga.diaria',
            'gridId' => 'cargaDiaria',
            'layoutName' => 'Compacta B',
            'stateJson' => ['columns' => ['fecha']],
        ], $this->tenantHeaders());
        $createB->assertStatus(201);
        $layoutId = (int) $createB->json('resultado.item.id');
        $this->assertTrue((bool) $createB->json('resultado.item.isOwner'));

        $listA = $this->actingAs($userA, 'sanctum')->getJson(
            '/api/v1/grid-layouts?proceso=partes.carga.diaria&gridId=cargaDiaria',
            $this->tenantHeaders()
        );
        $listA->assertOk();
        $items = collect($listA->json('resultado.items') ?? []);
        $seen = $items->firstWhere('id', $layoutId);
        $this->assertNotNull($seen, json_encode($items));
        $this->assertFalse((bool) ($seen['isOwner'] ?? true), json_encode($seen));

        $this->actingAs($userA, 'sanctum')->putJson('/api/v1/grid-layouts/'.$layoutId, [
            'stateJson' => ['columns' => ['clienteNombre']],
        ], $this->tenantHeaders())
            ->assertStatus(403)
            ->assertJsonPath('error', 3003);

        $this->actingAs($userA, 'sanctum')->deleteJson('/api/v1/grid-layouts/'.$layoutId, [], $this->tenantHeaders())
            ->assertStatus(403)
            ->assertJsonPath('error', 3003);

        $apply = $this->actingAs($userA, 'sanctum')->putJson('/api/v1/grid-layouts/active', [
            'proceso' => 'partes.carga.diaria',
            'gridId' => 'cargaDiaria',
            'layoutId' => $layoutId,
        ], $this->tenantHeaders());
        $apply->assertOk()->assertJsonPath('resultado.layoutId', $layoutId);
    }
}
