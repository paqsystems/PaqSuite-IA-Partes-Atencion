<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Emite token Sanctum sin timestamps Eloquent.
 * En SQL Server, createToken() cuelga al grabar created_at/updated_at (DATEFORMAT dmy).
 */
final class SanctumAuthTokenIssuer
{
    public function issue(User $user, string $name = 'auth'): string
    {
        $plainTextToken = $user->generateTokenString();
        $tokenHash = hash('sha256', $plainTextToken);
        $abilities = json_encode(['*']);
        $tokenableType = $user::class;
        $tokenableId = $user->getKey();

        if (DB::connection()->getDriverName() === 'sqlsrv') {
            $rows = DB::select(
                'INSERT INTO dbo.personal_access_tokens
                    (tokenable_type, tokenable_id, name, token, abilities, created_at, updated_at)
                 OUTPUT INSERTED.id
                 VALUES (?, ?, ?, ?, ?, SYSUTCDATETIME(), SYSUTCDATETIME())',
                [$tokenableType, $tokenableId, $name, $tokenHash, $abilities]
            );
            $tokenId = (int) ($rows[0]->id ?? 0);
        } else {
            $tokenId = (int) DB::table('personal_access_tokens')->insertGetId([
                'tokenable_type' => $tokenableType,
                'tokenable_id' => $tokenableId,
                'name' => $name,
                'token' => $tokenHash,
                'abilities' => $abilities,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $tokenId.'|'.$plainTextToken;
    }
}
