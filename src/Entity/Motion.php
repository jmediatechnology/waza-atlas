<?php

namespace App\Entity;

use App\Repository\MotionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A recorded (or hand-keyed) execution of a technique. The file itself lives on disk;
 * the row keeps what the API needs without opening it.
 */
#[ORM\Entity(repositoryClass: MotionRepository::class)]
class Motion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param list<array{t: float, name: string, en: string, text: string}> $phases
     */
    public function __construct(
        #[ORM\OneToOne(targetEntity: Technique::class, inversedBy: 'motion')]
        #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
        private Technique $technique,
        #[ORM\Column(enumType: MotionFormat::class)]
        private MotionFormat $format,
        #[ORM\Column(length: 255)]
        private string $filename,
        #[ORM\Column(length: 255)]
        private string $source,
        #[ORM\Column(nullable: true)]
        private ?float $durationSeconds = null,
        #[ORM\Column(type: 'json')]
        private array $phases = [],
    ) {
        $this->updatedAt = new \DateTimeImmutable();
        $technique->setMotion($this);
    }

    /** @param list<array{t: float, name: string, en: string, text: string}> $phases */
    public function replace(MotionFormat $format, string $filename, string $source, ?float $durationSeconds, array $phases): void
    {
        $this->format = $format;
        $this->filename = $filename;
        $this->source = $source;
        $this->durationSeconds = $durationSeconds;
        $this->phases = $phases;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTechnique(): Technique { return $this->technique; }
    public function getFormat(): MotionFormat { return $this->format; }
    public function getFilename(): string { return $this->filename; }
    public function getSource(): string { return $this->source; }
    public function getDurationSeconds(): ?float { return $this->durationSeconds; }
    /** @return list<array{t: float, name: string, en: string, text: string}> */
    public function getPhases(): array { return $this->phases; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
