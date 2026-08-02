<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Llm\Contracts\ActiveLlmCredentialPreferenceRepository;
use PaqSuite\LaravelCore\Llm\Contracts\LlmCredentialRepository;
use PaqSuite\LaravelCore\Llm\LlmCredential;
use PaqSuite\LaravelCore\Llm\LlmCredentialData;

/**
 * Persistencia GEN-16 vía SP (TR-008). No Eloquent sobre tablas de negocio.
 */
final class SpLlmCredentialRepository implements LlmCredentialRepository, ActiveLlmCredentialPreferenceRepository
{
    public function __construct(private readonly SpCaller $spCaller)
    {
    }

    public function listForUser(int $userId): array
    {
        $rows = $this->spCaller->call('pq_sp_llm_credentials_list', [
            'user_id' => $userId,
        ]);

        return array_map(fn (object $row): LlmCredential => $this->toEntity($row), $rows);
    }

    public function findForUser(int $credentialId, int $userId): ?LlmCredential
    {
        $row = $this->spCaller->callFirst('pq_sp_llm_credentials_get', [
            'credential_id' => $credentialId,
            'user_id' => $userId,
        ]);

        return $row === null ? null : $this->toEntity($row);
    }

    public function insert(int $userId, LlmCredentialData $data, string $encryptedSecret): LlmCredential
    {
        $row = $this->spCaller->callFirst('pq_sp_llm_credentials_insert', [
            'user_id' => $userId,
            'nombre' => $data->nombre,
            'proveedor' => $data->proveedor,
            'modelo' => $data->modelo,
            'secreto_cifrado' => $encryptedSecret,
            'base_url' => $data->baseUrl,
            'supports_vision' => $data->supportsVision ? 1 : 0,
            'enabled' => $data->enabled ? 1 : 0,
        ]);

        if ($row === null) {
            throw new \RuntimeException('pq_sp_llm_credentials_insert returned no row.');
        }

        return $this->toEntity($row);
    }

    public function update(LlmCredential $current, LlmCredentialData $data, string $encryptedSecret): LlmCredential
    {
        $row = $this->spCaller->callFirst('pq_sp_llm_credentials_update', [
            'credential_id' => $current->id,
            'user_id' => $current->userId,
            'nombre' => $data->nombre,
            'proveedor' => $data->proveedor,
            'modelo' => $data->modelo,
            'secreto_cifrado' => $encryptedSecret,
            'base_url' => $data->baseUrl,
            'supports_vision' => $data->supportsVision ? 1 : 0,
            'enabled' => $data->enabled ? 1 : 0,
        ]);

        if ($row === null) {
            return $current;
        }

        return $this->toEntity($row);
    }

    public function deleteForUser(int $credentialId, int $userId): bool
    {
        $row = $this->spCaller->callFirst('pq_sp_llm_credentials_delete', [
            'credential_id' => $credentialId,
            'user_id' => $userId,
        ]);

        $deleted = (int) ($row->deleted_count ?? $row->deletedCount ?? 0);

        return $deleted > 0;
    }

    public function getActiveCredentialId(int $userId): ?int
    {
        $row = $this->spCaller->callFirst('pq_sp_llm_active_preference_get', [
            'user_id' => $userId,
        ]);

        if ($row === null) {
            return null;
        }

        $value = $row->active_llm_credential_id ?? $row->activeLlmCredentialId ?? null;

        return $value === null || $value === '' ? null : (int) $value;
    }

    public function setActiveCredentialId(int $userId, ?int $credentialId): void
    {
        $this->spCaller->call('pq_sp_llm_active_preference_set', [
            'user_id' => $userId,
            'credential_id' => $credentialId,
        ]);
    }

    private function toEntity(object $row): LlmCredential
    {
        return new LlmCredential(
            (int) $row->id,
            (int) ($row->user_id ?? $row->userId),
            (string) $row->nombre,
            (string) $row->proveedor,
            (string) $row->modelo,
            (string) ($row->secreto_cifrado ?? $row->secretoCifrado ?? ''),
            isset($row->base_url) ? ($row->base_url !== null ? (string) $row->base_url : null) : (isset($row->baseUrl) ? ($row->baseUrl !== null ? (string) $row->baseUrl : null) : null),
            (bool) ($row->supports_vision ?? $row->supportsVision ?? false),
            (bool) ($row->enabled ?? true),
        );
    }
}
