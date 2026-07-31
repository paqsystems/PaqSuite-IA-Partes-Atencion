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
            'activeLlmCredentialId' => null,
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

        return $this->getForUser($userId);
    }
}
