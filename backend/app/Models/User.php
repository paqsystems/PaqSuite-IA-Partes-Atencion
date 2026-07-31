<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * SQL Server: Y-m-d es ambiguo con DATEFORMAT dmy; Ymd H:i:s es inequívoco.
     */
    protected $dateFormat = 'Ymd H:i:s';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'usuario',
        'email',
        'password',
        'first_login',
        'supervisor',
        'activo',
        'inhabilitado',
        'locale',
        'open_in_new_tab',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'first_login' => 'boolean',
        'supervisor' => 'boolean',
        'activo' => 'boolean',
        'inhabilitado' => 'boolean',
        'open_in_new_tab' => 'boolean',
    ];

    public function isLoginAllowed(): bool
    {
        return $this->activo && ! $this->inhabilitado;
    }

    public static function findByUsuarioOrEmail(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $lower = strtolower($identifier);

        return static::query()
            ->where(function ($query) use ($identifier, $lower): void {
                $query->where('usuario', $identifier)
                    ->orWhereRaw('LOWER(email) = ?', [$lower]);
            })
            ->first();
    }
}
