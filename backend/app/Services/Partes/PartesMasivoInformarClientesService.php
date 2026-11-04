<?php

namespace App\Services\Partes;

use App\Models\User;
use App\Notifications\PartesMasivoInformarClientesNotification;
use App\Repositories\Sp\Partes\PartesTareaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

final class PartesMasivoInformarClientesService
{
    public function __construct(
        private readonly PartesTareaRepository $tareaRepository,
        private readonly PartesMasivoAccessUrlBuilder $accessUrlBuilder,
    ) {
    }

    /**
     * @param  array<string, mixed>  $filterParams
     * @return array{destinatarios: int, enviados: int, errores: int}
     */
    public function informar(string $tenantCode, array $filterParams): array
    {
        $destinatarios = $this->tareaRepository->listMasivoDestinatariosNovedad($filterParams);
        $count = count($destinatarios);
        if ($count === 0) {
            throw new PartesTareaException('partes.masivo.sinDestinatarios', 422);
        }

        $max = PartesTareaOperations::resolveMasivoMaxIdsEfectivo();
        if ($count > $max) {
            $negocio = PartesTareaOperations::resolveMasivoMaxIdsNegocio();
            throw new PartesTareaException(
                $negocio > 0 ? 'partes.masivo.topeExcedido' : 'partes.masivo.loteDemasiadoGrande',
                422
            );
        }

        $fechaDesde = trim((string) ($filterParams['p_fecha_desde'] ?? ''));
        $fechaHasta = trim((string) ($filterParams['p_fecha_hasta'] ?? ''));
        $empresaNombre = $this->resolveEmpresaNombre();
        $portalUrl = $this->accessUrlBuilder->buildPortalUrl($tenantCode);

        $enviados = 0;
        $errores = 0;
        foreach ($destinatarios as $row) {
            $userId = (int) ($row['userId'] ?? $row['user_id'] ?? 0);
            $user = User::query()->find($userId);
            if ($user === null || ! $user->isLoginAllowed()) {
                $errores++;
                continue;
            }

            $locale = PartesMasivoLocale::normalize($row['locale'] ?? $user->locale);
            $codigo = (string) ($row['codigo'] ?? $user->usuario ?? '');
            $forgotUrl = $this->accessUrlBuilder->buildForgotPasswordUrl($tenantCode, $locale);

            try {
                Notification::send($user, new PartesMasivoInformarClientesNotification(
                    $locale,
                    $empresaNombre,
                    $fechaDesde,
                    $fechaHasta,
                    $portalUrl,
                    $forgotUrl,
                    $codigo,
                ));
                $enviados++;
            } catch (\Throwable) {
                $errores++;
            }
        }

        if ($enviados === 0) {
            throw new PartesTareaException('partes.masivo.sinDestinatarios', 422);
        }

        return [
            'destinatarios' => $count,
            'enviados' => $enviados,
            'errores' => $errores,
        ];
    }

    /**
     * @param  array<string, mixed>  $filterParams
     */
    public function countDestinatarios(array $filterParams): int
    {
        return count($this->tareaRepository->listMasivoDestinatariosNovedad($filterParams));
    }

    private function resolveEmpresaNombre(): string
    {
        $row = DB::table('pq_empresa')->orderBy('id')->first();
        if ($row === null) {
            return 'PaqSuite';
        }

        $nombre = trim((string) ($row->nombre ?? ''));

        return $nombre !== '' ? $nombre : 'PaqSuite';
    }
}
