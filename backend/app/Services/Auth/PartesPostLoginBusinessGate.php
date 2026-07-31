<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Sp\Partes\PartesIdentidadRepository;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PartesPostLoginBusinessGate implements PostLoginBusinessGate
{
    public function __construct(private readonly PartesIdentidadRepository $partesIdentidadRepository)
    {
    }

    /**
     * @return array{partes: array<string, mixed>}
     */
    public function assertAllowed(User $user): array
    {
        $resolved = $this->partesIdentidadRepository->resolveByUserId((int) $user->id);
        $codigo = $resolved['codigoResultado'];

        if ($codigo === 2) {
            throw new PostLoginBusinessGateException(
                PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
                'partes.auth.inconsistentProfile',
                403,
                'Partes functional profile inconsistent'
            );
        }

        if ($codigo !== 0 || $resolved['tipoFuncional'] === null) {
            throw new PostLoginBusinessGateException(
                PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
                'partes.auth.noFunctionalProfile',
                403,
                'Partes functional profile missing'
            );
        }

        return [
            'partes' => $this->partesIdentidadRepository->toPartesPayload($resolved),
        ];
    }
}
