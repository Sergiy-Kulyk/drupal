<?php
declare(strict_types=1);

namespace App;

use App\Catalog\Handler\ProductListHandler;
use App\Catalog\Repository\DoctrineProductRepository;
use App\Catalog\Repository\ProductRepository;
use App\Catalog\SearchAnalytics\FilesystemSearchAnalytics;
use App\Catalog\SearchAnalytics\SearchAnalytics;
use App\Clock\Clock;
use App\Clock\DateTimeClock;
use DI;
use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;

final class DependencyInjection
{
    public function createContainer(): Container
    {
        $di = new Container();
        $di->set(Connection::class, DI\factory(function () {
            return new Connection(
                [
                    'dbname' => 'pluralsight',
                    'user' => 'pluralsight',
                    'password' => 'pluralsight',
                    'host' => 'mysql.phpunit.local',
                    'port' => '3306',
                ],
                new Driver()
            );
        }));
        $di->set(EntityManagerInterface::class, DI\factory(function (Container $di): EntityManager {
            $config = Setup::createAnnotationMetadataConfiguration(
                [__DIR__ . '/src/Entity'],
                false,
                null,
                null,
                false
            );
            return EntityManager::create(
                $di->get(Connection::class),
                $config
            );
        }));
        $di->set(ProductRepository::class, DI\autowire(DoctrineProductRepository::class));
        $di->set(SearchAnalytics::class, DI\autowire(FilesystemSearchAnalytics::class));
        $di->set(Clock::class, DI\autowire(DateTimeClock::class));
        $di->set(ProductListHandler::class, DI\autowire(ProductListHandler::class));

        return $di;
    }
}
