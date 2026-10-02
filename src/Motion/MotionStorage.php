<?php

namespace App\Motion;

use App\Entity\Motion;
use App\Entity\MotionFormat;
use App\Entity\Technique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Stores one motion per technique under var/motions/<slug>.<ext> and keeps the Motion row in sync.
 */
final class MotionStorage
{
    private Filesystem $fs;

    public function __construct(
        private readonly string $motionDir,
        private readonly MotionInspector $inspector,
        private readonly EntityManagerInterface $em,
    ) {
        $this->fs = new Filesystem();
    }

    /**
     * @param list<array{t: float, name: string, en: string, text: string}>|null $phases overrides phases found in the file
     */
    public function store(Technique $technique, MotionFormat $format, string $contents, ?string $source = null, ?array $phases = null): Motion
    {
        $info = $this->inspector->inspect($format, $contents);
        $source = trim((string) ($source ?: $info['source'] ?: 'Uploaded '.strtoupper($format->value).' file'));
        $phases ??= $info['phases'];

        $filename = $technique->getSlug().'.'.match ($format) {
            MotionFormat::Keyframes => 'json',
            MotionFormat::Bvh => 'bvh',
            MotionFormat::C3d => 'c3d',
        };

        $old = $technique->getMotion();
        $this->fs->mkdir($this->motionDir);
        $this->fs->dumpFile($this->motionDir.'/'.$filename, $contents);
        if (null !== $old && $old->getFilename() !== $filename) {
            $this->fs->remove($this->motionDir.'/'.$old->getFilename());
        }

        if (null === $old) {
            $motion = new Motion($technique, $format, $filename, mb_substr($source, 0, 255), $info['duration'], $phases);
            $this->em->persist($motion);
        } else {
            $old->replace($format, $filename, mb_substr($source, 0, 255), $info['duration'], $phases);
            $motion = $old;
        }
        $this->em->flush();

        return $motion;
    }

    public function path(Motion $motion): string
    {
        return $this->motionDir.'/'.$motion->getFilename();
    }

    public function remove(Technique $technique): void
    {
        $motion = $technique->getMotion();
        if (null === $motion) {
            return;
        }
        $this->fs->remove($this->path($motion));
        $technique->setMotion(null);
        $this->em->remove($motion);
        $this->em->flush();
    }
}
