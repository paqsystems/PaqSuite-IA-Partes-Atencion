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
        $rows = $this->normalizeRows($this->tareaRepository->listMasivoDestinatariosNovedad($filterParams));
        $unique = $this->uniqueByUserId($rows);
        $count = count($unique);
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
        foreach ($unique as $row) {
            $user = User::query()->find($row['userId']);
            // To = users.email vía Notifiable; no usar PQ_PARTES_CLIENTES.email.
            if ($user === null || ! $user->isLoginAllowed() || trim((string) $user->email) === '') {
                $errores++;
                continue;
            }

            $locale = PartesMasivoLocale::normalize($row['locale'] !== '' ? $row['locale'] : $user->locale);
            $codigo = $row['codigo'] !== '' ? $row['codigo'] : (string) ($user->usuario ?? '');
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
     * @return array{
     *     destinatarioCount: int,
     *     fechaDesde: string,
     *     fechaHasta: string,
     *     destinatarios: list<array{clienteId: int, clienteCode: string, clienteNombre: string, email: string, nombre: string, codigo: string}>
     * }
     */
    public function preview(array $filterParams): array
    {
        $rows = $this->normalizeRows($this->tareaRepository->listMasivoDestinatariosNovedad($filterParams));
        $unique = $this->uniqueByUserId($rows);

        $destinatarios = [];
        $seenCliente = [];
        foreach ($rows as $row) {
            $clienteId = $row['clienteId'];
            if ($clienteId > 0 && isset($seenCliente[$clienteId])) {
                continue;
            }
            $seenCliente[$clienteId] = true;
            $destinatarios[] = [
                'clienteId' => $clienteId,
                'clienteCode' => $row['clienteCode'],
                'clienteNombre' => $row['clienteNombre'] !== '' ? $row['clienteNombre'] : $row['name'],
                'email' => $row['email'],
                'nombre' => $row['name'],
                'codigo' => $row['codigo'],
            ];
        }

        return [
            'destinatarioCount' => count($unique),
            'fechaDesde' => trim((string) ($filterParams['p_fecha_desde'] ?? '')),
            'fechaHasta' => trim((string) ($filterParams['p_fecha_hasta'] ?? '')),
            'destinatarios' => $destinatarios,
        ];
    }

    /**
     * @param  array<string, mixed>  $filterParams
     */
    public function countDestinatarios(array $filterParams): int
    {
        return $this->preview($filterParams)['destinatarioCount'];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{userId: int, email: string, codigo: string, locale: string, name: string, clienteId: int, clienteCode: string, clienteNombre: string}>
     */
    private function normalizeRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $email = trim((string) ($row['email'] ?? ''));
            $userId = (int) ($row['userId'] ?? $row['user_id'] ?? 0);
            if ($userId <= 0 || $email === '') {
                continue;
            }
            $out[] = [
                'userId' => $userId,
                'email' => $email,
                'codigo' => (string) ($row['codigo'] ?? ''),
                'locale' => (string) ($row['locale'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'clienteId' => (int) ($row['clienteId'] ?? $row['cliente_id'] ?? 0),
                'clienteCode' => (string) ($row['clienteCode'] ?? $row['cliente_code'] ?? ''),
                'clienteNombre' => (string) ($row['clienteNombre'] ?? $row['cliente_nombre'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * @param  list<array{userId: int, email: string, codigo: string, locale: string, name: string, clienteId: int, clienteCode: string, clienteNombre: string}>  $rows
     * @return list<array{userId: int, email: string, codigo: string, locale: string, name: string, clienteId: int, clienteCode: string, clienteNombre: string}>
     */
    private function uniqueByUserId(array $rows): array
    {
        $seen = [];
        $out = [];
        foreach ($rows as $row) {
            if (isset($seen[$row['userId']])) {
                continue;
            }
            $seen[$row['userId']] = true;
            $out[] = $row;
        }

        return $out;
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
