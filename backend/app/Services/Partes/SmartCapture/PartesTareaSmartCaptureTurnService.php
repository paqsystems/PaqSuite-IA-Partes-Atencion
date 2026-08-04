<?php

namespace App\Services\Partes\SmartCapture;

use Carbon\Carbon;
use PaqSuite\LaravelCore\Llm\LlmCredentialResolver;
use PaqSuite\LaravelCore\Llm\LlmDomainException;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureGuardOptions;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureTurnGuard;

/**
 * Orquestación del turno Smart Capture Partes (GEN-03).
 * Emite actions; no persiste tareas.
 */
final class PartesTareaSmartCaptureTurnService
{
    public function __construct(
        private readonly SmartCaptureTurnGuard $guard,
        private readonly LlmCredentialResolver $credentialResolver,
        private readonly PartesSmartCaptureProposalPort $proposalPort,
        private readonly PartesTareaSmartCaptureCatalogResolver $catalogResolver,
    ) {
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{replyText: string, actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>, pendingChoice: array<string, mixed>|null, configurationRequired: bool}
     */
    public function handle(int $userId, array $body): array
    {
        if (isset($body['credentialId']) && is_numeric($body['credentialId'])) {
            $body['credentialId'] = (int) $body['credentialId'];
        }

        $credential = null;
        $credentialId = $body['credentialId'] ?? null;
        if (is_int($credentialId) && $credentialId > 0) {
            try {
                $credential = $this->credentialResolver->resolveSecret($credentialId, $userId);
            } catch (LlmDomainException) {
                $credential = null;
            }
        }

        $options = new SmartCaptureGuardOptions(
            supportsVision: $credential?->supportsVision ?? false,
            hasValidLlmCredential: $credential !== null,
            companyId: null,
            assertDraftBelongsToCompany: null,
            companyRequired: false,
            authenticated: true,
        );

        $this->guard->validateGen($body, $options);

        $draftContext = is_array($body['draftContext'] ?? null) ? $body['draftContext'] : [];
        $pendingChoice = is_array($body['pendingChoice'] ?? null) ? $body['pendingChoice'] : null;
        $message = trim((string) ($body['message'] ?? ''));
        $images = is_array($body['images'] ?? null) ? $body['images'] : [];

        $pendingResolution = $this->tryResolvePendingChoice($message, $pendingChoice, $draftContext);
        if ($pendingResolution !== null) {
            return $pendingResolution;
        }

        $proposal = $this->proposalPort->propose(
            $message,
            $draftContext,
            $pendingChoice,
            $images,
            $credential,
        );

        return $this->buildResultFromProposal($proposal, $draftContext);
    }

    /**
     * @param  array<string, mixed>|null  $pendingChoice
     * @param  array<string, mixed>  $draftContext
     * @return array{replyText: string, actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>, pendingChoice: array<string, mixed>|null, configurationRequired: bool}|null
     */
    private function tryResolvePendingChoice(string $message, ?array $pendingChoice, array $draftContext): ?array
    {
        if ($pendingChoice === null) {
            return null;
        }

        $kind = (string) ($pendingChoice['kind'] ?? '');
        $options = is_array($pendingChoice['options'] ?? null) ? $pendingChoice['options'] : [];
        $deferred = is_array($pendingChoice['deferred'] ?? null) ? $pendingChoice['deferred'] : [];

        if ($kind === 'confirmFutureDate') {
            if (! $this->isAffirmative($message)) {
                return [
                    'replyText' => 'partes.smartCapture.fechaFuturaPendiente',
                    'actions' => [],
                    'pendingChoice' => $pendingChoice,
                    'configurationRequired' => false,
                ];
            }
            $fecha = null;
            foreach ($deferred as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $payload = is_array($item['payload'] ?? null) ? $item['payload'] : [];
                if (isset($payload['fecha']) && is_string($payload['fecha'])) {
                    $fecha = $payload['fecha'];
                    break;
                }
            }
            if ($fecha === null || ! $this->isValidIsoDate($fecha)) {
                return $this->noopResult('partes.smartCapture.fechaInvalida');
            }

            return [
                'replyText' => 'partes.smartCapture.fechaConfirmada',
                'actions' => [$this->action('setField', ['field' => 'fecha', 'value' => $fecha])],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        $index = $this->parseChoiceIndex($message);
        if ($index === null) {
            return null;
        }
        if ($index < 1 || $index > count($options)) {
            return [
                'replyText' => 'partes.smartCapture.opcionInvalida',
                'actions' => [],
                'pendingChoice' => $pendingChoice,
                'configurationRequired' => false,
            ];
        }

        $chosen = $options[$index - 1];
        $chosenId = is_array($chosen) ? ($chosen['id'] ?? null) : null;
        if (! is_numeric($chosenId)) {
            return $this->noopResult('partes.smartCapture.opcionInvalida');
        }

        $field = match ($kind) {
            'chooseCliente' => 'clienteId',
            'chooseTipoTarea' => 'tipoTareaId',
            'chooseAsistente' => 'asistenteId',
            default => null,
        };
        if ($field === null) {
            return null;
        }

        if ($field === 'asistenteId' && ! $this->isSupervisor($draftContext)) {
            return $this->noopResult('partes.smartCapture.asistenteSoloSupervisor');
        }

        return [
            'replyText' => 'partes.smartCapture.opcionAplicada',
            'actions' => [$this->action('setField', ['field' => $field, 'value' => (int) $chosenId])],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @param  array{replyText: string, save: bool, fields: array<string, mixed>}  $proposal
     * @param  array<string, mixed>  $draftContext
     * @return array{replyText: string, actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>, pendingChoice: array<string, mixed>|null, configurationRequired: bool}
     */
    private function buildResultFromProposal(array $proposal, array $draftContext): array
    {
        $actions = [];
        $pendingChoice = null;
        $replyParts = [trim((string) $proposal['replyText'])];
        $fields = $proposal['fields'];
        $esSupervisor = $this->isSupervisor($draftContext);
        $clienteIdDraft = $this->nullableInt($draftContext['clienteId'] ?? null);

        if (array_key_exists('cliente', $fields) && $fields['cliente'] !== null && $fields['cliente'] !== '') {
            $resolved = $this->catalogResolver->resolveCliente((string) $fields['cliente']);
            $branch = $this->branchCatalog(
                $resolved,
                'clienteId',
                'chooseCliente',
                'partes.smartCapture.clienteNoEncontrado',
                'partes.smartCapture.clienteAmbiguo',
            );
            $actions = array_merge($actions, $branch['actions']);
            if ($branch['pendingChoice'] !== null) {
                $pendingChoice = $branch['pendingChoice'];
            }
            if ($branch['replyHint'] !== null) {
                $replyParts[] = $branch['replyHint'];
            }
            if (($branch['resolvedId'] ?? null) !== null) {
                $clienteIdDraft = $branch['resolvedId'];
            }
        }

        if (array_key_exists('asistente', $fields) && $fields['asistente'] !== null && $fields['asistente'] !== '') {
            if (! $esSupervisor) {
                $replyParts[] = 'partes.smartCapture.asistenteSoloSupervisor';
            } else {
                $resolved = $this->catalogResolver->resolveAsistente((string) $fields['asistente']);
                $branch = $this->branchCatalog(
                    $resolved,
                    'asistenteId',
                    'chooseAsistente',
                    'partes.smartCapture.asistenteNoEncontrado',
                    'partes.smartCapture.asistenteAmbiguo',
                );
                $actions = array_merge($actions, $branch['actions']);
                if ($branch['pendingChoice'] !== null && $pendingChoice === null) {
                    $pendingChoice = $branch['pendingChoice'];
                }
                if ($branch['replyHint'] !== null) {
                    $replyParts[] = $branch['replyHint'];
                }
            }
        }

        if (array_key_exists('tipoTarea', $fields) && $fields['tipoTarea'] !== null && $fields['tipoTarea'] !== '') {
            $resolved = $this->catalogResolver->resolveTipoTarea((string) $fields['tipoTarea'], $clienteIdDraft);
            $branch = $this->branchCatalog(
                $resolved,
                'tipoTareaId',
                'chooseTipoTarea',
                'partes.smartCapture.tipoNoEncontrado',
                'partes.smartCapture.tipoAmbiguo',
            );
            $actions = array_merge($actions, $branch['actions']);
            if ($branch['pendingChoice'] !== null && $pendingChoice === null) {
                $pendingChoice = $branch['pendingChoice'];
            }
            if ($branch['replyHint'] !== null) {
                $replyParts[] = $branch['replyHint'];
            }
        }

        if (array_key_exists('fecha', $fields) && $fields['fecha'] !== null && $fields['fecha'] !== '') {
            $fecha = $this->normalizeFecha((string) $fields['fecha']);
            if ($fecha === null) {
                $actions[] = $this->action('needsRefine', ['field' => 'fecha']);
                $replyParts[] = 'partes.smartCapture.fechaInvalida';
            } elseif ($this->isFutureDate($fecha)) {
                if ($pendingChoice === null) {
                    $pendingChoice = [
                        'kind' => 'confirmFutureDate',
                        'options' => [
                            ['id' => 1, 'label' => 'Sí, confirmar fecha futura'],
                            ['id' => 2, 'label' => 'No'],
                        ],
                        'deferred' => [
                            [
                                'cause' => 'confirmationRequired',
                                'payload' => ['fecha' => $fecha],
                            ],
                        ],
                    ];
                    $replyParts[] = 'partes.smartCapture.fechaFuturaConfirmar';
                }
            } else {
                $actions[] = $this->action('setField', ['field' => 'fecha', 'value' => $fecha]);
            }
        }

        foreach (['duracionMinutos', 'observacion', 'sinCargo', 'presencial'] as $scalarField) {
            if (! array_key_exists($scalarField, $fields) || $fields[$scalarField] === null) {
                continue;
            }
            $value = $fields[$scalarField];
            if ($scalarField === 'duracionMinutos') {
                if (! is_numeric($value) || (int) $value <= 0) {
                    $actions[] = $this->action('needsRefine', ['field' => 'duracionMinutos']);
                    $replyParts[] = 'partes.smartCapture.duracionInvalida';
                    continue;
                }
                $value = (int) $value;
            }
            if ($scalarField === 'sinCargo' || $scalarField === 'presencial') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($value === null) {
                    continue;
                }
            }
            if ($scalarField === 'observacion') {
                $value = (string) $value;
            }
            $actions[] = $this->action('setField', ['field' => $scalarField, 'value' => $value]);
        }

        if (($proposal['save'] ?? false) === true && $pendingChoice === null && ! $this->hasBlockingCatalogAction($actions)) {
            $actions[] = $this->action('save', []);
        }

        $replyText = $this->composeReplyText($replyParts, $pendingChoice);
        if ($pendingChoice === null && $replyText === 'partes.smartCapture.noop' && $actions !== []) {
            $replyText = 'partes.smartCapture.ok';
        }

        return [
            'replyText' => $replyText,
            'actions' => $actions,
            'pendingChoice' => $pendingChoice,
            'configurationRequired' => false,
        ];
    }

    /**
     * Preferir claves i18n del host sobre prosa LLM engañosa (p. ej. «actualicé el cliente»
     * cuando el catálogo devolvió none/many).
     *
     * @param  list<string>  $replyParts
     * @param  array<string, mixed>|null  $pendingChoice
     */
    private function composeReplyText(array $replyParts, ?array $pendingChoice): string
    {
        $keys = [];
        foreach ($replyParts as $part) {
            $part = trim((string) $part);
            if ($part !== '' && str_starts_with($part, 'partes.smartCapture.')) {
                $keys[] = $part;
            }
        }

        if ($pendingChoice === null) {
            if ($keys !== []) {
                return implode("\n", array_values(array_unique($keys)));
            }

            $replyText = trim(implode(' ', array_filter(
                array_map(static fn ($p) => trim((string) $p), $replyParts),
                static fn ($p) => $p !== ''
            )));

            return $replyText !== '' ? $replyText : 'partes.smartCapture.noop';
        }

        if ($keys === []) {
            $keys[] = match ((string) ($pendingChoice['kind'] ?? '')) {
                'confirmFutureDate' => 'partes.smartCapture.fechaFuturaConfirmar',
                'chooseTipoTarea' => 'partes.smartCapture.tipoAmbiguo',
                'chooseAsistente' => 'partes.smartCapture.asistenteAmbiguo',
                default => 'partes.smartCapture.clienteAmbiguo',
            };
        }

        $lines = array_values(array_unique($keys));
        $options = is_array($pendingChoice['options'] ?? null) ? $pendingChoice['options'] : [];
        $n = 1;
        foreach ($options as $opt) {
            if (! is_array($opt)) {
                continue;
            }
            $label = trim((string) ($opt['label'] ?? $opt['id'] ?? ''));
            if ($label === '') {
                continue;
            }
            $lines[] = $n.' — '.$label;
            $n++;
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<array{action: string, payload: array<string, mixed>, resultado: string}>  $actions
     */
    private function hasBlockingCatalogAction(array $actions): bool
    {
        foreach ($actions as $action) {
            $name = (string) ($action['action'] ?? '');
            if ($name === 'needsRefine' || $name === 'needsChoice') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{status: string, item?: array{id: int, code: string, label: string}, options?: list<array{id: int, label: string}>}  $resolved
     * @return array{actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>, pendingChoice: array<string, mixed>|null, replyHint: string|null, resolvedId: int|null}
     */
    private function branchCatalog(
        array $resolved,
        string $field,
        string $choiceKind,
        string $noneKey,
        string $manyKey,
    ): array {
        return match ($resolved['status']) {
            'one' => [
                'actions' => [
                    $this->action('setField', [
                        'field' => $field,
                        'value' => $resolved['item']['id'],
                    ]),
                ],
                'pendingChoice' => null,
                'replyHint' => null,
                'resolvedId' => $resolved['item']['id'],
            ],
            'many' => [
                'actions' => [$this->action('needsChoice', ['field' => $field, 'kind' => $choiceKind])],
                'pendingChoice' => [
                    'kind' => $choiceKind,
                    'options' => array_values($resolved['options'] ?? []),
                    'deferred' => [
                        [
                            'cause' => 'ambiguity',
                            'payload' => ['field' => $field],
                        ],
                    ],
                ],
                'replyHint' => $manyKey,
                'resolvedId' => null,
            ],
            default => [
                'actions' => [$this->action('needsRefine', ['field' => $field])],
                'pendingChoice' => null,
                'replyHint' => $noneKey,
                'resolvedId' => null,
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{action: string, payload: array<string, mixed>, resultado: string}
     */
    private function action(string $name, array $payload): array
    {
        return [
            'action' => $name,
            'payload' => $payload,
            'resultado' => 'ok',
        ];
    }

    /**
     * @return array{replyText: string, actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>, pendingChoice: null, configurationRequired: bool}
     */
    private function noopResult(string $replyText): array
    {
        return [
            'replyText' => $replyText,
            'actions' => [$this->action('noop', [])],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /** @param  array<string, mixed>  $draftContext */
    private function isSupervisor(array $draftContext): bool
    {
        return filter_var($draftContext['esSupervisor'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function isAffirmative(string $message): bool
    {
        $normalized = mb_strtolower(trim($message));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $normalized) ?? $normalized;

        return in_array($normalized, ['si', 'sí', 'yes', 'ok', 'dale', 'confirmo', '1'], true);
    }

    private function parseChoiceIndex(string $message): ?int
    {
        $trimmed = trim($message);
        if (preg_match('/^\d{1,2}$/', $trimmed) === 1) {
            return (int) $trimmed;
        }

        return null;
    }

    private function normalizeFecha(string $raw): ?string
    {
        $value = trim($raw);
        if ($this->isValidIsoDate($value)) {
            return $value;
        }
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function isValidIsoDate(string $value): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return false;
        }
        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $value);

            return $parsed !== false && $parsed->format('Y-m-d') === $value;
        } catch (\Throwable) {
            return false;
        }
    }

    private function isFutureDate(string $isoDate): bool
    {
        return Carbon::parse($isoDate)->startOfDay()->gt(Carbon::today());
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }
        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
