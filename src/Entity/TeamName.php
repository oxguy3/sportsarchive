<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableEntity;
use App\Repository\TeamNameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamNameRepository::class)]
class TeamName
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'names')]
    private Team $team;

    #[ORM\Column(type: 'string', length: 16)]
    #[Assert\Choice(['primary', 'alternate'])]
    private string $type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Language]
    private ?string $language = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^[\d-]+$/', message: 'Years can only consist of numbers and dashes.')]
    private ?string $firstSeason = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^[\d-]+$/', message: 'Years can only consist of numbers and dashes.')]
    private ?string $lastSeason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getFirstSeason(): ?string
    {
        return $this->firstSeason;
    }

    public function setFirstSeason(?string $firstSeason): self
    {
        $this->firstSeason = $firstSeason;

        return $this;
    }

    public function getLastSeason(): ?string
    {
        return $this->lastSeason;
    }

    public function setLastSeason(?string $lastSeason): self
    {
        $this->lastSeason = $lastSeason;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
