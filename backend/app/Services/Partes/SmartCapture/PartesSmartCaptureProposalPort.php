<?php

namespace App\Services\Partes\SmartCapture;

/**
 * Propuesta cruda del modelo (antes de resolver catálogos).
 *
 * @phpstan-type Proposal array{
 *   replyText: string,
 *   save: bool,
 *   fields: array<string, mixed>
 * }
 */
interface PartesSmartCaptureProposalPort
{
    /**
     * @param  array<string, mixed>  $draftContext
     * @param  array<string, mixed>|null  $pendingChoice
     * @param  list<array<string, mixed>>  $images
     * @return array{replyText: string, save: bool, fields: array<string, mixed>}
     */
    public function propose(
        string $message,
        array $draftContext,
        ?array $pendingChoice,
        array $images,
        object $credentialContext,
    ): array;
}
