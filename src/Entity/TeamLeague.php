<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableEntity;
use App\Repository\TeamLeagueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A TeamLeague defines an affiliation between a team and a league that it plays/played in.
 */
#[ORM\Entity(repositoryClass: TeamLeagueRepository::class)]
class TeamLeague implements \Stringable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'teamLeagues')]
    private Team $team;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'memberTeamLeagues')]
    private Team $league;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^[\d-]+$/', message: 'Years can only consist of numbers and dashes.')]
    private ?string $firstSeason = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^[\d-]+$/', message: 'Years can only consist of numbers and dashes.')]
    private ?string $lastSeason = null;

    #[\Override]
    public function __toString(): string
    {
        return $this->team->getName().'@'.$this->league->getName().' ('.$this->firstSeason.'–'.$this->lastSeason.')';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLeague(): ?Team
    {
        return $this->league;
    }

    public function setLeague(?Team $league): self
    {
        $this->league = $league;

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
}
