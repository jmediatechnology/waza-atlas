<?php

namespace App\Tests\Unit;

use App\Search\SearchNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchNormalizerTest extends TestCase
{
    /** @return iterable<array{string, string}> */
    public static function cases(): iterable
    {
        yield ['Te Waza', 'tewaza'];
        yield ['te-waza', 'tewaza'];
        yield ['O-soto-gari', 'osotogari'];
        yield ['Ōsoto gari', 'osotogari'];
        yield ['踵返', '踵返'];
    }

    #[DataProvider('cases')]
    public function testFoldsSpellingVariants(string $input, string $expected): void
    {
        self::assertSame($expected, SearchNormalizer::normalize($input));
    }
}
