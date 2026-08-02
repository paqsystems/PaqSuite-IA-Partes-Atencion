<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\UserPreferencesRepository;

/**
 * Host adapter — SP pq_sp_user_preferences_get / pq_sp_user_preferences_set.
 */
final class SpUserPreferencesRepository implements UserPreferencesRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function getForUser(int $userId): array
    {
        $row = $this->spCaller->callFirst('pq_sp_user_preferences_get', [
            'user_id' => $userId,
        ]);

        if ($row === null) {
            return [
                'locale' => null,
                'openInNewTab' => false,
                'activeLlmCredentialId' => null,
            ];
        }

        return [
            'locale' => isset($row->locale) ? (string) $row->locale : null,
            'openInNewTab' => (bool) ($row->open_in_new_tab ?? $row->openInNewTab ?? false),
            'activeLlmCredentialId' => $this->resolveActiveLlmCredentialId($userId),
        ];
    }

    public function patchForUser(int $userId, array $partial): array
    {
        $this->spCaller->execute('pq_sp_user_preferences_set', [
            'user_id' => $userId,
            'locale' => array_key_exists('locale', $partial) ? $partial['locale'] : null,
            'open_in_new_tab' => array_key_exists('openInNewTab', $partial)
                ? (int) (bool) $partial['openInNewTab']
                : null,
        ]);

        if (array_key_exists('activeLlmCredentialId', $partial)) {
            $this->spCaller->call('pq_sp_llm_active_preference_set', [
                'user_id' => $userId,
                'credential_id' => $partial['activeLlmCredentialId'],
            ]);
        }

        return $this->getForUser($userId);
    }

    private function resolveActiveLlmCredentialId(int $userId): ?int
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
}
