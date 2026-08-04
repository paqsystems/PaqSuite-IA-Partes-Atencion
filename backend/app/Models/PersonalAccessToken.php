<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * SQL Server: timestamps inequívocos (DATEFORMAT dmy).
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $dateFormat = 'Ymd H:i:s';
}
