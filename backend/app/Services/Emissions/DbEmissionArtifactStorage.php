<?php

namespace App\Services\Emissions;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionArtifactStorage;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;

final class DbEmissionArtifactStorage implements EmissionArtifactStorage
{
    public function put(
        string $jobId,
        int|string|null $companyId,
        EmissionArtifact $artifact,
    ): string {
        DB::table('pq_emission_artifacts')->where('job_id', $jobId)->delete();
        DB::table('pq_emission_artifacts')->insert([
            'job_id' => $jobId,
            'file_name' => $artifact->fileName,
            'mime_type' => $artifact->mimeType,
            'size_bytes' => $artifact->sizeBytes(),
            'content_base64' => base64_encode($artifact->content),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $jobId;
    }

    public function get(string $path): ?EmissionArtifact
    {
        $row = DB::table('pq_emission_artifacts')->where('job_id', $path)->first();
        if ($row === null || $row->content_base64 === null) {
            return null;
        }

        $content = base64_decode((string) $row->content_base64, true);
        if ($content === false) {
            return null;
        }

        return new EmissionArtifact($content, (string) $row->file_name, (string) $row->mime_type);
    }

    public function delete(string $path): void
    {
        DB::table('pq_emission_artifacts')->where('job_id', $path)->delete();
    }
}
