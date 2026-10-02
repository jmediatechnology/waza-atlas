<?php

namespace App\Tests\Unit;

use App\Catalogue\KodokanCatalogue;
use App\Entity\MotionFormat;
use App\Motion\InvalidMotionException;
use App\Motion\MotionInspector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MotionInspectorTest extends TestCase
{
    public function testReadsDurationFromAC3dHeader(): void
    {
        // 512-byte header: parameter block 2, signature 0x50, frames 1..300 at 100 Hz.
        $header = pack('CCvvvvvgvvg', 2, 0x50, 39, 0, 1, 300, 10, -0.1, 7, 0, 100.0);
        $c3d = str_pad($header, 512, "\0");

        $info = (new MotionInspector())->inspect(MotionFormat::C3d, $c3d);

        self::assertEqualsWithDelta(3.0, $info['duration'], 0.001);
    }

    public function testRejectsAFileThatIsNotC3d(): void
    {
        $this->expectException(InvalidMotionException::class);
        (new MotionInspector())->inspect(MotionFormat::C3d, str_repeat('x', 600));
    }

    public function testPhasesMustBeInOrder(): void
    {
        $this->expectException(InvalidMotionException::class);
        (new MotionInspector())->phases([['t' => 2, 'name' => 'Kake'], ['t' => 1, 'name' => 'Kuzushi']]);
    }

    /** @return iterable<string, array{string}> */
    public static function bundledMotions(): iterable
    {
        foreach (glob(__DIR__.'/../../data/motions/*.json') ?: [] as $file) {
            yield basename($file, '.json') => [$file];
        }
    }

    #[DataProvider('bundledMotions')]
    public function testEveryBundledMotionIsValid(string $file): void
    {
        $info = (new MotionInspector())->inspect(MotionFormat::Keyframes, (string) file_get_contents($file));

        self::assertGreaterThanOrEqual(4.0, $info['duration']);
        self::assertSame(self::expectedPhases(basename($file, '.json')), array_column($info['phases'], 'name'));
        foreach ($info['phases'] as $phase) {
            self::assertNotSame('', $phase['text'], basename($file).': every phase needs a description');
        }
    }

    public function testEveryTechniqueHasAMotion(): void
    {
        $slugs = array_map(static fn (string $f) => basename($f, '.json'), glob(__DIR__.'/../../data/motions/*.json') ?: []);

        self::assertCount(100, $slugs);
        self::assertEqualsCanonicalizing(array_keys(self::categoryOf()), $slugs);
    }

    /**
     * Throws follow the five classic phases; ground techniques have four, ending in an escape attempt (pins)
     * or the submission (strangles and locks).
     *
     * @return list<string>
     */
    private static function expectedPhases(string $slug): array
    {
        return match (self::categoryOf()[$slug] ?? null) {
            'osaekomi' => ['Tsukuri', 'Kime', 'Osaekomi', 'Nogare'],
            'shime' => ['Tsukuri', 'Kime', 'Shime', 'Maitta'],
            'kansetsu' => ['Tsukuri', 'Kime', 'Kansetsu', 'Maitta'],
            default => ['Kumikata', 'Kuzushi', 'Tsukuri', 'Kake', 'Ukemi'],
        };
    }

    /** @return array<string, string> technique slug => category slug */
    private static function categoryOf(): array
    {
        $map = [];
        foreach (KodokanCatalogue::categories() as $category) {
            foreach ($category['techniques'] as $t) {
                $map[strtolower($t['name'])] = $category['slug'];
            }
        }

        return $map;
    }
}
