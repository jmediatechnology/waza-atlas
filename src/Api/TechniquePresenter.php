<?php

namespace App\Api;

use App\Entity\Motion;
use App\Entity\Technique;
use App\Entity\WazaCategory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Turns entities into the JSON shapes the API promises. Kept by hand so the contract is explicit.
 */
final class TechniquePresenter
{
    public function __construct(private readonly UrlGeneratorInterface $urls)
    {
    }

    /** @return array<string, mixed> */
    public function summary(Technique $t): array
    {
        return [
            'slug' => $t->getSlug(),
            'name' => $t->getName(),
            'kanji' => $t->getKanji(),
            'english' => $t->getEnglish(),
            'family' => $t->getCategory()->getFamily()->value,
            'category' => $t->getCategory()->getSlug(),
            'gokyo' => $t->getGokyoGroup(),
            'prohibited' => $t->isProhibited(),
            'hasMotion' => null !== $t->getMotion(),
            'url' => $this->urls->generate('api_technique_show', ['slug' => $t->getSlug()]),
        ];
    }

    /** @return array<string, mixed> */
    public function detail(Technique $t): array
    {
        $c = $t->getCategory();

        return [
            ...$this->summary($t),
            'category' => $this->category($c),
            'notes' => $t->getNotes(),
            'motion' => null === $t->getMotion() ? null : $this->motion($t->getMotion()),
        ];
    }

    /** @return array<string, mixed> */
    public function category(WazaCategory $c, ?int $count = null, ?int $withMotion = null): array
    {
        $f = $c->getFamily();

        return array_filter([
            'slug' => $c->getSlug(),
            'name' => $c->getName(),
            'kanji' => $c->getKanji(),
            'english' => $c->getEnglish(),
            'family' => ['slug' => $f->value, 'name' => $f->label(), 'kanji' => $f->kanji(), 'english' => $f->english()],
            'techniques' => $count,
            'withMotion' => $withMotion,
        ], static fn ($v) => null !== $v);
    }

    /** @return array<string, mixed> */
    public function motion(Motion $m): array
    {
        return [
            'format' => $m->getFormat()->value,
            'source' => $m->getSource(),
            'duration' => $m->getDurationSeconds(),
            'phases' => $m->getPhases(),
            'updatedAt' => $m->getUpdatedAt()->format(\DATE_ATOM),
            'dataUrl' => $this->urls->generate('api_motion_show', ['slug' => $m->getTechnique()->getSlug()]),
        ];
    }
}
