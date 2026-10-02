<?php

namespace App\Entity;

use App\Repository\WazaCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Kodokan technique group, e.g. Te-waza (hand techniques) or Shime-waza (strangles).
 */
#[ORM\Entity(repositoryClass: WazaCategoryRepository::class)]
#[ORM\UniqueConstraint(columns: ['slug'])]
class WazaCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Collection<int, Technique> */
    #[ORM\OneToMany(targetEntity: Technique::class, mappedBy: 'category')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $techniques;

    public function __construct(
        #[ORM\Column(length: 64)]
        private string $slug,
        #[ORM\Column(length: 64)]
        private string $name,
        #[ORM\Column(length: 16)]
        private string $kanji,
        #[ORM\Column(length: 128)]
        private string $english,
        #[ORM\Column(enumType: Family::class)]
        private Family $family,
        #[ORM\Column]
        private int $position,
    ) {
        $this->techniques = new ArrayCollection();
    }

    public function update(string $name, string $kanji, string $english, Family $family, int $position): void
    {
        $this->name = $name;
        $this->kanji = $kanji;
        $this->english = $english;
        $this->family = $family;
        $this->position = $position;
    }

    public function getId(): ?int { return $this->id; }
    public function getSlug(): string { return $this->slug; }
    public function getName(): string { return $this->name; }
    public function getKanji(): string { return $this->kanji; }
    public function getEnglish(): string { return $this->english; }
    public function getFamily(): Family { return $this->family; }
    public function getPosition(): int { return $this->position; }

    /** @return Collection<int, Technique> */
    public function getTechniques(): Collection { return $this->techniques; }
}
