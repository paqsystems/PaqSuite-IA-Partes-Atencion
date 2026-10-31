<?php

namespace Tests\Unit;

use App\Tasks\Backup\RdsSqlServerBackupGateway;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupS3Arn;
use PHPUnit\Framework\TestCase;

final class RdsSqlServerBackupGatewayTest extends TestCase
{
    public function testComposeExportDestinationCombinesPrefixAndFileName(): void
    {
        $arn = RdsBackupS3Arn::composeExportDestination(
            'arn:aws:s3:::sql-amazon-basico/backups',
            'paqsystems_partesatencion_dev',
        );

        $this->assertSame(
            'arn:aws:s3:::sql-amazon-basico/backups/paqsystems_partesatencion_dev.bak',
            $arn,
        );
    }

    public function testComposeExportDestinationAcceptsFullS3KeyFromSdkWithoutDuplicatingArn(): void
    {
        $fullKey = 'arn:aws:s3:::sql-amazon-basico/backups/paqsystems_partesatencion_dev.bak';

        $arn = RdsBackupS3Arn::composeExportDestination(
            'arn:aws:s3:::sql-amazon-basico/backups/',
            $fullKey,
        );

        $this->assertSame($fullKey, $arn);
    }
}
