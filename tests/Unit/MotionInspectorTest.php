<?php

namespace App\Tests\Unit;

use App\Entity\MotionFormat;
use App\Motion\InvalidMotionException;
use App\Motion\MotionInspector;
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

    public function testTheBundledKibisuGaeshiMotionIsValid(): void
    {
        $info = (new MotionInspector())->inspect(MotionFormat::Keyframes, (string) file_get_contents(__DIR__.'/../../data/motions/kibisu-gaeshi.json'));

        self::assertSame(5.0, $info['duration']);
        self::assertCount(5, $info['phases']);
    }
}
