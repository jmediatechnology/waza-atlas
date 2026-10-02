<?php

namespace App\Tests\Api;

use App\Tests\DatabaseTestCase;

final class CatalogueApiTest extends DatabaseTestCase
{
    public function testCatalogueHasAllHundredKodokanTechniques(): void
    {
        $data = $this->getJson('/api/categories');

        $counts = [];
        foreach ($data['families'] as $family) {
            $counts[$family['slug']] = $family['techniques'];
        }
        self::assertSame(['nage' => 68, 'katame' => 32], $counts);
        self::assertCount(5, $data['families'][0]['categories']);
        self::assertSame('te', $data['families'][0]['categories'][0]['slug']);
        self::assertSame(16, $data['families'][0]['categories'][0]['techniques']);
    }

    public function testSearchingForACategoryNameReturnsTheWholeCategory(): void
    {
        foreach (['te waza', 'Te-waza', 'TEWAZA', 'hand techniques'] as $q) {
            $data = $this->getJson('/api/techniques?q='.rawurlencode($q));
            self::assertSame(16, $data['count'], "Query \"$q\"");
        }
    }

    public function testSearchMatchesRomajiEnglishAndKanji(): void
    {
        self::assertSame(['kibisu-gaeshi'], array_column($this->getJson('/api/techniques?q=kibisu')['techniques'], 'slug'));
        self::assertSame(['kibisu-gaeshi'], array_column($this->getJson('/api/techniques?q='.rawurlencode('踵'))['techniques'], 'slug'));
        self::assertContains('kibisu-gaeshi', array_column($this->getJson('/api/techniques?q=heel')['techniques'], 'slug'));
        self::assertSame(0, $this->getJson('/api/techniques?q=zzzz')['count']);
    }

    public function testFiltersCombine(): void
    {
        self::assertSame(12, $this->getJson('/api/techniques?family=katame&category=shime')['count']);
        self::assertSame(0, $this->getJson('/api/techniques?family=nage&category=shime')['count']);

        $withMotion = $this->getJson('/api/techniques?motion=1');
        self::assertSame(['kibisu-gaeshi'], array_column($withMotion['techniques'], 'slug'));
    }

    public function testUnknownFamilyIsABadRequest(): void
    {
        $data = $this->getJson('/api/techniques?family=tachi', 400);
        self::assertStringContainsString('nage', $data['error']);
    }

    public function testTechniqueDetailIncludesCategoryAndMotion(): void
    {
        $t = $this->getJson('/api/techniques/kibisu-gaeshi');

        self::assertSame('踵返', $t['kanji']);
        self::assertSame('te', $t['category']['slug']);
        self::assertSame('nage', $t['category']['family']['slug']);
        self::assertSame('keyframes', $t['motion']['format']);
        self::assertSame(['Kumikata', 'Kuzushi', 'Tsukuri', 'Kake', 'Ukemi'], array_column($t['motion']['phases'], 'name'));
        self::assertSame('/api/techniques/kibisu-gaeshi/motion', $t['motion']['dataUrl']);
    }

    public function testGokyoAndProhibitedFlags(): void
    {
        self::assertSame(1, $this->getJson('/api/techniques/o-soto-gari')['gokyo']);
        self::assertNull($this->getJson('/api/techniques/o-soto-gari')['motion']);
        self::assertTrue($this->getJson('/api/techniques/kani-basami')['prohibited']);
    }

    public function testUnknownTechniqueIsAJson404(): void
    {
        $data = $this->getJson('/api/techniques/flying-armbar', 404);
        self::assertSame(404, $data['status']);
    }

    public function testSeedingTwiceDoesNotDuplicate(): void
    {
        $tester = new \Symfony\Component\Console\Tester\CommandTester(
            (new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel))->find('app:seed'));
        $tester->execute([]);
        self::assertStringContainsString('0 created, 100 updated', $tester->getDisplay());
        self::assertSame(100, $this->getJson('/api/techniques')['count']);
    }

    public function testAtlasPageRenders(): void
    {
        $crawler = $this->client->request('GET', '/waza/kibisu-gaeshi');
        self::assertResponseIsSuccessful();
        self::assertSame('kibisu-gaeshi', $crawler->filter('body')->attr('data-initial-slug'));
        self::assertCount(1, $crawler->filter('canvas#cv'));
    }
}
