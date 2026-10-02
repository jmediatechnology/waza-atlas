<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/** Rebuilds the test database and seeds the full catalogue before every test. */
abstract class DatabaseTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        (new Filesystem())->remove($container->getParameter('app.motion_dir'));

        $em = $container->get(EntityManagerInterface::class);
        $tool = new SchemaTool($em);
        $meta = $em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($meta);
        $tool->createSchema($meta);

        $tester = new CommandTester((new Application(static::$kernel))->find('app:seed'));
        $tester->execute([]);
        $tester->assertCommandIsSuccessful();
    }

    /** @return array<string, mixed> */
    protected function getJson(string $uri, int $expectedStatus = 200): array
    {
        $this->client->request('GET', $uri);
        self::assertResponseStatusCodeSame($expectedStatus);

        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }
}
