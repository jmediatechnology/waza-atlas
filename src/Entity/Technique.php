<?php

namespace App\Entity;

use App\Repository\TechniqueRepository;
use App\Search\SearchNormalizer;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TechniqueRepository::class)]
#[ORM\UniqueConstraint(columns: ['slug'])]
#[ORM\Index(columns: ['search_text'])]
class Technique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Name, English, kanji and category terms, normalised for LIKE search ("te waza" matches "Te-waza"). */
    #[ORM\Column(type: 'text')]
    private string $searchText = '';

    #[ORM\OneToOne(targetEntity: Motion::class, mappedBy: 'technique', cascade: ['remove'])]
    private ?Motion $motion = null;

    public function __construct(
        #[ORM\Column(length: 96)]
        private string $slug,
        #[ORM\Column(length: 96)]
        private string $name,
        #[ORM\Column(length: 32)]
        private string $kanji,
        #[ORM\Column(length: 160)]
        private string $english,
        #[ORM\ManyToOne(targetEntity: WazaCategory::class, inversedBy: 'techniques')]
        #[ORM\JoinColumn(nullable: false)]
        private WazaCategory $category,
        #[ORM\Column]
        private int $position,
        /** Gokyo no waza group 1-5, null when the throw is not part of the Gokyo. */
        #[ORM\Column(nullable: true)]
        private ?int $gokyoGroup = null,
        /** Prohibited in Kodokan shiai (e.g. Kani-basami, Do-jime). */
        #[ORM\Column]
        private bool $prohibited = false,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $notes = null,
    ) {
        $this->refreshSearchText();
    }

    public function update(string $name, string $kanji, string $english, WazaCategory $category, int $position, ?int $gokyoGroup, bool $prohibited, ?string $notes): void
    {
        $this->name = $name;
        $this->kanji = $kanji;
        $this->english = $english;
        $this->category = $category;
        $this->position = $position;
        $this->gokyoGroup = $gokyoGroup;
        $this->prohibited = $prohibited;
        $this->notes = $notes;
        $this->refreshSearchText();
    }

    private function refreshSearchText(): void
    {
        $family = $this->category->getFamily();
        $this->searchText = SearchNormalizer::normalize(implode(' | ', [
            $this->name, $this->english, $this->kanji,
            $this->category->getName(), $this->category->getEnglish(), $this->category->getKanji(),
            $family->label(), $family->english(),
        ]));
    }

    public function getId(): ?int { return $this->id; }
    public function getSlug(): string { return $this->slug; }
    public function getName(): string { return $this->name; }
    public function getKanji(): string { return $this->kanji; }
    public function getEnglish(): string { return $this->english; }
    public function getCategory(): WazaCategory { return $this->category; }
    public function getPosition(): int { return $this->position; }
    public function getGokyoGroup(): ?int { return $this->gokyoGroup; }
    public function isProhibited(): bool { return $this->prohibited; }
    public function getNotes(): ?string { return $this->notes; }
    public function getMotion(): ?Motion { return $this->motion; }
    public function setMotion(?Motion $motion): void { $this->motion = $motion; }
}
