<?php
declare(strict_types=1);

namespace App\Tests;

use App\DependencyInjection;
use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class IntegrationTestCase extends TestCase
{
    protected Container $diContainer;

    protected function initializeContainer(): void
    {
        $di = new DependencyInjection();
        $this->diContainer = $di->createContainer();
    }

    protected function resetDatabase(): void
    {
        $this->importFixture(__DIR__ . '/fixtures/truncate.sql');
    }

    protected function importFixture(string $fixtureFile): void
    {
        $params = $this->getConnection()->getParams();
        $command = implode(' ', [
            "mysql",
            "-h{$params['host']}",
            "-P{$params['port']}",
            "-u{$params['user']}",
            "-p{$params['password']}",
            "-D{$params['dbname']}",
            "< {$fixtureFile}"
        ]);
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new RuntimeException('Could not import fixture using command' . "\n" . $command);
        }
    }

    private function getConnection(): Connection
    {
        return $this->diContainer
            ->get(EntityManagerInterface::class)
            ->getConnection();
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function insertRecord(string $table, array $data): int
    {
        return $this->getConnection()->insert($table, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    protected function updateRecord(string $table, array $data, array $where): int
    {
        return $this->getConnection()->update($table, $data, $where);
    }
}
