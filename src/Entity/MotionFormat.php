<?php

namespace App\Entity;

enum MotionFormat: string
{
    /** Hand-keyed poses in this app's own JSON format, played directly by the viewer. */
    case Keyframes = 'keyframes';
    /** Biovision hierarchy file, as exported by most mocap suits and pose-estimation tools. */
    case Bvh = 'bvh';
    /** C3D marker data, as used by PLAViMoP and lab motion-capture systems. */
    case C3d = 'c3d';

    public static function fromFilename(string $filename): ?self
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'json' => self::Keyframes,
            'bvh' => self::Bvh,
            'c3d' => self::C3d,
            default => null,
        };
    }

    public function contentType(): string
    {
        return match ($this) {
            self::Keyframes => 'application/json',
            self::Bvh => 'text/plain; charset=utf-8',
            self::C3d => 'application/octet-stream',
        };
    }
}
