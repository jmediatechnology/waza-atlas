<?php

namespace App\Motion;

use App\Entity\MotionFormat;

/**
 * Checks that an uploaded file really is the format it claims to be and reads its duration.
 * It does not convert anything: the viewer decides what it can play.
 */
final class MotionInspector
{
    public const MAX_BYTES = 20 * 1024 * 1024;

    /**
     * @return array{duration: ?float, phases: list<array{t: float, name: string, en: string, text: string}>, source: ?string}
     */
    public function inspect(MotionFormat $format, string $contents): array
    {
        if ('' === $contents) {
            throw new InvalidMotionException('The file is empty.');
        }
        if (\strlen($contents) > self::MAX_BYTES) {
            throw new InvalidMotionException(sprintf('The file is larger than %d MB.', self::MAX_BYTES / 1024 / 1024));
        }

        return match ($format) {
            MotionFormat::Keyframes => $this->inspectKeyframes($contents),
            MotionFormat::Bvh => ['duration' => $this->bvhDuration($contents), 'phases' => [], 'source' => null],
            MotionFormat::C3d => ['duration' => $this->c3dDuration($contents), 'phases' => [], 'source' => null],
        };
    }

    /**
     * @return array{duration: float, phases: list<array{t: float, name: string, en: string, text: string}>, source: ?string}
     */
    private function inspectKeyframes(string $contents): array
    {
        try {
            $data = json_decode($contents, true, 64, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidMotionException('The keyframe file is not valid JSON: '.$e->getMessage());
        }
        if (!\is_array($data) || ($data['format'] ?? null) !== 'keyframes') {
            throw new InvalidMotionException('A keyframe file needs "format": "keyframes" at the top level.');
        }
        $duration = $data['duration'] ?? null;
        if (!\is_int($duration) && !\is_float($duration) || $duration <= 0 || $duration > 120) {
            throw new InvalidMotionException('"duration" must be a number of seconds between 0 and 120.');
        }
        foreach (['tori', 'uke'] as $role) {
            $track = $data['tracks'][$role] ?? null;
            if (!\is_array($track) || [] === $track || !array_is_list($track)) {
                throw new InvalidMotionException(sprintf('"tracks.%s" must be a non-empty list of [time, pose] keys.', $role));
            }
            $last = -1.0;
            foreach ($track as $i => $key) {
                if (!\is_array($key) || 2 !== \count($key) || !is_numeric($key[0]) || !\is_array($key[1])) {
                    throw new InvalidMotionException(sprintf('Key %d of "tracks.%s" must look like [1.25, {"bend": 20}].', $i, $role));
                }
                if ((float) $key[0] < $last) {
                    throw new InvalidMotionException(sprintf('Keys in "tracks.%s" must be in time order (key %d goes back in time).', $role, $i));
                }
                $last = (float) $key[0];
                foreach ($key[1] as $joint => $value) {
                    if (!\is_string($joint) || !is_numeric($value)) {
                        throw new InvalidMotionException(sprintf('Key %d of "tracks.%s" has a non-numeric value for "%s".', $i, $role, $joint));
                    }
                }
            }
        }

        return [
            'duration' => (float) $duration,
            'phases' => $this->phases($data['phases'] ?? [], (float) $duration),
            'source' => \is_string($data['source'] ?? null) ? $data['source'] : null,
        ];
    }

    /**
     * @return list<array{t: float, name: string, en: string, text: string}>
     */
    public function phases(mixed $phases, ?float $duration = null): array
    {
        if (!\is_array($phases) || !array_is_list($phases)) {
            throw new InvalidMotionException('"phases" must be a list.');
        }
        $out = [];
        $last = -1.0;
        foreach ($phases as $i => $p) {
            if (!\is_array($p) || !is_numeric($p['t'] ?? null) || !\is_string($p['name'] ?? null) || '' === trim($p['name'])) {
                throw new InvalidMotionException(sprintf('Phase %d needs a numeric "t" and a "name".', $i));
            }
            $t = (float) $p['t'];
            if ($t < $last || $t < 0 || (null !== $duration && $t > $duration)) {
                throw new InvalidMotionException(sprintf('Phase %d starts at %.2f s, which is out of order or past the end of the motion.', $i, $t));
            }
            $last = $t;
            $out[] = [
                't' => $t,
                'name' => mb_substr(trim($p['name']), 0, 40),
                'en' => mb_substr(trim((string) ($p['en'] ?? '')), 0, 60),
                'text' => mb_substr(trim((string) ($p['text'] ?? '')), 0, 600),
            ];
        }

        return $out;
    }

    private function bvhDuration(string $contents): ?float
    {
        if (!str_starts_with(ltrim($contents), 'HIERARCHY') || !str_contains($contents, 'MOTION')) {
            throw new InvalidMotionException('This does not look like a BVH file: it must start with HIERARCHY and contain a MOTION section.');
        }
        if (preg_match('/Frames:\s*(\d+)/', $contents, $f) && preg_match('/Frame Time:\s*([\d.eE+-]+)/', $contents, $ft)) {
            return round((int) $f[1] * (float) $ft[1], 3);
        }

        return null;
    }

    private function c3dDuration(string $contents): ?float
    {
        // C3D header: byte 2 is always 0x50. Frame numbers are 16-bit words 4 and 5, frame rate is a float at bytes 20-23.
        if (\strlen($contents) < 512 || 0x50 !== \ord($contents[1])) {
            throw new InvalidMotionException('This does not look like a C3D file: the header signature is missing.');
        }
        $first = unpack('v', $contents, 6)[1];
        $last = unpack('v', $contents, 8)[1];
        $rate = unpack('g', $contents, 20)[1];
        if ($rate > 0 && $rate < 10000 && $last >= $first) {
            return round(($last - $first + 1) / $rate, 3);
        }

        return null;
    }
}
