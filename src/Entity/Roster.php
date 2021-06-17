<?php

namespace App\Entity;

use App\Repository\RosterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=RosterRepository::class)
 * @UniqueEntity(
 *     fields={"team", "year"},
 *     errorPath="year",
 *     message="This team already has a roster for that year."
 * )
 */
class Roster
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="rosters")
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity=Headshot::class, mappedBy="roster")
     */
    private $headshots;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $teamName;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $notes;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

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

    /**
     * @return Collection|Headshot[]
     */
    public function getHeadshots(): Collection
    {
        return $this->headshots;
    }

    public function addHeadshot(Headshot $headshot): self
    {
        if (!$this->headshots->contains($headshot)) {
            $this->headshots[] = $headshot;
            $headshot->setRoster($this);
        }

        return $this;
    }

    public function removeEntry(Headshot $headshot): self
    {
        if ($this->headshots->removeElement($headshot)) {
            // set the owning side to null (unless already changed)
            if ($headshot->getRoster() === $this) {
                $headshot->setRoster(null);
            }
        }

        return $this;
    }

    public function getTeamName(): ?string
    {
        return $this->teamName;
    }

    public function setTeamName(?string $teamName): self
    {
        $this->teamName = $teamName;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
