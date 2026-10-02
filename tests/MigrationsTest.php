<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/** Guards against an entity change shipping without a migration. */
final class MigrationsTest extends KernelTestCase
{
    public function testMigrationsBuildTheSchemaTheEntitiesExpect(): void
    {
        $kernel = self::bootKernel();
        (new SchemaTool($em = self::getContainer()->get(EntityManagerInterface::class)))->dropDatabase();

        // ApplicationTester runs commands through the console events, like bin/console does.
        // The migrations bundle relies on those to hide its own version table from schema:validate.
        $app = new Application($kernel);
        $app->setAutoExit(false);
        $console = new ApplicationTester($app);

        $console->run(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true], ['interactive' => false]);
        $console->assertCommandIsSuccessful();

        $console->run(['command' => 'doctrine:schema:validate']);
        self::assertSame(0, $console->getStatusCode(), $console->getDisplay());
    }
}
