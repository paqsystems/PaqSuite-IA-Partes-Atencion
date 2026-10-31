<?php

namespace Tests\Unit;

use App\Tasks\Backup\RdsSqlServerBackupGateway;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class RdsSqlServerBackupGatewayTest extends TestCase
{
    public function testResolveDestinationArnCombinesPrefixAndFileName(): void
    {
        $arn = $this->invokeResolve(
            'arn:aws:s3:::sql-amazon-basico/backups',
            'paqsystems_partesatencion_dev',
        );

        $this->assertSame(
            'arn:aws:s3:::sql-amazon-basico/backups/paqsystems_partesatencion_dev.bak',
            $arn,
        );
    }

    public function testResolveDestinationArnAcceptsFullS3KeyFromSdkWithoutDuplicatingArn(): void
    {
        $fullKey = 'arn:aws:s3:::sql-amazon-basico/backups/paqsystems_partesatencion_dev.bak';

        $arn = $this->invokeResolve(
            'arn:aws:s3:::sql-amazon-basico/backups/',
            $fullKey,
        );

        $this->assertSame($fullKey, $arn);
    }

    private function invokeResolve(string $s3Arn, string $filePrefix): string
    {
        $gateway = new RdsSqlServerBackupGateway();
        $method = new ReflectionMethod(RdsSqlServerBackupGateway::class, 'resolveDestinationArn');
        $method->setAccessible(true);

        return $method->invoke($gateway, $s3Arn, $filePrefix);
    }
}
