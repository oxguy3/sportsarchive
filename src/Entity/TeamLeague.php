<?php

namespace App\Entity;

use App\Repository\TeamLeagueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A TeamLeague defines an affiliation between a team and a league that it plays/played in.
 * 
 * @ORM\Entity(repositoryClass=TeamLeagueRepository::class)
 */
class TeamLeague
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class)
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class)
     */
    private $league;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex(pattern="/^[\d-]+$/", message="Years can only consist of numbers and dashes.")
     */
    private $firstSeason;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex(pattern="/^[\d-]+$/", message="Years can only consist of numbers and dashes.")
     */
    private $lastSeason;

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
