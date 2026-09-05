<?php

namespace App\Services\Emissions;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionPreviewSessionStore;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;
use PaqSuite\LaravelCore\Emissions\Dto\PreviewSession;

final class DbEmissionPreviewSessionStore implements EmissionPreviewSessionStore
{
    public function put(PreviewSession $session): void
    {
        DB::table('pq_emission_preview_sessions')->updateOrInsert(
            ['id' => $session->previewSessionId],
            [
                'process_code' => $session->processCode,
                'report_id' => $session->reportId === null ? null : (int) $session->reportId,
                'mode' => $session->mode,
                'channel' => $session->channel,
                'group_id' => $session->groupId === null ? null : (string) $session->groupId,
                'user_id' => (int) $session->userId,
                'company_id' => $session->companyId === null ? null : (int) $session->companyId,
                'expires_at' => $session->expiresAt->format('Y-m-d H:i:s'),
                'artifact_content_base64' => base64_encode($session->artifact->content),
                'artifact_file_name' => $session->artifact->fileName,
                'artifact_mime' => $session->artifact->mimeType,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function get(string $previewSessionId): ?PreviewSession
    {
        $row = DB::table('pq_emission_preview_sessions')->where('id', $previewSessionId)->first();
        if ($row === null) {
            return null;
        }

        $expiresAt = new DateTimeImmutable((string) $row->expires_at);
        if ($expiresAt <= new DateTimeImmutable()) {
            DB::table('pq_emission_preview_sessions')->where('id', $previewSessionId)->delete();

            return null;
        }

        $content = base64_decode((string) $row->artifact_content_base64, true);
        if ($content === false) {
            return null;
        }

        return new PreviewSession(
            (string) $row->id,
            (string) $row->process_code,
            $row->report_id,
            (string) $row->mode,
            $row->group_id,
            (string) $row->channel,
            (int) $row->user_id,
            $row->company_id === null ? null : (int) $row->company_id,
            $expiresAt,
            new EmissionArtifact(
                $content,
                (string) $row->artifact_file_name,
                (string) $row->artifact_mime,
            ),
        );
    }
}
